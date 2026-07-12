<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    /**
     * Display assessments filtered by the current user's role and grade scope.
     */
    public function index()
    {
        $user = auth()->user();

    if ($user->role === 'teacher') {
        // Teachers see assessments only for their assigned grades.
        // Teachers see only the grades they are assigned to
        $grades = is_array($user->assigned_grades) ? $user->assigned_grades : json_decode($user->assigned_grades, true) ?? [];
        $assessments = Assessment::whereIn('grade_level', $grades)->orderBy('scheduled_at', 'asc')->get();
    } else {
        // Students see assessments only for their grade/section scope.
        // Students see only THEIR specific grade level
        // Assuming students have a 'grade_level' column in the users table
        $studentSection = $user->section;
        $assessments = Assessment::where('grade_level', $user->grade_level)
            ->when($studentSection, function ($query) use ($studentSection) {
                $query->where(function ($q) use ($studentSection) {
                    $q->whereNull('section')
                      ->orWhere('section', $studentSection)
                      ->orWhere('description', 'like', '%Section: ' . $studentSection . '%');
                });
            }, function ($query) {
                $query->whereNull('section');
            })
            ->orderBy('scheduled_at', 'asc')
            ->get();
    }

        return view('dashboard', compact('assessments'));
    }

    /**
     * Delete an assessment if the user is authorized to do so.
     */
    public function destroy(Assessment $assessment)
    {
        // Check if the logged-in user is an admin OR the owner of the assessment
        if (auth()->user()->role === 'admin' || $assessment->user_id === auth()->id()) {
            $assessment->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    /**
     * Update an assessment if the user owns it or is an administrator.
     */
    public function update(Request $request, Assessment $assessment)
    {
        $user = auth()->user();
        if (!$user || ($user->role !== 'admin' && $assessment->user_id !== $user->id)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'due_date' => 'required|date',
            'due_time' => 'required',
            'grade_level' => 'required|integer',
            'subject' => 'required|string',
            'section' => 'nullable|string|max:255',
        ]);

        $type = $validated['type'] === 'Alternative Assessment'
            ? 'Alternative Assessment (AA)'
            : $validated['type'];
        $grade = (int) $validated['grade_level'];
        $subjectName = $validated['subject'];
        $sectionInput = filled($validated['section'] ?? null) ? trim((string) $validated['section']) : null;
        $sectionList = $sectionInput
            ? array_values(array_filter(array_map('trim', explode(',', $sectionInput))))
            : [];

        if ($user->role === 'teacher') {
            $allowedGrades = is_array($user->assigned_grades)
                ? $user->assigned_grades
                : (json_decode($user->assigned_grades, true) ?? []);
            $allowedGrades = array_map('intval', $allowedGrades);
            $allowedSubjects = is_array($user->assigned_subjects)
                ? $user->assigned_subjects
                : (json_decode($user->assigned_subjects, true) ?? []);

            if (!in_array($grade, $allowedGrades, true) || !in_array($subjectName, $allowedSubjects, true)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized: You are not assigned to this Grade or Subject.'], 403);
            }

            $teacherSections = is_array($user->section)
                ? $user->section
                : array_values(array_filter(array_map('trim', explode(',', (string) $user->section))));
            if (!empty($teacherSections) && !empty(array_diff($sectionList, $teacherSections))) {
                return response()->json(['success' => false, 'message' => 'Unauthorized: One or more sections are not assigned to you.'], 403);
            }
        }

        $scheduledAt = Carbon::parse($validated['due_date'] . ' ' . $validated['due_time']);
        $dayStart = $scheduledAt->copy()->startOfDay();
        $dayEnd = $scheduledAt->copy()->endOfDay();

        $sectionChecks = !empty($sectionList) ? $sectionList : [null];
        foreach ($sectionChecks as $sectionCheck) {
            $dailyQuery = Assessment::where('id', '!=', $assessment->id)
                ->where(function ($q) use ($dayStart, $dayEnd) {
                    $q->whereBetween('scheduled_at', [$dayStart, $dayEnd])
                      ->orWhereBetween('due_date', [$dayStart, $dayEnd]);
                })
                ->where('grade_level', $grade);

            if ($sectionCheck) {
                $dailyQuery->where(function ($q) use ($sectionCheck) {
                    $q->where('section', $sectionCheck)
                        ->orWhere('section', 'like', '%' . $sectionCheck . '%')
                        ->orWhere('description', 'like', '%Section: ' . $sectionCheck . '%');
                });
            } else {
                $dailyQuery->whereNull('section');
            }

            if ($dailyQuery->count() >= 2) {
                $sectionLabel = $sectionCheck ?: 'All Sections';
                return response()->json(['success' => false, 'message' => "Conflict! Grade {$grade} ({$sectionLabel}) already has 2 assessments on this day."], 422);
            }
        }

        if (in_array($type, ['Formative Assessment', 'Alternative Assessment (AA)', 'Alternative Assessment'], true)) {
            $weekStart = $scheduledAt->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $weekEnd = $weekStart->copy()->addDays(4)->endOfDay();
            $weeklyCount = Assessment::where('id', '!=', $assessment->id)
                ->whereBetween('scheduled_at', [$weekStart, $weekEnd])
                ->where('grade_level', $grade)
                ->whereIn('type', ['Formative Assessment', 'Alternative Assessment (AA)', 'Alternative Assessment'])
                ->count();

            if ($weeklyCount >= 5) {
                return response()->json(['success' => false, 'message' => "Conflict! Grade {$grade} already has 5 assessments this week."], 422);
            }
        }

        $section = $sectionInput ? implode(', ', $sectionList) : null;
        $assessment->fill([
            'title' => $validated['title'],
            'type' => $type,
            'scheduled_at' => $scheduledAt,
            'due_date' => $scheduledAt,
            'grade_level' => $grade,
            'section' => $section,
            'subject_id' => null,
            'description' => 'Subject: ' . $subjectName . ($section ? " | Section: {$section}" : ''),
            'confirmation_status' => 'scheduled',
            'confirmation_requested_at' => null,
            'conducted_at' => null,
        ])->save();

        return response()->json(['success' => true]);
    }

    /**
     * Validate and create (or reschedule) an assessment with policy checks.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Role-scoped assignment lists (teachers/admins use these for authorization).
        $allowedGrades = is_array($user->assigned_grades) ? $user->assigned_grades : json_decode($user->assigned_grades, true) ?? [];
        $allowedSubjects = is_array($user->assigned_subjects) ? $user->assigned_subjects : json_decode($user->assigned_subjects, true) ?? [];
        $gradeSubjectMap = [
            7 => [
                'Integrated Science 1',
                'Mathematics 1',
                'English 1',
                'Filipino 1',
                'Social Science 1',
                'Physical Education 1',
                'Health 1',
                'Music 1',
                'Values Education 1',
                'AdTech 1',
                'Computer Science 1',
            ],
            8 => [
                'Biology 1',
                'Chemistry 1',
                'Physics 1',
                'Mathematics 2',
                'Mathematics 3',
                'Earth Science',
                'English 2',
                'Filipino 2',
                'Social Science 2',
                'Physical Education 2',
                'Health 2',
                'Music 2',
                'Values Education 2',
                'AdTech 2',
                'Computer Science 2',
            ],
            9 => [
                'Biology 1',
                'Chemistry 1',
                'Physics 1',
                'Mathematics 3',
                'English 3',
                'Filipino 3',
                'Social Science 3',
                'Physical Education 3',
                'Health 3',
                'Music 3',
                'Values Education 3',
                'Statistics 1',
                'Computer Science 3',
            ],
            10 => [
                'Biology 2',
                'Chemistry 2',
                'Physics 2',
                'Mathematics 4',
                'English 4',
                'Filipino 4',
                'Social Science 4',
                'Physical Education 4',
                'Health 4',
                'Music 4',
                'Values Education 4',
                'STEM Research 1',
                'Computer Science 4',
                'Philippine Biodiversity (AYP)',
                'Microbiology and Basic Molecular Techniques',
                'Data Science',
                'Field Sampling Techniques',
                'Intellectual Property Rights',
            ],
            11 => [
                'Biology 3 Class 1',
                'Biology 3 Class 2',
                'Chemistry 3 Class 1',
                'Chemistry 3 Class 2',
                'Physics 3 Class 1',
                'Physics 3 Class 2',
                'Mathematics 5',
                'English 5',
                'Filipino 5',
                'Social Science 5',
                'STEM Research 2',
                'Computer Science 5',
                'Engineering',
                'Design and Make Technology',
                'Agriculture',
                'Computer Science 5 Elective',
                'Biology 3 Elective',
                'Chemistry 3 Elective Class 1',
                'Chemistry 3 Elective Class 2',
                'Physics 3 Elective',
            ],
            12 => [
                'Biology 4 Class 1',
                'Biology 4 Class 2',
                'Chemistry 4 Class 1',
                'Chemistry 4 Class 2',
                'Physics 4 Class 1',
                'Physics 4 Class 2',
                'Mathematics 6',
                'English 6',
                'Filipino 6',
                'Social Science 6',
                'STEM Research 3',
                'Computer Science 5',
                'Engineering',
                'Design and Make Technology',
                'Agriculture',
                'Computer Science 5 Elective',
                'Biology 4 Elective',
                'Chemistry 4 Elective Class 1',
                'Chemistry 4 Elective Class 2',
                'Physics 4 Elective',
            ],
        ];
        // 1. Validate input
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'due_date' => 'required|date',
            'due_time' => 'required',
            'grade_level' => 'required|integer',
            'subject' => 'required|string',
            'section' => 'nullable|string|max:255',
        ]);

        $date = $request->due_date;
        $type = $request->type === 'Alternative Assessment' ? 'Alternative Assessment (AA)' : $request->type;
        $grade = (int) $request->grade_level;
        $sectionInput = $request->filled('section') ? trim((string) $request->section) : null;
        $sectionList = $sectionInput
            ? array_values(array_filter(array_map('trim', explode(',', $sectionInput))))
            : [];
        $subjectName = $request->subject;

        $rescheduleId = $request->input('reschedule_assessment_id');
        $rescheduleAssessment = null;
        if ($rescheduleId) {
            // Reschedule: lock to the original assessment's data and owner.
            $rescheduleAssessment = Assessment::where('id', $rescheduleId)
                ->where('user_id', $user->id)
                ->firstOrFail();
            $type = $rescheduleAssessment->type;
            $grade = (int) $rescheduleAssessment->grade_level;
            $sectionInput = $rescheduleAssessment->section;
            $sectionList = $sectionInput
                ? array_values(array_filter(array_map('trim', explode(',', (string) $sectionInput))))
                : [];
            $subjectName = $rescheduleAssessment->subject ? $rescheduleAssessment->subject->name : $subjectName;
        }

        $isLongTest1 = $type === 'Long Test 1 (Midterms)';
        $isLongTest2 = in_array($type, ['Long Test 2 (Quarterly Exam)', 'Long Test'], true);
        $isWeeklyCapped = in_array($type, ['Formative Assessment', 'Alternative Assessment (AA)', 'Alternative Assessment'], true);

        if ($user->role === 'admin') {
            // Admins may only schedule Long Test 2.
            if (!$isLongTest2) {
                return back()->with('error', 'Admins can only schedule Long Test 2 (Quarterly Exam).');
            }
        } elseif (!in_array($grade, $allowedGrades) || !in_array($subjectName, $allowedSubjects)) {
            // Teachers must be assigned to both the grade and subject.
            return back()->with('error', "Unauthorized: You are not assigned to this Grade or Subject.");
        }

        $requestedGrade = $grade;

        $gradeSectionMap = [
            7 => ['Opal', 'Turquoise', 'Aquamarine', 'Sapphire'],
            8 => ['Anthurium', 'Carnation', 'Daffodil', 'Sunflower'],
            9 => ['Calcium', 'Lithium', 'Barium', 'Sodium'],
            10 => ['Graviton', 'Proton', 'Neutron', 'Electron'],
            11 => ['Mars', 'Mercury', 'Venus'],
            12 => ['Orosa', 'Del Mundo', 'Zara'],
        ];

        $subjectMeta = Subject::where('name', $subjectName)
            ->where('grade_level_start', '<=', $requestedGrade)
            ->where('grade_level_end', '>=', $requestedGrade)
            ->first();

        if ($isLongTest2 && $user->role !== 'admin') {
            // Long Test 2 is admin-only.
            return back()->with('error', 'Only administrators can schedule Long Test 2 (Quarterly Exam).');
        }

        if ($isLongTest1 && $user->role === 'teacher') {
            // Long Test 1 is restricted to specific subjects; then capped per month.
            $isAllowedLongTest1 =
                stripos($subjectName, 'Computer Science') !== false ||
                stripos($subjectName, 'Integrated Science') !== false ||
                stripos($subjectName, 'Mathematics') !== false;

            if (!$isAllowedLongTest1 && $subjectMeta && $subjectMeta->type === 'elective') {
                $isAllowedLongTest1 =
                    stripos($subjectName, 'Biology') !== false ||
                    stripos($subjectName, 'Chemistry') !== false ||
                    stripos($subjectName, 'Physics') !== false;
            }

            if (!$isAllowedLongTest1) {
                return back()->with('error', 'Long Test 1 (Midterms) is only allowed for Computer Science, Integrated Science, Mathematics, and Bio/Chem/Physics electives.');
            }

            // Monthly cap check is enforced under a DB lock below.
        }

        // Section rules: electives/science_core can be exempt from section assignment.
        $isScienceCoreExempt = $subjectMeta && $subjectMeta->type === 'science_core' && in_array($requestedGrade, [11, 12], true);
        $isElectiveExempt = $subjectMeta && $subjectMeta->type === 'elective' && $requestedGrade >= 10 && $requestedGrade <= 12;
        $sectionExempt = $isScienceCoreExempt || $isElectiveExempt;

        if ($sectionExempt) {
            $sectionInput = null;
            $sectionList = [];
        } else {
            if (empty($sectionList)) {
                return back()->with('error', "Please select a section for this subject.");
            }
            if (isset($gradeSectionMap[$requestedGrade])) {
                $invalidSections = array_diff($sectionList, $gradeSectionMap[$requestedGrade]);
                if (!empty($invalidSections)) {
                    return back()->with('error', "Invalid section for Grade {$requestedGrade}.");
                }
            }

            if ($user->role === 'teacher') {
                // Teachers can only schedule for their assigned sections.
                $teacherSections = is_array($user->section)
                    ? $user->section
                    : array_values(array_filter(array_map('trim', explode(',', (string) $user->section))));
                if (!empty($teacherSections)) {
                    $invalidTeacherSections = array_diff($sectionList, $teacherSections);
                    if (!empty($invalidTeacherSections)) {
                        return back()->with('error', "Unauthorized: One or more sections are not assigned to you.");
                    }
                }
            }

        }
        $section = $sectionInput ? implode(', ', $sectionList) : null;

        // Lock keys to prevent race conditions when multiple teachers schedule at once.
        $lockKeys = [];
        $dayStart = Carbon::parse($date)->startOfDay();
        $dayEnd = Carbon::parse($date)->endOfDay();
        $sectionChecks = !empty($sectionList) ? $sectionList : [null];
        foreach ($sectionChecks as $sectionCheck) {
            $sectionKey = $sectionCheck ? preg_replace('/\s+/', '_', (string) $sectionCheck) : 'all';
            $lockKeys[] = "awas:grade:{$grade}:day:" . $dayStart->toDateString() . ":section:{$sectionKey}";
        }
        $weekStart = null;
        $weekEnd = null;
        if ($isWeeklyCapped) {
            $weekStart = Carbon::parse($date)->startOfWeek(Carbon::MONDAY)->startOfDay();
            $weekEnd = $weekStart->copy()->addDays(4)->endOfDay();
            $lockKeys[] = "awas:grade:{$grade}:week:" . $weekStart->toDateString();
        }
        $monthStart = null;
        $monthEnd = null;
        if ($isLongTest1 && $user->role === 'teacher') {
            $monthStart = Carbon::parse($date)->startOfMonth()->startOfDay();
            $monthEnd = Carbon::parse($date)->endOfMonth()->endOfDay();
            $lockKeys[] = "awas:lt1:user:{$user->id}:grade:{$grade}:month:" . $monthStart->format('Y-m');
        }

        $locks = [];
        $supportsAdvisoryLocks = in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
        if ($supportsAdvisoryLocks) {
            foreach ($lockKeys as $key) {
                $row = DB::selectOne('SELECT GET_LOCK(?, ?) as acquired', [$key, 5]);
                if (!$row || (int) $row->acquired !== 1) {
                    foreach ($locks as $held) {
                        DB::select('SELECT RELEASE_LOCK(?)', [$held]);
                    }
                    return back()->with('error', 'System is busy. Please retry in a few seconds.');
                }
                $locks[] = $key;
            }
        }

        try {
            return DB::transaction(function () use (
                $isWeeklyCapped,
                $isLongTest1,
                $user,
                $grade,
                $dayStart,
                $dayEnd,
                $sectionChecks,
                $weekStart,
                $weekEnd,
                $monthStart,
                $monthEnd,
                $rescheduleAssessment,
                $date,
                $request,
                $type,
                $section,
                $subjectName
            ) {
                // Daily cap: max 2 assessments per grade + section per day.
                foreach ($sectionChecks as $sectionCheck) {
                    $dailyQuery = Assessment::where(function ($q) use ($dayStart, $dayEnd) {
                        $q->whereBetween('scheduled_at', [$dayStart, $dayEnd])
                          ->orWhereBetween('due_date', [$dayStart, $dayEnd]);
                    })->where('grade_level', $grade);

                    if ($sectionCheck) {
                        $dailyQuery->where(function ($q) use ($sectionCheck) {
                            $q->where('section', $sectionCheck)
                                ->orWhere('section', 'like', '%' . $sectionCheck . '%')
                                ->orWhere('description', 'like', '%Section: ' . $sectionCheck . '%');
                        });
                    } else {
                        $dailyQuery->whereNull('section');
                    }

                    if ($rescheduleAssessment) {
                        $dailyQuery->where('id', '!=', $rescheduleAssessment->id);
                    }

                    if ($dailyQuery->count() >= 2) {
                        $sectionLabel = $sectionCheck ?: 'All Sections';
                        return back()->with('error', "Conflict! Grade $grade ({$sectionLabel}) already has 2 assessments on this day.");
                    }
                }

                // Weekly cap: max 5 FA/AA assessments per grade (Mon–Fri).
                if ($isWeeklyCapped && $weekStart && $weekEnd) {
                    $weeklyQuery = Assessment::whereBetween('scheduled_at', [$weekStart, $weekEnd])
                        ->where('grade_level', $grade)
                        ->whereIn('type', ['Formative Assessment', 'Alternative Assessment (AA)', 'Alternative Assessment']);

                    if ($rescheduleAssessment) {
                        $weeklyQuery->where('id', '!=', $rescheduleAssessment->id);
                    }

                    if ($weeklyQuery->count() >= 5) {
                        return back()->with('error', "Conflict! Grade $grade already has 5 assessments this week.");
                    }
                }

                // Monthly cap: only 1 Long Test 1 per teacher per grade per month.
                if ($isLongTest1 && $user->role === 'teacher' && $monthStart && $monthEnd) {
                    $monthlyQuery = Assessment::where('user_id', $user->id)
                        ->where('grade_level', $grade)
                        ->where('type', 'Long Test 1 (Midterms)')
                        ->whereBetween('scheduled_at', [$monthStart, $monthEnd]);
                    if ($rescheduleAssessment) {
                        $monthlyQuery->where('id', '!=', $rescheduleAssessment->id);
                    }
                    if ($monthlyQuery->count() >= 1) {
                        return back()->with('error', "Only 1 Long Test 1 (Midterms) per month is allowed for Grade $grade.");
                    }
                }

                if ($rescheduleAssessment) {
                    // Persist a reschedule instead of creating a new assessment.
                    $rescheduleAssessment->scheduled_at = $date . ' ' . $request->due_time;
                    $rescheduleAssessment->due_date = $date . ' ' . $request->due_time;
                    $rescheduleAssessment->confirmation_status = 'scheduled';
                    $rescheduleAssessment->confirmation_requested_at = null;
                    $rescheduleAssessment->conducted_at = null;
                    $rescheduleAssessment->save();

                    return back()->with('success', 'Assessment rescheduled successfully!');
                }

                // Save new assessment.
                Assessment::create([
                    'title'        => $request->title,
                    'type'         => $type,
                    'scheduled_at' => $date . ' ' . $request->due_time,
                    'due_date'     => $date . ' ' . $request->due_time,
                    'grade_level'  => $grade,
                    'section'      => $section,
                    'subject_id'   => null,
                    'description'  => "Subject: " . $subjectName . ($section ? " | Section: {$section}" : ''),
                    'user_id'      => $user->id,
                    'confirmation_status' => 'scheduled',
                ]);

                return back()->with('success', 'Assessment scheduled successfully!');
            });
        } finally {
            if ($supportsAdvisoryLocks) {
                foreach ($locks as $held) {
                    DB::select('SELECT RELEASE_LOCK(?)', [$held]);
                }
            }
        }
    }
}
