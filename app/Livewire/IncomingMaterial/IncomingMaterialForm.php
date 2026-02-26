<?php

namespace App\Livewire\IncomingMaterial;

use App\Models\Domains\IncomingMaterial\Models\IncomingMaterial;
use Illuminate\Support\Facades\DB;
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

    // ================= DOCUMENTS =================
    public array $documents = [];
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

    // ================= PHOTOS =================
    public array $photos = [];

    // ================= INSPECTION =================
    public array $inspectionItems = [];
    public string $inspection_decision = '';
    public ?string $inspection_notes = null;

    // ================= DETAIL MODAL =================
    public $material;
    public bool $showDetail = false;

    // ================= UI =================
    public bool $showModal = false;
    public bool $isEditing = false;

    protected $listeners = [
        'openIncomingMaterialForm' => 'openForm',
        'incoming-material:saved' => '$refresh',
    ];

    public function mount(): void
    {
        $this->initializeDocuments();
        $this->addInspectionItem();
    }

    protected function initializeDocuments(): void
    {
        $this->documents = [];
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
        $hasNotOk = collect($this->inspectionItems)
            ->contains(fn($item) => $item['inspection_result'] === 'NOT OK');

        $this->inspection_decision = $hasNotOk ? 'HOLD' : 'APPROVED';
    }

    // ================= SHOW DETAIL =================
    public function showIncomingMaterialDetail($id)
    {
        $this->material = IncomingMaterial::with('files')->find($id);

        if (! $this->material) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'title' => 'Data tidak ditemukan!',
            ]);
            return;
        }

        $this->showDetail = true;
    }

    // ================= SAVE =================
    public function save(): void
    {
        $this->validate([
            'name_of_goods' => ['required', 'string', 'max:255'],
            'supplier_name' => ['required', 'string', 'max:255'],
            'receipt_date'  => ['required', 'date'],
            'inspection_decision' => ['required'],
            'photos.*' => ['nullable', 'image', 'max:2048'],
        ]);

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

            // Upload Documents
            foreach ($this->documents as $key => $doc) {
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

            // Upload Photos
            foreach ($this->photos as $file) {
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

    // ================= MODAL CONTROL =================
    public function closeModal(): void
    {
        $this->reset();
        $this->initializeDocuments();
        $this->inspectionItems = [];
        $this->photos = [];
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
