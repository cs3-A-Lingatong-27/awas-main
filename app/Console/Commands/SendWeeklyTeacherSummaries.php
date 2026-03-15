<?php

namespace App\Console\Commands;

use App\Mail\WeeklyAssessmentSummary;
use App\Models\Assessment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendWeeklyTeacherSummaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-weekly-teacher-summaries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send weekly teacher assessment summaries (Monday to Friday)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $weekStart = now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $weekEnd = $weekStart->copy()->addDays(4)->endOfDay(); // Monday-Friday

        $teachers = User::where('role', 'teacher')->get();
        $sentCount = 0;
        $failedCount = 0;

        foreach ($teachers as $teacher) {
            $assignedGrades = is_array($teacher->assigned_grades)
                ? array_map('intval', $teacher->assigned_grades)
                : (json_decode((string) $teacher->assigned_grades, true) ?? []);
            $assignedSubjects = is_array($teacher->assigned_subjects)
                ? array_values(array_unique($teacher->assigned_subjects))
                : (json_decode((string) $teacher->assigned_subjects, true) ?? []);
            $assignedSections = $this->parseAssignedSections((string) $teacher->section);

            $assessments = Assessment::where('user_id', $teacher->id)
                ->whereBetween('scheduled_at', [$weekStart, $weekEnd])
                ->orderBy('scheduled_at', 'asc')
                ->get();

            $handledClasses = $this->buildHandledClasses($assignedGrades, $assignedSections);

            $classesWithAssessments = $assessments
                ->map(function (Assessment $assessment) {
                    $section = $assessment->section ?: $this->extractSection((string) $assessment->description) ?: 'All Sections';
                    return 'Grade ' . $assessment->grade_level . ' - ' . $section;
                })
                ->unique()
                ->values();

            $scopeAssessments = Assessment::query()
                ->whereBetween('scheduled_at', [$weekStart, $weekEnd])
                ->whereIn('grade_level', $assignedGrades ?: [-1])
                ->when(!empty($assignedSections), function ($query) use ($assignedSections) {
                    $query->where(function ($q) use ($assignedSections) {
                        $q->whereNull('section')->orWhereIn('section', $assignedSections);
                    });
                })
                ->orderBy('scheduled_at', 'asc')
                ->get()
                ->filter(function (Assessment $assessment) use ($assignedSubjects) {
                    if (empty($assignedSubjects)) {
                        return true;
                    }
                    $subject = $this->extractSubject((string) $assessment->description);
                    return in_array($subject, $assignedSubjects, true);
                })
                ->values();

            $scopeAssessmentList = $scopeAssessments->map(function (Assessment $assessment) {
                $subject = $this->extractSubject((string) $assessment->description);
                $section = $assessment->section ?: $this->extractSection((string) $assessment->description) ?: 'All Sections';
                return [
                    'title' => $assessment->title,
                    'type' => $assessment->type,
                    'grade_level' => $assessment->grade_level,
                    'section' => $section,
                    'subject' => $subject,
                    'scheduled_at' => $assessment->scheduled_at,
                ];
            });

            $groupedSummary = $assessments
                ->groupBy(function (Assessment $assessment) {
                    $subject = $this->extractSubject((string) $assessment->description);
                    $section = $assessment->section ?: $this->extractSection((string) $assessment->description) ?: 'All Sections';
                    return $subject . '||' . $section;
                })
                ->map(function ($items, $key) {
                    [$subject, $section] = explode('||', $key, 2);
                    return [
                        'subject' => $subject,
                        'section' => $section,
                        'items' => $items->map(function (Assessment $assessment) {
                            return [
                                'title' => $assessment->title,
                                'type' => $assessment->type,
                                'grade_level' => $assessment->grade_level,
                                'scheduled_at' => $assessment->scheduled_at,
                            ];
                        })->values(),
                    ];
                })
                ->values();

            try {
                Mail::to($teacher->email)->send(
                    new WeeklyAssessmentSummary(
                        groupedSummary: $groupedSummary,
                        handledClasses: $handledClasses,
                        classesWithAssessments: $classesWithAssessments,
                        assignedSubjects: collect($assignedSubjects)->values(),
                        scopeAssessmentList: $scopeAssessmentList,
                        teacherName: $teacher->name,
                        weekStart: $weekStart->copy(),
                        weekEnd: $weekEnd->copy()
                    )
                );
                $sentCount++;
            } catch (Throwable $e) {
                $failedCount++;
                $this->warn("Failed: {$teacher->email} ({$e->getMessage()})");
            }

            // Keep SMTP send rate low to avoid provider throttling on testing plans.
            usleep(1200000);
        }

        $this->info("Weekly summaries sent: {$sentCount}, failed: {$failedCount}");
        return self::SUCCESS;
    }

    private function extractSubject(string $description): string
    {
        if (preg_match('/Subject:\s*([^|]+)/i', $description, $matches) === 1) {
            return trim($matches[1]);
        }

        return 'Unspecified Subject';
    }

    private function extractSection(string $description): ?string
    {
        if (preg_match('/Section:\s*([^|]+)/i', $description, $matches) === 1) {
            return trim($matches[1]);
        }

        return null;
    }

    private function parseAssignedSections(string $raw): array
    {
        return collect(explode(',', $raw))
            ->map(fn(string $section) => trim($section))
            ->filter()
            ->values()
            ->all();
    }

    private function buildHandledClasses(array $assignedGrades, array $assignedSections): array
    {
        $gradeSectionMap = [
            7 => ['Opal', 'Turquoise', 'Aquamarine', 'Sapphire'],
            8 => ['Anthurium', 'Carnation', 'Daffodil', 'Sunflower'],
            9 => ['Calcium', 'Lithium', 'Barium', 'Sodium'],
            10 => ['Graviton', 'Proton', 'Neutron', 'Electron'],
            11 => ['Mars', 'Mercury', 'Venus'],
            12 => ['Orosa', 'Del Mundo', 'Zara'],
        ];

        $classes = [];

        foreach ($assignedGrades as $grade) {
            $validSections = $gradeSectionMap[(int) $grade] ?? [];
            $gradeSections = empty($assignedSections)
                ? []
                : array_values(array_intersect($validSections, $assignedSections));

            if (empty($gradeSections)) {
                $classes[] = 'Grade ' . (int) $grade . ' - All Sections';
                continue;
            }

            foreach ($gradeSections as $section) {
                $classes[] = 'Grade ' . (int) $grade . ' - ' . $section;
            }
        }

        return array_values(array_unique($classes));
    }
}
