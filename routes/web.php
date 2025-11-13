<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


// Dashboard Route (redirect /home to /dashboard)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

//  Management Department
Route::middleware(['auth', 'permission:users.view'])->group(function () {
    Route::get('/department', function () {
        return view('department.index');
    })->name('department.index');
});

//  Management DOCUMENT TYPES
Route::middleware(['auth', 'permission:document_types.view'])->group(function () {
    Route::get('/document-types', function () {
        return view('document_types.index');
    })->name('document_types.index');
});
//  Management Document Prefix Settings
Route::middleware(['auth', 'permission:document_prefix_settings.view'])->group(function () {
    Route::get('/document_prefix_settings', function () {
        return view('document_prefix.index');
    })->name('document_prefix_settings.index');
});

// User Management Routes
Route::middleware(['auth', 'permission:users.view'])->group(function () {
    Route::get('/users', function () {
        return view('users.index');
    })->name('users.index');
});

// Role Management Routes  
Route::middleware(['auth', 'permission:roles.view'])->group(function () {
    Route::get('/roles', function () {
        return view('roles.index');
    })->name('roles.index');
});

// Profile Settings Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', function () {
        return view('profile.index');
    })->name('profile.index');
});

Auth::routes();

Route::get('/home', function () {
    return redirect()->route('dashboard');
})->middleware(['auth'])->name('home');
