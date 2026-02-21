<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use Carbon\Carbon;

class AssessmentController extends Controller
{
    public function destroy(\App\Models\Assessment $assessment)
{
    // Optional: Add a check if the user is allowed to delete this
    $assessment->delete();

    return response()->json(['success' => true]);
}
    public function store(Request $request)
    {
        // 1. Validate the teacher's input
$request->validate([
    'title' => 'required|string|max:255',
    'type' => 'required',
    'due_date' => 'required|date',
    'due_time' => 'required',
    'grade_level' => 'required|integer', // Added
    'subject' => 'required|string',      // Added
]);

        // 2. Combine date and time into one "scheduled_at" field
        $scheduledAt = $request->due_date . ' ' . $request->due_time;

        // 3. THE ALGORITHM: Conflict Detection (Section 1.1 of your paper)
        // Count assessments already scheduled for that day
// THE ALGORITHM: Section-Specific Conflict Detection
$count = Assessment::whereDate('scheduled_at', $request->due_date)
    ->where('grade_level', $request->grade_level) // Only count for the same grade
    // ->where('section', $request->section) // Add this if you want section-specific
    ->count();

if ($count >= 3) {
    return back()->with('error', "Conflict! Grade {$request->grade_level} already has $count assessments on this day.");
}
Assessment::create([
    'title'        => $request->title,
    'type'         => $request->type,
    'scheduled_at' => $request->due_date . ' ' . $request->due_time,
    'subject_id' => null, // You can change this to match your real subjects later
    'description'  => "Grade " . $request->grade_level . " - " . $request->subject, // Temporary way to save it
]);

        return back()->with('success', 'Assessment scheduled and cataloged successfully!');
    }
}   