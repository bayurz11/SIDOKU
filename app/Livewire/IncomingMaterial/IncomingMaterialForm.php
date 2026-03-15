<?php

namespace App\Livewire\IncomingMaterial;

use App\Models\Domains\IncomingMaterial\Models\IncomingMaterial;
use App\Models\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

            foreach ($material->files as $file) {

                if (isset($this->documents[$file->category])) {

                    $this->documents[$file->category]['existing_path'] = $file->file_path;
                    $this->documents[$file->category]['is_checked'] = true;
                }
            }
        }

        $this->showModal = true;
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
        $this->validateAll();        // ← ganti $this->validate([...]) lama

        DB::beginTransaction();

        try {
            $material = $this->upsertMaterial();
            $this->saveInspectionItems($material);
            $this->uploadDocuments($material);
            $this->uploadPhotos($material);

            DB::commit();

            $this->dispatch('show-toast', ['type' => 'success', 'title' => 'Data Incoming Material berhasil disimpan!']);
            $this->dispatch('incoming-material:saved');
            $this->closeModal();
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('IncomingMaterial save failed', [
                'incoming_id' => $this->incomingId ?? null,
                'user_id'     => auth()->id(),
                'error'       => $e->getMessage(),
            ]);
            $this->dispatch('show-toast', ['type' => 'error', 'title' => 'Gagal menyimpan data!']);
        }
    }


    // ✅ TAMBAHKAN semua method ini di bawah save(), masih dalam class yang sama:

    private function validateAll(): void
    {
        $this->validate([
            'name_of_goods'             => ['required', 'string', 'max:255'],
            'supplier_name'             => ['required', 'string', 'max:255'],
            'receipt_date'              => ['required', 'date'],
            'inspectionItems'           => ['present', 'array'],
            'inspectionItems.*.parameter'   => ['nullable', 'string', 'max:255'],
            'inspectionItems.*.standard'    => ['nullable', 'string', 'max:255'],
            'inspectionItems.*.test_result' => ['nullable', 'string', 'max:1000'],
            'documents'         => ['present', 'array'],
            'documents.*.file'  => ['nullable', 'file', 'max:5120', 'mimes:pdf,doc,docx,xls,xlsx'],
            'photos'   => ['present', 'array', 'max:10'],
            'photos.*' => ['nullable', 'image', 'max:2048', 'mimes:jpeg,jpg,png,webp'],
        ]);
    }

    private function upsertMaterial(): IncomingMaterial
    {
        $data = [
            'date'              => $this->receipt_date,
            'expired_date'      => $this->expired_date     ?? null,
            'receipt_time'      => $this->receipt_time     ?? null,
            'supplier'          => $this->supplier_name,
            'material_name'     => $this->name_of_goods,
            'batch_number'      => $this->batch_number     ?? null,
            'quantity'          => $this->quantity         ?? null,
            'quantity_unit'     => $this->quantity_unit    ?? null,
            'sample_quantity'   => $this->sample_quantity  ?? null,
            'vehicle_number'    => $this->vehicle_number   ?? null,
            'test_moisture'     => $this->test_moisture    ?? false,
            'test_microbiology' => $this->test_microbiology ?? false,
            'test_chemical'     => $this->test_chemical    ?? false,
            'lab_status'        => $this->resolveLabStatus(),
            'status'            => $this->inspection_decision,
            'notes'             => $this->inspection_notes ?? null,
        ];

        if ($this->incomingId) {
            $material = IncomingMaterial::findOrFail($this->incomingId);
            $data['updated_by'] = auth()->id();
            $material->update($data);
            $material->inspections()->delete();
        } else {
            $data['created_by'] = auth()->id();
            $material = IncomingMaterial::create($data);
        }

        return $material;
    }

    private function resolveLabStatus(): ?string
    {
        return (($this->test_moisture ?? false)
            || ($this->test_microbiology ?? false)
            || ($this->test_chemical ?? false))
            ? 'WAITING_TEST'
            : null;
    }

    private function saveInspectionItems(IncomingMaterial $material): void
    {
        if (empty($this->inspectionItems) || !is_array($this->inspectionItems)) return;

        foreach ($this->inspectionItems as $item) {
            if (
                empty(trim($item['parameter']   ?? '')) &&
                empty(trim($item['standard']    ?? '')) &&
                empty(trim($item['test_result'] ?? ''))
            ) continue;

            $material->inspections()->create([
                'parameter'         => $item['parameter']         ?? null,
                'standard'          => $item['standard']          ?? null,
                'test_result'       => $item['test_result']        ?? null,
                'inspection_result' => $item['inspection_result'] ?? null,
                'created_by'        => auth()->id(),
            ]);
        }
    }

    private function uploadDocuments(IncomingMaterial $material): void
    {
        if (empty($this->documents) || !is_array($this->documents)) return;

        foreach ($this->documents as $category => $doc) {
            $file = $doc['file'] ?? null;

            if (!$file instanceof \Illuminate\Http\UploadedFile) continue;

            if (!in_array(strtolower($file->getClientOriginalExtension()), ['pdf', 'doc', 'docx', 'xls', 'xlsx'], true)) continue;

            $path = $file->store('incoming-material/' . date('Y'), 'public');

            if (!$path) throw new \RuntimeException("Gagal upload dokumen [{$category}].");

            $material->files()->create([
                'file_name'   => $this->sanitizeFilename($file->getClientOriginalName()),
                'file_path'   => $path,
                'file_type'   => strtolower($file->getClientOriginalExtension()),
                'category'    => $category,
                'uploaded_by' => auth()->id(),
            ]);
        }
    }

    private function uploadPhotos(IncomingMaterial $material): void
    {
        if (empty($this->photos) || !is_array($this->photos)) return;

        $photos = array_filter(
            array_slice($this->photos, 0, 10),
            fn($f) => $f instanceof \Illuminate\Http\UploadedFile
        );

        foreach ($photos as $file) {
            if (!in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'webp'], true)) continue;

            $path = $file->store('incoming-material/' . date('Y'), 'public');

            if (!$path) throw new \RuntimeException('Gagal upload foto.');

            $material->files()->create([
                'file_name'   => $this->sanitizeFilename($file->getClientOriginalName()),
                'file_path'   => $path,
                'file_type'   => strtolower($file->getClientOriginalExtension()),
                'category'    => 'photo',
                'uploaded_by' => auth()->id(),
            ]);
        }
    }

    private function sanitizeFilename(string $name): string
    {
        $ext      = pathinfo($name, PATHINFO_EXTENSION);
        $basename = pathinfo($name, PATHINFO_FILENAME);
        $basename = preg_replace('/[^\w\-.]/', '_', $basename);
        $basename = substr($basename, 0, 100);
        return $basename . ($ext ? '.' . $ext : '');
    }

    // Opsional — panggil manual jika perlu hapus file lama saat update
    protected function deleteOldFiles(IncomingMaterial $material, ?string $category = null): void
    {
        $query = $category ? $material->files()->where('category', $category) : $material->files();

        foreach ($query->get() as $record) {
            Storage::disk('public')->delete($record->file_path);
            $record->delete();
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
