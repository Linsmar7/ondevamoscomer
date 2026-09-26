<?php

declare(strict_types=1);

use App\Http\Controllers\JoinListController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
  return auth()->check() ? redirect()->route('dashboard') : view('welcome');
});

Route::middleware(['auth'])->group(function () {
  Volt::route('dashboard', 'restaurant-lists.index')->name('dashboard');
  Volt::route('lists/{restaurantList}', 'restaurant-lists.show')->name('lists.show');
  Route::get('lists/join/{code}', JoinListController::class)->name('lists.join');
});

Route::view('profile', 'profile')
  ->middleware(['auth'])
  ->name('profile');

require __DIR__.'/auth.php';
