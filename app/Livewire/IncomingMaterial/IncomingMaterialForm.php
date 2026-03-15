<?php

namespace App\Livewire\IncomingMaterial;

use App\Models\Domains\IncomingMaterial\Models\IncomingMaterial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
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

    // ================= TEST PARAMETERS =================
    public bool $test_moisture = false;
    public bool $test_microbiology = false;
    public bool $test_chemical = false;

    // ================= DOCUMENTS =================
    public array $documents = [];

    // ================= PHOTOS =================
    public array $photos = [];
    public array $existingPhotos = [];

    // ================= INSPECTION =================
    public array $inspectionItems = [];
    public string $inspection_decision = '';
    public ?string $inspection_notes = null;

    // ================= UI =================
    public bool $showModal = false;
    public bool $isEditing = false;

    public array $documentTypes = [

        'coa' => 'COA',
        'halal_certificate' => 'Sertifikat Halal',
        'original_packaging' => 'Kemasan Asli',
        'repacking' => 'Repacking',
        'flow_chart' => 'Flow Chart',
        'no_animal_use' => 'No Animal Use',
        'msds' => 'MSDS',
        'allergen' => 'Allergen Statement',
        'food_grade' => 'Food Grade',
        'non_gmo' => 'Non GMO',
        'bse_tse' => 'BSE / TSE',
        'porcine_free' => 'Porcine Free',
        'breakdown_composition' => 'Breakdown Composition',

    ];

    protected $listeners = [
        'openIncomingMaterialForm' => 'openForm',
        'incoming-material:saved' => '$refresh',
    ];

    public function mount(): void
    {
        $this->initializeDocuments();
        $this->addInspectionItem();
    }

    // ================= INIT DOCUMENT =================

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

    // ================= OPEN FORM =================

    public function openForm(?int $id = null): void
    {
        $this->resetForm();

        if ($id) {

            $material = IncomingMaterial::with(['files', 'inspections'])->findOrFail($id);

            $this->incomingId = $material->id;
            $this->isEditing = true;

            $this->name_of_goods = $material->material_name;
            $this->supplier_name = $material->supplier;
            $this->receipt_date = optional($material->date)?->format('Y-m-d');
            $this->expired_date = optional($material->expired_date)?->format('Y-m-d');
            $this->receipt_time = $material->receipt_time;
            $this->batch_number = $material->batch_number;
            $this->quantity = $material->quantity;
            $this->quantity_unit = $material->quantity_unit;
            $this->sample_quantity = $material->sample_quantity;
            $this->vehicle_number = $material->vehicle_number;

            $this->test_moisture = (bool) $material->test_moisture;
            $this->test_microbiology = (bool) $material->test_microbiology;
            $this->test_chemical = (bool) $material->test_chemical;

            $this->inspection_decision = $material->status;
            $this->inspection_notes = $material->notes;

            // ================= LOAD INSPECTIONS =================

            foreach ($material->inspections as $inspection) {

                $this->inspectionItems[] = [

                    'parameter' => $inspection->parameter,
                    'standard' => $inspection->standard,
                    'test_result' => $inspection->test_result,
                    'inspection_result' => $inspection->inspection_result,
                ];
            }

            if (empty($this->inspectionItems)) {
                $this->addInspectionItem();
            }

            // ================= LOAD FILES =================

            foreach ($material->files as $file) {

                if ($file->category === 'photo') {

                    $this->existingPhotos[] = $file;
                } elseif (isset($this->documents[$file->category])) {

                    $this->documents[$file->category]['existing_path'] = $file->file_path;
                    $this->documents[$file->category]['is_checked'] = true;
                }
            }
        }

        $this->showModal = true;
    }

    // ================= RESET FORM =================

    private function resetForm(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->reset([
            'incomingId',
            'name_of_goods',
            'supplier_name',
            'receipt_date',
            'expired_date',
            'batch_number',
            'quantity',
            'receipt_time',
            'quantity_unit',
            'sample_quantity',
            'vehicle_number',
            'inspection_decision',
            'inspection_notes',
            'photos',
            'existingPhotos',
            'inspectionItems',
        ]);

        $this->initializeDocuments();
        $this->addInspectionItem();
    }

    // ================= INSPECTION =================

    public function addInspectionItem(): void
    {
        $this->inspectionItems[] = [

            'parameter' => '',
            'standard' => '',
            'test_result' => '',
            'inspection_result' => '',
        ];
    }

    public function removeInspectionItem($index): void
    {
        unset($this->inspectionItems[$index]);
        $this->inspectionItems = array_values($this->inspectionItems);
    }

    public function updatedInspectionItems(): void
    {
        foreach ($this->inspectionItems as $i => $item) {

            $this->inspectionItems[$i]['inspection_result'] = match (strtolower($item['test_result'])) {

                'ok' => 'OK',
                'not ok' => 'NOT OK',
                default => '',
            };
        }
    }

    // ================= SAVE =================

    public function save(): void
    {
        $this->validate([

            'name_of_goods' => 'required|string|max:255',
            'supplier_name' => 'required|string|max:255',
            'receipt_date' => 'required|date',

            'photos.*' => 'nullable|image|max:2048',

            'documents.*.file' => 'nullable|file|max:4096',
        ]);

        DB::beginTransaction();

        try {

            $data = [

                'date' => $this->receipt_date,
                'expired_date' => $this->expired_date,
                'receipt_time' => $this->receipt_time,
                'supplier' => $this->supplier_name,
                'material_name' => $this->name_of_goods,
                'batch_number' => $this->batch_number,
                'quantity' => $this->quantity,
                'quantity_unit' => $this->quantity_unit,
                'sample_quantity' => $this->sample_quantity,
                'vehicle_number' => $this->vehicle_number,
                'test_moisture' => $this->test_moisture,
                'test_microbiology' => $this->test_microbiology,
                'test_chemical' => $this->test_chemical,
                'status' => $this->inspection_decision,
                'notes' => $this->inspection_notes,
            ];

            if ($this->incomingId) {

                $material = IncomingMaterial::findOrFail($this->incomingId);
                $material->update($data);

                $material->inspections()->delete();
            } else {

                $material = IncomingMaterial::create($data);
            }

            // ================= UPLOAD DOCUMENT =================

            foreach ($this->documents as $key => $doc) {

                if ($doc['file'] instanceof TemporaryUploadedFile) {

                    $extension = $doc['file']->getClientOriginalExtension();
                    $name = Str::uuid() . '.' . $extension;

                    $path = $doc['file']->storeAs(
                        'incoming-material/' . date('Y/m'),
                        $name,
                        'public'
                    );

                    $material->files()->create([

                        'file_name' => $name,
                        'file_path' => $path,
                        'file_type' => $extension,
                        'category' => $key,
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }

            // ================= UPLOAD PHOTO =================

            foreach ($this->photos as $photo) {

                $extension = $photo->getClientOriginalExtension();
                $name = Str::uuid() . '.' . $extension;

                $path = $photo->storeAs(
                    'incoming-material/' . date('Y/m'),
                    $name,
                    'public'
                );

                $material->files()->create([

                    'file_name' => $name,
                    'file_path' => $path,
                    'file_type' => $extension,
                    'category' => 'photo',
                    'uploaded_by' => auth()->id(),
                ]);
            }

            DB::commit();

            $this->dispatch('show-toast', [
                'type' => 'success',
                'title' => 'Data berhasil disimpan'
            ]);

            $this->closeModal();
        } catch (\Throwable $e) {

            DB::rollBack();
            report($e);
        }
    }

    // ================= CLOSE =================

    public function closeModal(): void
    {
        $this->resetForm();
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
