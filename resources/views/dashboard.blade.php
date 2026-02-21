<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
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
    <div class="app">
        <header class="topbar">
            <div class="logo">
                <div class="logo-circle"></div>
                <div class="logo-text">
                    <div>PHILIPPINE SCIENCE HIGH SCHOOL</div>
                    <div class="logo-sub">Caraga Region Campus in Butuan City</div>
                </div>
            </div>

 <div class="top-actions">
    @auth
        <button class="scholar-btn" onclick="openScholarPanel()">
            <div class="flex items-center gap-2 bg-blue-700/50 px-4 py-2 rounded-full border border-blue-400/30">
    <i class="fas fa-user text-blue-200"></i>
    <span class="text-white font-bold">
        @if(auth()->user()->role === 'admin')
            Admin: 
        @elseif(auth()->user()->role === 'teacher')
            Teacher: 
        @else
            Scholar: 
        @endif
        {{ auth()->user()->name }}
    </span>
</div>
        </button>
    @endauth

    @guest
        <a href="{{ route('login') }}" class="top-link">Log in</a>
        <a href="{{ route('register') }}" class="top-link">Register</a>
    @endguest
</div>
        </header>

<main class="main">
<aside id="sidebar" class="sidebar">
    <div class="menu-icon" onclick="toggleSidebar()">☰</div>
    <div class="menu-title">Menu</div>
    
    <div class="menu-item active" onclick="showTab('calendar')">Dashboard</div>
    
    @if(auth()->user()->role === 'admin')
        <div class="menu-item" onclick="showTab('enrollment')">Enrollment</div>
    @endif

    <div class="menu-item">Subjects</div>
    <div class="menu-item">Grades</div>
</aside>

    <section id="calendar-tab" class="content-tab">
        <section class="calendar-section">
            <div class="calendar-title">{{ $date->format('F Y') }}</div>
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

                <div class="calendar-day cursor-pointer hover:bg-blue-50 transition" onclick="openPanel({{ $day }}, '{{ $currentDateString }}')">
                    <span>{{ $day }}</span>
                    
                    @if($count > 0)
                        <div class="flex gap-1 justify-center">
                            @if($count >= 3)
                                <span class="h-2 w-2 bg-red-500 rounded-full" title="Fully Booked"></span>
                            @elseif($count >= 2)
                                <span class="h-2 w-2 bg-yellow-500 rounded-full" title="Approaching Limit"></span>
                            @else
                                <span class="h-2 w-2 bg-blue-500 rounded-full" title="Assessments Scheduled"></span>
                            @endif
                        </div>
                    @endif
                </div>
            @endfor </div> </section> </section> @if(auth()->user()->role === 'admin')
    @endif
</div>
        </section>
    </section>

    @if(auth()->user()->role === 'admin')
    <section id="enrollment-tab" class="content-tab" style="display: none; padding: 40px;">
        <div class="enrollment-card">
            <h2>Student Enrollment</h2>
            <p>Register a new scholar into the system.</p>
            <hr style="margin: 20px 0; opacity: 0.2;">
            
            <form action="{{ route('admin.enroll') }}" method="POST" class="mini-form">
                @csrf
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" placeholder="Juan Dela Cruz" required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="juan@pshs.edu.ph" required>
                </div>

                <div class="form-row" style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Grade Level</label>
                        <select name="grade_level" style="width: 100%;">
                            <option value="7">Grade 7</option>
                            <option value="8">Grade 8</option>
                            <option value="9">Grade 9</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Section</label>
                        <input type="text" name="section" placeholder="Diamond" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Temporary Password</label>
                    <input type="password" name="password" required value="PSHS_2026">
                </div>

                <button type="submit" class="scholar-btn" style="width: 100%; margin-top: 10px;">Enroll Student</button>
            </form>
        </div>
    </section>
    @endif
</main>
    </div>



<div id="scholarPanel" class="side-panel">
    <div class="panel-header">
        <h3>Scholar Details</h3>
        <button onclick="closeAllPanels()" class="close-btn">&times;</button>
    </div>
    <div class="panel-body">
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
        <strong class="text-blue-700">{{ auth()->user()->section ?? 'Unassigned' }}</strong>
    </div>
</div>
                </div>

                <div class="info-group">
                    <label>Recent Grades</label>
                    <ul class="grade-list">
                        <li><span>Mathematics</span> <strong class="grade">94</strong></li>
                        <li><span>Science</span> <strong class="grade">91</strong></li>
                        <li><span>English</span> <strong class="grade">88</strong></li>
                    </ul>
                </div>
            @else
                <p class="text-muted">Accessing administrative dashboard tools.</p>
            @endif
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

    <script>
let tempDate = '';
let tempDateString = '';
const sectionsByGrade = {
    "7": ["Opal", "Turquoise", "Aquamarine", "Sapphire"],
    "8": ["Anthurium", "Carnation", "Daffodil", "Sunflower"],
    "9": ["Calcium", "Lithium", "Barium", "Sodium"],
    "10": ["Graviton", "Proton", "Neutron", "Electron"],
    "11": ["Mars", "Mercury", "Venus"],
    "12": ["Orosa", "Del Mundo", "Zara"]
};

function updateEnrollmentSections() {
    const grade = document.querySelector('select[name="grade_level"]').value;
    const sectionInput = document.querySelector('input[name="section"]');
    
    // Optional: Turn the section input into a dropdown for better UX
    console.log("Suggested sections for Grade " + grade + ": " + sectionsByGrade[grade].join(", "));
}
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

    // If the button exists, hide it for students
    if (scheduleBtn) {
        if (userRole === 'student') {
            scheduleBtn.classList.add('hidden'); // Hide for students
        } else {
            scheduleBtn.classList.remove('hidden'); // Show for teachers
        }
    }

    // Show the Choice Modal
    document.getElementById('choiceDateTitle').innerText = dateString;
    document.getElementById('choiceModal').classList.remove('hidden');
    document.getElementById('choiceModal').classList.add('flex');
}
async function handleChoice(action) {
    closeChoiceModal();
    
    if (action === 'schedule') {
        document.getElementById('panelDateTitle').innerText = "Schedule for " + tempDateString;
        document.getElementById('hiddenDate').value = tempDate;
        document.getElementById('assessmentPanel').classList.add('open');
        document.getElementById('panelOverlay').classList.add('active');
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
            const response = await fetch(`/api/assessments-by-date?date=${tempDate}`);
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

function updateSubjects() {
    const selectedGrade = document.getElementById('gradeSelect').value;
    const subjectSelect = document.getElementById('subjectSelect');
    
    subjectSelect.innerHTML = '<option value="">Select Subject</option>';
    
    // 2. Logic: Only show subjects if the teacher is assigned to this grade
    if (assignedGrades.includes(parseInt(selectedGrade))) {
        assignedSubjects.forEach(subject => {
            const option = document.createElement('option');
            option.value = subject;
            option.text = subject;
            subjectSelect.appendChild(option);
        });
    } else {
        const option = document.createElement('option');
        option.text = "You aren't assigned to this grade";
        option.disabled = true;
        subjectSelect.appendChild(option);
    }
}
        function openScholarPanel() {
            document.getElementById('scholarPanel').classList.add('open');
            document.getElementById('panelOverlay').classList.add('active');
        }

        function closeAllPanels() {
            document.querySelectorAll('.side-panel').forEach(p => p.classList.remove('open'));
            document.getElementById('panelOverlay').classList.remove('active');
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

            <div class="form-group">
                <label>Assessment Title</label>
                <input type="text" name="title" placeholder="e.g., Long Test in Math" required>
            </div>

            <div class="form-group">
                <label>Target Grade Level</label>
                <select name="grade_level" id="gradeSelect" onchange="updateSubjects()" required>
                    <option value="">Select Grade</option>
                    <option value="7">Grade 7</option>
                    <option value="8">Grade 8</option>
                    <option value="9">Grade 9</option>
                    <option value="10">Grade 10</option>
                    <option value="11">Grade 11</option>
                    <option value="12">Grade 12</option>
                </select>
            </div>

            <div class="form-group">
                <label>Subject</label>
                <select name="subject" id="subjectSelect" required>
                    <option value="">Select a Grade first</option>
                </select>
            </div>

            <div class="form-group">
                <label>Assessment Type</label>
                <select name="type" required>
                    <option value="Formative Assessment">Formative Assessment (FA)</option>
                    <option value="Alternative Assessment">Alternative Assessment (AA)</option>
                    <option value="Long Test">Long Test</option>
                    <option value="Summative">Summative Assessment</option>
                </select>
            </div>

            <div class="form-row" style="display: flex; gap: 10px;">
                <div class="form-group" style="flex: 1;">
                    <label>Deadline Time</label>
                    <input type="time" name="due_time" required>
                </div>
            </div>
            <div id="awas-advice" class="text-xs mt-2 p-2 rounded bg-blue-50 text-blue-700 hidden">
    </div>
            <button type="submit" class="scholar-btn" style="width: 100%; margin-top: 15px;">Save Assessment</button>
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
</body>
</html>