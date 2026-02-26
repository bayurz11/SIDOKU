<?php

namespace App\Livewire\IncomingMaterial;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IncomingMaterialForm extends Component
{
    use WithFileUploads;

    // ================= PRIMARY =================
    public ?int $incomingId = null;

    // ================= BASIC INFO =================
    public string $name_of_goods = '';
    public string $supplier_name = '';
    public ?string $receipt_date = null;
    public ?string $expired_date = null;
    public string $batch_number = '';
    public ?int $quantity = null;

    // ================= DOCUMENT SUITABILITY =================
    public array $documents = [];

    // ================= PHOTOS =================
    public $photos = [];

    // ================= INSPECTION =================
    public string $inspection_decision = '';
    public ?string $inspection_notes = null;

    // ================= UI =================
    public bool $showModal = false;
    public bool $isEditing = false;

    // ================= DOCUMENT TYPES =================
    public array $documentTypes = [
        'coa' => 'COA',
        'halal' => 'Sertifikat Halal',
        'packaging' => 'Packaging',
        'original_packaging' => 'Original Packaging',
        'repacking' => 'Repacking',
        'flow_chart' => 'Flow Chart',
        'no_animal_use' => 'No Animal Use',
        'msds' => 'MSDS',
        'allergen' => 'Allergen Statement',
        'food_grade' => 'Food Grade',
        'non_gmo' => 'Non GMO Statement',
        'bse_tse' => 'BSE / TSE Statement',
        'porcine_free' => 'Porcine Free Statement',
        'breakdown_composition' => 'Breakdown Composition',
    ];

    protected $listeners = [
        'openIncomingMaterialForm' => 'openForm',
    ];

    protected function rules(): array
    {
        return [
            'name_of_goods' => ['required', 'string', 'max:255'],
            'supplier_name' => ['required', 'string', 'max:255'],
            'receipt_date'  => ['required', 'date'],
            'inspection_decision' => ['required'],
            'photos.*' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function mount(): void
    {
        $this->initializeDocuments();
    }

    protected function initializeDocuments(): void
    {
        foreach ($this->documentTypes as $key => $label) {
            $this->documents[$key] = [
                'is_checked' => false,
                'file' => null,
                'existing_path' => null,
            ];
        }
    }

    public function openForm(?int $id = null): void
    {
        $this->resetValidation();
        $this->resetErrorBag();
        $this->initializeDocuments();

        $this->showModal = true;
        $this->isEditing = false;

        if ($id) {
            // nanti bisa load dari DB
            $this->incomingId = $id;
            $this->isEditing = true;
        }
    }

    protected function uploadDocumentFiles(): array
    {
        $paths = [];

        foreach ($this->documents as $key => $doc) {
            if (!empty($doc['file'])) {

                $filename = Str::random(20) . '.' .
                    $doc['file']->getClientOriginalExtension();

                $path = $doc['file']->storeAs(
                    'incoming-material/documents',
                    $filename,
                    'public'
                );

                $paths[$key] = $path;
            }
        }

        return $paths;
    }

    protected function uploadPhotos(): array
    {
        $photoPaths = [];

        if ($this->photos) {
            foreach ($this->photos as $photo) {
                $filename = Str::random(20) . '.' .
                    $photo->getClientOriginalExtension();

                $path = $photo->storeAs(
                    'incoming-material/photos',
                    $filename,
                    'public'
                );

                $photoPaths[] = $path;
            }
        }

        return $photoPaths;
    }

    public function save(): void
    {
        $this->validate();

        // Upload dokumen
        $documentFiles = $this->uploadDocumentFiles();

        // Upload foto
        $photoFiles = $this->uploadPhotos();

        /*
        NANTI SIMPAN KE DATABASE
        contoh:
        IncomingMaterial::create([
            ...
            'documents' => json_encode($documentFiles),
            'photos' => json_encode($photoFiles),
        ]);
        */

        $this->dispatch('incoming-material:saved');

        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->reset();
        $this->initializeDocuments();
        $this->showModal = false;
        $this->isEditing = false;
    }

    public function render()
    {
        return view('livewire.incoming-material.incoming-material-form', [
            'documentTypes' => $this->documentTypes,
        ]);
    }
}
