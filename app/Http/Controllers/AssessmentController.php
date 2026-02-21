<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use Carbon\Carbon;

class AssessmentController extends Controller
{
    /**
     * Display assessments filtered by the teacher's assigned grade.
     */
public function index()
{
    $user = auth()->user();

    if ($user->role === 'teacher') {
        // Teachers see only the grades they are assigned to
        $grades = is_array($user->assigned_grades) ? $user->assigned_grades : json_decode($user->assigned_grades, true) ?? [];
        $assessments = Assessment::whereIn('grade_level', $grades)->orderBy('scheduled_at', 'asc')->get();
    } else {
        // Students see only THEIR specific grade level
        // Assuming students have a 'grade_level' column in the users table
        $assessments = Assessment::where('grade_level', $user->grade_level)->orderBy('scheduled_at', 'asc')->get();
    }

    return view('dashboard', compact('assessments'));
}

    public function destroy(Assessment $assessment)
    {
        // Security check: Only allow the creator or an admin to delete
        if (auth()->id() !== $assessment->user_id && auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $assessment->delete();
        return response()->json(['success' => true]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $allowedGrades = is_array($user->assigned_grades) ? $user->assigned_grades : json_decode($user->assigned_grades, true) ?? [];
        $allowedSubjects = is_array($user->assigned_subjects) ? $user->assigned_subjects : json_decode($user->assigned_subjects, true) ?? [];
        // 1. Validate input
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'due_date' => 'required|date',
            'due_time' => 'required',
            'grade_level' => 'required|integer',
            'subject' => 'required|string',
        ]);

if (!in_array((int)$request->grade_level, $allowedGrades) || !in_array($request->subject, $allowedSubjects)) {
        return back()->with('error', "Unauthorized: You are not assigned to this Grade or Subject.");
    }

        $date = $request->due_date;
        $type = $request->type;
        $grade = $request->grade_level;

        // 3. THE AWAS ALGORITHM (Conflict Detection)
        if ($type === 'Formative Assessment') {
            $faCount = Assessment::whereDate('scheduled_at', $date)
                ->where('grade_level', $grade)
                ->where('type', 'Formative Assessment')
                ->count();

            if ($faCount >= 2) {
                return back()->with('error', "Conflict! Grade $grade already has 2 Formative Assessments on this day.");
            }
        }

        if ($type === 'Alternative Assessment (AA)') {
            $aaCount = Assessment::whereDate('scheduled_at', $date)
                ->where('grade_level', $grade)
                ->where('type', 'Alternative Assessment (AA)')
                ->count();

            if ($aaCount >= 2) {
                return back()->with('error', "Conflict! Grade $grade already has 2 Alternative Assessments on this day.");
            }
        }

        // Admin-only Long Test check
        if ($type === 'Long Test' && $user->role !== 'admin') {
            return back()->with('error', "Only administrators can schedule Long Tests.");
        }

        // 4. Save
        Assessment::create([
            'title'        => $request->title,
            'type'         => $type,
            'scheduled_at' => $date . ' ' . $request->due_time,
            'grade_level'  => $grade,
            'subject_id'   => null, 
            'description'  => "Subject: " . $request->subject,
            'user_id'      => $user->id, 
        ]);

        return back()->with('success', 'Assessment scheduled successfully!');
    }
}