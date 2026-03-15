<?php

namespace App\Livewire\IncomingMaterial;

use App\Models\Domains\IncomingMaterial\Models\IncomingMaterial;
use App\Models\Domains\IncomingMaterial\Models\IncomingMaterialFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
    public array $existingPhotos = [];
    // ================= DETAIL DOCUMENTS =================
    public array $existingDocuments = [];

    // ================= INSPECTION =================
    public array $inspectionItems = [];
    public string $inspection_decision = '';
    public ?string $inspection_notes = null;

    // ================= DETAIL =================
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
    public function refreshData()
    {
        $this->render();
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

    // ================= OPEN FORM =================
    public function openForm(?int $id = null): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->incomingId = null;
        $this->isEditing = false;

        $this->name_of_goods = '';
        $this->supplier_name = '';
        $this->receipt_date = null;
        $this->expired_date = null;
        $this->batch_number = '';
        $this->quantity = null;
        $this->receipt_time = null;
        $this->quantity_unit = null;
        $this->sample_quantity = null;
        $this->vehicle_number = null;

        $this->test_moisture = false;
        $this->test_microbiology = false;
        $this->test_chemical = false;

        $this->inspection_decision = '';
        $this->inspection_notes = null;

        $this->photos = [];
        $this->inspectionItems = [];
        $this->addInspectionItem();
        $this->initializeDocuments();

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

            // TEST PARAMETERS
            $this->test_moisture = (bool) $material->test_moisture;
            $this->test_microbiology = (bool) $material->test_microbiology;
            $this->test_chemical = (bool) $material->test_chemical;

            $this->inspection_decision = $material->status;
            $this->inspection_notes = $material->notes;

            $this->inspectionItems = [];

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

            $this->existingPhotos = [];
            $this->existingDocuments = [];

            foreach ($material->files as $file) {

                if ($file->category === 'photo') {

                    $this->existingPhotos[] = $file->file_path;
                    continue;
                }

                // pastikan category ada di documents
                if (!array_key_exists($file->category, $this->documents)) {
                    continue;
                }

                $this->documents[$file->category]['existing_path'] = $file->file_path;
                $this->documents[$file->category]['is_checked'] = true;

                $this->existingDocuments[$file->category] = $file->file_path;
            }
        }

        $this->showModal = true;
    }

    // ================= PHOTO MANAGEMENT =================
    public function removeExistingPhoto($path)
    {
        // hapus dari database
        $file = IncomingMaterialFile::where('file_path', $path)->first();

        if ($file) {

            Storage::disk('public')->delete($file->file_path);

            $file->delete();
        }

        // hapus dari state livewire
        $this->existingPhotos = array_values(
            array_filter($this->existingPhotos, fn($p) => $p !== $path)
        );
    }
    // ================= DOCUMENT MANAGEMENT =================
    public function removeExistingDocument($key)
    {
        if (!isset($this->documents[$key]['existing_path'])) {
            return;
        }

        $path = $this->documents[$key]['existing_path'];

        // cari file di database
        $file = IncomingMaterialFile::where('file_path', $path)->first();

        if ($file) {

            // hapus file dari storage
            Storage::disk('public')->delete($file->file_path);

            // hapus record database
            $file->delete();
        }

        // reset state livewire
        $this->documents[$key]['existing_path'] = null;
        $this->documents[$key]['is_checked'] = false;

        // hapus dari existingDocuments jika ada
        if (isset($this->existingDocuments[$key])) {
            unset($this->existingDocuments[$key]);
        }
    }

    // ================= INSPECTION =================

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

        // reset index array supaya Livewire tidak error
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

        $this->inspection_decision = $hasNotOk ? 'HOLD' : 'ACCEPTED';
    }

    // ================= SAVE =================

    public function save(): void
    {
        $this->evaluateFinalDecision();

        $this->validate([
            'name_of_goods' => ['required', 'string', 'max:255'],
            'supplier_name' => ['required', 'string', 'max:255'],
            'receipt_date' => ['required', 'date'],

            'inspectionItems.*.parameter' => ['nullable', 'string', 'max:255'],
            'inspectionItems.*.standard' => ['nullable', 'string', 'max:255'],
            'inspectionItems.*.test_result' => ['nullable', 'string'],

            'photos.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],

            'documents.*.file' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
                'max:4096'
            ],
        ]);

        DB::beginTransaction();

        try {

            $data = [

                'date' => $this->receipt_date,
                'expired_date' => $this->expired_date,
                'receipt_time' => $this->receipt_time ?? null,
                'supplier' => $this->supplier_name,
                'material_name' => $this->name_of_goods,
                'batch_number' => $this->batch_number,
                'quantity' => $this->quantity,
                'quantity_unit' => $this->quantity_unit ?? null,
                'sample_quantity' => $this->sample_quantity ?? null,
                'vehicle_number' => $this->vehicle_number ?? null,

                // TEST PARAMETERS
                'test_moisture' => $this->test_moisture,
                'test_microbiology' => $this->test_microbiology,
                'test_chemical' => $this->test_chemical,

                // AUTO STATUS LAB
                'lab_status' => (
                    $this->test_moisture ||
                    $this->test_microbiology ||
                    $this->test_chemical
                ) ? 'WAITING_TEST' : null,

                'status' => $this->inspection_decision,
                'notes' => $this->inspection_notes,
            ];

            /*
        |--------------------------------------------------------------------------
        | CREATE / UPDATE MATERIAL
        |--------------------------------------------------------------------------
        */

            if ($this->incomingId) {

                $material = IncomingMaterial::findOrFail($this->incomingId);

                $data['updated_by'] = auth()->id();

                $material->update($data);

                // hapus inspection lama
                $material->inspections()->delete();
            } else {

                $data['created_by'] = auth()->id();

                $material = IncomingMaterial::create($data);
            }


            /*
        |--------------------------------------------------------------------------
        | SIMPAN INSPECTION ITEMS
        |--------------------------------------------------------------------------
        */

            foreach ($this->inspectionItems as $item) {

                if (
                    empty($item['parameter']) &&
                    empty($item['standard']) &&
                    empty($item['test_result'])
                ) {
                    continue;
                }

                $material->inspections()->create([
                    'parameter' => $item['parameter'],
                    'standard' => $item['standard'],
                    'test_result' => $item['test_result'],
                    'inspection_result' => $item['inspection_result'] ?? null,
                    'created_by' => auth()->id(),
                ]);
            }


            /*
        |--------------------------------------------------------------------------
        | UPLOAD DOCUMENTS
        |--------------------------------------------------------------------------
        */

            foreach ($this->documents ?? [] as $key => $doc) {

                if (
                    isset($doc['file']) &&
                    $doc['file'] instanceof TemporaryUploadedFile
                ) {

                    $file = $doc['file'];

                    $extension = strtolower($file->getClientOriginalExtension());

                    foreach ($this->documents ?? [] as $key => $doc) {

                        if (
                            isset($doc['file']) &&
                            $doc['file'] instanceof TemporaryUploadedFile
                        ) {

                            $file = $doc['file'];

                            $extension = strtolower($file->getClientOriginalExtension());

                            // nama dokumen dari checklist
                            $docName = Str::slug($key);

                            $fileName = $docName . '_' . now()->format('Y-m-d_His') . '.' . $extension;

                            $path = $file->storeAs(
                                'incoming-material/' . date('Y/m'),
                                $fileName,
                                'public'
                            );

                            $material->files()->create([
                                'file_name' => $fileName,
                                'file_path' => $path,
                                'file_type' => $extension,
                                'category' => $key,
                                'uploaded_by' => auth()->id(),
                            ]);
                        }
                    }
                }
            }


            /*
        |--------------------------------------------------------------------------
        | UPLOAD PHOTOS
        |--------------------------------------------------------------------------
        */

            foreach ($this->photos ?? [] as $file) {

                if ($file instanceof TemporaryUploadedFile) {

                    $extension = strtolower($file->getClientOriginalExtension());

                    foreach ($this->photos ?? [] as $index => $file) {

                        if ($file instanceof TemporaryUploadedFile) {

                            $extension = strtolower($file->getClientOriginalExtension());

                            $materialName = Str::slug($this->name_of_goods);

                            $fileName = $materialName . '_' . now()->format('Y-m-d_His') . '_' . ($index + 1) . '.' . $extension;

                            $path = $file->storeAs(
                                'incoming-material/' . date('Y/m'),
                                $fileName,
                                'public'
                            );

                            $material->files()->create([
                                'file_name' => $fileName,
                                'file_path' => $path,
                                'file_type' => $extension,
                                'category' => 'photo',
                                'uploaded_by' => auth()->id(),
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            DB::commit();

            // cek apakah edit atau create
            $message = $this->incomingId
                ? 'Data Incoming Material berhasil diperbarui!'
                : 'Data Incoming Material berhasil disimpan!';

            $this->dispatch('show-toast', [
                'type' => 'success',
                'title' => $message
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

    // ================= CLOSE =================

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
