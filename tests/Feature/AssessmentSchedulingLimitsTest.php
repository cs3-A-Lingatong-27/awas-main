<?php

use App\Models\Assessment;
use App\Models\User;

function teacherForScheduling(array $overrides = []): User
{
    return User::factory()->create(array_merge([
        'role' => 'teacher',
        'grade_level' => null,
        'section' => 'Opal',
        'assigned_grades' => [7],
        'assigned_subjects' => ['Mathematics 1', 'Integrated Science 1', 'English 1'],
    ], $overrides));
}

function makeAssessment(User $teacher, array $overrides = []): Assessment
{
    $scheduledAt = $overrides['scheduled_at'] ?? '2026-07-13 08:00:00';

    return Assessment::create(array_merge([
        'user_id' => $teacher->id,
        'grade_level' => 7,
        'section' => 'Opal',
        'type' => 'Formative Assessment',
        'title' => 'Existing Assessment',
        'description' => 'Subject: Mathematics 1 | Section: Opal',
        'scheduled_at' => $scheduledAt,
        'due_date' => $scheduledAt,
        'confirmation_status' => 'scheduled',
    ], $overrides));
}

test('backend blocks more than two assessments for the same grade section on one day', function () {
    $teacher = teacherForScheduling();
    $this->actingAs($teacher);

    makeAssessment($teacher, ['title' => 'First', 'scheduled_at' => '2026-07-13 08:00:00', 'due_date' => '2026-07-13 08:00:00']);
    makeAssessment($teacher, ['title' => 'Second', 'scheduled_at' => '2026-07-13 10:00:00', 'due_date' => '2026-07-13 10:00:00']);

    $response = $this->from(route('dashboard'))->post(route('assessments.store'), [
        'title' => 'Third',
        'type' => 'Formative Assessment',
        'due_date' => '2026-07-13',
        'due_time' => '13:00',
        'grade_level' => 7,
        'subject' => 'Mathematics 1',
        'section' => 'Opal',
    ]);

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('error', 'Conflict! Grade 7 (Opal) already has 2 assessments on this day.');
    expect(Assessment::where('grade_level', 7)->whereDate('scheduled_at', '2026-07-13')->count())->toBe(2);
});

test('backend blocks the sixth formative or alternative assessment in a grade week', function () {
    $teacher = teacherForScheduling(['section' => 'Opal, Turquoise, Aquamarine, Sapphire']);
    $this->actingAs($teacher);

    foreach ([
        ['2026-07-13 08:00:00', 'Opal'],
        ['2026-07-14 08:00:00', 'Opal'],
        ['2026-07-15 08:00:00', 'Turquoise'],
        ['2026-07-16 08:00:00', 'Aquamarine'],
        ['2026-07-17 08:00:00', 'Sapphire'],
    ] as [$scheduledAt, $section]) {
        makeAssessment($teacher, [
            'scheduled_at' => $scheduledAt,
            'due_date' => $scheduledAt,
            'section' => $section,
            'description' => "Subject: Mathematics 1 | Section: {$section}",
        ]);
    }

    $response = $this->from(route('dashboard'))->post(route('assessments.store'), [
        'title' => 'Sixth Weekly Assessment',
        'type' => 'Alternative Assessment (AA)',
        'due_date' => '2026-07-17',
        'due_time' => '13:00',
        'grade_level' => 7,
        'subject' => 'Mathematics 1',
        'section' => 'Opal',
    ]);

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('error', 'Conflict! Grade 7 already has 5 assessments this week.');
    expect(Assessment::where('grade_level', 7)->count())->toBe(5);
});

test('backend blocks a second long test 1 for the same teacher grade month', function () {
    $teacher = teacherForScheduling();
    $this->actingAs($teacher);

    makeAssessment($teacher, [
        'type' => 'Long Test 1 (Midterms)',
        'title' => 'Midterm 1',
        'scheduled_at' => '2026-07-13 08:00:00',
        'due_date' => '2026-07-13 08:00:00',
        'description' => 'Subject: Mathematics 1 | Section: Opal',
    ]);

    $response = $this->from(route('dashboard'))->post(route('assessments.store'), [
        'title' => 'Another Midterm',
        'type' => 'Long Test 1 (Midterms)',
        'due_date' => '2026-07-20',
        'due_time' => '09:00',
        'grade_level' => 7,
        'subject' => 'Mathematics 1',
        'section' => 'Opal',
    ]);

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('error', 'Only 1 Long Test 1 (Midterms) per month is allowed for Grade 7.');
    expect(Assessment::where('type', 'Long Test 1 (Midterms)')->count())->toBe(1);
});

test('conflict api reports a daily cap conflict before submit', function () {
    $teacher = teacherForScheduling();
    $this->actingAs($teacher);

    makeAssessment($teacher, ['scheduled_at' => '2026-07-13 08:00:00', 'due_date' => '2026-07-13 08:00:00']);
    makeAssessment($teacher, ['scheduled_at' => '2026-07-13 10:00:00', 'due_date' => '2026-07-13 10:00:00']);

    $response = $this->getJson('/api/check-conflict?date=2026-07-13&grade_level=7&type=Formative%20Assessment&section=Opal');

    $response->assertOk()
        ->assertJson([
            'status' => 'CRITICAL',
            'message' => 'Conflict! Grade 7 (Opal) already has 2 assessments on this day.',
        ]);
});
