<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentGradeSection;
use App\Models\User;

class AdminController extends Controller
{
    // List all students
    public function students()
    {
        $students = User::where('role', 'student')->get();
        return view('admin.students.index', compact('students'));
    }

    // Show create student form
    public function createStudent()
    {
        return view('admin.students.create');
    }

public function storeStudent(Request $request)
{
    $gradeSectionMap = [
        7 => ['Opal', 'Turquoise', 'Aquamarine', 'Sapphire'],
        8 => ['Anthurium', 'Carnation', 'Daffodil', 'Sunflower'],
        9 => ['Calcium', 'Lithium', 'Barium', 'Sodium'],
    ];

    // 1. Adjusted validation to match your dashboard form
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6', // Removed 'confirmed' as it's a temp password
        'grade_level' => 'required|integer|in:7,8,9,10,11,12',
        'section' => 'required|string|max:50',
    ]);

    $gradeLevel = (int) $request->grade_level;
    if (isset($gradeSectionMap[$gradeLevel]) && !in_array($request->section, $gradeSectionMap[$gradeLevel], true)) {
        return back()->withErrors([
            'section' => 'Invalid section for the selected grade level.',
        ])->withInput();
    }

    // 2. Create the User
    $student = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'role' => 'student',
        'grade_level' => $gradeLevel,
        'section' => $request->section,
    ]);

    if ($gradeLevel >= 7 && $gradeLevel <= 9) {
        StudentGradeSection::updateOrCreate(
            ['user_id' => $student->id],
            [
                'grade_level' => $gradeLevel,
                'section' => $request->section,
            ]
        );
    }

    // 3. Redirect back to dashboard (since that's where your form is)
    return redirect()->route('dashboard')->with('success', 'Student enrolled successfully!');
}

public function storeTeacher(Request $request)
{
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

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8',
        'assigned_grades' => 'required|array|min:1',
        'assigned_grades.*' => 'integer|in:7,8,9,10,11,12',
        'assigned_subjects' => 'required|array|min:1',
        'assigned_subjects.*' => 'string|max:100',
    ]);

    $assignedSubjects = array_values(array_unique($validated['assigned_subjects']));
    $assignedGrades = array_map('intval', array_values(array_unique($validated['assigned_grades'])));

    // Temporarily allow Grade 7 subject selection to avoid blocking enrollment.

    User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => bcrypt($validated['password']),
        'role' => 'teacher',
        'assigned_grades' => $assignedGrades,
        'assigned_subjects' => $assignedSubjects,
    ]);

    return redirect()->route('dashboard')->with('success', 'Teacher registered successfully!');
}
}
