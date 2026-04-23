<?php

namespace App\Livewire\Ipc;

use App\Domains\Ipc\Services\IpcProductCheckImportService;
use App\Imports\IpcProductCheckImport;
use App\Shared\Traits\WithAlerts;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class IpcProductImport extends Component
{
    use WithAlerts, WithFileUploads;

    public bool $showModal = false;

    public $excel_file;

    public int $importedCount = 0;

    public int $skippedCount = 0;

    public array $importErrors = [];

    protected $listeners = [
        'openIpcProductImport' => 'openModal',
    ];

    protected function rules(): array
    {
        return [
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ];
    }

    public function openModal(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->showModal = true;
        $this->excel_file = null;
        $this->importedCount = 0;
        $this->skippedCount = 0;
        $this->importErrors = [];
    }

    public function closeModal(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->reset([
            'excel_file',
            'importedCount',
            'skippedCount',
            'importErrors',
            'showModal',
        ]);

        $this->showModal = false;
    }

    public function import(): void
    {
        $this->validate();

        Storage::disk('local')->makeDirectory('imports');

        $path = $this->excel_file->store('imports', 'local');

        if (! Storage::disk('local')->exists($path)) {
            $this->addError('excel_file', 'File upload tidak ditemukan di server. Coba upload ulang.');

            return;
        }

        $this->importedCount = 0;
        $this->skippedCount = 0;
        $this->importErrors = [];

        try {
            $import = new IpcProductCheckImport(
                app(IpcProductCheckImportService::class),
                auth()->id()
            );

            Excel::import($import, $path, 'local');

            $this->importedCount = $import->importedCount();
            $this->skippedCount = $import->skippedCount();
            $this->importErrors = $import->errors();

            if ($this->importedCount === 0 && $this->skippedCount === 0) {
                $this->addError('excel_file', 'File Excel kosong atau tidak memiliki data.');

                return;
            }

            if ($this->importedCount === 0 && $this->skippedCount > 0) {
                $this->addError('excel_file', 'Tidak ada data yang berhasil diimport. Periksa header dan isi file Excel.');

                return;
            }

            $this->showSuccessToast(
                "Import IPC selesai. Berhasil: {$this->importedCount}, dilewati: {$this->skippedCount}."
            );

            $this->dispatch('ipc:product_check_saved');
            $this->closeModal();
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError(
                'excel_file',
                app()->environment('testing')
                    ? $exception->getMessage()
                    : 'Import gagal diproses. Periksa format file lalu coba lagi.'
            );
        } finally {
            Storage::disk('local')->delete($path);
        }
    }

    public function render()
    {
        return view('livewire.ipc.ipc-product-import');
    }
}
