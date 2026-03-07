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

    if (in_array(7, $assignedGrades, true)) {
        $invalidGrade7Subjects = array_diff($assignedSubjects, $gradeSubjectMap[7]);
        if (!empty($invalidGrade7Subjects)) {
            return back()->withErrors([
                'assigned_subjects' => 'For Grade 7, use only the official Grade 7 subject list.',
            ])->withInput();
        }
    }

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
