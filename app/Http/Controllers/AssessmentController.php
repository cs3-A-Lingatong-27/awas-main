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
        ]);

        // 2. Combine date and time into one "scheduled_at" field
        $scheduledAt = $request->due_date . ' ' . $request->due_time;

        // 3. THE ALGORITHM: Conflict Detection (Section 1.1 of your paper)
        // Count assessments already scheduled for that day
        $count = Assessment::whereDate('scheduled_at', $request->due_date)->count();

        // PSHS Policy: Let's say max 3 assessments per day
        if ($count >= 3) {
            return back()->with('error', "Conflict! There are already $count assessments on this day.");
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