<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/build-placeholder.js'])
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
</head>
<body class="antialiased text-slate-900" style="font-family: 'Plus Jakarta Sans', sans-serif;">
<div class="relative min-h-screen overflow-hidden bg-slate-100">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_10%_20%,#dbeafe_0%,transparent_35%),radial-gradient(circle_at_90%_15%,#fde68a_0%,transparent_30%),radial-gradient(circle_at_80%_85%,#bfdbfe_0%,transparent_30%)]"></div>
    <div class="relative mx-auto w-full max-w-[1500px] px-4 py-6 sm:px-6 lg:px-8">
@if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
        <p class="font-bold">Conflict Detected</p>
        <p>{{ session('error') }}</p>
    </div>
@endif
    
  @if(session('success'))
    <div id="successToast" class="toast-notification">
        <span class="icon">✅</span> {{ session('success') }}
    </div>

    <script>
        setTimeout(() => {
            const toast = document.getElementById('successToast');
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    </script>
@endif
    <div class="app auth-card overflow-hidden">
<header class="topbar flex items-center justify-between px-8 py-4 bg-slate-900 text-white shadow-lg">
    <div class="logo flex items-center gap-3">
    <img src="{{ asset('pshs.png') }}" alt="PSHS Logo" class="h-12 w-auto object-contain">
    
    <div class="logo-text">
        <div class="font-bold leading-tight uppercase tracking-wide text-white">
            Philippine Science High School
        </div>
        <div class="text-[10px] opacity-80 text-white">
            Caraga Region Campus in Butuan City
        </div>
    </div>
</div>

    <div class="flex items-center gap-8">
        <nav class="flex gap-6 font-semibold">
            <a href="{{ route('dashboard') }}" class="hover:text-blue-200 transition underline-offset-8 hover:underline">Dashboard</a>

            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.enrollment') }}" class="hover:text-blue-200 transition underline-offset-8 hover:underline">Enrollment</a>
            @endif
        </nav>

        <div class="top-actions">
            @auth
                <button class="bg-sky-700/40 hover:bg-sky-600/50 px-5 py-2 rounded-full border border-sky-300/30 transition flex items-center gap-2" onclick="openScholarPanel()">
                    <i class="fas fa-user text-blue-200"></i>
                    <span class="text-sm font-bold tracking-tight">
                        {{ ucfirst(auth()->user()->role) }}: {{ auth()->user()->name }}
                    </span>
                </button>
            @endauth
        </div>
    </div>

        </header>

<main class="main" style="display: flex; align-items: flex-start;">


    <div class="flex-1 w-full">
        
        <section id="calendar-tab" class="content-tab" style="display: block;">
            <section class="calendar-section">
                <div class="calendar-title">{{ $date->format('F Y') }}</div>
                <div class="calendar-legend">
                    <span class="legend-item"><span class="calendar-dot dot-alternative"></span> Alternative Assessment</span>
                    <span class="legend-item"><span class="calendar-dot dot-formative"></span> Formative Assessment</span>
                    <span class="legend-item"><span class="calendar-dot dot-longtest"></span> Long Test</span>
                </div>
                
                <div class="calendar-nav" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0;">
                    <form action="{{ route('dashboard') }}" method="GET" class="flex gap-2">
                        <select name="month" onchange="this.form.submit()" class="border p-1 rounded">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $date->month == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endforeach
                        </select>
                        <select name="year" onchange="this.form.submit()" class="border p-1 rounded">
                            @for($y = 2024; $y <= 2030; $y++)
                                <option value="{{ $y }}" {{ $date->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </form>
                    <h2 class="text-xl font-bold">{{ $date->format('F Y') }}</h2>
                </div>
                @if(auth()->user()->role === 'teacher')
                    @php
                        $teacherFilterGrades = is_array(auth()->user()->assigned_grades) ? auth()->user()->assigned_grades : (json_decode(auth()->user()->assigned_grades, true) ?? []);
                        $teacherFilterSubjects = is_array(auth()->user()->assigned_subjects) ? auth()->user()->assigned_subjects : (json_decode(auth()->user()->assigned_subjects, true) ?? []);
                        $teacherFilterSections = collect(explode(',', (string) auth()->user()->section))
                            ->map(fn($s) => trim($s))
                            ->filter()
                            ->values()
                            ->all();
                    @endphp
                    <div class="mb-3 relative inline-block z-[120]">
                        <button id="openCalendarFilterPanelBtn" type="button" class="rounded border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Filters
                        </button>
                        <div id="teacherCalendarFilterPanel" class="hidden fixed z-[220] w-72 rounded border border-gray-200 bg-white p-3 shadow-2xl">
                            <div class="mb-2">
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Grade</label>
                                <select id="teacherCalendarGradeFilter" class="w-full border p-1 rounded text-sm">
                                    <option value="">All Grades</option>
                                    @foreach($teacherFilterGrades as $grade)
                                        <option value="{{ $grade }}">Grade {{ $grade }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Section</label>
                                <select id="teacherCalendarSectionFilter" class="w-full border p-1 rounded text-sm">
                                    <option value="">All Sections</option>
                                    @foreach($teacherFilterSections as $section)
                                        <option value="{{ $section }}">{{ $section }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Subject</label>
                                <select id="teacherCalendarSubjectFilter" class="w-full border p-1 rounded text-sm">
                                    <option value="">All Subjects</option>
                                    @foreach($teacherFilterSubjects as $subject)
                                        <option value="{{ $subject }}">{{ $subject }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button id="closeCalendarFilterPanelBtn" type="button" class="rounded border border-gray-300 px-3 py-1 text-xs font-semibold text-slate-700">Close</button>
                                <button id="confirmCalendarFilterBtn" type="button" class="rounded bg-blue-600 px-3 py-1 text-xs font-semibold text-white">Confirm</button>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="calendar-grid">
                    <div class="calendar-header">Sun</div>
                    <div class="calendar-header">Mon</div>
                    <div class="calendar-header">Tue</div>
                    <div class="calendar-header">Wed</div>
                    <div class="calendar-header">Thu</div>
                    <div class="calendar-header">Fri</div>
                    <div class="calendar-header">Sat</div>

                    @for ($i = 0; $i < $firstDayOfMonth; $i++)
                        <div class="calendar-day empty" style="background: #fafafa;"></div>
                    @endfor

                    @for ($day = 1; $day <= $daysInMonth; $day++)
                        @php 
                            $count = $notifications[$day] ?? 0; 
                            $currentDateString = $date->copy()->day($day)->format('F j, Y');
                        @endphp
                        <div
                            class="calendar-day cursor-pointer hover:bg-blue-50 transition"
                            data-day="{{ $day }}"
                            data-date-string="{{ $currentDateString }}"
                            onclick="openPanel({{ $day }}, '{{ $currentDateString }}')"
                        >
                            <span class="calendar-day-number">{{ $day }}</span>
                            <div class="calendar-day-dots"></div>
                        </div>
                    @endfor 
                </div> 
            </section> 
        </section>

    </div> </main>
    </div>



<div id="scholarPanel" class="side-panel">
    <div class="panel-header">
        <h3>Scholar Details</h3>
        <button onclick="closeAllPanels()" class="close-btn">&times;</button>
    </div>
    <div class="panel-body">
        <div class="mb-4 grid grid-cols-2 gap-2 rounded-lg bg-slate-100 p-1">
            <button id="profileTabBtn" type="button" onclick="showScholarPanelTab('profile')" class="rounded-md px-3 py-2 text-sm font-semibold bg-white text-slate-800 shadow-sm">
                Profile
            </button>
            <button id="settingsTabBtn" type="button" onclick="showScholarPanelTab('settings')" class="rounded-md px-3 py-2 text-sm font-semibold text-slate-600">
                Settings
            </button>
        </div>

        <div id="profileTabContent">
            <div class="profile-card">
                <div class="profile-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                <h4>{{ auth()->user()->name }}</h4>
                <span class="badge">{{ ucfirst(auth()->user()->role) }}</span>
            </div>

            <div class="academic-info">
                @if(auth()->user()->role === 'student')
                    <div class="info-group">
                        <label>Current Standing</label>
    <div class="stat-grid">
        <div class="stat-item">
            <span>Grade</span>
            <strong class="text-blue-700">{{ auth()->user()->grade_level ?? 'N/A' }}</strong>
        </div>
        <div class="stat-item">
            <span>Section</span>
            <strong class="text-blue-700">
                {{
                    (in_array((int) (auth()->user()->grade_level ?? 0), [7, 8, 9], true) && auth()->user()->studentGradeSection)
                        ? auth()->user()->studentGradeSection->section
                        : (auth()->user()->section ?? 'Unassigned')
                }}
            </strong>
        </div>
    </div>
                    </div>
                @elseif(auth()->user()->role === 'teacher')
                    @php
                        $teacher = auth()->user();
                        $teacherGrades = is_array($teacher->assigned_grades) ? $teacher->assigned_grades : (json_decode($teacher->assigned_grades, true) ?? []);
                        $teacherSubjects = is_array($teacher->assigned_subjects) ? $teacher->assigned_subjects : (json_decode($teacher->assigned_subjects, true) ?? []);
                        $teacherSections = collect(explode(',', (string) $teacher->section))
                            ->map(fn($s) => trim($s))
                            ->filter()
                            ->values()
                            ->all();
                    @endphp

                    <div class="info-group mb-4">
                        <label>Handled Grade Levels</label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @forelse($teacherGrades as $grade)
                                <span class="px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800">Grade {{ $grade }}</span>
                            @empty
                                <span class="text-sm text-gray-500 italic">No assigned grade levels.</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="info-group mb-4">
                        <label>Handled Sections</label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @forelse($teacherSections as $section)
                                <span class="px-2 py-1 text-xs font-semibold rounded bg-emerald-100 text-emerald-800">{{ $section }}</span>
                            @empty
                                <span class="text-sm text-gray-500 italic">No assigned sections.</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="info-group">
                        <label>Assigned Subjects</label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @forelse($teacherSubjects as $subject)
                                <span class="px-2 py-1 text-xs font-semibold rounded bg-amber-100 text-amber-800">{{ $subject }}</span>
                            @empty
                                <span class="text-sm text-gray-500 italic">No assigned subjects.</span>
                            @endforelse
                        </div>
                    </div>
                @else
                    <p class="text-muted">Accessing administrative dashboard tools.</p>
                @endif
            </div>
        </div>

        <div id="settingsTabContent" class="hidden">
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <h4 class="font-semibold text-slate-800 mb-3">Display</h4>
                <label class="flex items-center justify-between text-sm">
                    <span>Dark Mode</span>
                    <input id="darkModeToggle" type="checkbox" class="h-4 w-4">
                </label>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-4 mt-3">
                <h4 class="font-semibold text-slate-800 mb-2">Feedback</h4>
                <a href="https://bit.ly/awasfeedback" target="_blank" rel="noopener noreferrer" class="text-blue-700 font-semibold hover:underline">
                    Open Feedback Survey
                </a>
            </div>
        </div>

        <div class="menu-divider"></div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-action-btn">Logout Account</button>
        </form>
    </div>
</div>
<div id="viewPanel" class="side-panel">
    <div class="panel-header">
        <h3 id="viewPanelDateTitle" class="font-bold text-xl text-gray-800">Assessments</h3>
        <button onclick="closeAllPanels()" class="close-btn text-2xl">&times;</button>
    </div>
    <div class="panel-body mt-4">
        <div id="assessmentList">
            </div>
    </div>
</div>

<div id="calendarHoverCard" class="fixed z-[110] w-80 rounded-lg border border-gray-200 bg-white p-4 shadow-xl opacity-0 pointer-events-none -translate-y-1 transition-all duration-200 ease-out">
    <h4 id="hoverCardDateTitle" class="text-sm font-bold text-gray-800 mb-2">Assessments</h4>
    <div id="hoverCardAssessmentList" class="max-h-44 overflow-y-auto text-sm text-gray-700 mb-3">
        <p class="text-gray-500 italic">Loading assessments...</p>
    </div>
    <div class="flex gap-2">
        <button id="hoverViewBtn" type="button" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-3 rounded transition">
            View Assessments
        </button>
        <button id="hoverScheduleBtn" type="button" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium py-2 px-3 rounded transition">
            Schedule Assessment
        </button>
    </div>
</div>

    <script>
        const currentUserId = {{ auth()->id() }};
const currentCalendarMonth = {{ $date->month }};
const currentCalendarYear = {{ $date->year }};
let tempDate = '';
let tempDateString = '';
let hoverHideTimer = null;
const hoverCache = new Map();
const sectionsByGrade = {
    "7": ["Opal", "Turquoise", "Aquamarine", "Sapphire"],
    "8": ["Anthurium", "Carnation", "Daffodil", "Sunflower"],
    "9": ["Calcium", "Lithium", "Barium", "Sodium"],
    "10": ["Graviton", "Proton", "Neutron", "Electron"],
    "11": ["Mars", "Mercury", "Venus"],
    "12": ["Orosa", "Del Mundo", "Zara"]
};
const subjectsByGrade = {
    7: [
        "Integrated Science 1",
        "Mathematics 1",
        "English 1",
        "Filipino 1",
        "Social Science 1",
        "Physical Education 1",
        "Health 1",
        "Music 1",
        "Values Education 1",
        "AdTech 1",
        "Computer Science 1"
    ],
};
const fallbackSubjects = [
    "Computer Science",
    "Mathematics",
    "Physics",
    "Biology",
    "Chemistry",
    "English",
    "Research"
];

function getSubjectsForGrade(grade) {
    return subjectsByGrade[grade] ?? fallbackSubjects;
}

function getTeacherTypeOptionsForGrade(grade) {
    if (grade === 10) return ['elective'];
    if (grade === 11 || grade === 12) return ['science_core', 'elective'];
    return [];
}

function getTeacherTypeLabel(type) {
    if (type === 'science_core') return 'Science Core';
    if (type === 'elective') return 'Elective';
    return type;
}

function getTeacherSubjectsByGradeAndType(grade, type) {
    return subjectCatalog
        .filter((subject) =>
            Number(subject.grade_level_start) <= grade &&
            Number(subject.grade_level_end) >= grade &&
            subject.type === type
        )
        .map((subject) => subject.name);
}

function showRoleFlash(message, backgroundColor = '#2563eb') {
    const existingToast = document.getElementById('roleInfoToast');
    if (existingToast) {
        existingToast.remove();
    }

    const toast = document.createElement('div');
    toast.id = 'roleInfoToast';
    toast.className = 'toast-notification';
    toast.textContent = message;
    toast.style.background = backgroundColor;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 500);
    }, 3000);
}

const THEME_KEY = 'awas-theme';

function applyTheme(theme) {
    const isDark = theme === 'dark';
    document.body.classList.toggle('dark-mode', isDark);
    document.documentElement.classList.toggle('dark-mode', isDark);
    document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
}

function initThemeSettings() {
    const savedTheme = localStorage.getItem(THEME_KEY) || 'light';
    applyTheme(savedTheme);

    const toggle = document.getElementById('darkModeToggle');
    if (!toggle) return;

    toggle.checked = savedTheme === 'dark';
    toggle.addEventListener('change', (e) => {
        const nextTheme = e.target.checked ? 'dark' : 'light';
        localStorage.setItem(THEME_KEY, nextTheme);
        applyTheme(nextTheme);
    });
}

// Fallback listener in case the settings panel is re-rendered.
document.addEventListener('change', (e) => {
    if (e.target && e.target.id === 'darkModeToggle') {
        const nextTheme = e.target.checked ? 'dark' : 'light';
        localStorage.setItem(THEME_KEY, nextTheme);
        applyTheme(nextTheme);
    }
});


// This replaces your old openPanel
function openPanel(day, dateString) {
    const year = {{ $date->year }};
    const month = String({{ $date->month }}).padStart(2, '0');
    const dayStr = String(day).padStart(2, '0');
    
    tempDate = `${year}-${month}-${dayStr}`;
    tempDateString = dateString;

    // Check role from PHP
    const userRole = "{{ auth()->user()->role }}";
    const scheduleBtn = document.getElementById('scheduleBtn');

    // Show scheduling only for teachers
    if (scheduleBtn) {
        scheduleBtn.classList.toggle('hidden', userRole !== 'teacher');
    }

    if (userRole === 'admin') {
        showRoleFlash('View-only mode: Admins can view assessments but cannot schedule them in calendar.');
    }

    // Show the Choice Modal
    document.getElementById('choiceDateTitle').innerText = dateString;
    document.getElementById('choiceModal').classList.remove('hidden');
    document.getElementById('choiceModal').classList.add('flex');
}
async function handleChoice(action) {
    closeChoiceModal();
    
    if (action === 'schedule' && userRole === 'teacher') {
        document.getElementById('panelDateTitle').innerText = "Schedule for " + tempDateString;
        document.getElementById('hiddenDate').value = tempDate;
        document.getElementById('assessmentPanel').classList.add('open');
        document.getElementById('panelOverlay').classList.add('active');
        updateAssessmentSubjectOptions();
        updateAssessmentSectionOptions();
        syncAssessmentSectionState();
        checkConflict();
    } else {
        // --- THIS IS THE PART TO FIX ---
        const listContainer = document.getElementById('assessmentList');
        
        // Open the View Panel first so the user sees something is happening
        document.getElementById('viewPanelDateTitle').innerText = "Assessments on " + tempDateString;
        document.getElementById('viewPanel').classList.add('open');
        document.getElementById('panelOverlay').classList.add('active');
        
        listContainer.innerHTML = '<p class="text-blue-500 italic p-4">Loading assessments...</p>';

        try {
            // Fetch the filtered assessments based on the student's grade
            const response = await fetch(`/api/assessments-by-date?${buildAssessmentQueryParams(tempDate).toString()}`);
            const assessments = await response.json();

            if (assessments.length === 0) {
                listContainer.innerHTML = `
                    <div class="text-center py-10">
                        <p class="text-gray-500 italic">No assessments scheduled for your grade level on this day.</p>
                    </div>`;
            } else {
                listContainer.innerHTML = assessments.map(a => `
<div id="assessment-item-${a.id}" class="p-3 bg-white border-l-4 border-blue-600 rounded shadow-sm mb-2">
    <div class="flex justify-between items-start">
        <div>
            <span class="text-[10px] uppercase tracking-wider font-bold text-blue-600">${a.subject}</span>
            <div class="font-bold text-gray-800">${a.title}</div>
            <div class="text-xs text-gray-500">${a.description || ''}</div>
        </div>
    </div>
    <div class="text-xs text-gray-400 mt-2 font-mono">🕒 Deadline: ${a.due_time}</div>
</div>
                `).join('');
            }
        } catch (error) {
            listContainer.innerHTML = '<p class="text-red-500 p-4">Error loading data. Please check your connection.</p>';
        }
    }
}

function closeChoiceModal() {
    document.getElementById('choiceModal').classList.add('hidden');
    document.getElementById('choiceModal').classList.remove('flex');
}
// 1. Get the subjects assigned to this specific teacher from PHP
const assignedSubjects = @json(auth()->user()->assigned_subjects ?? []);
const assignedGrades = @json(auth()->user()->assigned_grades ?? []);
const subjectCatalog = @json($subjectCatalog ?? []);
const userRole = "{{ auth()->user()->role }}";
const hoverCard = document.getElementById('calendarHoverCard');
const hoverCardDateTitle = document.getElementById('hoverCardDateTitle');
const hoverCardAssessmentList = document.getElementById('hoverCardAssessmentList');
const hoverViewBtn = document.getElementById('hoverViewBtn');
const hoverScheduleBtn = document.getElementById('hoverScheduleBtn');
const openCalendarFilterPanelBtn = document.getElementById('openCalendarFilterPanelBtn');
const closeCalendarFilterPanelBtn = document.getElementById('closeCalendarFilterPanelBtn');
const confirmCalendarFilterBtn = document.getElementById('confirmCalendarFilterBtn');
const teacherCalendarFilterPanel = document.getElementById('teacherCalendarFilterPanel');
const teacherCalendarGradeFilter = document.getElementById('teacherCalendarGradeFilter');
const teacherCalendarSectionFilter = document.getElementById('teacherCalendarSectionFilter');
const teacherCalendarSubjectFilter = document.getElementById('teacherCalendarSubjectFilter');
let appliedTeacherCalendarFilters = { grade_level: '', section: '', subject: '' };

function getIsoDateFromDay(day) {
    const year = {{ $date->year }};
    const month = String({{ $date->month }}).padStart(2, '0');
    const dayStr = String(day).padStart(2, '0');
    return `${year}-${month}-${dayStr}`;
}

function getTeacherCalendarFilters() {
    if (userRole !== 'teacher') {
        return { grade_level: '', section: '', subject: '' };
    }

    return appliedTeacherCalendarFilters;
}

function openTeacherCalendarFilterPanel() {
    if (!teacherCalendarFilterPanel || !openCalendarFilterPanelBtn) return;
    ensureTeacherCalendarFilterPanelTopLayer();
    positionTeacherCalendarFilterPanel();
    teacherCalendarFilterPanel.classList.remove('hidden');
}

function closeTeacherCalendarFilterPanel() {
    if (!teacherCalendarFilterPanel) return;
    teacherCalendarFilterPanel.classList.add('hidden');
}

function positionTeacherCalendarFilterPanel() {
    if (!teacherCalendarFilterPanel || !openCalendarFilterPanelBtn) return;

    const btnRect = openCalendarFilterPanelBtn.getBoundingClientRect();
    const panelWidth = teacherCalendarFilterPanel.offsetWidth || 288;
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    const margin = 12;
    const offset = 8;

    const left = Math.max(margin, Math.min(btnRect.left, viewportWidth - panelWidth - margin));
    let top = btnRect.bottom + offset;

    // If there isn't enough room below, place it above the button.
    const estimatedPanelHeight = Math.min(teacherCalendarFilterPanel.scrollHeight || 360, viewportHeight * 0.7);
    if (top + estimatedPanelHeight > viewportHeight - margin) {
        top = Math.max(margin, btnRect.top - estimatedPanelHeight - offset);
    }

    teacherCalendarFilterPanel.style.left = `${left}px`;
    teacherCalendarFilterPanel.style.top = `${top}px`;
}

function ensureTeacherCalendarFilterPanelTopLayer() {
    if (!teacherCalendarFilterPanel) return;
    if (teacherCalendarFilterPanel.parentElement !== document.body) {
        document.body.appendChild(teacherCalendarFilterPanel);
    }
    teacherCalendarFilterPanel.style.zIndex = '9999';
}

function confirmTeacherCalendarFilters() {
    appliedTeacherCalendarFilters = {
        grade_level: teacherCalendarGradeFilter ? teacherCalendarGradeFilter.value : '',
        section: teacherCalendarSectionFilter ? teacherCalendarSectionFilter.value : '',
        subject: teacherCalendarSubjectFilter ? teacherCalendarSubjectFilter.value : '',
    };

    hoverCache.clear();
    refreshCalendarNotifications();
    closeTeacherCalendarFilterPanel();
}

function buildAssessmentQueryParams(date) {
    const params = new URLSearchParams({ date });
    const filters = getTeacherCalendarFilters();

    if (filters.grade_level) params.set('grade_level', filters.grade_level);
    if (filters.section) params.set('section', filters.section);
    if (filters.subject) params.set('subject', filters.subject);

    return params;
}

function hideHoverCard() {
    if (hoverCard) {
        hoverCard.classList.add('opacity-0', 'pointer-events-none', '-translate-y-1');
        hoverCard.classList.remove('opacity-100', 'translate-y-0');
    }
}

function queueHideHoverCard() {
    clearTimeout(hoverHideTimer);
    hoverHideTimer = setTimeout(() => {
        hideHoverCard();
    }, 150);
}

function positionHoverCard(targetEl) {
    if (!hoverCard || !targetEl) return;

    const rect = targetEl.getBoundingClientRect();
    const cardWidth = 320;
    const verticalOffset = 8;
    const viewportHeight = window.innerHeight;
    const viewportWidth = window.innerWidth;
    const minLeft = 12;
    const maxLeft = viewportWidth - cardWidth - 12;
    const left = Math.max(minLeft, Math.min(rect.left, maxLeft));

    const cardHeight = hoverCard.offsetHeight || 220;
    const fitsBelow = rect.bottom + verticalOffset + cardHeight <= viewportHeight - 8;
    const top = fitsBelow
        ? rect.bottom + verticalOffset
        : Math.max(8, rect.top - cardHeight - verticalOffset);

    hoverCard.style.top = `${top}px`;
    hoverCard.style.left = `${left}px`;
}

function renderHoverAssessments(assessments) {
    if (!hoverCardAssessmentList) return;

    if (!assessments || assessments.length === 0) {
        hoverCardAssessmentList.innerHTML = '<p class="text-gray-500 italic">No assessments scheduled on this date.</p>';
        return;
    }

    const previewItems = assessments.slice(0, 4);
    hoverCardAssessmentList.innerHTML = previewItems.map((a) => `
        <div class="mb-2 rounded border border-gray-100 p-2">
            <div class="text-[11px] uppercase tracking-wide font-bold text-blue-600">${a.subject}</div>
            <div class="font-semibold text-gray-800">${a.title}</div>
            <div class="text-xs text-gray-500">${a.due_time}</div>
        </div>
    `).join('');
}

async function openHoverCard(day, dateString, targetEl) {
    if (!hoverCard || !hoverCardDateTitle || !hoverViewBtn || !hoverScheduleBtn) return;

    const isoDate = getIsoDateFromDay(day);
    tempDate = isoDate;
    tempDateString = dateString;

    hoverCardDateTitle.innerText = `Assessments on ${dateString}`;
    hoverScheduleBtn.classList.toggle('hidden', userRole !== 'teacher');
    positionHoverCard(targetEl);
    hoverCard.classList.remove('opacity-0', 'pointer-events-none', '-translate-y-1');
    hoverCard.classList.add('opacity-100', 'translate-y-0');

    hoverViewBtn.onclick = () => {
        hideHoverCard();
        handleChoice('view');
    };
    hoverScheduleBtn.onclick = () => {
        hideHoverCard();
        handleChoice('schedule');
    };

    const filters = getTeacherCalendarFilters();
    const cacheKey = `${isoDate}|${filters.grade_level}|${filters.section}|${filters.subject}`;

    if (hoverCache.has(cacheKey)) {
        renderHoverAssessments(hoverCache.get(cacheKey));
        return;
    }

    hoverCardAssessmentList.innerHTML = '<p class="text-gray-500 italic">Loading assessments...</p>';

    try {
        const response = await fetch(`/api/assessments-by-date?${buildAssessmentQueryParams(isoDate).toString()}`);
        const assessments = await response.json();
        hoverCache.set(cacheKey, assessments);
        renderHoverAssessments(assessments);
    } catch (error) {
        hoverCardAssessmentList.innerHTML = '<p class="text-red-500 italic">Failed to load assessments.</p>';
    }
}

async function refreshCalendarNotifications() {
    const dayElements = document.querySelectorAll('.calendar-day[data-day]');
    if (!dayElements.length) return;

    try {
        const params = new URLSearchParams({
            month: String(currentCalendarMonth),
            year: String(currentCalendarYear),
        });
        const filters = getTeacherCalendarFilters();
        if (filters.grade_level) params.set('grade_level', filters.grade_level);
        if (filters.section) params.set('section', filters.section);
        if (filters.subject) params.set('subject', filters.subject);

        const response = await fetch(`/api/assessment-notifications?${params.toString()}`);
        const data = await response.json();

        dayElements.forEach((dayEl) => {
            const day = dayEl.dataset.day;
            const dayCounts = data[day] ?? {};
            const altCount = Number(dayCounts.alternative ?? 0);
            const formativeCount = Number(dayCounts.formative ?? 0);
            const longTestCount = Number(dayCounts.long_test ?? 0);
            const dayNumber = dayEl.querySelector('.calendar-day-number')?.textContent || day;

            const dots = [];
            for (let i = 0; i < altCount; i++) dots.push('<span class="calendar-dot dot-alternative"></span>');
            for (let i = 0; i < formativeCount; i++) dots.push('<span class="calendar-dot dot-formative"></span>');
            for (let i = 0; i < longTestCount; i++) dots.push('<span class="calendar-dot dot-longtest"></span>');

            dayEl.innerHTML = `
                <span class="calendar-day-number">${dayNumber}</span>
                <div class="calendar-day-dots">${dots.join('')}</div>
            `;
        });
    } catch (error) {
        console.error('Failed to refresh notification dots', error);
    }
}

function renderTeacherSubjectTypeOptions() {
    const teacherForm = document.getElementById('teacherEnrollmentForm');
    const typeContainer = document.getElementById('teacherSubjectTypeContainer');

    if (!teacherForm || !typeContainer) return;

    const selectedGradeInputs = teacherForm.querySelectorAll('input[name="assigned_grades[]"]:checked');
    const selectedGrades = Array.from(selectedGradeInputs).map((input) => parseInt(input.value, 10));
    const selectedUpperGrades = selectedGrades.filter((grade) => [10, 11, 12].includes(grade));
    const previousSelections = {};

    selectedUpperGrades.forEach((grade) => {
        const selectedTypes = teacherForm.querySelectorAll(`input[name="teacher_subject_groups[${grade}][]"]:checked`);
        previousSelections[grade] = new Set(Array.from(selectedTypes).map((input) => input.value));
    });

    if (selectedUpperGrades.length === 0) {
        typeContainer.innerHTML = '<p class="text-sm text-gray-500">Select Grade 10, 11, or 12 first.</p>';
        return;
    }

    typeContainer.innerHTML = '';

    selectedUpperGrades.forEach((grade) => {
        const gradeBlock = document.createElement('div');
        gradeBlock.className = 'rounded border border-gray-200 p-2';

        const title = document.createElement('p');
        title.className = 'text-sm font-semibold text-gray-700 mb-2';
        title.textContent = `Grade ${grade}`;
        gradeBlock.appendChild(title);

        const optionsWrap = document.createElement('div');
        optionsWrap.className = 'flex flex-wrap gap-3';

        getTeacherTypeOptionsForGrade(grade).forEach((type) => {
            const label = document.createElement('label');
            label.className = 'inline-flex items-center gap-2 text-sm';

            const input = document.createElement('input');
            input.type = 'checkbox';
            input.name = `teacher_subject_groups[${grade}][]`;
            input.value = type;
            input.className = 'rounded border-gray-300';
            if (previousSelections[grade]?.has(type)) {
                input.checked = true;
            }

            const span = document.createElement('span');
            span.textContent = getTeacherTypeLabel(type);

            label.appendChild(input);
            label.appendChild(span);
            optionsWrap.appendChild(label);
        });

        gradeBlock.appendChild(optionsWrap);
        typeContainer.appendChild(gradeBlock);
    });
}

function renderTeacherSubjectOptions() {
    const teacherForm = document.getElementById('teacherEnrollmentForm');
    const subjectContainer = document.getElementById('teacherSubjectContainer');

    if (!teacherForm || !subjectContainer) return;

    const selectedGradeInputs = teacherForm.querySelectorAll('input[name="assigned_grades[]"]:checked');
    const selectedGrades = Array.from(selectedGradeInputs).map((input) => parseInt(input.value, 10));
    const previousSelections = new Set(
        Array.from(subjectContainer.querySelectorAll('input[name="assigned_subjects[]"]:checked')).map((input) => input.value)
    );

    if (selectedGrades.length === 0) {
        subjectContainer.innerHTML = '<p class="col-span-2 text-sm text-gray-500">Select one or more grade levels first.</p>';
        return;
    }

    const subjectPool = [...new Set(selectedGrades.flatMap((grade) => {
        if (grade <= 9) {
            return getSubjectsForGrade(grade);
        }

        const selectedTypes = Array.from(
            teacherForm.querySelectorAll(`input[name="teacher_subject_groups[${grade}][]"]:checked`)
        ).map((input) => input.value);

        if (selectedTypes.length === 0) {
            return [];
        }

        return [...new Set(selectedTypes.flatMap((type) => getTeacherSubjectsByGradeAndType(grade, type)))];
    }))];
    subjectContainer.innerHTML = '';

    if (subjectPool.length === 0) {
        subjectContainer.innerHTML = '<p class="col-span-2 text-sm text-gray-500">Select at least one assignment type for Grade 10-12 to show subjects.</p>';
        return;
    }

    subjectPool.forEach((subjectName) => {
        const label = document.createElement('label');
        label.className = 'inline-flex items-center gap-2 text-sm';

        const input = document.createElement('input');
        input.type = 'checkbox';
        input.name = 'assigned_subjects[]';
        input.value = subjectName;
        input.className = 'rounded border-gray-300';
        if (previousSelections.has(subjectName)) {
            input.checked = true;
        }

        const span = document.createElement('span');
        span.textContent = subjectName;

        label.appendChild(input);
        label.appendChild(span);
        subjectContainer.appendChild(label);
    });
}

function renderTeacherSectionOptions() {
    const teacherForm = document.getElementById('teacherEnrollmentForm');
    const sectionContainer = document.getElementById('teacherSectionContainer');

    if (!teacherForm || !sectionContainer) return;

    const selectedGradeInputs = teacherForm.querySelectorAll('input[name="assigned_grades[]"]:checked');
    const selectedGrades = Array.from(selectedGradeInputs).map((input) => input.value);
    const previousSelections = new Set(
        Array.from(sectionContainer.querySelectorAll('input[name="assigned_sections[]"]:checked')).map((input) => input.value)
    );

    if (selectedGrades.length === 0) {
        sectionContainer.innerHTML = '<p class="col-span-2 text-sm text-gray-500">Select one or more grade levels first.</p>';
        return;
    }

    const sectionPool = [...new Set(selectedGrades.flatMap((grade) => sectionsByGrade[grade] ?? []))];
    sectionContainer.innerHTML = '';

    if (sectionPool.length === 0) {
        sectionContainer.innerHTML = '<p class="col-span-2 text-sm text-gray-500">No sections configured for the selected grade level(s).</p>';
        return;
    }

    sectionPool.forEach((sectionName) => {
        const label = document.createElement('label');
        label.className = 'inline-flex items-center gap-2 text-sm';

        const input = document.createElement('input');
        input.type = 'checkbox';
        input.name = 'assigned_sections[]';
        input.value = sectionName;
        input.className = 'rounded border-gray-300';
        if (previousSelections.has(sectionName)) {
            input.checked = true;
        }

        const span = document.createElement('span');
        span.textContent = sectionName;

        label.appendChild(input);
        label.appendChild(span);
        sectionContainer.appendChild(label);
    });
}

function updateAssessmentSubjectOptions() {
    const gradeSelect = document.getElementById('gradeSelect');
    const subjectSelect = document.getElementById('subjectSelect');

    if (!gradeSelect || !subjectSelect) return;

    const selectedGrade = parseInt(gradeSelect.value, 10);
    subjectSelect.innerHTML = '';

    if (!selectedGrade) {
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'Select a grade first';
        option.disabled = true;
        option.selected = true;
        subjectSelect.appendChild(option);
        return;
    }

    const gradeSubjects = getSubjectsForGrade(selectedGrade);
    const allowedSubjectsForUser = userRole === 'teacher'
        ? gradeSubjects.filter((subject) => assignedSubjects.includes(subject))
        : gradeSubjects;

    if (allowedSubjectsForUser.length === 0) {
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'No assigned subjects for this grade';
        option.disabled = true;
        option.selected = true;
        subjectSelect.appendChild(option);
        return;
    }

    allowedSubjectsForUser.forEach((subject) => {
        const option = document.createElement('option');
        option.value = subject;
        option.textContent = subject;
        subjectSelect.appendChild(option);
    });
}

function updateAssessmentSectionOptions() {
    const gradeSelect = document.getElementById('gradeSelect');
    const sectionSelect = document.getElementById('sectionSelect');
    if (!gradeSelect || !sectionSelect) return;

    const selectedGrade = String(gradeSelect.value || '');
    const sections = sectionsByGrade[selectedGrade] ?? [];

    sectionSelect.innerHTML = '';
    if (!selectedGrade || sections.length === 0) {
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'Select a grade first';
        option.disabled = true;
        option.selected = true;
        sectionSelect.appendChild(option);
        return;
    }

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = 'Select Section';
    placeholder.disabled = true;
    placeholder.selected = true;
    sectionSelect.appendChild(placeholder);

    sections.forEach((section) => {
        const option = document.createElement('option');
        option.value = section;
        option.textContent = section;
        sectionSelect.appendChild(option);
    });
}

function shouldDisableSectionForSubject(subjectName, grade) {
    const gradeNum = parseInt(grade, 10);
    if (!subjectName || !gradeNum) return false;

    const subjectMeta = subjectCatalog.find((s) =>
        s.name === subjectName &&
        Number(s.grade_level_start) <= gradeNum &&
        Number(s.grade_level_end) >= gradeNum
    );

    if (!subjectMeta) return false;
    if (subjectMeta.type === 'science_core' && (gradeNum === 11 || gradeNum === 12)) return true;
    if (subjectMeta.type === 'elective' && gradeNum >= 10 && gradeNum <= 12) return true;

    return false;
}

function syncAssessmentSectionState() {
    const gradeSelect = document.getElementById('gradeSelect');
    const subjectSelect = document.getElementById('subjectSelect');
    const sectionSelect = document.getElementById('sectionSelect');
    const sectionHint = document.getElementById('sectionRuleHint');

    if (!gradeSelect || !subjectSelect || !sectionSelect || !sectionHint) return;

    const selectedGrade = gradeSelect.value;
    const selectedSubject = subjectSelect.value;
    const disableSection = shouldDisableSectionForSubject(selectedSubject, selectedGrade);

    sectionSelect.disabled = disableSection;
    sectionSelect.required = !disableSection;

    if (disableSection) {
        sectionSelect.value = '';
        sectionHint.classList.remove('hidden');
        sectionHint.textContent = 'Section selection is disabled for this subject and grade.';
    } else {
        sectionHint.classList.add('hidden');
        sectionHint.textContent = '';
    }
}

        function openScholarPanel() {
            document.getElementById('scholarPanel').classList.add('open');
            document.getElementById('panelOverlay').classList.add('active');
            showScholarPanelTab('profile');
        }

        function closeAllPanels() {
            document.querySelectorAll('.side-panel').forEach(p => p.classList.remove('open'));
            document.getElementById('panelOverlay').classList.remove('active');
        }

        function showScholarPanelTab(tabName) {
            const profileTabContent = document.getElementById('profileTabContent');
            const settingsTabContent = document.getElementById('settingsTabContent');
            const profileTabBtn = document.getElementById('profileTabBtn');
            const settingsTabBtn = document.getElementById('settingsTabBtn');

            if (!profileTabContent || !settingsTabContent || !profileTabBtn || !settingsTabBtn) return;

            const isSettings = tabName === 'settings';
            profileTabContent.classList.toggle('hidden', isSettings);
            settingsTabContent.classList.toggle('hidden', !isSettings);

            profileTabBtn.className = isSettings
                ? 'rounded-md px-3 py-2 text-sm font-semibold text-slate-600'
                : 'rounded-md px-3 py-2 text-sm font-semibold bg-white text-slate-800 shadow-sm';
            settingsTabBtn.className = isSettings
                ? 'rounded-md px-3 py-2 text-sm font-semibold bg-white text-slate-800 shadow-sm'
                : 'rounded-md px-3 py-2 text-sm font-semibold text-slate-600';
        }
        function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.content-tab').forEach(tab => {
        tab.style.display = 'none';
    });

    // Show the selected tab
    document.getElementById(tabName + '-tab').style.display = 'block';

    // Update active state in sidebar
    document.querySelectorAll('.menu-item').forEach(item => {
        item.classList.remove('active');
        if(item.innerText.toLowerCase().includes(tabName)) {
            item.classList.add('active');
        }
    });
}
async function checkConflict() {
    const grade = document.getElementById('gradeSelect').value;
    const date = document.getElementById('hiddenDate').value;
    const type = document.getElementById('assessmentTypeSelect')?.value || '';
    const adviceDiv = document.getElementById('awas-advice');

    if (!grade || !date) return;

    try {
        // Calling the API route we created in web.php
        const response = await fetch(`/api/check-conflict?date=${date}&grade_level=${grade}&type=${encodeURIComponent(type)}`);
        const data = await response.json();

        adviceDiv.classList.remove('hidden');
        adviceDiv.innerText = data.message || "Safe to schedule.";

        // Change color based on status
        if (data.status === 'CRITICAL') {
            adviceDiv.className = "text-xs mt-2 p-2 rounded bg-red-100 text-red-700 border border-red-200";
        } else if (data.status === 'WARNING') {
            adviceDiv.className = "text-xs mt-2 p-2 rounded bg-yellow-100 text-yellow-700 border border-yellow-200";
        } else {
            adviceDiv.className = "text-xs mt-2 p-2 rounded bg-green-100 text-green-700 border border-green-200";
        }
    } catch (e) {
        console.error("AWAS Check failed", e);
    }
}
function initSchedulingAssessmentPanel() {
    const assessmentPanelForm = document.querySelector('#assessmentPanel form');
    const assessmentGradeSelect = document.getElementById('gradeSelect');
    const assessmentTypeSelect = document.getElementById('assessmentTypeSelect');

    if (assessmentPanelForm) {
        assessmentPanelForm.addEventListener('submit', function(e) {
            const adviceDiv = document.getElementById('awas-advice');
            
            // Check if the advice box is currently showing the 'CRITICAL' (red) state
            if (adviceDiv && adviceDiv.classList.contains('text-red-700')) {
                e.preventDefault(); // This stops the form from submitting!
                alert(adviceDiv.innerText || "Action Blocked: Conflict detected for this schedule.");
            }
        });
    }

    if (assessmentGradeSelect) {
        assessmentGradeSelect.addEventListener('change', updateAssessmentSubjectOptions);
        assessmentGradeSelect.addEventListener('change', updateAssessmentSectionOptions);
        assessmentGradeSelect.addEventListener('change', syncAssessmentSectionState);
        assessmentGradeSelect.addEventListener('change', checkConflict);
    }

    if (assessmentTypeSelect) {
        assessmentTypeSelect.addEventListener('change', checkConflict);
    }

    const assessmentSubjectSelect = document.getElementById('subjectSelect');
    if (assessmentSubjectSelect) {
        assessmentSubjectSelect.addEventListener('change', syncAssessmentSectionState);
    }

    updateAssessmentSubjectOptions();
    updateAssessmentSectionOptions();
    syncAssessmentSectionState();
}

const teacherGradeCheckboxes = document.querySelectorAll('#teacherEnrollmentForm input[name="assigned_grades[]"]');
const teacherSubjectTypeContainer = document.getElementById('teacherSubjectTypeContainer');
teacherGradeCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener('change', renderTeacherSubjectTypeOptions);
    checkbox.addEventListener('change', renderTeacherSubjectOptions);
    checkbox.addEventListener('change', renderTeacherSectionOptions);
});

if (teacherSubjectTypeContainer) {
    teacherSubjectTypeContainer.addEventListener('change', renderTeacherSubjectOptions);
}

const calendarDayElements = document.querySelectorAll('.calendar-day[data-day]');
calendarDayElements.forEach((dayEl) => {
    dayEl.addEventListener('mouseenter', () => {
        clearTimeout(hoverHideTimer);
        openHoverCard(dayEl.dataset.day, dayEl.dataset.dateString, dayEl);
    });
    dayEl.addEventListener('mouseleave', queueHideHoverCard);
});

if (userRole === 'teacher') {
    if (openCalendarFilterPanelBtn) {
        openCalendarFilterPanelBtn.addEventListener('click', openTeacherCalendarFilterPanel);
    }
    if (closeCalendarFilterPanelBtn) {
        closeCalendarFilterPanelBtn.addEventListener('click', closeTeacherCalendarFilterPanel);
    }
    if (confirmCalendarFilterBtn) {
        confirmCalendarFilterBtn.addEventListener('click', confirmTeacherCalendarFilters);
    }

    document.addEventListener('click', (e) => {
        if (!teacherCalendarFilterPanel || teacherCalendarFilterPanel.classList.contains('hidden')) return;
        if (teacherCalendarFilterPanel.contains(e.target) || openCalendarFilterPanelBtn?.contains(e.target)) return;
        closeTeacherCalendarFilterPanel();
    });

    window.addEventListener('resize', () => {
        if (teacherCalendarFilterPanel && !teacherCalendarFilterPanel.classList.contains('hidden')) {
            positionTeacherCalendarFilterPanel();
        }
    });

    window.addEventListener('scroll', () => {
        if (teacherCalendarFilterPanel && !teacherCalendarFilterPanel.classList.contains('hidden')) {
            positionTeacherCalendarFilterPanel();
        }
    }, { passive: true });
}

if (hoverCard) {
    hoverCard.addEventListener('mouseenter', () => clearTimeout(hoverHideTimer));
    hoverCard.addEventListener('mouseleave', queueHideHoverCard);
}

window.addEventListener('scroll', hideHoverCard, { passive: true });
window.addEventListener('resize', hideHoverCard);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initSchedulingAssessmentPanel();
        initThemeSettings();
        showScholarPanelTab('profile');
    });
} else {
    initSchedulingAssessmentPanel();
    initThemeSettings();
    showScholarPanelTab('profile');
}

renderTeacherSubjectTypeOptions();
renderTeacherSubjectOptions();
renderTeacherSectionOptions();
refreshCalendarNotifications();
    </script>
    <div id="panelOverlay" class="panel-overlay" onclick="closeAllPanels()"></div>
    
<div id="assessmentPanel" class="side-panel">
    <div class="panel-header">
        <h3 id="panelDateTitle">Schedule Assessment</h3>
        <button onclick="closeAllPanels()" class="close-btn">&times;</button>
    </div>
    <div class="panel-body">
        <form action="{{ route('assessments.store') }}" method="POST" class="mini-form">
            @csrf
            <input type="hidden" name="due_date" id="hiddenDate">

            <div class="form-group mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-1">Assessment Title</label>
                <input type="text" name="title" class="w-full p-2 border rounded border-gray-300" placeholder="e.g., Long Test in CS" required>
            </div>

            <div class="form-group mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-1">Target Grade Level</label>
                <select name="grade_level" id="gradeSelect" class="w-full p-2 border rounded border-gray-300" required>
                    @php
                        $user = auth()->user();
                        $assignedGrades = is_array($user->assigned_grades) ? $user->assigned_grades : json_decode($user->assigned_grades, true) ?? [];
                        $assignedSubjects = is_array($user->assigned_subjects) ? $user->assigned_subjects : json_decode($user->assigned_subjects, true) ?? [];
                    @endphp

                    @if($user->role === 'admin')
                        <option value="">Select Grade</option>
                        @foreach([7,8,9,10,11,12] as $g)
                            <option value="{{ $g }}">Grade {{ $g }}</option>
                        @endforeach
                    @else
                        @foreach($assignedGrades as $grade)
                            <option value="{{ $grade }}">Grade {{ $grade }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div class="form-group mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-1">Subject</label>
                <select name="subject" id="subjectSelect" required class="w-full p-2 border rounded border-gray-300 bg-gray-50">
                    <option value="">Select a grade first</option>
                </select>
            </div>

            <div class="form-group mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-1">Section</label>
                <select name="section" id="sectionSelect" class="w-full p-2 border rounded border-gray-300 bg-gray-50" required>
                    <option value="">Select a grade first</option>
                </select>
                <p id="sectionRuleHint" class="hidden text-xs mt-1 text-blue-600"></p>
            </div>

            <div class="form-group mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-1">Assessment Type</label>
                <select name="type" id="assessmentTypeSelect" class="w-full p-2 border rounded border-gray-300" required>
                    <option value="Formative Assessment">Formative Assessment (FA)</option>
                    <option value="Alternative Assessment (AA)">Alternative Assessment (AA)</option>
                    <option value="Long Test">Long Test</option>
                </select>
            </div>

            <div class="form-group mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-1">Deadline Time</label>
                <input type="time" name="due_time" class="w-full p-2 border rounded border-gray-300" required>
            </div>

            <div id="awas-advice" class="text-xs mt-2 p-2 rounded bg-blue-50 text-blue-700 hidden"></div>
            
            <button type="submit" class="scholar-btn w-full mt-4 bg-[#0038a8] text-white py-3 rounded-lg font-bold hover:bg-blue-800 transition shadow-md">
                Save Assessment
            </button>
        </form>
    </div>
</div>

<div id="choiceModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-lg shadow-xl p-6 w-80 text-center">
        <h3 id="choiceDateTitle" class="text-lg font-bold mb-4 text-gray-800">Date</h3>
        <div class="flex flex-col gap-3">
            <button onclick="handleChoice('view')" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition">
                View Assessments
            </button>
            
            <button id="scheduleBtn" onclick="handleChoice('schedule')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-4 rounded transition">
                Schedule New
            </button>
            
            <button onclick="closeChoiceModal()" class="text-gray-500 hover:text-gray-700 text-sm mt-2">
                Cancel
            </button>
        </div>
    </div>
</div>
</div>
</div>
</body>
</html>
