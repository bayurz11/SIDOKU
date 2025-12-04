<?php

namespace App\Livewire\Ipc;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Shared\Traits\WithAlerts;
use App\Domains\Ipc\Models\TiupBotolCheck;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TiupBotolForm extends Component
{
    use WithFileUploads, WithAlerts;

    public bool $showModal = false;
    public bool $isEditing = false;

    public ?int $tiupId = null;

    public ?string $tanggal = null;
    public string $nama_botol = '';
    public ?string $drop_test = null;

    public ?string $penyebaran_rata = null;
    public ?string $bottom_tidak_menonjol = null;
    public ?string $tidak_ada_material = null;

    public ?string $catatan = null;

    // upload baru (opsional)
    public $gambar_drop_test;
    public $gambar_penyebaran_rata;
    public $gambar_bottom_tidak_menonjol;
    public $gambar_tidak_ada_material;

    // url gambar lama (untuk preview)
    public ?string $existing_gambar_drop_test_url = null;
    public ?string $existing_gambar_penyebaran_rata_url = null;
    public ?string $existing_gambar_bottom_tidak_menonjol_url = null;
    public ?string $existing_gambar_tidak_ada_material_url = null;

    protected $listeners = [
        'openTiupBotolForm' => 'open',
    ];

    public function rules(): array
    {
        return [
            'tanggal'               => ['required', 'date'],
            'nama_botol'            => ['required', 'string', 'max:255'],
            'drop_test'             => ['required', 'in:' . implode(',', array_keys(TiupBotolCheck::DROP_TEST))],

            'penyebaran_rata'       => ['nullable', 'in:' . implode(',', array_keys(TiupBotolCheck::OK_NOK))],
            'bottom_tidak_menonjol' => ['nullable', 'in:' . implode(',', array_keys(TiupBotolCheck::OK_NOK))],
            'tidak_ada_material'    => ['nullable', 'in:' . implode(',', array_keys(TiupBotolCheck::OK_NOK))],

            'catatan'               => ['nullable', 'string'],

            // gambar semua opsional
            'gambar_drop_test'              => ['nullable', 'image', 'max:2048'],
            'gambar_penyebaran_rata'        => ['nullable', 'image', 'max:2048'],
            'gambar_bottom_tidak_menonjol'  => ['nullable', 'image', 'max:2048'],
            'gambar_tidak_ada_material'     => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function open(?int $id = null): void
    {
        $this->resetValidation();
        $this->resetUploadFields();

        if ($id) {
            $this->isEditing = true;
            $this->tiupId = $id;

            $record = TiupBotolCheck::findOrFail($id);

            $this->tanggal               = optional($record->tanggal)->format('Y-m-d');
            $this->nama_botol            = $record->nama_botol;
            $this->drop_test             = $record->drop_test;
            $this->penyebaran_rata       = $record->penyebaran_rata;
            $this->bottom_tidak_menonjol = $record->bottom_tidak_menonjol;
            $this->tidak_ada_material    = $record->tidak_ada_material;
            $this->catatan               = $record->catatan;

            // pakai accessor dari model
            $this->existing_gambar_drop_test_url          = $record->drop_test_image_url;
            $this->existing_gambar_penyebaran_rata_url    = $record->penyebaran_rata_image_url;
            $this->existing_gambar_bottom_tidak_menonjol_url = $record->bottom_tidak_menonjol_image_url;
            $this->existing_gambar_tidak_ada_material_url = $record->tidak_ada_material_image_url;
        } else {
            $this->isEditing = false;
            $this->tiupId = null;

            $this->reset([
                'tanggal',
                'nama_botol',
                'drop_test',
                'penyebaran_rata',
                'bottom_tidak_menonjol',
                'tidak_ada_material',
                'catatan',
            ]);
        }

        $this->showModal = true;
    }

    protected function resetUploadFields(): void
    {
        $this->gambar_drop_test = null;
        $this->gambar_penyebaran_rata = null;
        $this->gambar_bottom_tidak_menonjol = null;
        $this->gambar_tidak_ada_material = null;

        $this->existing_gambar_drop_test_url = null;
        $this->existing_gambar_penyebaran_rata_url = null;
        $this->existing_gambar_bottom_tidak_menonjol_url = null;
        $this->existing_gambar_tidak_ada_material_url = null;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    /**
     * Simpan file ke disk 'public_tiup_botol' (public/tiup_botol).
     * Kalau $file null → kembalikan nama lama (biar tidak dihapus).
     */
    protected function storeImage($file, ?string $oldFilename = null): ?string
    {
        if (! $file) {
            return $oldFilename;
        }

        if ($oldFilename) {
            Storage::disk('public_tiup_botol')->delete($oldFilename);
        }

        // root disk = public/tiup_botol → store di root disk
        $storedPath = $file->store('', 'public_tiup_botol'); // menghasilkan namaFile.ext
        return basename($storedPath);
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'tanggal'               => $this->tanggal,
            'nama_botol'            => $this->nama_botol,
            'drop_test'             => $this->drop_test,
            'penyebaran_rata'       => $this->penyebaran_rata,
            'bottom_tidak_menonjol' => $this->bottom_tidak_menonjol,
            'tidak_ada_material'    => $this->tidak_ada_material,
            'catatan'               => $this->catatan,
            'created_by'            => Auth::id(),
        ];

        if ($this->tiupId) {
            $record = TiupBotolCheck::findOrFail($this->tiupId);

            $data['drop_test_image']              = $this->storeImage($this->gambar_drop_test, $record->drop_test_image);
            $data['penyebaran_rata_image']        = $this->storeImage($this->gambar_penyebaran_rata, $record->penyebaran_rata_image);
            $data['bottom_tidak_menonjol_image']  = $this->storeImage($this->gambar_bottom_tidak_menonjol, $record->bottom_tidak_menonjol_image);
            $data['tidak_ada_material_image']     = $this->storeImage($this->gambar_tidak_ada_material, $record->tidak_ada_material_image);

            $record->update($data);

            $this->showSuccessToast('Data tiup botol berhasil diupdate!');
        } else {
            $data['drop_test_image']              = $this->storeImage($this->gambar_drop_test);
            $data['penyebaran_rata_image']        = $this->storeImage($this->gambar_penyebaran_rata);
            $data['bottom_tidak_menonjol_image']  = $this->storeImage($this->gambar_bottom_tidak_menonjol);
            $data['tidak_ada_material_image']     = $this->storeImage($this->gambar_tidak_ada_material);

            TiupBotolCheck::create($data);

            $this->showSuccessToast('Data tiup botol berhasil disimpan!');
        }

        $this->showModal = false;

        $this->dispatch('tiup-botol:saved');
    }

    public function render()
    {
        return view('livewire.ipc.tiup-botol-form');
    }
}
