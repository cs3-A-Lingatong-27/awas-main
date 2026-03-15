<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\StudentGradeSection;
use App\Models\StudentSubject;
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

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Assessments
    Route::post('/assessments', [AssessmentController::class, 'store'])->name('assessments.store');
    Route::delete('/assessments/{assessment}', [AssessmentController::class, 'destroy'])->name('assessments.destroy');

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
            ->map->count();
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

        return back()->with('success', 'Student enrolled successfully!');
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
        ];
        $gradeSectionMap = [
            7 => ['Opal', 'Turquoise', 'Aquamarine', 'Sapphire'],
            8 => ['Anthurium', 'Carnation', 'Daffodil', 'Sunflower'],
            9 => ['Calcium', 'Lithium', 'Barium', 'Sodium'],
            10 => ['Graviton', 'Proton', 'Neutron', 'Electron'],
            11 => ['Mars', 'Mercury', 'Venus'],
            12 => ['Orosa', 'Del Mundo', 'Zara'],
        ];
        $teacherSubjectCatalog = [
            10 => [
                'elective' => [
                    'Philippine Biodiversity',
                    'Field Sampling',
                    'Astronomy',
                    'Philosophy of Science',
                ],
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
            ],
        ];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'assigned_grades' => 'required|array|min:1',
            'assigned_grades.*' => 'integer|in:7,8,9,10,11,12',
            'teacher_subject_groups' => 'nullable|array',
            'teacher_subject_groups.*' => 'array',
            'teacher_subject_groups.*.*' => 'string|in:science_core,elective',
            'assigned_subjects' => 'required|array|min:1',
            'assigned_subjects.*' => 'string|max:100',
            'assigned_sections' => 'required|array|min:1',
            'assigned_sections.*' => 'string|max:100',
        ]);

        $assignedSubjects = array_values(array_unique($validated['assigned_subjects']));
        $assignedGrades = array_map('intval', array_values(array_unique($validated['assigned_grades'])));
        $assignedSections = array_values(array_unique($validated['assigned_sections']));
        $teacherSubjectGroupsInput = $validated['teacher_subject_groups'] ?? [];
        $teacherSubjectGroups = [];

        foreach ($assignedGrades as $grade) {
            if (!in_array($grade, [10, 11, 12], true)) {
                continue;
            }

            $allowedTypes = $grade === 10 ? ['elective'] : ['science_core', 'elective'];
            $selectedTypes = $teacherSubjectGroupsInput[$grade] ?? [];
            $selectedTypes = is_array($selectedTypes) ? array_values(array_unique($selectedTypes)) : [];

            $invalidTypes = array_diff($selectedTypes, $allowedTypes);
            if (!empty($invalidTypes)) {
                return back()->withErrors([
                    'teacher_subject_groups' => "Invalid assignment type selected for Grade {$grade}.",
                ])->withInput();
            }

            if (empty($selectedTypes)) {
                return back()->withErrors([
                    'teacher_subject_groups' => "Select at least one assignment type for Grade {$grade}.",
                ])->withInput();
            }

            $teacherSubjectGroups[$grade] = $selectedTypes;
        }

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

        if (count($assignedGrades) === 1 && in_array(7, $assignedGrades, true)) {
            $invalidGrade7Subjects = array_diff($assignedSubjects, $gradeSubjectMap[7]);
            if (!empty($invalidGrade7Subjects)) {
                return back()->withErrors([
                    'assigned_subjects' => 'For Grade 7, use only the official Grade 7 subject list.',
                ])->withInput();
            }
        }

        $allUpperCatalogSubjects = [];
        $allowedUpperSubjects = [];
        foreach ([10, 11, 12] as $grade) {
            foreach (($teacherSubjectCatalog[$grade] ?? []) as $type => $subjects) {
                $allUpperCatalogSubjects = array_merge($allUpperCatalogSubjects, $subjects);

                if (in_array($grade, $assignedGrades, true) && in_array($type, $teacherSubjectGroups[$grade] ?? [], true)) {
                    $allowedUpperSubjects = array_merge($allowedUpperSubjects, $subjects);
                }
            }
        }
        $allUpperCatalogSubjects = array_values(array_unique($allUpperCatalogSubjects));
        $allowedUpperSubjects = array_values(array_unique($allowedUpperSubjects));

        $selectedUpperSubjects = array_values(array_unique(array_filter(
            $assignedSubjects,
            fn(string $subject) => in_array($subject, $allUpperCatalogSubjects, true)
        )));

        if (!empty(array_intersect($assignedGrades, [10, 11, 12])) && empty($selectedUpperSubjects)) {
            return back()->withErrors([
                'assigned_subjects' => 'Select at least one Grade 10-12 subject based on the chosen assignment type(s).',
            ])->withInput();
        }

        $invalidUpperSubjects = array_diff($selectedUpperSubjects, $allowedUpperSubjects);
        if (!empty($invalidUpperSubjects)) {
            return back()->withErrors([
                'assigned_subjects' => 'Selected Grade 10-12 subject(s) do not match the chosen assignment type(s).',
            ])->withInput();
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => 'teacher',
            'assigned_grades' => $assignedGrades,
            'assigned_subjects' => $assignedSubjects,
            'section' => implode(', ', $assignedSections),
        ]);

        return back()->with('success', 'Teacher registered successfully!');
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
