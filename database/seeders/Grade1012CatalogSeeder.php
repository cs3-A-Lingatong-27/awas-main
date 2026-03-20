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

        foreach ([7, 8, 9, 10, 11, 12] as $gradeLevel) {
            DB::table('grades')->updateOrInsert(
                ['grade_level' => $gradeLevel],
                ['name' => 'Grade ' . $gradeLevel, 'updated_at' => $now, 'created_at' => $now]
            );
        }

        $sections = [
            7 => ['Opal', 'Turquoise', 'Aquamarine', 'Sapphire'],
            8 => ['Anthurium', 'Carnation', 'Daffodil', 'Sunflower'],
            9 => ['Calcium', 'Lithium', 'Barium', 'Sodium'],
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
                'assigned_grades' => [7, 8, 9, 10, 11, 12],
                'assigned_subjects' => [],
                'section' => '',
            ]
        );

        $subjects = [
            // Grade 8 core
            ['name' => 'Biology 1', 'type' => 'core', 'grade_level_start' => 8, 'grade_level_end' => 8],
            ['name' => 'Chemistry 1', 'type' => 'core', 'grade_level_start' => 8, 'grade_level_end' => 8],
            ['name' => 'Physics 1', 'type' => 'core', 'grade_level_start' => 8, 'grade_level_end' => 8],
            ['name' => 'Mathematics 2', 'type' => 'core', 'grade_level_start' => 8, 'grade_level_end' => 8],
            ['name' => 'Mathematics 3', 'type' => 'core', 'grade_level_start' => 8, 'grade_level_end' => 8],
            ['name' => 'Earth Science', 'type' => 'core', 'grade_level_start' => 8, 'grade_level_end' => 8],
            ['name' => 'English 2', 'type' => 'core', 'grade_level_start' => 8, 'grade_level_end' => 8],
            ['name' => 'Filipino 2', 'type' => 'core', 'grade_level_start' => 8, 'grade_level_end' => 8],
            ['name' => 'Social Science 2', 'type' => 'core', 'grade_level_start' => 8, 'grade_level_end' => 8],
            ['name' => 'Physical Education 2', 'type' => 'core', 'grade_level_start' => 8, 'grade_level_end' => 8],
            ['name' => 'Health 2', 'type' => 'core', 'grade_level_start' => 8, 'grade_level_end' => 8],
            ['name' => 'Music 2', 'type' => 'core', 'grade_level_start' => 8, 'grade_level_end' => 8],
            ['name' => 'Values Education 2', 'type' => 'core', 'grade_level_start' => 8, 'grade_level_end' => 8],
            ['name' => 'AdTech 2', 'type' => 'core', 'grade_level_start' => 8, 'grade_level_end' => 8],
            ['name' => 'Computer Science 2', 'type' => 'core', 'grade_level_start' => 8, 'grade_level_end' => 8],

            // Grade 9 core
            ['name' => 'Biology 1', 'type' => 'core', 'grade_level_start' => 9, 'grade_level_end' => 9],
            ['name' => 'Chemistry 1', 'type' => 'core', 'grade_level_start' => 9, 'grade_level_end' => 9],
            ['name' => 'Physics 1', 'type' => 'core', 'grade_level_start' => 9, 'grade_level_end' => 9],
            ['name' => 'Mathematics 3', 'type' => 'core', 'grade_level_start' => 9, 'grade_level_end' => 9],
            ['name' => 'English 3', 'type' => 'core', 'grade_level_start' => 9, 'grade_level_end' => 9],
            ['name' => 'Filipino 3', 'type' => 'core', 'grade_level_start' => 9, 'grade_level_end' => 9],
            ['name' => 'Social Science 3', 'type' => 'core', 'grade_level_start' => 9, 'grade_level_end' => 9],
            ['name' => 'Physical Education 3', 'type' => 'core', 'grade_level_start' => 9, 'grade_level_end' => 9],
            ['name' => 'Health 3', 'type' => 'core', 'grade_level_start' => 9, 'grade_level_end' => 9],
            ['name' => 'Music 3', 'type' => 'core', 'grade_level_start' => 9, 'grade_level_end' => 9],
            ['name' => 'Values Education 3', 'type' => 'core', 'grade_level_start' => 9, 'grade_level_end' => 9],
            ['name' => 'Statistics 1', 'type' => 'core', 'grade_level_start' => 9, 'grade_level_end' => 9],
            ['name' => 'Computer Science 3', 'type' => 'core', 'grade_level_start' => 9, 'grade_level_end' => 9],

            // Grade 10 core
            ['name' => 'Biology 2', 'type' => 'core', 'grade_level_start' => 10, 'grade_level_end' => 10],
            ['name' => 'Chemistry 2', 'type' => 'core', 'grade_level_start' => 10, 'grade_level_end' => 10],
            ['name' => 'Physics 2', 'type' => 'core', 'grade_level_start' => 10, 'grade_level_end' => 10],
            ['name' => 'Mathematics 4', 'type' => 'core', 'grade_level_start' => 10, 'grade_level_end' => 10],
            ['name' => 'English 4', 'type' => 'core', 'grade_level_start' => 10, 'grade_level_end' => 10],
            ['name' => 'Filipino 4', 'type' => 'core', 'grade_level_start' => 10, 'grade_level_end' => 10],
            ['name' => 'Social Science 4', 'type' => 'core', 'grade_level_start' => 10, 'grade_level_end' => 10],
            ['name' => 'Physical Education 4', 'type' => 'core', 'grade_level_start' => 10, 'grade_level_end' => 10],
            ['name' => 'Health 4', 'type' => 'core', 'grade_level_start' => 10, 'grade_level_end' => 10],
            ['name' => 'Music 4', 'type' => 'core', 'grade_level_start' => 10, 'grade_level_end' => 10],
            ['name' => 'Values Education 4', 'type' => 'core', 'grade_level_start' => 10, 'grade_level_end' => 10],
            ['name' => 'STEM Research 1', 'type' => 'core', 'grade_level_start' => 10, 'grade_level_end' => 10],
            ['name' => 'Computer Science 4', 'type' => 'core', 'grade_level_start' => 10, 'grade_level_end' => 10],

            // Grade 10 electives
            ['name' => 'Philippine Biodiversity (AYP)', 'type' => 'elective', 'grade_level_start' => 10, 'grade_level_end' => 10],
            ['name' => 'Microbiology and Basic Molecular Techniques', 'type' => 'elective', 'grade_level_start' => 10, 'grade_level_end' => 10],
            ['name' => 'Data Science', 'type' => 'elective', 'grade_level_start' => 10, 'grade_level_end' => 10],
            ['name' => 'Field Sampling Techniques', 'type' => 'elective', 'grade_level_start' => 10, 'grade_level_end' => 10],
            ['name' => 'Intellectual Property Rights', 'type' => 'elective', 'grade_level_start' => 10, 'grade_level_end' => 10],

            // Grade 11 core
            ['name' => 'Mathematics 5', 'type' => 'core', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'English 5', 'type' => 'core', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Filipino 5', 'type' => 'core', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Social Science 5', 'type' => 'core', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'STEM Research 2', 'type' => 'core', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Computer Science 5', 'type' => 'core', 'grade_level_start' => 11, 'grade_level_end' => 12],

            // Grade 11 electives
            ['name' => 'Engineering', 'type' => 'elective', 'grade_level_start' => 11, 'grade_level_end' => 12],
            ['name' => 'Design and Make Technology', 'type' => 'elective', 'grade_level_start' => 11, 'grade_level_end' => 12],
            ['name' => 'Agriculture', 'type' => 'elective', 'grade_level_start' => 11, 'grade_level_end' => 12],
            ['name' => 'Biology 3 Elective', 'type' => 'elective', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Chemistry 3 Elective Class 1', 'type' => 'elective', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Chemistry 3 Elective Class 2', 'type' => 'elective', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Physics 3 Elective', 'type' => 'elective', 'grade_level_start' => 11, 'grade_level_end' => 11],

            // Grade 11 science cores
            ['name' => 'Biology 3 Class 1', 'type' => 'science_core', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Biology 3 Class 2', 'type' => 'science_core', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Chemistry 3 Class 1', 'type' => 'science_core', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Chemistry 3 Class 2', 'type' => 'science_core', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Physics 3 Class 1', 'type' => 'science_core', 'grade_level_start' => 11, 'grade_level_end' => 11],
            ['name' => 'Physics 3 Class 2', 'type' => 'science_core', 'grade_level_start' => 11, 'grade_level_end' => 11],

            // Grade 12 core
            ['name' => 'Mathematics 6', 'type' => 'core', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'English 6', 'type' => 'core', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Filipino 6', 'type' => 'core', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Social Science 6', 'type' => 'core', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'STEM Research 3', 'type' => 'core', 'grade_level_start' => 12, 'grade_level_end' => 12],

            // Grade 12 electives
            ['name' => 'Biology 4 Elective', 'type' => 'elective', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Chemistry 4 Elective Class 1', 'type' => 'elective', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Chemistry 4 Elective Class 2', 'type' => 'elective', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Physics 4 Elective', 'type' => 'elective', 'grade_level_start' => 12, 'grade_level_end' => 12],

            // Grade 12 science cores
            ['name' => 'Biology 4 Class 1', 'type' => 'science_core', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Biology 4 Class 2', 'type' => 'science_core', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Chemistry 4 Class 1', 'type' => 'science_core', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Chemistry 4 Class 2', 'type' => 'science_core', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Physics 4 Class 1', 'type' => 'science_core', 'grade_level_start' => 12, 'grade_level_end' => 12],
            ['name' => 'Physics 4 Class 2', 'type' => 'science_core', 'grade_level_start' => 12, 'grade_level_end' => 12],
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
