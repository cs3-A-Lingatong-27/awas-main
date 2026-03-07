<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\Subject;
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
    // Check if the logged-in user is an admin OR the owner of the assessment
    if (auth()->user()->role === 'admin' || $assessment->user_id === auth()->id()) {
        $assessment->delete();
        return response()->json(['success' => true]);
    }

    return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
}

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user->role === 'admin') {
            return back()->with('error', 'Admins can only view assessments in the calendar.');
        }

        $allowedGrades = is_array($user->assigned_grades) ? $user->assigned_grades : json_decode($user->assigned_grades, true) ?? [];
        $allowedSubjects = is_array($user->assigned_subjects) ? $user->assigned_subjects : json_decode($user->assigned_subjects, true) ?? [];
        $gradeSubjectMap = [
            7 => [
                'Integrated Science 1',
                'Mathematics 1',
                'English 1',
                'Filipino 1',
                'Social Science 1',
                'Physical Education 1',
                'Health 1',
                'Music 1',
                'Values Education 1',
                'AdTech 1',
                'Computer Science 1',
            ],
        ];
        // 1. Validate input
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'due_date' => 'required|date',
            'due_time' => 'required',
            'grade_level' => 'required|integer',
            'subject' => 'required|string',
            'section' => 'nullable|string|max:100',
        ]);
    

if (!in_array((int)$request->grade_level, $allowedGrades) || !in_array($request->subject, $allowedSubjects)) {
        return back()->with('error', "Unauthorized: You are not assigned to this Grade or Subject.");
    }

        $requestedGrade = (int) $request->grade_level;
        if (isset($gradeSubjectMap[$requestedGrade]) && !in_array($request->subject, $gradeSubjectMap[$requestedGrade], true)) {
            return back()->with('error', "Invalid subject for Grade {$requestedGrade}.");
        }

        $gradeSectionMap = [
            7 => ['Opal', 'Turquoise', 'Aquamarine', 'Sapphire'],
            8 => ['Anthurium', 'Carnation', 'Daffodil', 'Sunflower'],
            9 => ['Calcium', 'Lithium', 'Barium', 'Sodium'],
            10 => ['Graviton', 'Proton', 'Neutron', 'Electron'],
            11 => ['Mars', 'Mercury', 'Venus'],
            12 => ['Orosa', 'Del Mundo', 'Zara'],
        ];

        $section = $request->filled('section') ? trim((string) $request->section) : null;
        $subjectMeta = Subject::where('name', $request->subject)
            ->where('grade_level_start', '<=', $requestedGrade)
            ->where('grade_level_end', '>=', $requestedGrade)
            ->first();

        $isScienceCoreExempt = $subjectMeta && $subjectMeta->type === 'science_core' && in_array($requestedGrade, [11, 12], true);
        $isElectiveExempt = $subjectMeta && $subjectMeta->type === 'elective' && $requestedGrade >= 10 && $requestedGrade <= 12;
        $sectionExempt = $isScienceCoreExempt || $isElectiveExempt;

        if ($sectionExempt) {
            $section = null;
        } else {
            if (!$section) {
                return back()->with('error', "Please select a section for this subject.");
            }
            if (isset($gradeSectionMap[$requestedGrade]) && !in_array($section, $gradeSectionMap[$requestedGrade], true)) {
                return back()->with('error', "Invalid section for Grade {$requestedGrade}.");
            }
        }

        $date = $request->due_date;
        $type = $request->type === 'Alternative Assessment' ? 'Alternative Assessment (AA)' : $request->type;
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
                ->whereIn('type', ['Alternative Assessment (AA)', 'Alternative Assessment'])
                ->count();

            if ($aaCount >= 1) {
                return back()->with('error', "Conflict! Grade $grade already has 1 Alternative Assessment on this day.");
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
            'section'      => $section,
            'subject_id'   => null, 
            'description'  => "Subject: " . $request->subject . ($section ? " | Section: {$section}" : ''),
            'user_id'      => $user->id, 
        ]);

        return back()->with('success', 'Assessment scheduled successfully!');
    }
}
