<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminStudentController extends Controller
{
    /**
     * Show the teacher subjects index view.
     */
    public function index()
    {
        return view('teacher.subjects.index');
    }
}
