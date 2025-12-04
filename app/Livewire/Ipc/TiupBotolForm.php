<?php

namespace App\Livewire\Ipc;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Shared\Traits\WithAlerts;
use App\Domains\Ipc\Models\TiupBotolCheck;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

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

    // upload baru (HARUS pakai nama properti ini)
    public $drop_test_image;
    public $penyebaran_rata_image;
    public $bottom_tidak_menonjol_image;
    public $tidak_ada_material_image;

    // url gambar lama (untuk preview)
    public ?string $existing_drop_test_image_url = null;
    public ?string $existing_penyebaran_rata_image_url = null;
    public ?string $existing_bottom_tidak_menonjol_image_url = null;
    public ?string $existing_tidak_ada_material_image_url = null;

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

            // gambar opsional
            'drop_test_image'              => ['nullable', 'image', 'max:2048'],
            'penyebaran_rata_image'        => ['nullable', 'image', 'max:2048'],
            'bottom_tidak_menonjol_image'  => ['nullable', 'image', 'max:2048'],
            'tidak_ada_material_image'     => ['nullable', 'image', 'max:2048'],
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

            // URL gambar lama
            $this->existing_drop_test_image_url =
                $record->drop_test_image_url;
            $this->existing_penyebaran_rata_image_url =
                $record->penyebaran_rata_image_url;
            $this->existing_bottom_tidak_menonjol_image_url =
                $record->bottom_tidak_menonjol_image_url;
            $this->existing_tidak_ada_material_image_url =
                $record->tidak_ada_material_image_url;
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
        $this->drop_test_image = null;
        $this->penyebaran_rata_image = null;
        $this->bottom_tidak_menonjol_image = null;
        $this->tidak_ada_material_image = null;

        $this->existing_drop_test_image_url = null;
        $this->existing_penyebaran_rata_image_url = null;
        $this->existing_bottom_tidak_menonjol_image_url = null;
        $this->existing_tidak_ada_material_image_url = null;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    protected function storeImage($file, ?string $oldFilename = null): ?string
    {
        if (! $file) {
            // tidak upload baru, pakai file lama (kalau ada)
            return $oldFilename;
        }

        // hapus file lama
        if ($oldFilename) {
            Storage::disk('public')->delete(TiupBotolCheck::imagePath() . '/' . $oldFilename);
        }

        $storedPath = $file->store(TiupBotolCheck::imagePath(), 'public'); // tiup_botol/xxx.jpg
        return basename($storedPath); // simpan hanya nama file
    }

    public function save(): void
    {
        $this->validate();

        $hari = $this->tanggal
            ? Carbon::parse($this->tanggal)->translatedFormat('l')
            : null;

        $data = [
            'tanggal'               => $this->tanggal,
            'hari'                  => $hari,
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

            $data['drop_test_image'] = $this->storeImage($this->drop_test_image, $record->drop_test_image);
            $data['penyebaran_rata_image'] = $this->storeImage($this->penyebaran_rata_image, $record->penyebaran_rata_image);
            $data['bottom_tidak_menonjol_image'] = $this->storeImage($this->bottom_tidak_menonjol_image, $record->bottom_tidak_menonjol_image);
            $data['tidak_ada_material_image'] = $this->storeImage($this->tidak_ada_material_image, $record->tidak_ada_material_image);

            $record->update($data);
            $this->showSuccessToast('Data tiup botol berhasil diupdate!');
        } else {
            $data['drop_test_image'] = $this->storeImage($this->drop_test_image);
            $data['penyebaran_rata_image'] = $this->storeImage($this->penyebaran_rata_image);
            $data['bottom_tidak_menonjol_image'] = $this->storeImage($this->bottom_tidak_menonjol_image);
            $data['tidak_ada_material_image'] = $this->storeImage($this->tidak_ada_material_image);

            TiupBotolCheck::create($data);
            $this->showSuccessToast('Data tiup botol berhasil disimpan!');
        }

        $this->showModal = false;

        // Refresh list di komponen list
        $this->dispatch('tiup-botol:saved');
    }

    public function render()
    {
        return view('livewire.ipc.tiup-botol-form');
    }
}
