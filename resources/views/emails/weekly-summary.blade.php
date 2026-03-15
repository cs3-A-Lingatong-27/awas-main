<h1>Hello, {{ $teacherName }}!</h1>
<p>
    Here is your weekly assessment summary for
    {{ \Carbon\Carbon::parse($weekStart)->format('M d, Y') }}
    to
    {{ \Carbon\Carbon::parse($weekEnd)->format('M d, Y') }}
    (Monday-Friday).
</p>

<h2>1) Classes You Handle (Assigned)</h2>
@if(collect($handledClasses)->isEmpty())
    <p>No assigned classes found.</p>
@else
    <ul>
        @foreach($handledClasses as $classLine)
            <li>{{ $classLine }}</li>
        @endforeach
    </ul>
@endif

<h2>2) Classes Where You Have Assessments This Week</h2>
@if(collect($classesWithAssessments)->isEmpty())
    <p>No classes with assessments this week.</p>
@else
    <ul>
        @foreach($classesWithAssessments as $classLine)
            <li>{{ $classLine }}</li>
        @endforeach
    </ul>
@endif

<h2>3) Your Assigned Subjects</h2>
@if(collect($assignedSubjects)->isEmpty())
    <p>No assigned subjects found.</p>
@else
    <ul>
        @foreach($assignedSubjects as $subject)
            <li>{{ $subject }}</li>
        @endforeach
    </ul>
@endif

<h2>4) Assessments In Your Assigned Grade/Section Scope</h2>
@if(collect($scopeAssessmentList)->isEmpty())
    <p>No assessments found for your assigned grades/sections this week.</p>
@else
    <ul>
        @foreach($scopeAssessmentList as $item)
            <li>
                <strong>{{ $item['title'] }}</strong> ({{ $item['type'] }})<br>
                Subject: {{ $item['subject'] }}<br>
                Class: Grade {{ $item['grade_level'] }} - {{ $item['section'] }}<br>
                Date: {{ \Carbon\Carbon::parse($item['scheduled_at'])->format('D, M d, Y h:i A') }}
            </li>
        @endforeach
    </ul>
@endif

<h2>5) Grouped Summary (By Subject/Class)</h2>
@if($groupedSummary->isEmpty())
    <p>No assessments are scheduled for your classes this week.</p>
@else
    @foreach($groupedSummary as $group)
        <h3 style="margin-bottom: 4px;">
            Subject: {{ $group['subject'] }} | Class: {{ $group['section'] }}
        </h3>
        <ul style="margin-top: 0;">
            @foreach($group['items'] as $item)
                <li>
                    <strong>{{ $item['title'] }}</strong> ({{ $item['type'] }})<br>
                    Date: {{ \Carbon\Carbon::parse($item['scheduled_at'])->format('D, M d, Y h:i A') }}<br>
                    Grade Level: {{ $item['grade_level'] }}
                </li>
            @endforeach
        </ul>
    @endforeach
@endif
