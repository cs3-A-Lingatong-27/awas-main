<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminStudentController extends Controller
{
    /**
     * Show the student subjects index view.
     */
    public function index()
    {
        return view('student.subjects.index');
    }
}
