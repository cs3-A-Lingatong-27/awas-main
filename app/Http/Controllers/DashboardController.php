<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get current month/year from request or default to now
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));
        
        // Create Carbon instance for the selected month
        $date = Carbon::createFromDate($year, $month, 1);

        $daysInMonth = $date->daysInMonth;
        $firstDayOfMonth = $date->dayOfWeek;

        // Use startOfMonth and endOfMonth for a more reliable database query
        $startOfMonth = $date->copy()->startOfMonth()->toDateString();
        $endOfMonth = $date->copy()->endOfMonth()->toDateString();

        // 1. Fetch assessments using a Date Range
        // We check 'scheduled_at' and 'due_date' to ensure compatibility with various DB naming conventions
        $query = Assessment::where(function($q) use ($startOfMonth, $endOfMonth) {
            $q->whereBetween('scheduled_at', [$startOfMonth, $endOfMonth])
              ->orWhereBetween('due_date', [$startOfMonth, $endOfMonth]);
        });

        // 2. Process the results for the calendar view (Red Dots)
        $notifications = $query->get()
            ->groupBy(function($val) {
                // Use whichever column is actually populated in the database
                $dateValue = $val->scheduled_at ?? $val->due_date;
                return Carbon::parse($dateValue)->format('j');
            })
            ->map->count();

        return view('dashboard', compact(
            'date', 
            'notifications', 
            'daysInMonth', 
            'firstDayOfMonth'
        ));
    }
}