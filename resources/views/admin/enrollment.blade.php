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

        @if(session('success'))
            <div id="successToast" class="toast-notification">
                <span class="icon">OK</span> {{ session('success') }}
            </div>
            <script>
                setTimeout(() => {
                    const toast = document.getElementById('successToast');
                    if (!toast) return;
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
                        <a href="{{ route('admin.enrollment') }}" class="hover:text-blue-200 transition underline-offset-8 hover:underline">Enrollment</a>
                    </nav>
                    <div class="top-actions">
                        @auth
                            <span class="text-sm font-bold tracking-tight">
                                {{ ucfirst(auth()->user()->role) }}: {{ auth()->user()->name }}
                            </span>
                        @endauth
                    </div>
                </div>
            </header>

            <main class="main" style="display: flex; align-items: flex-start;">
                <div class="flex-1 w-full">
                    <section class="content-tab" style="display: block; padding: 40px;">
                        <div class="mx-auto grid max-w-6xl gap-6 lg:grid-cols-2">
                            <div class="enrollment-card bg-white shadow-lg rounded-lg p-8">
                                <h2 class="text-2xl font-bold text-blue-900">Student Enrollment</h2>
                                <p class="text-gray-500">Register a new scholar into the system.</p>
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
const subjectCatalog = @json($subjectCatalog ?? []);

const studentGroupSubjectCatalog = {
    10: {
        elective: [
            'Philippine Biodiversity',
            'Field Sampling',
            'Astronomy',
            'Philosophy of Science',
        ],
        science_core: [],
        regular: [],
    },
    11: {
        elective: [
            'Biology 3 Level 1',
            'Biology 3 Level 2',
            'Chemistry 3 Level 1',
            'Chemistry 3 Level 2',
            'Physics 3 Level 1',
            'Physics 3 Level 2',
            'Computer Science 5 Level 1',
            'Computer Science 5 Level 2',
            'Design and Make Technology 1 Level 1',
            'Design and Make Technology 1 Level 2',
            'Engineering 1 Level 1',
            'Engineering 1 Level 2',
            'Agriculture 1 Level 1',
            'Agriculture 1 Level 2',
        ],
        science_core: [
            'Physics 3 Level 1',
            'Physics 3 Level 2',
            'Biology 3 Level 1',
            'Biology 3 Level 2',
            'Chemistry 3 Level 1',
            'Chemistry 3 Level 2',
        ],
        regular: [],
    },
    12: {
        elective: [
            'Biology 3 Level 1',
            'Biology 3 Level 2',
            'Chemistry 3 Level 1',
            'Chemistry 3 Level 2',
            'Physics 3 Level 1',
            'Physics 3 Level 2',
            'Computer Science 5 Level 1',
            'Computer Science 5 Level 2',
            'Design and Make Technology 1 Level 1',
            'Design and Make Technology 1 Level 2',
            'Engineering 1 Level 1',
            'Engineering 1 Level 2',
            'Agriculture 1 Level 1',
            'Agriculture 1 Level 2',
        ],
        science_core: [
            'Physics 4 Level 1',
            'Physics 4 Level 2',
            'Biology 4 Level 1',
            'Biology 4 Level 2',
            'Chemistry 4 Level 1',
            'Chemistry 4 Level 2',
        ],
        regular: [],
    },
};

function getSubjectsForGrade(grade) {
    return subjectsByGrade[grade] ?? fallbackSubjects;
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
                            <input type="checkbox" name="selected_subjects[regular][]" value="${item.replace(/"/g, '&quot;')}" class="rounded border-gray-300" ${previous.regular.has(item) ? 'checked' : ''}>
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
    const groupScienceCore = document.getElementById('studentGroupScienceCore');
    const groupElective = document.getElementById('studentGroupElective');
    const groupHint = document.getElementById('studentGroupHint');
    const sectionDropdown = document.getElementById('sectionDropdown');

    if (!gradeDropdown || !sectionDropdown || !groupRegular || !groupScienceCore || !groupElective || !groupHint) return;

    const selectedGrade = gradeDropdown.value;
    const selectedGradeInt = parseInt(selectedGrade, 10);

    groupRegular.disabled = false;
    groupScienceCore.disabled = false;
    groupElective.disabled = false;
    groupHint.textContent = '';

    if (selectedGradeInt >= 7 && selectedGradeInt <= 9) {
        groupRegular.checked = true;
        groupRegular.disabled = true;
        groupScienceCore.checked = false;
        groupScienceCore.disabled = true;
        groupElective.checked = false;
        groupElective.disabled = true;
        groupHint.textContent = 'Grades 7-9 use Regular only.';
    } else if (selectedGradeInt === 10) {
        groupRegular.disabled = false;
        groupScienceCore.checked = false;
        groupScienceCore.disabled = true;
        groupElective.disabled = false;
        if (!groupRegular.checked && !groupElective.checked) {
            groupElective.checked = true;
        }
        groupHint.textContent = 'Grade 10 can use Regular and/or Elective.';
    } else if (selectedGradeInt === 11 || selectedGradeInt === 12) {
        groupRegular.disabled = false;
        groupScienceCore.disabled = false;
        groupElective.disabled = false;
        if (!groupRegular.checked && !groupScienceCore.checked && !groupElective.checked) {
            groupElective.checked = true;
        }
        groupHint.textContent = 'Grades 11-12 can use Regular, Elective, and/or Science Core.';
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
</script>
</body>
</html>
