<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Grade1012PlaceholdersSeeder extends Seeder
{
    /**
     * Seed placeholders for Grades 10-12:
     * - Grade records
     * - Grade sections
     * - Subject groups: Core (11-12), Elective (10-12)
     */
    public function run(): void
    {
        $teacher = User::firstOrCreate(
            ['email' => 'placeholder.subjects@crc.pshs.edu.ph'],
            [
                'name' => 'Placeholder Subject Teacher',
                'password' => Hash::make('PSHS_2026'),
                'role' => 'teacher',
                'assigned_grades' => [10, 11, 12],
                'assigned_subjects' => [
                    'Core Group 11-12 Placeholder A',
                    'Core Group 11-12 Placeholder B',
                    'Elective Group 10-12 Placeholder A',
                ],
                'section' => 'Graviton, Proton, Mars, Mercury, Orosa, Del Mundo',
            ]
        );

        $now = now();

        foreach ([10, 11, 12] as $gradeLevel) {
            DB::table('grades')->updateOrInsert(
                ['grade_level' => $gradeLevel],
                ['name' => 'Grade ' . $gradeLevel, 'updated_at' => $now, 'created_at' => $now]
            );
        }

        $sections = [
            10 => ['Graviton', 'Proton', 'Neutron', 'Electron'],
            11 => ['Mars', 'Mercury', 'Venus'],
            12 => ['Orosa', 'Del Mundo', 'Zara'],
        ];

        foreach ($sections as $gradeLevel => $gradeSections) {
            foreach ($gradeSections as $section) {
                DB::table('grade_sections')->updateOrInsert(
                    ['grade_level' => $gradeLevel, 'section' => $section],
                    ['updated_at' => $now, 'created_at' => $now]
                );
            }
        }

        $subjectGroups = [
            ['name' => 'Core Group 11-12 Placeholder A', 'type' => 'core', 'grade_level_start' => 11, 'grade_level_end' => 12],
            ['name' => 'Core Group 11-12 Placeholder B', 'type' => 'core', 'grade_level_start' => 11, 'grade_level_end' => 12],
            ['name' => 'Core Group 11-12 Placeholder C', 'type' => 'core', 'grade_level_start' => 11, 'grade_level_end' => 12],
            ['name' => 'Elective Group 10-12 Placeholder A', 'type' => 'elective', 'grade_level_start' => 10, 'grade_level_end' => 12],
            ['name' => 'Elective Group 10-12 Placeholder B', 'type' => 'elective', 'grade_level_start' => 10, 'grade_level_end' => 12],
            ['name' => 'Elective Group 10-12 Placeholder C', 'type' => 'elective', 'grade_level_start' => 10, 'grade_level_end' => 12],
        ];

        foreach ($subjectGroups as $subject) {
            DB::table('subjects')->updateOrInsert(
                [
                    'name' => $subject['name'],
                    'type' => $subject['type'],
                    'grade_level_start' => $subject['grade_level_start'],
                    'grade_level_end' => $subject['grade_level_end'],
                ],
                [
                    'teacher_id' => $teacher->id,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}

