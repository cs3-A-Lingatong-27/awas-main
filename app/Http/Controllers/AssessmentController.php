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
            8 => [
                'Biology 1',
                'Chemistry 1',
                'Physics 1',
                'Mathematics 2',
                'Mathematics 3',
                'Earth Science',
                'English 2',
                'Filipino 2',
                'Social Science 2',
                'Physical Education 2',
                'Health 2',
                'Music 2',
                'Values Education 2',
                'AdTech 2',
                'Computer Science 2',
            ],
            9 => [
                'Biology 1',
                'Chemistry 1',
                'Physics 1',
                'Mathematics 3',
                'English 3',
                'Filipino 3',
                'Social Science 3',
                'Physical Education 3',
                'Health 3',
                'Music 3',
                'Values Education 3',
                'Statistics 1',
                'Computer Science 3',
            ],
            10 => [
                'Biology 2',
                'Chemistry 2',
                'Physics 2',
                'Mathematics 4',
                'English 4',
                'Filipino 4',
                'Social Science 4',
                'Physical Education 4',
                'Health 4',
                'Music 4',
                'Values Education 4',
                'STEM Research 1',
                'Computer Science 4',
                'Philippine Biodiversity (AYP)',
                'Microbiology and Basic Molecular Techniques',
                'Data Science',
                'Field Sampling Techniques',
                'Intellectual Property Rights',
            ],
            11 => [
                'Biology 3 Class 1',
                'Biology 3 Class 2',
                'Chemistry 3 Class 1',
                'Chemistry 3 Class 2',
                'Physics 3 Class 1',
                'Physics 3 Class 2',
                'Mathematics 5',
                'English 5',
                'Filipino 5',
                'Social Science 5',
                'STEM Research 2',
                'Computer Science 5',
                'Engineering',
                'Design and Make Technology',
                'Agriculture',
                'Biology 3 Elective',
                'Chemistry 3 Elective Class 1',
                'Chemistry 3 Elective Class 2',
                'Physics 3 Elective',
            ],
            12 => [
                'Biology 4 Class 1',
                'Biology 4 Class 2',
                'Chemistry 4 Class 1',
                'Chemistry 4 Class 2',
                'Physics 4 Class 1',
                'Physics 4 Class 2',
                'Mathematics 6',
                'English 6',
                'Filipino 6',
                'Social Science 6',
                'STEM Research 3',
                'Computer Science 5',
                'Engineering',
                'Design and Make Technology',
                'Agriculture',
                'Biology 4 Elective',
                'Chemistry 4 Elective Class 1',
                'Chemistry 4 Elective Class 2',
                'Physics 4 Elective',
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
            'section' => 'nullable|string|max:255',
        ]);

        $date = $request->due_date;
        $type = $request->type === 'Alternative Assessment' ? 'Alternative Assessment (AA)' : $request->type;
        $grade = (int) $request->grade_level;
        $sectionInput = $request->filled('section') ? trim((string) $request->section) : null;
        $sectionList = $sectionInput
            ? array_values(array_filter(array_map('trim', explode(',', $sectionInput))))
            : [];
        $subjectName = $request->subject;

        $rescheduleId = $request->input('reschedule_assessment_id');
        $rescheduleAssessment = null;
        if ($rescheduleId) {
            $rescheduleAssessment = Assessment::where('id', $rescheduleId)
                ->where('user_id', $user->id)
                ->firstOrFail();
            $type = $rescheduleAssessment->type;
            $grade = (int) $rescheduleAssessment->grade_level;
            $sectionInput = $rescheduleAssessment->section;
            $sectionList = $sectionInput
                ? array_values(array_filter(array_map('trim', explode(',', (string) $sectionInput))))
                : [];
            $subjectName = $rescheduleAssessment->subject ? $rescheduleAssessment->subject->name : $subjectName;
        }

        if (!in_array($grade, $allowedGrades) || !in_array($subjectName, $allowedSubjects)) {
            return back()->with('error', "Unauthorized: You are not assigned to this Grade or Subject.");
        }

        $requestedGrade = $grade;

        $gradeSectionMap = [
            7 => ['Opal', 'Turquoise', 'Aquamarine', 'Sapphire'],
            8 => ['Anthurium', 'Carnation', 'Daffodil', 'Sunflower'],
            9 => ['Calcium', 'Lithium', 'Barium', 'Sodium'],
            10 => ['Graviton', 'Proton', 'Neutron', 'Electron'],
            11 => ['Mars', 'Mercury', 'Venus'],
            12 => ['Orosa', 'Del Mundo', 'Zara'],
        ];

        $subjectMeta = Subject::where('name', $subjectName)
            ->where('grade_level_start', '<=', $requestedGrade)
            ->where('grade_level_end', '>=', $requestedGrade)
            ->first();

        $isScienceCoreExempt = $subjectMeta && $subjectMeta->type === 'science_core' && in_array($requestedGrade, [11, 12], true);
        $isElectiveExempt = $subjectMeta && $subjectMeta->type === 'elective' && $requestedGrade >= 10 && $requestedGrade <= 12;
        $sectionExempt = $isScienceCoreExempt || $isElectiveExempt;

        if ($sectionExempt) {
            $sectionInput = null;
            $sectionList = [];
        } else {
            if (empty($sectionList)) {
                return back()->with('error', "Please select a section for this subject.");
            }
            if (isset($gradeSectionMap[$requestedGrade])) {
                $invalidSections = array_diff($sectionList, $gradeSectionMap[$requestedGrade]);
                if (!empty($invalidSections)) {
                    return back()->with('error', "Invalid section for Grade {$requestedGrade}.");
                }
            }

            if ($user->role === 'teacher') {
                $teacherSections = is_array($user->section)
                    ? $user->section
                    : array_values(array_filter(array_map('trim', explode(',', (string) $user->section))));
                if (!empty($teacherSections)) {
                    $invalidTeacherSections = array_diff($sectionList, $teacherSections);
                    if (!empty($invalidTeacherSections)) {
                        return back()->with('error', "Unauthorized: One or more sections are not assigned to you.");
                    }
                }
            }

        }
        $section = $sectionInput ? implode(', ', $sectionList) : null;

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

        if ($rescheduleAssessment) {
            $rescheduleAssessment->scheduled_at = $date . ' ' . $request->due_time;
            $rescheduleAssessment->due_date = $date . ' ' . $request->due_time;
            $rescheduleAssessment->confirmation_status = 'scheduled';
            $rescheduleAssessment->confirmation_requested_at = null;
            $rescheduleAssessment->conducted_at = null;
            $rescheduleAssessment->save();

            return back()->with('success', 'Assessment rescheduled successfully!');
        }

        // 4. Save
        Assessment::create([
            'title'        => $request->title,
            'type'         => $type,
            'scheduled_at' => $date . ' ' . $request->due_time,
            'due_date'     => $date . ' ' . $request->due_time,
            'grade_level'  => $grade,
            'section'      => $section,
            'subject_id'   => null, 
            'description'  => "Subject: " . $subjectName . ($section ? " | Section: {$section}" : ''),
            'user_id'      => $user->id, 
            'confirmation_status' => 'scheduled',
        ]);

        return back()->with('success', 'Assessment scheduled successfully!');
    }
}
