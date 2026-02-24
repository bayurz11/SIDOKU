<?php

use App\Domains\Document\Models\Document;
use App\Domains\Ipc\Models\IpcProductCheck;
use App\Livewire\Document\DocumentImportTemplateExport;
use App\Livewire\Ipc\IpcProductImportTemplateExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;


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
    // Moisture Summary (untuk chart dashboard)
    $baseQuery = IpcProductCheck::query()
        ->when($this->search, function ($q) {
            $term = '%' . $this->search . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('product_name', 'like', $term);
            });
        })
        ->when($this->filterLineGroup, fn($q) => $q->where('line_group', $this->filterLineGroup))
        ->when($this->filterSubLine, fn($q) => $q->where('sub_line', $this->filterSubLine))
        ->when($this->filterDateFrom, fn($q) => $q->whereDate('test_date', '>=', $this->filterDateFrom))
        ->when($this->filterDateTo, fn($q) => $q->whereDate('test_date', '<=', $this->filterDateTo));

    // data utama untuk tabel (pakai pagination & sorting)
    $data = (clone $baseQuery)
        ->orderBy($this->sortField, $this->sortDirection)
        ->paginate($this->perPage);

    // RINGKASAN UNTUK CHART:
    // rata-rata moisture per line_group + sub_line di rentang filter
    $moistureSummary = (clone $baseQuery)
        ->whereNotNull('avg_moisture_percent')
        ->selectRaw('line_group, sub_line, AVG(avg_moisture_percent) as avg_moisture, COUNT(*) as total_samples')
        ->groupBy('line_group', 'sub_line')
        ->get();
    return view('dashboard', [
        'stats' => [
            'recent_documents' => $recentDocuments,
            'moistureSummary' => $moistureSummary,
        ],
    ]);
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

//  Management Document 
Route::middleware(['auth', 'permission:documents.view'])->group(function () {
    Route::get('/documents', function () {
        return view('documents.index');
    })->name('documents.index');
});

Route::get('/documents/import/template', function () {
    return Excel::download(
        new DocumentImportTemplateExport(),
        'template-import-dokumen.xlsx'
    );
})->name('documents.import-template');

// DOCUMENT APPROVAL QUEUE
Route::middleware(['auth', 'permission:documents.review|documents.approve'])
    ->get('/documents/approval-queue', function () {
        return view('approval-queue.index');
    })
    ->name('documents.approval-queue');


//IPC Kadar Air
Route::middleware(['auth', 'permission:ipc_product_checks.view'])
    ->name('ipc.product-checks.')
    ->prefix('ipc/product-checks')
    ->group(function () {
        Route::get('/', function () {
            return view('product_checks.index');
        })->name('index');
    });

Route::get('/ipc/product-checks/import/template', function () {
    return Excel::download(
        new IpcProductImportTemplateExport(),
        'template-import-ipc-kadar-air-berat.xlsx'
    );
})->name('ipc.product-checks.import-template');

//IPC Tiup Botol
Route::middleware(['auth', 'permission:ipc_product_checks.view'])
    ->name('ipc.tiup-botol.')
    ->prefix('ipc/tiup-botol')
    ->group(function () {
        Route::get('/', function () {
            return view('tiup-botol.index');
        })->name('index');
    });

Route::get('/ipc/tiup-botol/import/template', function () {
    return Excel::download(
        new IpcProductImportTemplateExport(),
        'template-import-ipc-kadar-air-berat.xlsx'
    );
})->name('ipc.tiup-botol.import-template');

// IPC PRODUK
Route::middleware(['auth', 'permission:ipc_product_checks.view'])
    ->name('ipc.product.')
    ->prefix('ipc/product')
    ->group(function () {
        Route::get('/', function () {
            return view('product.index');
        })->name('index');
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
