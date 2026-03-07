<h1>Hello, {{ $teacherName }}!</h1>
<p>Here are the assessments scheduled for your assigned grades next week:</p>

<ul>
    @foreach($assessments as $assessment)
        <li>
            <strong>{{ $assessment->title }}</strong> ({{ $assessment->type }}) <br>
            Date: {{ \Carbon\Carbon::parse($assessment->scheduled_at)->format('M d, Y') }} <br>
            Grade Level: {{ $assessment->grade_level }}
        </li>
    @endforeach
</ul>