<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment</title>
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

        @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <p class="font-semibold">Please fix the following:</p>
                <ul class="mt-2 list-disc pl-5">
                    @foreach($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
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
                        <a href="{{ route('admin.enrollment') }}" class="hover:text-blue-200 transition underline-offset-8 hover:underline">Enrollment</a>
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
                    <section class="content-tab" style="display: block; padding: 40px;">
                        @if(session('success'))
                            <div id="enrollmentSuccessToast" class="toast-notification">
                                <span class="icon">OK</span> {{ session('success') }}
                            </div>
                            <script>
                                const enrollmentToast = document.getElementById('enrollmentSuccessToast');
                                if (enrollmentToast) {
                                    enrollmentToast.scrollIntoView({ block: 'start', behavior: 'smooth' });
                                    setTimeout(() => {
                                        enrollmentToast.style.opacity = '0';
                                        setTimeout(() => enrollmentToast.remove(), 500);
                                    }, 3000);
                                }
                            </script>
                        @endif
                        <div class="mx-auto grid max-w-6xl gap-6 lg:grid-cols-2">
                            <div class="enrollment-card bg-white shadow-lg rounded-lg p-8">
                                <h2 class="text-2xl font-bold text-blue-900">Student Enrollment</h2>
                                <p class="text-gray-500">Register a new scholar into the system.</p>
                                <div class="mt-4">
                                    <button type="button" id="showStudentListBtn" class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
                                        View Current Students
                                    </button>
                                </div>
                                <hr style="margin: 20px 0; opacity: 0.2;">

                                <form action="{{ route('admin.enroll') }}" method="POST" class="mini-form">
                                    @csrf
                                    <div class="form-group mb-4">
                                        <label class="block mb-1">Full Name</label>
                                        <input type="text" name="name" class="w-full border p-2 rounded" placeholder="Juan Dela Cruz" required>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label class="block mb-1">Email Address</label>
                                        <input type="email" name="email" class="w-full border p-2 rounded" placeholder="juan@pshs.edu.ph" required>
                                    </div>

                                    <div class="flex gap-4 mb-4">
                                        <div class="form-group flex-1">
                                            <label>Grade Level</label>
                                            @php
                                                $assigned = is_array($user->assigned_grades)
                                                    ? $user->assigned_grades
                                                    : json_decode($user->assigned_grades, true) ?? [];
                                                $enrollableGrades = !empty($assigned) ? $assigned : [7, 8, 9, 10, 11, 12];
                                            @endphp

                                            <select name="grade_level" required class="w-full p-2 border rounded">
                                                @foreach($enrollableGrades as $grade)
                                                    <option value="{{ $grade }}">Grade {{ $grade }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group flex-1">
                                            <label class="block mb-1">Subject Groups</label>
                                            <div id="studentSubjectGroupChecklist" class="grid gap-1 rounded border border-gray-200 p-2">
                                                <label class="inline-flex items-center gap-2 text-sm">
                                                    <input type="checkbox" id="studentGroupRegular" name="student_subject_groups[]" value="regular" class="rounded border-gray-300">
                                                    <span>Regular</span>
                                                </label>
                                                <input type="hidden" id="studentGroupRegularHidden" name="student_subject_groups[]" value="regular" disabled>
                                                <label class="inline-flex items-center gap-2 text-sm">
                                                    <input type="checkbox" id="studentGroupScienceCore" name="student_subject_groups[]" value="science_core" class="rounded border-gray-300">
                                                    <span>Science Core</span>
                                                </label>
                                                <label class="inline-flex items-center gap-2 text-sm">
                                                    <input type="checkbox" id="studentGroupElective" name="student_subject_groups[]" value="elective" class="rounded border-gray-300">
                                                    <span>Elective</span>
                                                </label>
                                            </div>
                                            <p id="studentGroupHint" class="text-xs mt-1 text-gray-500"></p>
                                            <div id="studentGroupSubjectList" class="hidden mt-2 rounded border border-gray-200 bg-gray-50 p-2 text-xs"></div>
                                        </div>
                                        <div class="form-group flex-1">
                                            <label class="block mb-1">Section</label>
                                            <select name="section" id="sectionDropdown" class="w-full border p-2 rounded" required>
                                                <option value="">Select Grade First</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group mb-6">
                                        <label class="block mb-1">Temporary Password</label>
                                        <input type="password" name="password" class="w-full border p-2 rounded" required placeholder="Set a temporary password">
                                    </div>

                                    <button type="submit" class="bg-blue-700 text-white w-full py-3 rounded-lg font-bold hover:bg-blue-800 transition">
                                        Enroll Student
                                    </button>
                                </form>
                            </div>

                            <div class="enrollment-card bg-white shadow-lg rounded-lg p-8">
                                <h2 class="text-2xl font-bold text-blue-900">Teacher Registration</h2>
                                <p class="text-gray-500">Create a teacher account and assign grade levels and subjects.</p>
                                <div class="mt-4">
                                    <button type="button" id="showTeacherListBtn" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                        View Current Teachers
                                    </button>
                                </div>
                                <hr style="margin: 20px 0; opacity: 0.2;">

                                <form id="teacherEnrollmentForm" action="{{ route('admin.enroll.teacher') }}" method="POST" class="mini-form">
                                    @csrf
                                    <div class="form-group mb-4">
                                        <label class="block mb-1">Full Name</label>
                                        <input type="text" name="name" class="w-full border p-2 rounded" placeholder="Maria Santos" required>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label class="block mb-1">Email Address</label>
                                        <input type="email" name="email" class="w-full border p-2 rounded" placeholder="maria@pshs.edu.ph" required>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label class="block mb-2">Assigned Grade Levels</label>
                                        <div class="grid grid-cols-3 gap-2 rounded border border-gray-200 p-3">
                                            @foreach([7,8,9,10,11,12] as $grade)
                                                <label class="inline-flex items-center gap-2 text-sm">
                                                    <input type="checkbox" name="assigned_grades[]" value="{{ $grade }}" class="rounded border-gray-300">
                                                    <span>Grade {{ $grade }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="form-group mb-6">
                                        <label class="block mb-2">Grade 10-12 Assignment Type</label>
                                        <div id="teacherSubjectTypeContainer" class="grid grid-cols-1 gap-3 rounded border border-gray-200 p-3">
                                            <p class="text-sm text-gray-500">Select Grade 10, 11, or 12 first.</p>
                                        </div>
                                    </div>

                                    <div class="form-group mb-6">
                                        <label class="block mb-2">Assigned Subjects</label>
                                        <div id="teacherSubjectContainer" class="grid grid-cols-2 gap-2 rounded border border-gray-200 p-3">
                                            <p class="col-span-2 text-sm text-gray-500">Select one or more grade levels first.</p>
                                        </div>
                                    </div>

                                    <div class="form-group mb-6">
                                        <label class="block mb-2">Assigned Sections</label>
                                        <div id="teacherSectionContainer" class="grid grid-cols-2 gap-2 rounded border border-gray-200 p-3">
                                            <p class="col-span-2 text-sm text-gray-500">Select one or more grade levels first.</p>
                                        </div>
                                    </div>

                                    <div class="form-group mb-6">
                                        <label class="block mb-1">Temporary Password</label>
                                        <input type="password" name="password" class="w-full border p-2 rounded" required placeholder="Set a temporary password">
                                    </div>

                                    <button type="submit" class="bg-emerald-700 text-white w-full py-3 rounded-lg font-bold hover:bg-emerald-800 transition">
                                        Register Teacher
                                    </button>
                                </form>
                            </div>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>
</div>

<div id="scholarPanel" class="side-panel">
    <div class="panel-header">
        <h3>
            @if(auth()->user()->role === 'admin')
                Admin Details
            @elseif(auth()->user()->role === 'teacher')
                Teacher Details
            @else
                Scholar Details
            @endif
        </h3>
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
                <div class="info-group">
                    <label>Assigned Grades</label>
                    <div class="stat-grid">
                        @forelse($teacherGrades as $grade)
                            <div class="stat-item"><span>Grade</span><strong class="text-blue-700 badge-blue">{{ $grade }}</strong></div>
                        @empty
                            <div class="stat-item"><span>No assigned grades.</span></div>
                        @endforelse
                    </div>
                </div>
                <div class="info-group">
                    <label>Assigned Sections</label>
                    <ul class="grade-list">
                        @forelse($teacherSections as $section)
                            <li><span class="px-2 py-1 text-xs font-semibold rounded bg-emerald-100 text-emerald-800 badge-emerald">{{ $section }}</span></li>
                        @empty
                            <li><span class="text-muted">No assigned sections.</span></li>
                        @endforelse
                    </ul>
                </div>
                <div class="info-group">
                    <label>Assigned Subjects</label>
                    <div class="flex flex-wrap gap-2">
                        @forelse($teacherSubjects as $subject)
                            <span class="px-2 py-1 text-xs font-semibold rounded bg-amber-100 text-amber-800 badge-amber">{{ $subject }}</span>
                        @empty
                            <span class="text-sm text-gray-500 italic">No assigned subjects.</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div id="settingsTabContent" class="hidden">
            <div class="profile-card">
                <p class="text-muted">Settings are managed in the main profile page.</p>
                <div class="menu-divider"></div>
                <a class="logout-action-btn" href="{{ route('profile.edit') }}">Open Profile Settings</a>
            </div>
        </div>
    </div>
</div>
<div id="panelOverlay" class="panel-overlay" onclick="closeAllPanels()"></div>

<div id="studentListModal" class="fixed inset-0 z-[200] hidden items-center justify-center bg-black/50 p-4">
    <div class="flex w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl" style="height: calc(100vh - 2rem); max-height: calc(100vh - 2rem);">
        <div class="flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4">
            <div>
                <h3 class="text-xl font-bold text-slate-900">Current Students</h3>
                <p class="text-sm text-slate-500">Filter by grade, section, and group.</p>
            </div>
            <button type="button" id="closeStudentListBtn" class="rounded-full border border-gray-300 px-3 py-1 text-sm text-gray-600 hover:bg-gray-100">Close</button>
        </div>
        <div class="flex min-h-0 flex-1 flex-col px-6 py-4" style="min-height: 0;">
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Grade Level</label>
                    <select id="studentFilterGrade" class="mt-2 w-full rounded-lg border border-gray-300 p-2">
                        <option value="">All Grades</option>
                        @foreach([7,8,9,10,11,12] as $grade)
                            <option value="{{ $grade }}">Grade {{ $grade }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Section</label>
                    <select id="studentFilterSection" class="mt-2 w-full rounded-lg border border-gray-300 p-2">
                        <option value="">All Sections</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Group</label>
                    <select id="studentFilterGroup" class="mt-2 w-full rounded-lg border border-gray-300 p-2" disabled>
                        <option value="">All Groups</option>
                        <option value="regular">Regular</option>
                        <option value="science_core">Science Core</option>
                        <option value="elective">Elective</option>
                    </select>
                </div>
            </div>
            <div id="studentListStatus" class="mt-4 text-sm text-slate-500">Choose filters to load students.</div>
            <div class="mt-4 min-h-0 flex-1 overflow-y-auto rounded-xl border border-gray-200" style="min-height: 0; max-height: calc(100vh - 26rem);">
                <table class="w-full text-left text-sm">
                    <thead class="sticky top-0 z-10 bg-white text-xs uppercase tracking-wide text-slate-500 shadow-sm">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Grade</th>
                            <th class="px-4 py-3">Section</th>
                            <th class="px-4 py-3">Subjects</th>
                        </tr>
                    </thead>
                    <tbody id="studentListBody" class="divide-y divide-gray-100 bg-white">
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">No data loaded yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="teacherListModal" class="fixed inset-0 z-[210] hidden items-center justify-center bg-black/50 p-4">
    <div class="flex w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl" style="height: calc(100vh - 2rem); max-height: calc(100vh - 2rem);">
        <div class="flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4">
            <div>
                <h3 class="text-xl font-bold text-slate-900">Current Teachers</h3>
                <p class="text-sm text-slate-500">Filter by grade and subject.</p>
            </div>
            <button type="button" id="closeTeacherListBtn" class="rounded-full border border-gray-300 px-3 py-1 text-sm text-gray-600 hover:bg-gray-100">Close</button>
        </div>
        <div class="flex min-h-0 flex-1 flex-col px-6 py-4" style="min-height: 0;">
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Grade Level</label>
                    <select id="teacherFilterGrade" class="mt-2 w-full rounded-lg border border-gray-300 p-2">
                        <option value="">All Grades</option>
                        @foreach([7,8,9,10,11,12] as $grade)
                            <option value="{{ $grade }}">Grade {{ $grade }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Subject</label>
                    <select id="teacherFilterSubject" class="mt-2 w-full rounded-lg border border-gray-300 p-2" disabled>
                        <option value="">All Subjects</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Role</label>
                    <div class="mt-2 rounded-lg border border-gray-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">Teacher</div>
                </div>
            </div>
            <div id="teacherListStatus" class="mt-4 text-sm text-slate-500">Choose filters to load teachers.</div>
            <div class="mt-4 min-h-0 flex-1 overflow-y-auto rounded-xl border border-gray-200" style="min-height: 0; max-height: calc(100vh - 26rem);">
                <table class="w-full text-left text-sm">
                    <thead class="sticky top-0 z-10 bg-white text-xs uppercase tracking-wide text-slate-500 shadow-sm">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Grades</th>
                            <th class="px-4 py-3">Sections</th>
                            <th class="px-4 py-3">Subjects</th>
                        </tr>
                    </thead>
                    <tbody id="teacherListBody" class="divide-y divide-gray-100 bg-white">
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">No data loaded yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
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
    8: [
        "Biology 1",
        "Chemistry 1",
        "Physics 1",
        "Mathematics 2",
        "Mathematics 3",
        "Earth Science",
        "English 2",
        "Filipino 2",
        "Social Science 2",
        "Physical Education 2",
        "Health 2",
        "Music 2",
        "Values Education 2",
        "AdTech 2",
        "Computer Science 2"
    ],
    9: [
        "Biology 1",
        "Chemistry 1",
        "Physics 1",
        "Mathematics 3",
        "English 3",
        "Filipino 3",
        "Social Science 3",
        "Physical Education 3",
        "Health 3",
        "Music 3",
        "Values Education 3",
        "Statistics 1",
        "Computer Science 3"
    ],
    10: [
        "Biology 2",
        "Chemistry 2",
        "Physics 2",
        "Mathematics 4",
        "English 4",
        "Filipino 4",
        "Social Science 4",
        "Physical Education 4",
        "Health 4",
        "Music 4",
        "Values Education 4",
        "STEM Research 1",
        "Computer Science 4",
        "Philippine Biodiversity (AYP)",
        "Microbiology and Basic Molecular Techniques",
        "Data Science",
        "Field Sampling Techniques",
        "Intellectual Property Rights"
    ],
    11: [
        "Biology 3 Class 1",
        "Biology 3 Class 2",
        "Chemistry 3 Class 1",
        "Chemistry 3 Class 2",
        "Physics 3 Class 1",
        "Physics 3 Class 2",
        "Mathematics 5",
        "English 5",
        "Filipino 5",
        "Social Science 5",
        "STEM Research 2",
        "Computer Science 5",
        "Engineering",
        "Design and Make Technology",
        "Agriculture",
        "Biology 3 Elective",
        "Chemistry 3 Elective Class 1",
        "Chemistry 3 Elective Class 2",
        "Physics 3 Elective"
    ],
    12: [
        "Biology 4 Class 1",
        "Biology 4 Class 2",
        "Chemistry 4 Class 1",
        "Chemistry 4 Class 2",
        "Physics 4 Class 1",
        "Physics 4 Class 2",
        "Mathematics 6",
        "English 6",
        "Filipino 6",
        "Social Science 6",
        "STEM Research 3",
        "Computer Science 5",
        "Engineering",
        "Design and Make Technology",
        "Agriculture",
        "Biology 4 Elective",
        "Chemistry 4 Elective Class 1",
        "Chemistry 4 Elective Class 2",
        "Physics 4 Elective"
    ],
};
const subjectCatalog = @json($subjectCatalog ?? []);
const regularSubjectsByGrade = {
    10: [
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
    ],
    11: [
        'Mathematics 5',
        'English 5',
        'Filipino 5',
        'Social Science 5',
        'STEM Research 2',
        'Computer Science 5',
    ],
    12: [
        'Mathematics 6',
        'English 6',
        'Filipino 6',
        'Social Science 6',
        'STEM Research 3',
        'Computer Science 5',
    ],
};

const studentGroupSubjectCatalog = {
    10: {
        elective: [
            'Philippine Biodiversity (AYP)',
            'Microbiology and Basic Molecular Techniques',
            'Data Science',
            'Field Sampling Techniques',
            'Intellectual Property Rights',
        ],
        science_core: [],
        regular: [
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
        ],
    },
    11: {
        elective: [
            'Engineering',
            'Design and Make Technology',
            'Agriculture',
            'Biology 3 Elective',
            'Chemistry 3 Elective Class 1',
            'Chemistry 3 Elective Class 2',
            'Physics 3 Elective',
        ],
        science_core: [
            'Biology 3 Class 1',
            'Biology 3 Class 2',
            'Chemistry 3 Class 1',
            'Chemistry 3 Class 2',
            'Physics 3 Class 1',
            'Physics 3 Class 2',
        ],
        regular: [
            'Mathematics 5',
            'English 5',
            'Filipino 5',
            'Social Science 5',
            'STEM Research 2',
            'Computer Science 5',
        ],
    },
    12: {
        elective: [
            'Engineering',
            'Design and Make Technology',
            'Agriculture',
            'Biology 4 Elective',
            'Chemistry 4 Elective Class 1',
            'Chemistry 4 Elective Class 2',
            'Physics 4 Elective',
        ],
        science_core: [
            'Biology 4 Class 1',
            'Biology 4 Class 2',
            'Chemistry 4 Class 1',
            'Chemistry 4 Class 2',
            'Physics 4 Class 1',
            'Physics 4 Class 2',
        ],
        regular: [
            'Mathematics 6',
            'English 6',
            'Filipino 6',
            'Social Science 6',
            'STEM Research 3',
            'Computer Science 5',
        ],
    },
};

function getSubjectsForGrade(grade) {
    return subjectsByGrade[grade] ?? [];
}

function renderStudentSubjectGroupList(selectedGradeInt, selectedGroups) {
    const container = document.getElementById('studentGroupSubjectList');
    if (!container) return;

    const gradeCatalog = studentGroupSubjectCatalog[selectedGradeInt];
    if (!gradeCatalog || selectedGroups.length === 0) {
        container.classList.add('hidden');
        container.innerHTML = '';
        return;
    }

    const previous = {
        elective: new Set(Array.from(container.querySelectorAll('input[name="selected_subjects[elective][]"]:checked')).map((i) => i.value)),
        science_core: new Set(Array.from(container.querySelectorAll('input[name="selected_subjects[science_core][]"]:checked')).map((i) => i.value)),
        regular: new Set(Array.from(container.querySelectorAll('input[name="selected_subjects[regular][]"]:checked')).map((i) => i.value)),
    };

    const blocks = [];
    if (selectedGroups.includes('regular') && gradeCatalog.regular && gradeCatalog.regular.length > 0) {
        blocks.push(`
            <div class="mb-2">
                <div class="font-semibold text-blue-700">Grade ${selectedGradeInt} Regular Subjects</div>
                <div class="mt-1 grid gap-1">
                    ${gradeCatalog.regular.map((item) => `
                        <label class="inline-flex items-start gap-2">
                            <input type="checkbox" name="selected_subjects[regular][]" value="${item.replace(/"/g, '&quot;')}" class="rounded border-gray-300" checked disabled>
                            <span>${item}</span>
                        </label>
                    `).join('')}
                </div>
            </div>
        `);
    }
    if (selectedGroups.includes('elective') && gradeCatalog.elective && gradeCatalog.elective.length > 0) {
        blocks.push(`
            <div class="mb-2">
                <div class="font-semibold text-blue-700">Grade ${selectedGradeInt} Elective List</div>
                <div class="mt-1 grid gap-1">
                    ${gradeCatalog.elective.map((item) => `
                        <label class="inline-flex items-start gap-2">
                            <input type="checkbox" name="selected_subjects[elective][]" value="${item.replace(/"/g, '&quot;')}" class="rounded border-gray-300" ${previous.elective.has(item) ? 'checked' : ''}>
                            <span>${item}</span>
                        </label>
                    `).join('')}
                </div>
            </div>
        `);
    }
    if (selectedGroups.includes('science_core') && gradeCatalog.science_core && gradeCatalog.science_core.length > 0) {
        blocks.push(`
            <div>
                <div class="font-semibold text-blue-700">Grade ${selectedGradeInt} Science Cores</div>
                <div class="mt-1 grid gap-1">
                    ${gradeCatalog.science_core.map((item) => `
                        <label class="inline-flex items-start gap-2">
                            <input type="checkbox" name="selected_subjects[science_core][]" value="${item.replace(/"/g, '&quot;')}" class="rounded border-gray-300" ${previous.science_core.has(item) ? 'checked' : ''}>
                            <span>${item}</span>
                        </label>
                    `).join('')}
                </div>
            </div>
        `);
    }

    if (blocks.length === 0) {
        container.classList.add('hidden');
        container.innerHTML = '';
        return;
    }

    container.innerHTML = blocks.join('');
    container.classList.remove('hidden');
}

function updateEnrollmentSections() {
    const gradeDropdown = document.querySelector('select[name="grade_level"]');
    const groupRegular = document.getElementById('studentGroupRegular');
    const groupRegularHidden = document.getElementById('studentGroupRegularHidden');
    const groupScienceCore = document.getElementById('studentGroupScienceCore');
    const groupElective = document.getElementById('studentGroupElective');
    const groupHint = document.getElementById('studentGroupHint');
    const sectionDropdown = document.getElementById('sectionDropdown');

    if (!gradeDropdown || !sectionDropdown || !groupRegular || !groupRegularHidden || !groupScienceCore || !groupElective || !groupHint) return;

    const selectedGrade = gradeDropdown.value;
    const selectedGradeInt = parseInt(selectedGrade, 10);

    groupRegular.disabled = false;
    groupScienceCore.disabled = false;
    groupElective.disabled = false;
    groupHint.textContent = '';

    if (selectedGradeInt >= 7 && selectedGradeInt <= 9) {
        groupRegular.checked = true;
        groupRegular.disabled = true;
        groupRegularHidden.disabled = false;
        groupScienceCore.checked = false;
        groupScienceCore.disabled = true;
        groupElective.checked = false;
        groupElective.disabled = true;
        groupHint.textContent = 'Grades 7-9 use Regular only (auto-assigned).';
    } else if (selectedGradeInt === 10) {
        groupRegular.checked = true;
        groupRegular.disabled = true;
        groupRegularHidden.disabled = false;
        groupScienceCore.checked = false;
        groupScienceCore.disabled = true;
        groupElective.disabled = false;
        if (!groupElective.checked) {
            groupElective.checked = true;
        }
        groupHint.textContent = 'Grade 10 regular subjects are auto-assigned; electives optional.';
    } else if (selectedGradeInt === 11 || selectedGradeInt === 12) {
        groupRegular.checked = true;
        groupRegular.disabled = true;
        groupRegularHidden.disabled = false;
        groupScienceCore.disabled = false;
        groupElective.disabled = false;
        if (!groupRegular.checked && !groupScienceCore.checked && !groupElective.checked) {
            groupElective.checked = true;
        }
        groupHint.textContent = 'Grades 11-12 regular subjects are auto-assigned; science core/electives optional.';
    }

    const selectedGroups = [];
    if (groupRegular.checked) selectedGroups.push('regular');
    if (groupScienceCore.checked) selectedGroups.push('science_core');
    if (groupElective.checked) selectedGroups.push('elective');

    renderStudentSubjectGroupList(selectedGradeInt, selectedGroups);

    const hasRegular = selectedGroups.includes('regular');
    const sectionExempt =
        !hasRegular &&
        (
            (selectedGradeInt === 10 && selectedGroups.includes('elective')) ||
            ((selectedGradeInt === 11 || selectedGradeInt === 12) &&
                (selectedGroups.includes('science_core') || selectedGroups.includes('elective')))
        );

    sectionDropdown.innerHTML = '<option value="">Select Section</option>';
    sectionDropdown.disabled = sectionExempt;
    sectionDropdown.required = !sectionExempt;

    if (sectionExempt) {
        sectionDropdown.innerHTML = '<option value="">Section not required for selected group</option>';
        return;
    }

    if (sectionsByGrade[selectedGrade]) {
        sectionsByGrade[selectedGrade].forEach(section => {
            const option = document.createElement('option');
            option.value = section;
            option.textContent = section;
            sectionDropdown.appendChild(option);
        });
    }
}
function getTeacherTypeOptionsForGrade(grade) {
    if (grade === 10) return ['regular', 'elective'];
    if (grade === 11 || grade === 12) return ['regular', 'science_core', 'elective'];
    return [];
}

const studentFilterGrade = document.getElementById('studentFilterGrade');
const studentFilterSection = document.getElementById('studentFilterSection');
const studentFilterGroup = document.getElementById('studentFilterGroup');
const studentListBody = document.getElementById('studentListBody');
const studentListStatus = document.getElementById('studentListStatus');
const teacherFilterGrade = document.getElementById('teacherFilterGrade');
const teacherFilterSubject = document.getElementById('teacherFilterSubject');
const teacherListBody = document.getElementById('teacherListBody');
const teacherListStatus = document.getElementById('teacherListStatus');

function updateStudentFilterSectionOptions() {
    if (!studentFilterGrade || !studentFilterSection || !studentFilterGroup) return;

    const gradeValue = studentFilterGrade.value;
    const gradeInt = parseInt(gradeValue, 10);
    studentFilterSection.innerHTML = '<option value="">All Sections</option>';

    if (gradeValue && sectionsByGrade[gradeValue]) {
        sectionsByGrade[gradeValue].forEach((section) => {
            const option = document.createElement('option');
            option.value = section;
            option.textContent = section;
            studentFilterSection.appendChild(option);
        });
    }

    if (!gradeValue) {
        studentFilterGroup.value = '';
        studentFilterGroup.disabled = true;
        return;
    }

    if (gradeInt >= 10 && gradeInt <= 12) {
        studentFilterGroup.disabled = false;
        if (gradeInt === 10) {
            studentFilterGroup.value = 'elective';
            Array.from(studentFilterGroup.options).forEach((option) => {
                option.disabled = option.value !== '' && option.value !== 'elective';
            });
        } else {
            Array.from(studentFilterGroup.options).forEach((option) => {
                option.disabled = false;
            });
            if (!studentFilterGroup.value) {
                studentFilterGroup.value = '';
            }
        }
    } else {
        studentFilterGroup.value = 'regular';
        studentFilterGroup.disabled = true;
    }
}

async function fetchStudentList() {
    if (!studentFilterGrade || !studentListBody || !studentListStatus) return;

    const params = new URLSearchParams();
    if (studentFilterGrade.value) params.set('grade_level', studentFilterGrade.value);
    if (studentFilterSection.value) params.set('section', studentFilterSection.value);
    if (studentFilterGroup.value) params.set('group', studentFilterGroup.value);

    studentListStatus.textContent = 'Loading students...';
    studentListBody.innerHTML = '<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Loading...</td></tr>';

    try {
        const response = await fetch(`/admin/enrollment/students?${params.toString()}`);
        const data = await response.json();

        if (!Array.isArray(data) || data.length === 0) {
            studentListBody.innerHTML = '<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No students found.</td></tr>';
            studentListStatus.textContent = 'No results for the selected filters.';
            return;
        }

        studentListBody.innerHTML = data.map((student) => {
            const subjects = student.subjects
                ? Object.entries(student.subjects)
                    .map(([type, list]) => `${type.replace('_', ' ')}: ${list.join(', ')}`)
                    .join(' | ')
                : '';

            return `
                <tr>
                    <td class="px-4 py-3 font-semibold text-slate-800">${student.name}</td>
                    <td class="px-4 py-3 text-slate-600">${student.email}</td>
                    <td class="px-4 py-3 text-slate-600">${student.grade_level ?? '-'}</td>
                    <td class="px-4 py-3 text-slate-600">${student.section ?? '-'}</td>
                    <td class="px-4 py-3 text-slate-500 text-xs">${subjects || '-'}</td>
                </tr>
            `;
        }).join('');

        studentListStatus.textContent = `Showing ${data.length} student(s).`;
    } catch (error) {
        studentListBody.innerHTML = '<tr><td colspan="5" class="px-4 py-6 text-center text-red-500">Failed to load students.</td></tr>';
        studentListStatus.textContent = 'Failed to load students.';
    }
}

async function updateTeacherSubjectOptions() {
    if (!teacherFilterGrade || !teacherFilterSubject) return;

    teacherFilterSubject.innerHTML = '<option value="">All Subjects</option>';

    const gradeValue = teacherFilterGrade.value;
    if (!gradeValue) {
        teacherFilterSubject.disabled = true;
        return;
    }

    try {
        const response = await fetch(`/admin/enrollment/teachers/subjects?grade_level=${gradeValue}`);
        const subjects = await response.json();

        if (Array.isArray(subjects) && subjects.length > 0) {
            subjects.forEach((subjectName) => {
                const option = document.createElement('option');
                option.value = subjectName;
                option.textContent = subjectName;
                teacherFilterSubject.appendChild(option);
            });
            teacherFilterSubject.disabled = false;
        } else {
            teacherFilterSubject.disabled = true;
        }
    } catch (error) {
        teacherFilterSubject.disabled = true;
    }
}

async function fetchTeacherList() {
    if (!teacherListBody || !teacherListStatus) return;

    const params = new URLSearchParams();
    if (teacherFilterGrade && teacherFilterGrade.value) params.set('grade_level', teacherFilterGrade.value);
    if (teacherFilterSubject && !teacherFilterSubject.disabled && teacherFilterSubject.value) {
        params.set('subject', teacherFilterSubject.value);
    }

    teacherListStatus.textContent = 'Loading teachers...';
    teacherListBody.innerHTML = '<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Loading...</td></tr>';

    try {
        const response = await fetch(`/admin/enrollment/teachers?${params.toString()}`);
        const data = await response.json();

        if (!Array.isArray(data) || data.length === 0) {
            teacherListBody.innerHTML = '<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No teachers found.</td></tr>';
            teacherListStatus.textContent = 'No results for the selected filters.';
            return;
        }

        teacherListBody.innerHTML = data.map((teacher) => {
            const gradeText = Array.isArray(teacher.grades) && teacher.grades.length > 0
                ? teacher.grades.map((g) => `G${g}`).join(', ')
                : '-';
            const subjectText = Array.isArray(teacher.subjects) && teacher.subjects.length > 0
                ? teacher.subjects.join(', ')
                : '-';

            return `
                <tr>
                    <td class="px-4 py-3 font-semibold text-slate-800">${teacher.name}</td>
                    <td class="px-4 py-3 text-slate-600">${teacher.email}</td>
                    <td class="px-4 py-3 text-slate-600">${gradeText}</td>
                    <td class="px-4 py-3 text-slate-600">${teacher.sections || '-'}</td>
                    <td class="px-4 py-3 text-slate-500 text-xs">${subjectText}</td>
                </tr>
            `;
        }).join('');

        teacherListStatus.textContent = `Showing ${data.length} teacher(s).`;
    } catch (error) {
        teacherListBody.innerHTML = '<tr><td colspan="5" class="px-4 py-6 text-center text-red-500">Failed to load teachers.</td></tr>';
        teacherListStatus.textContent = 'Failed to load teachers.';
    }
}

function getTeacherTypeLabel(type) {
    if (type === 'regular') return 'Regular';
    if (type === 'science_core') return 'Science Core';
    if (type === 'elective') return 'Elective';
    return type;
}

function getTeacherSubjectsByGradeAndType(grade, type) {
    const normalizedType = type === 'regular' ? 'core' : type;
    const results = subjectCatalog
        .filter((subject) =>
            Number(subject.grade_level_start) <= grade &&
            Number(subject.grade_level_end) >= grade &&
            subject.type === normalizedType
        )
        .map((subject) => subject.name);

    if (results.length === 0 && normalizedType === 'core') {
        return regularSubjectsByGrade[grade] ?? [];
    }

    return results;
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
            } else if (!previousSelections[grade] || previousSelections[grade]?.size === 0) {
                if (type === 'regular') {
                    input.checked = true;
                }
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

    subjectContainer.innerHTML = '';
    let hasContent = false;

    selectedGrades.forEach((grade) => {
        const gradeSubjects = [];

        if (grade <= 9) {
            const regularSubjects = getSubjectsForGrade(grade);
            if (regularSubjects.length > 0) {
                gradeSubjects.push({ label: 'Regular', items: regularSubjects });
            }
        } else {
            const selectedTypes = Array.from(
                teacherForm.querySelectorAll(`input[name="teacher_subject_groups[${grade}][]"]:checked`)
            ).map((input) => input.value);

            if (selectedTypes.includes('regular')) {
                const regularSubjects = getTeacherSubjectsByGradeAndType(grade, 'regular');
                if (regularSubjects.length > 0) {
                    gradeSubjects.push({ label: 'Regular', items: regularSubjects });
                }
            }
            if (selectedTypes.includes('science_core')) {
                const scienceSubjects = getTeacherSubjectsByGradeAndType(grade, 'science_core');
                if (scienceSubjects.length > 0) {
                    gradeSubjects.push({ label: 'Science Core', items: scienceSubjects });
                }
            }
            if (selectedTypes.includes('elective')) {
                const electiveSubjects = getTeacherSubjectsByGradeAndType(grade, 'elective');
                if (electiveSubjects.length > 0) {
                    gradeSubjects.push({ label: 'Elective', items: electiveSubjects });
                }
            }
        }

        if (gradeSubjects.length === 0) {
            return;
        }

        hasContent = true;

        const gradeBlock = document.createElement('div');
        gradeBlock.className = 'col-span-2 rounded border border-gray-200 bg-gray-50 p-3';

        const title = document.createElement('div');
        title.className = 'mb-2 text-sm font-semibold text-slate-700';
        title.textContent = `Grade ${grade}`;
        gradeBlock.appendChild(title);

        gradeSubjects.forEach((group) => {
            const groupTitle = document.createElement('div');
            groupTitle.className = 'text-xs font-semibold text-blue-700';
            groupTitle.textContent = group.label;
            gradeBlock.appendChild(groupTitle);

            const list = document.createElement('div');
            list.className = 'mt-1 grid grid-cols-2 gap-2';

            group.items.forEach((subjectName) => {
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
                list.appendChild(label);
            });

            gradeBlock.appendChild(list);
        });

        subjectContainer.appendChild(gradeBlock);
    });

    if (!hasContent) {
        subjectContainer.innerHTML = '<p class="col-span-2 text-sm text-gray-500">Select at least one assignment type for Grade 10-12 to show subjects.</p>';
    }
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

    sectionContainer.innerHTML = '';
    let hasSections = false;

    selectedGrades.forEach((grade) => {
        const gradeSections = sectionsByGrade[grade] ?? [];
        if (gradeSections.length === 0) {
            return;
        }

        hasSections = true;

        const gradeBlock = document.createElement('div');
        gradeBlock.className = 'col-span-2 rounded border border-gray-200 bg-gray-50 p-3';

        const title = document.createElement('div');
        title.className = 'mb-2 text-sm font-semibold text-slate-700';
        title.textContent = `Grade ${grade}`;
        gradeBlock.appendChild(title);

        const list = document.createElement('div');
        list.className = 'grid grid-cols-2 gap-2';

        gradeSections.forEach((sectionName) => {
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
            list.appendChild(label);
        });

        gradeBlock.appendChild(list);
        sectionContainer.appendChild(gradeBlock);
    });

    if (!hasSections) {
        sectionContainer.innerHTML = '<p class="col-span-2 text-sm text-gray-500">No sections configured for the selected grade level(s).</p>';
    }
}

const enrollmentGradeDropdown = document.querySelector('select[name="grade_level"]');
if (enrollmentGradeDropdown) {
    enrollmentGradeDropdown.addEventListener('change', updateEnrollmentSections);
    updateEnrollmentSections();
}
const enrollmentSubjectGroupCheckboxes = document.querySelectorAll('#studentSubjectGroupChecklist input[name="student_subject_groups[]"]');
enrollmentSubjectGroupCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener('change', updateEnrollmentSections);
});

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

renderTeacherSubjectTypeOptions();
renderTeacherSubjectOptions();
renderTeacherSectionOptions();
updateStudentFilterSectionOptions();

const showStudentListBtn = document.getElementById('showStudentListBtn');
const closeStudentListBtn = document.getElementById('closeStudentListBtn');
const studentListModal = document.getElementById('studentListModal');
const showTeacherListBtn = document.getElementById('showTeacherListBtn');
const closeTeacherListBtn = document.getElementById('closeTeacherListBtn');
const teacherListModal = document.getElementById('teacherListModal');
const bodyEl = document.body;

if (showStudentListBtn && studentListModal) {
    showStudentListBtn.addEventListener('click', () => {
        studentListModal.classList.remove('hidden');
        studentListModal.classList.add('flex');
        bodyEl.classList.add('overflow-hidden');
        fetchStudentList();
    });
}

if (closeStudentListBtn && studentListModal) {
    closeStudentListBtn.addEventListener('click', () => {
        studentListModal.classList.add('hidden');
        studentListModal.classList.remove('flex');
        bodyEl.classList.remove('overflow-hidden');
    });
}

if (studentListModal) {
    studentListModal.addEventListener('click', (event) => {
        if (event.target === studentListModal) {
            studentListModal.classList.add('hidden');
            studentListModal.classList.remove('flex');
            bodyEl.classList.remove('overflow-hidden');
        }
    });
}

if (studentFilterGrade) {
    studentFilterGrade.addEventListener('change', () => {
        updateStudentFilterSectionOptions();
        fetchStudentList();
    });
}

if (studentFilterSection) {
    studentFilterSection.addEventListener('change', fetchStudentList);
}

if (studentFilterGroup) {
    studentFilterGroup.addEventListener('change', fetchStudentList);
}

if (showTeacherListBtn && teacherListModal) {
    showTeacherListBtn.addEventListener('click', () => {
        teacherListModal.classList.remove('hidden');
        teacherListModal.classList.add('flex');
        bodyEl.classList.add('overflow-hidden');
        updateTeacherSubjectOptions();
        fetchTeacherList();
    });
}

if (closeTeacherListBtn && teacherListModal) {
    closeTeacherListBtn.addEventListener('click', () => {
        teacherListModal.classList.add('hidden');
        teacherListModal.classList.remove('flex');
        bodyEl.classList.remove('overflow-hidden');
    });
}

if (teacherListModal) {
    teacherListModal.addEventListener('click', (event) => {
        if (event.target === teacherListModal) {
            teacherListModal.classList.add('hidden');
            teacherListModal.classList.remove('flex');
            bodyEl.classList.remove('overflow-hidden');
        }
    });
}

if (teacherFilterGrade) {
    teacherFilterGrade.addEventListener('change', () => {
        updateTeacherSubjectOptions();
        fetchTeacherList();
    });
}

if (teacherFilterSubject) {
    teacherFilterSubject.addEventListener('change', fetchTeacherList);
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
</script>
</body>
</html>
