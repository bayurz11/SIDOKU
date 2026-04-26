<?php

namespace App\Livewire\IncomingMaterial;

use App\Models\Domains\IncomingMaterial\Models\IncomingMaterial;
use App\Models\Domains\IncomingMaterial\Models\IncomingMaterialMicrobiologyTest;
use App\Models\Domains\IncomingMaterial\Models\IncomingMaterialStage2Check;
use App\Shared\Traits\WithAlerts;
use Livewire\Component;

class IncomingMaterialStage2Form extends Component
{
    use WithAlerts;

    public bool $showModal = false;

    public ?int $incomingMaterialId = null;

    public ?IncomingMaterial $material = null;

    public array $fieldResults = [];

    public string $physical_result = IncomingMaterialStage2Check::RESULT_PENDING;

    public string $microbiology_result = IncomingMaterialStage2Check::MICRO_NOT_REQUIRED;

    public string $final_decision = IncomingMaterialStage2Check::DECISION_HOLD;

    public ?string $notes = null;

    protected $listeners = [
        'openIncomingMaterialStage2Form' => 'openForm',
    ];

    public function openForm(int $id): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->reset([
            'incomingMaterialId',
            'material',
            'fieldResults',
            'physical_result',
            'microbiology_result',
            'final_decision',
            'notes',
        ]);

        $this->material = IncomingMaterial::with(['item', 'stage2Check', 'microbiologyTest'])->findOrFail($id);
        $this->incomingMaterialId = $this->material->id;

        $check = $this->material->stage2Check;
        $fields = $this->material->item?->stage2FieldList() ?? [];
        $existingResults = collect($check?->field_results ?? [])
            ->keyBy('field');

        foreach ($fields as $field) {
            $existing = $existingResults->get($field);

            $this->fieldResults[] = [
                'field' => $field,
                'result' => $existing['result'] ?? '',
                'note' => $existing['note'] ?? '',
            ];
        }

        $this->physical_result = $check?->physical_result ?? IncomingMaterialStage2Check::RESULT_PENDING;
        $this->microbiology_result = $check?->microbiology_result ?? $this->resolveMicrobiologyResult($this->material);
        $this->final_decision = $check?->final_decision ?? $this->evaluateFinalDecision();
        $this->notes = $check?->notes;
        $this->showModal = true;
    }

    public function updatedFieldResults(): void
    {
        $this->physical_result = $this->evaluatePhysicalResult();
        $this->microbiology_result = $this->resolveMicrobiologyResult($this->material);
        $this->final_decision = $this->evaluateFinalDecision();
    }

    public function save(): void
    {
        if (! $this->material || ! $this->incomingMaterialId) {
            $this->showErrorToast('Data Incoming Material tidak valid.');

            return;
        }

        $this->validate([
            'fieldResults.*.result' => ['nullable', 'in:OK,NOT_OK'],
            'fieldResults.*.note' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->physical_result = $this->evaluatePhysicalResult();
        $this->microbiology_result = $this->resolveMicrobiologyResult($this->material->fresh(['item', 'microbiologyTest']));
        $this->final_decision = $this->evaluateFinalDecision();

        IncomingMaterialStage2Check::query()->updateOrCreate(
            ['incoming_material_id' => $this->incomingMaterialId],
            [
                'incoming_material_item_id' => $this->material->incoming_material_item_id,
                'field_results' => $this->fieldResults,
                'physical_result' => $this->physical_result,
                'microbiology_result' => $this->microbiology_result,
                'final_decision' => $this->final_decision,
                'notes' => $this->notes,
                'checked_by' => auth()->id(),
                'checked_at' => now(),
            ]
        );

        $this->showSuccessToast('Incoming Material Tahap 2 berhasil disimpan.');
        $this->dispatch('incoming-material-stage2:saved');
        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->material = null;
        $this->incomingMaterialId = null;
        $this->fieldResults = [];
    }

    private function evaluatePhysicalResult(): string
    {
        if ($this->fieldResults === []) {
            return IncomingMaterialStage2Check::RESULT_PENDING;
        }

        $results = collect($this->fieldResults)->pluck('result')->filter();

        if ($results->isEmpty()) {
            return IncomingMaterialStage2Check::RESULT_PENDING;
        }

        return $results->contains('NOT_OK')
            ? IncomingMaterialStage2Check::RESULT_NOT_OK
            : IncomingMaterialStage2Check::RESULT_OK;
    }

    private function resolveMicrobiologyResult(?IncomingMaterial $material): string
    {
        if (! $material?->needsMicroTest()) {
            return IncomingMaterialStage2Check::MICRO_NOT_REQUIRED;
        }

        $micro = $material->microbiologyTest;

        if (! $micro || ! $micro->isCompleted()) {
            return IncomingMaterialStage2Check::MICRO_WAITING;
        }

        return $micro->result === IncomingMaterialMicrobiologyTest::RESULT_PASS
            ? IncomingMaterialStage2Check::RESULT_OK
            : IncomingMaterialStage2Check::RESULT_NOT_OK;
    }

    private function evaluateFinalDecision(): string
    {
        $physical = $this->physical_result ?: $this->evaluatePhysicalResult();
        $micro = $this->microbiology_result;

        if ($physical === IncomingMaterialStage2Check::RESULT_NOT_OK || $micro === IncomingMaterialStage2Check::RESULT_NOT_OK) {
            return IncomingMaterialStage2Check::DECISION_REJECTED;
        }

        if ($physical === IncomingMaterialStage2Check::RESULT_PENDING || $micro === IncomingMaterialStage2Check::MICRO_WAITING) {
            return IncomingMaterialStage2Check::DECISION_HOLD;
        }

        return IncomingMaterialStage2Check::DECISION_ACCEPTED;
    }

    public function render()
    {
        return view('livewire.incoming-material.incoming-material-stage2-form');
    }
}
