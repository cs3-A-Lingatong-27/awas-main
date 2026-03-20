<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\StudentGradeSection;
use App\Models\StudentSubject;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

/**
 * PUBLIC ROUTES
 */
Route::get('/', function () {
    return view('welcome');
})->name('home'); // CRITICAL FIX: Tests and some components look for the 'home' route.

/**
 * AUTHENTICATED ROUTES
 */
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin Enrollment Page
    Route::get('/admin/enrollment', function () {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        return view('admin.enrollment', [
            'user' => auth()->user(),
            'subjectCatalog' => Subject::select('name', 'type', 'grade_level_start', 'grade_level_end')->get(),
        ]);
    })->name('admin.enrollment');

    Route::get('/admin/enrollment/students', function (Request $request) {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $gradeLevel = $request->query('grade_level');
        $section = $request->query('section');
        $group = $request->query('group');

        $studentsQuery = User::where('role', 'student');
        $students = $studentsQuery->get();
        if ($students->isEmpty()) {
            return response()->json([]);
        }

        $studentIds = $students->pluck('id')->all();
        $sectionRows = StudentGradeSection::whereIn('user_id', $studentIds)->get()->keyBy('user_id');
        $subjectRows = StudentSubject::whereIn('user_id', $studentIds)->get()->groupBy('user_id');

        $filtered = $students->filter(function (User $student) use ($sectionRows, $subjectRows, $section, $group, $gradeLevel) {
            $gradeValue = $sectionRows->get($student->id)->grade_level ?? $student->grade_level;
            if ($gradeLevel !== null && $gradeLevel !== '') {
                $gradeInt = (int) $gradeLevel;
                if ((int) $gradeValue !== $gradeInt) {
                    return false;
                }
            }

            $sectionValue = $sectionRows->get($student->id)->section ?? $student->section;
            if ($section !== null && $section !== '' && $sectionValue !== $section) {
                return false;
            }

            if ($group !== null && $group !== '') {
                $grade = $gradeLevel !== null && $gradeLevel !== '' ? (int) $gradeLevel : (int) $gradeValue;
                if ($grade === 10 && $group !== 'elective') {
                    return false;
                }
                if ($grade >= 7 && $grade <= 9 && $group === 'regular') {
                    return true;
                }
                $subjectsFor = $subjectRows->get($student->id, collect());
                if ($subjectsFor->where('subject_type', $group)->isEmpty()) {
                    return false;
                }
            }

            return true;
        });

        $payload = $filtered->map(function (User $student) use ($sectionRows, $subjectRows) {
            $subjectsFor = $subjectRows->get($student->id, collect());
            $subjectsByType = $subjectsFor->groupBy('subject_type')->map(function ($items) {
                return $items->pluck('subject_name')->values();
            });

            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'grade_level' => $sectionRows->get($student->id)->grade_level ?? $student->grade_level,
                'section' => $sectionRows->get($student->id)->section ?? $student->section,
                'subjects' => $subjectsByType,
            ];
        })->values();

        return response()->json($payload);
    })->name('admin.enrollment.students');

    Route::get('/admin/enrollment/teachers', function (Request $request) {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $gradeLevel = $request->query('grade_level');
        $subject = $request->query('subject');

        $teachers = User::where('role', 'teacher')->get();
        if ($teachers->isEmpty()) {
            return response()->json([]);
        }

        $filtered = $teachers->filter(function (User $teacher) use ($gradeLevel, $subject) {
            $grades = is_array($teacher->assigned_grades)
                ? $teacher->assigned_grades
                : (json_decode($teacher->assigned_grades, true) ?? []);
            $grades = array_map('intval', $grades);

            if ($gradeLevel !== null && $gradeLevel !== '') {
                $gradeInt = (int) $gradeLevel;
                if (!in_array($gradeInt, $grades, true)) {
                    return false;
                }
            }

            if ($subject !== null && $subject !== '') {
                $subjects = is_array($teacher->assigned_subjects)
                    ? $teacher->assigned_subjects
                    : (json_decode($teacher->assigned_subjects, true) ?? []);
                if (!in_array($subject, $subjects, true)) {
                    return false;
                }
            }

            return true;
        });

        $payload = $filtered->map(function (User $teacher) {
            $grades = is_array($teacher->assigned_grades)
                ? $teacher->assigned_grades
                : (json_decode($teacher->assigned_grades, true) ?? []);
            $subjects = is_array($teacher->assigned_subjects)
                ? $teacher->assigned_subjects
                : (json_decode($teacher->assigned_subjects, true) ?? []);

            return [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'grades' => array_values(array_unique($grades)),
                'sections' => $teacher->section,
                'subjects' => array_values(array_unique($subjects)),
            ];
        })->values();

        return response()->json($payload);
    })->name('admin.enrollment.teachers');

    Route::get('/admin/enrollment/teachers/subjects', function (Request $request) {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $gradeLevel = $request->query('grade_level');
        if ($gradeLevel === null || $gradeLevel === '') {
            return response()->json([]);
        }
        $gradeInt = (int) $gradeLevel;

        $teachers = User::where('role', 'teacher')->get();
        if ($teachers->isEmpty()) {
            return response()->json([]);
        }

        $subjects = [];
        foreach ($teachers as $teacher) {
            $grades = is_array($teacher->assigned_grades)
                ? $teacher->assigned_grades
                : (json_decode($teacher->assigned_grades, true) ?? []);
            $grades = array_map('intval', $grades);
            if (!in_array($gradeInt, $grades, true)) {
                continue;
            }

            $assignedSubjects = is_array($teacher->assigned_subjects)
                ? $teacher->assigned_subjects
                : (json_decode($teacher->assigned_subjects, true) ?? []);
            if (!empty($assignedSubjects)) {
                $subjects = array_merge($subjects, $assignedSubjects);
                continue;
            }

            if (!empty($teacher->subject)) {
                $subjects[] = $teacher->subject;
            }
        }

        $subjects = array_values(array_unique(array_filter($subjects)));
        sort($subjects);

        return response()->json($subjects);
    })->name('admin.enrollment.teacher-subjects');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Assessments
    Route::post('/assessments', [AssessmentController::class, 'store'])->name('assessments.store');
    Route::delete('/assessments/{assessment}', [AssessmentController::class, 'destroy'])->name('assessments.destroy');

    Route::get('/api/teacher-assessments', function (Request $request) {
        $user = auth()->user();
        if (!$user || $user->role !== 'teacher') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $extractSubject = function (?string $description): ?string {
            if (!$description) {
                return null;
            }
            if (preg_match('/Subject:\\s*([^|]+)/i', $description, $matches) === 1) {
                return trim($matches[1]);
            }
            return null;
        };

        return Assessment::where('user_id', $user->id)
            ->orderBy('scheduled_at', 'desc')
            ->get()
            ->map(function ($a) use ($extractSubject) {
                $subject = $a->subject ? $a->subject->name : null;
                if (!$subject) {
                    $subject = $extractSubject($a->description);
                }
                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'type' => $a->type,
                    'grade_level' => $a->grade_level,
                    'subject' => $subject ?: 'Unspecified Subject',
                    'scheduled_at' => $a->scheduled_at ? Carbon::parse($a->scheduled_at)->format('M d, Y g:i A') : 'No time set',
                ];
            });
    })->name('teacher.assessments');

    Route::get('/api/teacher-confirmations', function () {
        $user = auth()->user();
        if (!$user || $user->role !== 'teacher') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $now = now();

        $extractSubject = function (?string $description): ?string {
            if (!$description) {
                return null;
            }
            if (preg_match('/Subject:\\s*([^|]+)/i', $description, $matches) === 1) {
                return trim($matches[1]);
            }
            return null;
        };

        $extractSection = function (?string $description): ?string {
            if (!$description) {
                return null;
            }
            if (preg_match('/Section:\\s*([^|]+)/i', $description, $matches) === 1) {
                return trim($matches[1]);
            }
            return null;
        };

        $assessments = Assessment::where('user_id', $user->id)
            ->where('scheduled_at', '<', $now)
            ->whereIn('confirmation_status', ['scheduled', 'pending'])
            ->orderBy('scheduled_at', 'desc')
            ->get();

        foreach ($assessments as $assessment) {
            if ($assessment->confirmation_status === 'scheduled') {
                $assessment->confirmation_status = 'pending';
                $assessment->confirmation_requested_at = $now;
                $assessment->save();
            }
        }

        return $assessments->map(function ($a) use ($extractSubject, $extractSection) {
            $subject = $a->subject ? $a->subject->name : $extractSubject($a->description);
            $section = $a->section ?: $extractSection($a->description);
            return [
                'id' => $a->id,
                'title' => $a->title,
                'type' => $a->type,
                'grade_level' => $a->grade_level,
                'section' => $section ?: 'All Sections',
                'subject' => $subject ?: 'Unspecified Subject',
                'scheduled_at' => $a->scheduled_at ? Carbon::parse($a->scheduled_at)->format('M d, Y g:i A') : 'No time set',
            ];
        });
    })->name('teacher.confirmations');

    Route::post('/api/teacher-confirmations/{assessment}/conducted', function (Assessment $assessment) {
        $user = auth()->user();
        if (!$user || $user->role !== 'teacher' || $assessment->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $assessment->confirmation_status = 'conducted';
        $assessment->conducted_at = now();
        $assessment->save();

        return response()->json(['status' => 'ok']);
    })->name('teacher.confirmations.conducted');

    Route::post('/api/teacher-confirmations/{assessment}/not-conducted', function (Assessment $assessment) {
        $user = auth()->user();
        if (!$user || $user->role !== 'teacher' || $assessment->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $assessment->confirmation_status = 'not_conducted';
        $assessment->save();

        return response()->json(['status' => 'ok']);
    })->name('teacher.confirmations.not_conducted');

    Route::get('/api/admin-assessments', function (Request $request) {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $extractSubject = function (?string $description): ?string {
            if (!$description) {
                return null;
            }
            if (preg_match('/Subject:\\s*([^|]+)/i', $description, $matches) === 1) {
                return trim($matches[1]);
            }
            return null;
        };

        $extractSection = function (?string $description): ?string {
            if (!$description) {
                return null;
            }
            if (preg_match('/Section:\\s*([^|]+)/i', $description, $matches) === 1) {
                return trim($matches[1]);
            }
            return null;
        };

        $grade = $request->query('grade_level');
        $section = $request->query('section');
        $subject = $request->query('subject');
        $title = $request->query('title');
        $types = $request->query('types');
        $typeFilters = is_string($types) && $types !== '' ? array_filter(array_map('trim', explode(',', $types))) : [];

        $subjectTypeMap = Subject::query()->pluck('type', 'name')->all();
        $subjectsForTypes = [];
        if (!empty($typeFilters)) {
            $subjectsForTypes = Subject::query()
                ->whereIn('type', $typeFilters)
                ->pluck('name')
                ->all();
        }

        $query = Assessment::query();

        if ($grade !== null && $grade !== '') {
            $query->where('grade_level', (int) $grade);
        }

        if ($section !== null && $section !== '') {
            $query->where(function ($q) use ($section) {
                $q->where('section', $section)
                  ->orWhere('description', 'like', '%Section: ' . $section . '%');
            });
        }

        if ($title !== null && $title !== '') {
            $query->where('title', 'like', '%' . $title . '%');
        }

        if ($subject !== null && $subject !== '') {
            $query->where(function ($q) use ($subject) {
                $q->whereHas('subject', function ($sq) use ($subject) {
                    $sq->where('name', $subject);
                })->orWhere('description', 'like', '%Subject: ' . $subject . '%');
            });
        }

        if (!empty($subjectsForTypes)) {
            $query->where(function ($q) use ($subjectsForTypes) {
                $q->whereHas('subject', function ($sq) use ($subjectsForTypes) {
                    $sq->whereIn('name', $subjectsForTypes);
                })->orWhere(function ($sq) use ($subjectsForTypes) {
                    foreach ($subjectsForTypes as $name) {
                        $sq->orWhere('description', 'like', '%Subject: ' . $name . '%');
                    }
                });
            });
        }

        return $query
            ->orderBy('scheduled_at', 'desc')
            ->get()
            ->map(function ($a) use ($extractSubject, $extractSection, $subjectTypeMap) {
                $subjectName = $a->subject ? $a->subject->name : $extractSubject($a->description);
                $subjectType = $subjectName && isset($subjectTypeMap[$subjectName]) ? $subjectTypeMap[$subjectName] : null;
                $section = $a->section ?: $extractSection($a->description);
                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'type' => $a->type,
                    'grade_level' => $a->grade_level,
                    'section' => $section ?: 'All Sections',
                    'subject' => $subjectName ?: 'Unspecified Subject',
                    'subject_type' => $subjectType ?: 'Unspecified',
                    'scheduled_at' => $a->scheduled_at ? Carbon::parse($a->scheduled_at)->format('M d, Y g:i A') : 'No time set',
                ];
            });
    })->name('admin.assessments');

    /**
     * API: FETCH ASSESSMENTS FOR THE SIDE PANEL
     */
Route::get('/api/assessments-by-date', function (Request $request) {
    $user = auth()->user();
    $targetDate = $request->query('date');
    $filterGrade = $request->query('grade_level');
    $filterSection = $request->query('section');
    $filterSubject = $request->query('subject');
    if (!$targetDate) return response()->json([]);

    $query = Assessment::whereDate('scheduled_at', $targetDate);
    
    if ($user && $user->role === 'student') {
        $query->where('grade_level', $user->grade_level);
    } elseif ($user && $user->role === 'teacher') {
        $assignedGrades = is_array($user->assigned_grades) ? $user->assigned_grades : (json_decode($user->assigned_grades, true) ?? []);
        $assignedGrades = array_map('intval', $assignedGrades);
        if (empty($assignedGrades)) {
            return response()->json([]);
        }
        $query->whereIn('grade_level', $assignedGrades);

        if ($filterGrade !== null && $filterGrade !== '') {
            $filterGradeInt = (int) $filterGrade;
            if (!in_array($filterGradeInt, $assignedGrades, true)) {
                return response()->json([]);
            }
            $query->where('grade_level', $filterGradeInt);
        }
    } elseif ($filterGrade !== null && $filterGrade !== '') {
        $query->where('grade_level', (int) $filterGrade);
    }

    if ($filterSection !== null && $filterSection !== '') {
        $query->where(function ($q) use ($filterSection) {
            $q->where('section', $filterSection)
              ->orWhere('description', 'like', '%Section: ' . $filterSection . '%');
        });
    }

    if ($filterSubject !== null && $filterSubject !== '') {
        $query->where('description', 'like', '%Subject: ' . $filterSubject . '%');
    }

    return $query->get()->map(function($a) {
        return [
            'id' => $a->id,
            'title' => $a->title,
            'user_id' => $a->user_id, // CRITICAL: Required for the Delete button logic
            'teacher' => $a->teacher ? $a->teacher->name : null,
            'subject' => $a->subject ? $a->subject->name : $a->type,
            'description' => $a->description,
            'due_time' => $a->scheduled_at ? Carbon::parse($a->scheduled_at)->format('g:i A') : 'No time set'
        ];
    });
});

    /**
     * API: FETCH NOTIFICATION DOTS
     */
    Route::get('/api/assessment-notifications', function (Request $request) {
        $user = auth()->user();
        $month = $request->query('month', date('m'));
        $year = $request->query('year', date('Y'));
        $filterGrade = $request->query('grade_level');
        $filterSection = $request->query('section');
        $filterSubject = $request->query('subject');

        $query = Assessment::whereMonth('scheduled_at', $month)->whereYear('scheduled_at', $year);
        
        if ($user && $user->role === 'student') {
            $query->where('grade_level', $user->grade_level);
        } elseif ($user && $user->role === 'teacher') {
            $assignedGrades = is_array($user->assigned_grades) ? $user->assigned_grades : (json_decode($user->assigned_grades, true) ?? []);
            $assignedGrades = array_map('intval', $assignedGrades);
            if (empty($assignedGrades)) {
                return collect();
            }
            $query->whereIn('grade_level', $assignedGrades);

            if ($filterGrade !== null && $filterGrade !== '') {
                $filterGradeInt = (int) $filterGrade;
                if (!in_array($filterGradeInt, $assignedGrades, true)) {
                    return collect();
                }
                $query->where('grade_level', $filterGradeInt);
            }
        } elseif ($filterGrade !== null && $filterGrade !== '') {
            $query->where('grade_level', (int) $filterGrade);
        }

        if ($filterSection !== null && $filterSection !== '') {
            $query->where(function ($q) use ($filterSection) {
                $q->where('section', $filterSection)
                  ->orWhere('description', 'like', '%Section: ' . $filterSection . '%');
            });
        }

        if ($filterSubject !== null && $filterSubject !== '') {
            $query->where('description', 'like', '%Subject: ' . $filterSubject . '%');
        }

        return $query->get()
            ->groupBy(fn($val) => Carbon::parse($val->scheduled_at)->format('j'))
            ->map(function ($items) {
                $counts = [
                    'formative' => 0,
                    'alternative' => 0,
                    'long_test' => 0,
                ];

                foreach ($items as $assessment) {
                    $type = $assessment->type;
                    if ($type === 'Formative Assessment') {
                        $counts['formative']++;
                    } elseif ($type === 'Long Test') {
                        $counts['long_test']++;
                    } elseif ($type === 'Alternative Assessment (AA)' || $type === 'Alternative Assessment') {
                        $counts['alternative']++;
                    }
                }

                return $counts;
            });
    });

    /**
     * ADMIN: ENROLL LOGIC
     */
    Route::post('/admin/enroll', function (Request $request) {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $gradeSectionMap = [
            7 => ['Opal', 'Turquoise', 'Aquamarine', 'Sapphire'],
            8 => ['Anthurium', 'Carnation', 'Daffodil', 'Sunflower'],
            9 => ['Calcium', 'Lithium', 'Barium', 'Sodium'],
        ];
        $subjectCatalog = [
            10 => [
                'elective' => [
                    'Philippine Biodiversity',
                    'Field Sampling',
                    'Astronomy',
                    'Philosophy of Science',
                ],
                'science_core' => [],
                'regular' => [],
            ],
            11 => [
                'elective' => [
                    'Biology 3 Level 1',
                    'Biology 3 Level 2',
                    'Chemistry 3 Level 1',
                    'Chemistry 3 Level 2',
                    'Physics 3 Level 1',
                    'Physics 3 Level 2',
                    'Computer Science 5 Level 1',
                    'Computer Science 5 Level 2',
                    'Design and Make Technology 1 Level 1',
                    'Design and Make Technology 1 Level 2',
                    'Engineering 1 Level 1',
                    'Engineering 1 Level 2',
                    'Agriculture 1 Level 1',
                    'Agriculture 1 Level 2',
                ],
                'science_core' => [
                    'Physics 3 Level 1',
                    'Physics 3 Level 2',
                    'Biology 3 Level 1',
                    'Biology 3 Level 2',
                    'Chemistry 3 Level 1',
                    'Chemistry 3 Level 2',
                ],
                'regular' => [],
            ],
            12 => [
                'elective' => [
                    'Biology 3 Level 1',
                    'Biology 3 Level 2',
                    'Chemistry 3 Level 1',
                    'Chemistry 3 Level 2',
                    'Physics 3 Level 1',
                    'Physics 3 Level 2',
                    'Computer Science 5 Level 1',
                    'Computer Science 5 Level 2',
                    'Design and Make Technology 1 Level 1',
                    'Design and Make Technology 1 Level 2',
                    'Engineering 1 Level 1',
                    'Engineering 1 Level 2',
                    'Agriculture 1 Level 1',
                    'Agriculture 1 Level 2',
                ],
                'science_core' => [
                    'Physics 4 Level 1',
                    'Physics 4 Level 2',
                    'Biology 4 Level 1',
                    'Biology 4 Level 2',
                    'Chemistry 4 Level 1',
                    'Chemistry 4 Level 2',
                ],
                'regular' => [],
            ],
        ];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'grade_level' => 'required|integer|in:7,8,9,10,11,12',
            'section' => 'nullable|string',
            'student_subject_groups' => 'required|array|min:1',
            'student_subject_groups.*' => 'string|in:regular,science_core,elective',
            'selected_subjects' => 'nullable|array',
            'selected_subjects.*' => 'array',
            'selected_subjects.*.*' => 'string|max:255',
            'password' => 'required|min:8',
        ]);

        $gradeLevel = (int) $validated['grade_level'];
        $subjectGroups = array_values(array_unique($validated['student_subject_groups']));

        // Enforce grade -> allowed subject-group checklist rules
        if ($gradeLevel >= 7 && $gradeLevel <= 9) {
            if ($subjectGroups !== ['regular']) {
                return back()->withErrors([
                    'student_subject_groups' => 'Grades 7-9 must use Regular only.',
                ])->withInput();
            }
        } elseif ($gradeLevel === 10) {
            $invalid = array_diff($subjectGroups, ['regular', 'elective']);
            if (!empty($invalid) || empty($subjectGroups)) {
                return back()->withErrors([
                    'student_subject_groups' => 'Grade 10 can only use Regular and/or Elective.',
                ])->withInput();
            }
        } elseif (in_array($gradeLevel, [11, 12], true)) {
            $invalid = array_diff($subjectGroups, ['regular', 'science_core', 'elective']);
            if (!empty($invalid) || empty($subjectGroups)) {
                return back()->withErrors([
                    'student_subject_groups' => 'Grades 11-12 can only use Regular, Science Core, and/or Elective.',
                ])->withInput();
            }
        }

        $selectedSubjectsInput = $validated['selected_subjects'] ?? [];
        $selectedSubjectsByType = [
            'elective' => array_values(array_unique($selectedSubjectsInput['elective'] ?? [])),
            'science_core' => array_values(array_unique($selectedSubjectsInput['science_core'] ?? [])),
            'regular' => array_values(array_unique($selectedSubjectsInput['regular'] ?? [])),
        ];

        foreach (['regular', 'elective', 'science_core'] as $type) {
            if (!in_array($type, $subjectGroups, true)) {
                continue;
            }

            $allowed = $subjectCatalog[$gradeLevel][$type] ?? [];
            $selected = $selectedSubjectsByType[$type];
            if (empty($allowed)) {
                // Allow empty catalog buckets (e.g., regular list not yet finalized).
                continue;
            }
            if (empty($selected)) {
                return back()->withErrors([
                    'selected_subjects' => "Please choose at least one {$type} subject.",
                ])->withInput();
            }
            $invalid = array_diff($selected, $allowed);
            if (!empty($invalid)) {
                return back()->withErrors([
                    'selected_subjects' => "Invalid {$type} subject selection for Grade {$gradeLevel}.",
                ])->withInput();
            }
        }

        if ($gradeLevel >= 7 && $gradeLevel <= 9) {
            // No explicit regular subject list yet for Grades 7-9.
            // Accept enrollment without selected regular subjects for now.
            $selectedSubjectsByType['regular'] = [];
        }

        $sectionExempt =
            ($gradeLevel === 10 && in_array('elective', $subjectGroups, true) && !in_array('regular', $subjectGroups, true)) ||
            (in_array($gradeLevel, [11, 12], true) &&
                !in_array('regular', $subjectGroups, true) &&
                (in_array('science_core', $subjectGroups, true) || in_array('elective', $subjectGroups, true)));
        $section = $sectionExempt ? null : (($validated['section'] ?? null) ? trim((string) $validated['section']) : null);

        if (!$sectionExempt && !$section) {
            return back()->withErrors([
                'section' => 'Section is required for this grade and subject group.',
            ])->withInput();
        }

        if (!$sectionExempt && isset($gradeSectionMap[$gradeLevel]) && !in_array($section, $gradeSectionMap[$gradeLevel], true)) {
            return back()->withErrors([
                'section' => 'Invalid section for the selected grade level.',
            ])->withInput();
        }

        $student = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => 'student',
            'grade_level' => $gradeLevel,
            'section' => $section,
        ]);

        if (!$sectionExempt && $gradeLevel >= 7 && $gradeLevel <= 9) {
            StudentGradeSection::updateOrCreate(
                ['user_id' => $student->id],
                [
                    'grade_level' => $gradeLevel,
                    'section' => $section,
                ]
            );
        }

        $subjectRows = [];
        foreach (['elective', 'science_core'] as $type) {
            foreach ($selectedSubjectsByType[$type] as $subjectName) {
                $subjectRows[] = [
                    'user_id' => $student->id,
                    'grade_level' => $gradeLevel,
                    'subject_name' => $subjectName,
                    'subject_type' => $type,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        if (!empty($subjectRows)) {
            StudentSubject::insert($subjectRows);
        }

        return redirect()->route('admin.enrollment')->with('success', 'Student enrolled successfully!');
    })->name('admin.enroll');

    Route::post('/admin/enroll-teacher', function (Request $request) {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

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
                'Grade 8 Placeholder Subject 1',
                'Grade 8 Placeholder Subject 2',
                'Grade 8 Placeholder Subject 3',
                'Grade 8 Placeholder Subject 4',
                'Grade 8 Placeholder Subject 5',
            ],
            9 => [
                'Grade 9 Placeholder Subject 1',
                'Grade 9 Placeholder Subject 2',
                'Grade 9 Placeholder Subject 3',
                'Grade 9 Placeholder Subject 4',
                'Grade 9 Placeholder Subject 5',
            ],
            10 => [
                'Grade 10 Placeholder Subject 1',
                'Grade 10 Placeholder Subject 2',
                'Grade 10 Placeholder Subject 3',
                'Grade 10 Placeholder Subject 4',
                'Grade 10 Placeholder Subject 5',
            ],
            11 => [
                'Grade 11 Placeholder Subject 1',
                'Grade 11 Placeholder Subject 2',
                'Grade 11 Placeholder Subject 3',
                'Grade 11 Placeholder Subject 4',
                'Grade 11 Placeholder Subject 5',
            ],
            12 => [
                'Grade 12 Placeholder Subject 1',
                'Grade 12 Placeholder Subject 2',
                'Grade 12 Placeholder Subject 3',
                'Grade 12 Placeholder Subject 4',
                'Grade 12 Placeholder Subject 5',
            ],
        ];
        $gradeSectionMap = [
            7 => ['Opal', 'Turquoise', 'Aquamarine', 'Sapphire'],
            8 => ['Anthurium', 'Carnation', 'Daffodil', 'Sunflower'],
            9 => ['Calcium', 'Lithium', 'Barium', 'Sodium'],
            10 => ['Graviton', 'Proton', 'Neutron', 'Electron'],
            11 => ['Mars', 'Mercury', 'Venus'],
            12 => ['Orosa', 'Del Mundo', 'Zara'],
        ];
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'assigned_grades' => 'required|array|min:1',
            'assigned_grades.*' => 'integer|in:7,8,9,10,11,12',
            'assigned_subjects' => 'required|array|min:1',
            'assigned_subjects.*' => 'string|max:100',
            'assigned_sections' => 'required|array|min:1',
            'assigned_sections.*' => 'string|max:100',
        ]);

        $assignedSubjects = array_values(array_unique($validated['assigned_subjects']));
        $assignedGrades = array_map('intval', array_values(array_unique($validated['assigned_grades'])));
        $assignedSections = array_values(array_unique($validated['assigned_sections']));
        $allowedSections = [];
        foreach ($assignedGrades as $grade) {
            $allowedSections = array_merge($allowedSections, $gradeSectionMap[$grade] ?? []);
        }
        $allowedSections = array_values(array_unique($allowedSections));
        $invalidSections = array_diff($assignedSections, $allowedSections);
        if (!empty($invalidSections)) {
            return back()->withErrors([
                'assigned_sections' => 'Invalid section selection for the chosen grade levels.',
            ])->withInput();
        }

        // Temporarily allow Grade 7 subject selection to avoid blocking enrollment.

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => 'teacher',
            'assigned_grades' => $assignedGrades,
            'assigned_subjects' => $assignedSubjects,
            'section' => implode(', ', $assignedSections),
        ]);

        return redirect()->route('admin.enrollment')->with('success', 'Teacher registered successfully!');
    })->name('admin.enroll.teacher');

    /**
     * ADMIN: EMAIL SUMMARY
     */
Route::get('/api/check-conflict', function (Request $request) {
    $date = $request->query('date');
    $grade = $request->query('grade_level');
    $type = $request->query('type');

    if (!$date || !$grade) return response()->json(['status' => 'SAFE']);

    $faCount = Assessment::whereDate('scheduled_at', $date)
        ->where('grade_level', $grade)
        ->where('type', 'Formative Assessment')
        ->count();

    $aaCount = Assessment::whereDate('scheduled_at', $date)
        ->where('grade_level', $grade)
        ->whereIn('type', ['Alternative Assessment (AA)', 'Alternative Assessment'])
        ->count();

    // Type-aware checks for scheduling panel.
    if ($type === 'Formative Assessment' && $faCount >= 2) {
        return response()->json([
            'status' => 'CRITICAL',
            'message' => "Conflict! This day already has 2 Formative Assessments for this grade."
        ]);
    }

    if (($type === 'Alternative Assessment (AA)' || $type === 'Alternative Assessment') && $aaCount >= 1) {
        return response()->json([
            'status' => 'CRITICAL',
            'message' => "Conflict! This day already has 1 Alternative Assessment for this grade."
        ]);
    }

    // Backward-safe generic behavior if type is not provided.
    if (!$type && ($faCount >= 2 || $aaCount >= 1)) {
        return response()->json([
            'status' => 'CRITICAL',
            'message' => "Conflict! This day already reached the type limit for this grade."
        ]);
    }

    return response()->json([
        'status' => ($faCount > 0 || $aaCount > 0) ? 'WARNING' : 'SAFE',
        'message' => 'Safe to schedule.'
    ]);
});
    Route::get('/admin/send-daily-summary', function () {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $today = now()->toDateString();
        $assessments = Assessment::whereDate('scheduled_at', $today)->get();

        if ($assessments->isEmpty()) return "No assessments today.";

        $teachers = User::where('role', 'admin')->get();
        foreach ($teachers as $teacher) {
            Mail::send([], [], function ($message) use ($teacher, $today) {
                $message->to($teacher->email)
                    ->subject("Daily Schedule - $today")
                    ->html("Check the dashboard for today's tasks.");
            });
        }
        return "Emails sent.";
    });
});

// Load the Auth routes (login, register, etc)
require __DIR__.'/auth.php';
