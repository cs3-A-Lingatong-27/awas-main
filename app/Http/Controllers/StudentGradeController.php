<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminStudentController extends Controller
{
    /**
     * Show the student grades index view.
     */
    public function index()
    {
        return view('student.grades.index');
    }
}
