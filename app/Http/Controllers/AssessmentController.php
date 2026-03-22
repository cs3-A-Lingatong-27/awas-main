<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\Subject;
use Carbon\Carbon;

class AssessmentController extends Controller
{
    /**
     * Display assessments filtered by the teacher's assigned grade.
     */
public function index()
{
    $user = auth()->user();

    if ($user->role === 'teacher') {
        // Teachers see only the grades they are assigned to
        $grades = is_array($user->assigned_grades) ? $user->assigned_grades : json_decode($user->assigned_grades, true) ?? [];
        $assessments = Assessment::whereIn('grade_level', $grades)->orderBy('scheduled_at', 'asc')->get();
    } else {
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

    public function destroy(Assessment $assessment)
{
    // Check if the logged-in user is an admin OR the owner of the assessment
    if (auth()->user()->role === 'admin' || $assessment->user_id === auth()->id()) {
        $assessment->delete();
        return response()->json(['success' => true]);
    }

    return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
}

    public function store(Request $request)
    {
        $user = auth()->user();

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

        if ($user->role === 'admin') {
            if (!$isLongTest2) {
                return back()->with('error', 'Admins can only schedule Long Test 2 (Quarterly Exam).');
            }
        } elseif (!in_array($grade, $allowedGrades) || !in_array($subjectName, $allowedSubjects)) {
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
            return back()->with('error', 'Only administrators can schedule Long Test 2 (Quarterly Exam).');
        }

        if ($isLongTest1 && $user->role === 'teacher') {
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

            $monthStart = Carbon::parse($date)->startOfMonth()->startOfDay();
            $monthEnd = Carbon::parse($date)->endOfMonth()->endOfDay();
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

        // 3. THE AWAS ALGORITHM (Weekly combined cap for FA/AA)
        if (in_array($type, ['Formative Assessment', 'Alternative Assessment (AA)', 'Alternative Assessment'], true)) {
            $weekStart = Carbon::parse($date)->startOfWeek(Carbon::MONDAY)->startOfDay();
            $weekEnd = $weekStart->copy()->addDays(4)->endOfDay();

            $weeklyQuery = Assessment::whereBetween('scheduled_at', [$weekStart, $weekEnd])
                ->where('grade_level', $grade)
                ->whereIn('type', ['Formative Assessment', 'Alternative Assessment (AA)', 'Alternative Assessment']);

            if ($rescheduleAssessment) {
                $weeklyQuery->where('id', '!=', $rescheduleAssessment->id);
            }

            $weeklyCount = $weeklyQuery->count();
            if ($weeklyCount >= 5) {
                return back()->with('error', "Conflict! Grade $grade already has 5 assessments this week.");
            }
        }

        if ($rescheduleAssessment) {
            $rescheduleAssessment->scheduled_at = $date . ' ' . $request->due_time;
            $rescheduleAssessment->due_date = $date . ' ' . $request->due_time;
            $rescheduleAssessment->confirmation_status = 'scheduled';
            $rescheduleAssessment->confirmation_requested_at = null;
            $rescheduleAssessment->conducted_at = null;
            $rescheduleAssessment->save();

            return back()->with('success', 'Assessment rescheduled successfully!');
        }

        // 4. Save
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
    }
}
