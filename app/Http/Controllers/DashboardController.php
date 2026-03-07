<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\Subject;
use Carbon\Carbon;

class DashboardController extends Controller
{
   public function index(Request $request)
{
    $user = auth()->user();
    $month = $request->get('month', date('m'));
    $year = $request->get('year', date('Y'));
    $date = Carbon::createFromDate($year, $month, 1);

    // Get the arrays we set up in Tinker
    $assignedGrades = is_array($user->assigned_grades) ? $user->assigned_grades : json_decode($user->assigned_grades, true) ?? [];

    $startOfMonth = $date->copy()->startOfMonth()->toDateTimeString();
    $endOfMonth = $date->copy()->endOfMonth()->toDateTimeString();

    // 1. Fetch assessments filtered by the teacher's assigned grades
// 1. Fetch assessments filtered by the teacher's assigned grades
$query = Assessment::whereIn('grade_level', $assignedGrades)
    ->where(function($q) use ($startOfMonth, $endOfMonth) {
        // We check both columns to be safe
        $q->whereBetween('scheduled_at', [$startOfMonth, $endOfMonth])
          ->orWhereBetween('due_date', [$startOfMonth, $endOfMonth]);
    });

// 2. Process counts (Grouping by the correct date)
$notifications = $query->get()
    ->groupBy(function($val) {
        // Use scheduled_at if it exists, otherwise use due_date
        $actualDate = $val->scheduled_at ?? $val->due_date;
        return Carbon::parse($actualDate)->format('j');
    })
    ->map->count();

    return view('dashboard', [
        'user' => $user, 
        'date' => $date,
        'notifications' => $notifications,
        'daysInMonth' => $date->daysInMonth,
        'firstDayOfMonth' => $date->dayOfWeek,
        'assignedGrades' => $assignedGrades,
        'subjectCatalog' => Subject::select('name', 'type', 'grade_level_start', 'grade_level_end')->get(),
    ]);
}
}
