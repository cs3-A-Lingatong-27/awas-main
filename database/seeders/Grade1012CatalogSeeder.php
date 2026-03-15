<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Grade1012CatalogSeeder extends Seeder
{
    /**
     * Seed actual Grade 10-12 catalog data:
     * - Grade records
     * - Grade sections
     * - Elective and science core subjects
     */
    public function run(): void
    {
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

        $catalogPassword = env('CATALOG_TEACHER_PASSWORD');
        if (!is_string($catalogPassword) || trim($catalogPassword) === '') {
            $catalogPassword = Str::random(24);
        }

        $catalogTeacher = User::firstOrCreate(
            ['email' => 'subject.catalog@crc.pshs.edu.ph'],
            [
                'name' => 'Subject Catalog',
                'password' => Hash::make($catalogPassword),
                'role' => 'teacher',
                'assigned_grades' => [10, 11, 12],
                'assigned_subjects' => [],
                'section' => '',
            ]
        );

        $subjects = [
            // Grade 10 electives
            ['name' => 'Philippine Biodiversity', 'type' => 'elective', 'grade_level_start' => 10, 'grade_level_end' => 10],
            ['name' => 'Field Sampling', 'type' => 'elective', 'grade_level_start' => 10, 'grade_level_end' => 10],
            ['name' => 'Astronomy', 'type' => 'elective', 'grade_level_start' => 10, 'grade_level_end' => 10],
            ['name' => 'Philosophy of Science', 'type' => 'elective', 'grade_level_start' => 10, 'grade_level_end' => 10],

            // Grade 11 electives
            ['name' => 'Biology 3 Level 1', 'type' => 'elective', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Biology 3 Level 2', 'type' => 'elective', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Chemistry 3 Level 1', 'type' => 'elective', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Chemistry 3 Level 2', 'type' => 'elective', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Physics 3 Level 1', 'type' => 'elective', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Physics 3 Level 2', 'type' => 'elective', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Computer Science 5 Level 1', 'type' => 'elective', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Computer Science 5 Level 2', 'type' => 'elective', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Design and Make Technology 1 Level 1', 'type' => 'elective', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Design and Make Technology 1 Level 2', 'type' => 'elective', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Engineering 1 Level 1', 'type' => 'elective', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Engineering 1 Level 2', 'type' => 'elective', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Agriculture 1 Level 1', 'type' => 'elective', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Agriculture 1 Level 2', 'type' => 'elective', 'grade_level_start' => 11, 'grade_level_end' => 11],

            // Grade 11 science cores
            ['name' => 'Physics 3 Level 1', 'type' => 'science_core', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Physics 3 Level 2', 'type' => 'science_core', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Biology 3 Level 1', 'type' => 'science_core', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Biology 3 Level 2', 'type' => 'science_core', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Chemistry 3 Level 1', 'type' => 'science_core', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Chemistry 3 Level 2', 'type' => 'science_core', 'grade_level_start' => 11, 'grade_level_end' => 11],

            // Grade 12 electives
            ['name' => 'Biology 3 Level 1', 'type' => 'elective', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Biology 3 Level 2', 'type' => 'elective', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Chemistry 3 Level 1', 'type' => 'elective', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Chemistry 3 Level 2', 'type' => 'elective', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Physics 3 Level 1', 'type' => 'elective', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Physics 3 Level 2', 'type' => 'elective', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Computer Science 5 Level 1', 'type' => 'elective', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Computer Science 5 Level 2', 'type' => 'elective', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Design and Make Technology 1 Level 1', 'type' => 'elective', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Design and Make Technology 1 Level 2', 'type' => 'elective', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Engineering 1 Level 1', 'type' => 'elective', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Engineering 1 Level 2', 'type' => 'elective', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Agriculture 1 Level 1', 'type' => 'elective', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Agriculture 1 Level 2', 'type' => 'elective', 'grade_level_start' => 12, 'grade_level_end' => 12],

            // Grade 12 science cores
            ['name' => 'Physics 4 Level 1', 'type' => 'science_core', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Physics 4 Level 2', 'type' => 'science_core', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Biology 4 Level 1', 'type' => 'science_core', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Biology 4 Level 2', 'type' => 'science_core', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Chemistry 4 Level 1', 'type' => 'science_core', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Chemistry 4 Level 2', 'type' => 'science_core', 'grade_level_start' => 12, 'grade_level_end' => 12],
        ];

        foreach ($subjects as $subject) {
            DB::table('subjects')->updateOrInsert(
                [
                    'name' => $subject['name'],
                    'type' => $subject['type'],
                    'grade_level_start' => $subject['grade_level_start'],
                    'grade_level_end' => $subject['grade_level_end'],
                ],
                [
                    'teacher_id' => $catalogTeacher->id,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        DB::table('subjects')
            ->where('name', 'like', '%Placeholder%')
            ->delete();

        DB::table('users')
            ->where('email', 'placeholder.subjects@crc.pshs.edu.ph')
            ->delete();
    }
}
