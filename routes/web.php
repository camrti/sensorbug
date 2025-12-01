<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;


Route::redirect('/', '/login')
    ->name('home');


Route::view('dashboard', 'dashboard.index') //  url, view name
    ->middleware(['auth', 'verified'])
    ->name('dashboard'); // assign name to the route

Route::view('pages', 'dashboard.pages')
    ->middleware(['auth', 'verified'])
    ->name('pages');

Route::view('shops', 'dashboard.shops')
    ->middleware(['auth', 'verified'])
    ->name('shops');

Route::view('sellers', 'dashboard.sellers')
    ->middleware(['auth', 'verified'])
    ->name('sellers');

Route::view('sqs', 'dashboard.sqs')
    ->middleware(['auth', 'verified'])
    ->name('sqs');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

Route::middleware(['auth', 'isAdmin'])->group(function () {
    Route::view('users-list', 'dashboard.users-list')
        ->name('users-list');

    Route::view('tenants/{tenant}/users', 'dashboard.users-list')
        ->name('tenants.users');
});

Route::middleware(['auth', 'isSuperadmin'])->group(function () {
    Route::view('superadmin/tenants', 'dashboard.superadmin-tenants')
        ->name('superadmin.tenants');

    Route::view('admin/news', 'dashboard.admin-news')
        ->name('admin.news');

    Route::view('admin/tracking-interests', 'dashboard.tracking-interests-management')
        ->name('admin.tracking-interests');

    Route::view('admin/tracking-interest-assignments', 'dashboard.tracking-interest-assignments')
        ->name('admin.tracking-interest-assignments');

    Route::view('csv-upload', 'dashboard.csv-upload')
        ->name('csv-upload');
});

Route::impersonate();

require __DIR__.'/auth.php';