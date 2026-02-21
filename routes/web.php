<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

/**
 * PUBLIC ROUTES
 */
Route::get('/', function () {
    return view('welcome');
})->name('home'); // CRITICAL FIX: Tests and some components look for the 'home' route.

/**
 * AUTHENTICATED ROUTES
 */
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Assessments
    Route::post('/assessments', [AssessmentController::class, 'store'])->name('assessments.store');
    Route::delete('/assessments/{assessment}', [AssessmentController::class, 'destroy'])->name('assessments.destroy');

    /**
     * API: FETCH ASSESSMENTS FOR THE SIDE PANEL
     */
    Route::get('/api/assessments-by-date', function (Request $request) {
        $user = auth()->user();
        $targetDate = $request->query('date');
        if (!$targetDate) return response()->json([]);

        $query = Assessment::whereDate('scheduled_at', $targetDate);
        
        if ($user && $user->role === 'student') {
            $query->where('grade_level', $user->grade_level);
        }

        return $query->get()->map(function($a) {
            return [
                'id' => $a->id,
                'title' => $a->title,
                'subject' => $a->subject ? $a->subject->name : $a->type,
                'description' => $a->description,
                'due_time' => $a->scheduled_at ? Carbon::parse($a->scheduled_at)->format('g:i A') : 'No time set'
            ];
        });
    });

    /**
     * API: FETCH NOTIFICATION DOTS
     */
    Route::get('/api/assessment-notifications', function (Request $request) {
        $user = auth()->user();
        $month = $request->query('month', date('m'));
        $year = $request->query('year', date('Y'));

        $query = Assessment::whereMonth('scheduled_at', $month)->whereYear('scheduled_at', $year);
        
        if ($user && $user->role === 'student') {
            $query->where('grade_level', $user->grade_level);
        }

        return $query->get()
            ->groupBy(fn($val) => Carbon::parse($val->scheduled_at)->format('j'))
            ->map->count();
    });

    /**
     * ADMIN: ENROLL LOGIC
     */
    Route::post('/admin/enroll', function (Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'grade_level' => 'required',
            'section' => 'required|string',
            'password' => 'required|min:8',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => 'student',
            'grade_level' => $validated['grade_level'],
            'section' => $validated['section'],
        ]);

        return back()->with('success', 'Student enrolled successfully!');
    })->name('admin.enroll');

    /**
     * ADMIN: EMAIL SUMMARY
     */
    Route::get('/admin/send-daily-summary', function () {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $today = now()->toDateString();
        $assessments = Assessment::whereDate('scheduled_at', $today)->get();

        if ($assessments->isEmpty()) return "No assessments today.";

        $teachers = User::where('role', 'admin')->get();
        foreach ($teachers as $teacher) {
            Mail::send([], [], function ($message) use ($teacher, $today) {
                $message->to($teacher->email)
                    ->subject("Daily Schedule - $today")
                    ->html("Check the dashboard for today's tasks.");
            });
        }
        return "Emails sent.";
    });
});

// Load the Auth routes (login, register, etc)
require __DIR__.'/auth.php';