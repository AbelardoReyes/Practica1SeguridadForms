<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });
Route::get('/', function () {
    return Redirect::route('loginView');
});

Route::prefix('auth')->group(function () {
    Route::get('/login', function () {
        return Inertia::render('Auth/LoginForm');
    })->name('loginView');
    Route::get('/register', function () {
        return Inertia::render('Auth/RegisterForm');
    })->name('registerView');
    Route::get('/verify-email', function () {
        return Inertia::render('VerifyEmailForm');
    })->name('verifyEmailView');
    Route::get('activeUser', function () {
        return Inertia::render('Auth/ActiveUser');
    })->name('activeUserView');
    Route::get('/Home', function () {
        $user = session('user');
        return Inertia::render('Sections/Home', [
            'user' => $user
        ]);
    })->name('Home')->middleware('auth');
});

require __DIR__ . '/auth.php';
