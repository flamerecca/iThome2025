<?php

use App\Models\Book;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/books', function () {
    DB::enableQueryLog();
    $users = Cache::remember('users_with_books', 600, function () {
        return User::with('books')->get(); // 避免 N+1
    });
    $books = collect();
    foreach ($users as $user) {
        $userData = collect();
        foreach ($user->books as $book) {
            $userData->push($book->title);
        }
        $books->push($user);
    }
    return DB::getQueryLog();
})->name('books');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Demo route for the Livewire counter component
Route::view('counter', 'counter-demo')->name('counter');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
