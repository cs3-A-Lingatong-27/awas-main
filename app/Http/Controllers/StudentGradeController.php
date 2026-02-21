<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminStudentController extends Controller
{
    public function index()
    {
        return view('student.grades.index');
    }
}
