<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminStudentController extends Controller
{
    /**
     * Show the teacher assessments index view.
     */
    public function index()
    {
        return view('teacher.assessments.index');
    }
}
