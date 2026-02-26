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
    public array $photos = [];

    // ================= INSPECTION TABLE =================
    public array $inspectionItems = [];

    // ================= FINAL DECISION =================
    public string $inspection_decision = '';
    public ?string $inspection_notes = null;
    public $material;
    public bool $showDetail = false;

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
        'incoming-material:saved' => '$refresh',
        'showIncomingMaterialDetail' => 'showIncomingMaterialDetail',
    ];

    public function mount(): void
    {
        $this->initializeDocuments();
        $this->addInspectionItem();
    }

    protected function initializeDocuments(): void
    {
        $this->documents = []; // pastikan reset ke array
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
        $this->inspectionItems = array_values($this->inspectionItems ?? []);
        $this->evaluateFinalDecision();
    }

    public function updatedInspectionItems()
    {
        foreach ($this->inspectionItems ?? [] as $i => $item) {
            $this->inspectionItems[$i]['inspection_result'] = match (strtolower($item['test_result'])) {
                'ok' => 'OK',
                'not ok' => 'NOT OK',
                default => '',
            };
        }

        $this->evaluateFinalDecision();
    }

    protected function evaluateFinalDecision()
    {
        $hasNotOk = collect($this->inspectionItems ?? [])
            ->contains(fn($item) => $item['inspection_result'] === 'NOT OK');

        $this->inspection_decision = $hasNotOk ? 'HOLD' : 'APPROVED';
    }

    // ================= FORM CONTROL =================
    public function openForm(?int $id = null): void
    {
        $this->resetValidation();
        $this->resetErrorBag();
        $this->initializeDocuments();
        $this->inspectionItems = $this->inspectionItems ?? [];
        $this->photos = $this->photos ?? [];

        $this->showModal = true;
        $this->isEditing = false;

        if ($id) {
            $this->incomingId = $id;
            $this->isEditing = true;
        }
    }

    public function showIncomingMaterialDetail($id)
    {
        $this->material = IncomingMaterial::with('files')->findOrFail($id);
        $this->showDetail = true;
    }

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

    // ================= FILE UPLOAD =================
    protected function uploadDocumentFiles(): array
    {
        $paths = [];

        foreach ($this->documents ?? [] as $key => $doc) {
            if (!empty($doc['file'])) {
                $filename = Str::random(20) . '.' . $doc['file']->getClientOriginalExtension();
                $paths[$key] = $doc['file']->storeAs('incoming-material/documents', $filename, 'public');
            }
        }

        return $paths;
    }

    protected function uploadPhotos(): array
    {
        $photoPaths = [];

        foreach ($this->photos ?? [] as $photo) {
            $filename = Str::random(20) . '.' . $photo->getClientOriginalExtension();
            $photoPaths[] = $photo->storeAs('incoming-material/photos', $filename, 'public');
        }

        return $photoPaths;
    }

    // ================= SAVE =================
    public function save(): void
    {
        $this->validate();

        DB::beginTransaction();

        try {
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

            // Upload Dokumen
            foreach ($this->documents ?? [] as $key => $doc) {
                if (!empty($doc['file'])) {
                    $file = $doc['file'];
                    $path = $file->store('incoming-material/' . date('Y'), 'public');
                    $material->files()->create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->extension(),
                        'category'  => $key,
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }

            // Upload Foto
            foreach ($this->photos ?? [] as $file) {
                $path = $file->store('incoming-material/' . date('Y'), 'public');
                $material->files()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->extension(),
                    'category'  => 'photo',
                    'uploaded_by' => auth()->id(),
                ]);
            }

            DB::commit();

            $this->dispatch('show-toast', [
                'type' => 'success',
                'title' => 'Data Incoming Material berhasil disimpan!'
            ]);

            $this->dispatch('incoming-material:saved');

            $this->closeModal();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            $this->dispatch('show-toast', [
                'type' => 'error',
                'title' => 'Gagal menyimpan data!'
            ]);
        }
    }

    public function closeModal(): void
    {
        $this->reset();
        $this->initializeDocuments();
        $this->addInspectionItem();
        $this->showModal = false;
        $this->isEditing = false;
        $this->photos = [];
        $this->inspectionItems = [];
    }

    public function render()
    {
        return view('livewire.incoming-material.incoming-material-form', [
            'documentTypes' => $this->documentTypes,
        ]);
    }
}
