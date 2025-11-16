<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Domains\Document\Models\Document;


Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Dashboard Route (redirect /home to /dashboard)
Route::get('/dashboard', function () {

    $recentDocuments = Document::query()
        ->with(['documentType', 'department', 'updatedBy', 'createdBy'])
        ->orderByDesc('updated_at')
        ->limit(5)
        ->get();

    return view('dashboard', [
        'stats' => [
            'recent_documents' => $recentDocuments
        ]
    ]);
})
    ->middleware(['auth'])
    ->name('dashboard');
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

//  Management Document 
Route::middleware(['auth', 'permission:documents.view'])->group(function () {
    Route::get('/documents', function () {
        return view('documents.index');
    })->name('documents.index');
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
