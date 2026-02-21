<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

    // Store new student
    public function storeStudent(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'grade_level' => 'required|integer',
            'section' => 'required|string|max:50',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'student',
            'grade_level' => $request->grade_level,
            'section' => $request->section,
        ]);

        return redirect()->route('admin.students')->with('success', 'Student added successfully.');
    }
}
