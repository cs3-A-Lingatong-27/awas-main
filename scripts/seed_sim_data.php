<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Assessment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$teachers = [
    [
        'name' => 'SIM Teacher G7',
        'email' => 'sim.teacher.g7@crc.pshs.edu.ph',
        'assigned_grades' => [7],
        'assigned_subjects' => ['Integrated Science 1', 'Mathematics 1', 'English 1'],
        'section' => 'Opal, Turquoise',
    ],
    [
        'name' => 'SIM Teacher G8',
        'email' => 'sim.teacher.g8@crc.pshs.edu.ph',
        'assigned_grades' => [8],
        'assigned_subjects' => ['Mathematics 2', 'English 2', 'Social Science 2'],
        'section' => 'Anthurium, Carnation',
    ],
    [
        'name' => 'SIM Teacher G9',
        'email' => 'sim.teacher.g9@crc.pshs.edu.ph',
        'assigned_grades' => [9],
        'assigned_subjects' => ['Biology 3', 'Physics 3', 'English 3'],
        'section' => 'Calcium, Lithium',
    ],
];

foreach ($teachers as $t) {
    User::updateOrCreate(
        ['email' => $t['email']],
        [
            'name' => $t['name'],
            'password' => Hash::make('PSHS_2026'),
            'role' => 'teacher',
            'assigned_grades' => $t['assigned_grades'],
            'assigned_subjects' => $t['assigned_subjects'],
            'section' => $t['section'],
        ]
    );
}

$students = [
    ['name' => 'SIM Student G7 Opal', 'email' => 'sim.student.g7.opal@crc.pshs.edu.ph', 'grade_level' => 7, 'section' => 'Opal'],
    ['name' => 'SIM Student G8 Anthurium', 'email' => 'sim.student.g8.anthurium@crc.pshs.edu.ph', 'grade_level' => 8, 'section' => 'Anthurium'],
    ['name' => 'SIM Student G9 Calcium', 'email' => 'sim.student.g9.calcium@crc.pshs.edu.ph', 'grade_level' => 9, 'section' => 'Calcium'],
];

foreach ($students as $s) {
    User::updateOrCreate(
        ['email' => $s['email']],
        [
            'name' => $s['name'],
            'password' => Hash::make('PSHS_2026'),
            'role' => 'student',
            'grade_level' => $s['grade_level'],
            'section' => $s['section'],
        ]
    );
}

$teacherG7Id = User::where('email', 'sim.teacher.g7@crc.pshs.edu.ph')->value('id');
$teacherG8Id = User::where('email', 'sim.teacher.g8@crc.pshs.edu.ph')->value('id');
$teacherG9Id = User::where('email', 'sim.teacher.g9@crc.pshs.edu.ph')->value('id');

Assessment::where('title', 'like', 'SIM-%')->delete();

$now = now();

$rows = [
    // 2026-03-09 Grade 7: FA x2, AA x1
    ['SIM-G7-FA-1', 'Formative Assessment', '2026-03-09 09:00:00', 7, 'Subject: Integrated Science 1', $teacherG7Id],
    ['SIM-G7-FA-2', 'Formative Assessment', '2026-03-09 13:00:00', 7, 'Subject: Mathematics 1', $teacherG7Id],
    ['SIM-G7-AA-1', 'Alternative Assessment (AA)', '2026-03-09 15:30:00', 7, 'Subject: English 1', $teacherG7Id],

    // 2026-03-10 Grade 8: FA x2, AA x1
    ['SIM-G8-FA-1', 'Formative Assessment', '2026-03-10 10:00:00', 8, 'Subject: Mathematics 2', $teacherG8Id],
    ['SIM-G8-FA-2', 'Formative Assessment', '2026-03-10 14:00:00', 8, 'Subject: English 2', $teacherG8Id],
    ['SIM-G8-AA-1', 'Alternative Assessment (AA)', '2026-03-10 16:00:00', 8, 'Subject: Social Science 2', $teacherG8Id],

    // 2026-03-11 Grade 9: FA x2, AA x1
    ['SIM-G9-FA-1', 'Formative Assessment', '2026-03-11 08:30:00', 9, 'Subject: Biology 3', $teacherG9Id],
    ['SIM-G9-FA-2', 'Formative Assessment', '2026-03-11 12:30:00', 9, 'Subject: Physics 3', $teacherG9Id],
    ['SIM-G9-AA-1', 'Alternative Assessment (AA)', '2026-03-11 15:00:00', 9, 'Subject: English 3', $teacherG9Id],

    // 2026-03-12 Grade 7: FA x2, AA x1
    ['SIM-G7-FA-3', 'Formative Assessment', '2026-03-12 09:30:00', 7, 'Subject: Integrated Science 1', $teacherG7Id],
    ['SIM-G7-FA-4', 'Formative Assessment', '2026-03-12 13:30:00', 7, 'Subject: Mathematics 1', $teacherG7Id],
    ['SIM-G7-AA-2', 'Alternative Assessment (AA)', '2026-03-12 16:00:00', 7, 'Subject: English 1', $teacherG7Id],

    // 2026-03-13 Grade 8: FA x2, AA x1
    ['SIM-G8-FA-3', 'Formative Assessment', '2026-03-13 09:00:00', 8, 'Subject: Mathematics 2', $teacherG8Id],
    ['SIM-G8-FA-4', 'Formative Assessment', '2026-03-13 13:00:00', 8, 'Subject: English 2', $teacherG8Id],
    ['SIM-G8-AA-2', 'Alternative Assessment (AA)', '2026-03-13 15:30:00', 8, 'Subject: Social Science 2', $teacherG8Id],
];

$payload = [];
foreach ($rows as [$title, $type, $scheduledAt, $grade, $description, $userId]) {
    $payload[] = [
        'title' => $title,
        'type' => $type,
        'scheduled_at' => $scheduledAt,
        'grade_level' => $grade,
        'description' => $description,
        'user_id' => $userId,
        'created_at' => $now,
        'updated_at' => $now,
    ];
}

DB::table('assessments')->insert($payload);

echo 'SIM_TEACHERS=' . User::where('email', 'like', 'sim.teacher.%')->count() . PHP_EOL;
echo 'SIM_STUDENTS=' . User::where('email', 'like', 'sim.student.%')->count() . PHP_EOL;
echo 'SIM_ASSESSMENTS=' . Assessment::where('title', 'like', 'SIM-%')->count() . PHP_EOL;
