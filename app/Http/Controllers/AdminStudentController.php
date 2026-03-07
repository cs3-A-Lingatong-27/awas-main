<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentGradeSection;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminStudentController extends Controller
{
    public function index()
    {
        $students = User::where('role', 'student')->get();
        return view('admin.students.index', compact('students'));
    }

    public function create()
    {
        return view('admin.students.create');
    }

    public function store(Request $request)
    {
        $gradeSectionMap = [
            7 => ['Opal', 'Turquoise', 'Aquamarine', 'Sapphire'],
            8 => ['Anthurium', 'Carnation', 'Daffodil', 'Sunflower'],
            9 => ['Calcium', 'Lithium', 'Barium', 'Sodium'],
        ];

        $request->validate([
            'name'=>'required|string|max:255',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|string|min:6|confirmed',
            'grade_level'=>'required|integer|in:7,8,9,10,11,12',
            'section'=>'required|string|max:50',
        ]);

        $gradeLevel = (int) $request->grade_level;
        if (isset($gradeSectionMap[$gradeLevel]) && !in_array($request->section, $gradeSectionMap[$gradeLevel], true)) {
            return back()->withErrors([
                'section' => 'Invalid section for the selected grade level.',
            ])->withInput();
        }

        $student = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'role'=>'student',
            'grade_level'=>$gradeLevel,
            'section'=>$request->section,
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

        return redirect()->route('admin.students')->with('success','Student registered successfully.');
    }
}
