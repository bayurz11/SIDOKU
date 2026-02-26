<?php

namespace App\Livewire\IncomingMaterial;

use App\Models\Domains\IncomingMaterial\Models\IncomingMaterial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

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
    public ?string $receipt_time = null;
    public ?string $quantity_unit = null;
    public ?float $sample_quantity = null;
    public ?string $vehicle_number = null;

    // ================= DOCUMENT SUITABILITY =================
    public array $documents = [];

    // ================= PHOTOS =================
    public $photos = [];

    // ================= INSPECTION TABLE =================
    public array $inspectionItems = [];

    // ================= FINAL DECISION =================
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
        $this->addInspectionItem();
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

    // ================= INSPECTION METHODS =================

    public function addInspectionItem()
    {
        $this->inspectionItems[] = [
            'parameter' => '',
            'standard' => '',
            'test_result' => '',
            'inspection_result' => '',
        ];
    }

    public function removeInspectionItem($index)
    {
        unset($this->inspectionItems[$index]);
        $this->inspectionItems = array_values($this->inspectionItems);
        $this->evaluateFinalDecision();
    }

    public function updatedInspectionItems()
    {
        foreach ($this->inspectionItems as $i => $item) {

            if (strtolower($item['test_result']) === 'ok') {
                $this->inspectionItems[$i]['inspection_result'] = 'OK';
            } elseif (strtolower($item['test_result']) === 'not ok') {
                $this->inspectionItems[$i]['inspection_result'] = 'NOT OK';
            } else {
                $this->inspectionItems[$i]['inspection_result'] = '';
            }
        }

        $this->evaluateFinalDecision();
    }

    protected function evaluateFinalDecision()
    {
        $hasNotOk = collect($this->inspectionItems)
            ->contains(fn($item) => $item['inspection_result'] === 'NOT OK');

        $this->inspection_decision = $hasNotOk ? 'HOLD' : 'APPROVED';
    }

    // ================= FORM CONTROL =================

    public function openForm(?int $id = null): void
    {
        $this->resetValidation();
        $this->resetErrorBag();
        $this->initializeDocuments();

        $this->showModal = true;
        $this->isEditing = false;

        if ($id) {
            $this->incomingId = $id;
            $this->isEditing = true;
        }
    }

    // ================= FILE UPLOAD =================

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

        foreach ($this->photos ?? [] as $photo) {
            $filename = Str::random(20) . '.' .
                $photo->getClientOriginalExtension();

            $path = $photo->storeAs(
                'incoming-material/photos',
                $filename,
                'public'
            );

            $photoPaths[] = $path;
        }

        return $photoPaths;
    }

    // ================= SAVE =================


    public function save(): void
    {
        $this->validate();

        DB::beginTransaction();

        try {

            // 1️⃣ Simpan data utama
            $material = IncomingMaterial::create([
                'date'            => $this->receipt_date,
                'receipt_time'    => $this->receipt_time ?? null,
                'supplier'        => $this->supplier_name,
                'material_name'   => $this->name_of_goods,
                'batch_number'    => $this->batch_number,
                'quantity'        => $this->quantity,
                'quantity_unit'   => $this->quantity_unit ?? null,
                'sample_quantity' => $this->sample_quantity ?? null,
                'vehicle_number'  => $this->vehicle_number ?? null,
                'status'          => $this->inspection_decision,
                'notes'           => $this->inspection_notes,
                'created_by'      => auth()->id(),
            ]);

            // 2️⃣ Upload Dokumen
            if (!empty($this->documents)) {
                foreach ($this->documents as $key => $doc) {
                    if (!empty($doc['file'])) {

                        $file = $doc['file'];

                        $path = $file->store(
                            'incoming-material/' . date('Y'),
                            'public'
                        );

                        $material->files()->create([
                            'file_name'   => $file->getClientOriginalName(),
                            'file_path'   => $path,
                            'file_type'   => $file->extension(),
                            'category'    => $key,
                            'uploaded_by' => auth()->id(),
                        ]);
                    }
                }
            }

            // 3️⃣ Upload Foto
            if (!empty($this->photos)) {
                foreach ($this->photos as $file) {

                    $path = $file->store(
                        'incoming-material/' . date('Y'),
                        'public'
                    );

                    $material->files()->create([
                        'file_name'   => $file->getClientOriginalName(),
                        'file_path'   => $path,
                        'file_type'   => $file->extension(),
                        'category'    => 'photo',
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            // ✅ TOAST SUKSES
            $this->showSuccessToast('Document created successfully!');

            $this->dispatch('incoming-material:saved');
            $this->closeModal();
        } catch (\Throwable $e) {

            DB::rollBack();

            // ❌ TOAST ERROR
            $this->showErrorToast('Failed to create document. Please try again.');

            report($e); // tetap log error
        }
    }

    public function closeModal(): void
    {
        $this->reset();
        $this->initializeDocuments();
        $this->addInspectionItem();
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
