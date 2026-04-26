<?php

namespace App\Livewire\IncomingMaterial;

use App\Models\Domains\IncomingMaterial\Models\IncomingMaterial;
use App\Models\Domains\IncomingMaterial\Models\IncomingMaterialMicrobiologyTest;
use App\Shared\Traits\WithAlerts;
use Livewire\Component;

class IncomingMaterialMicrobiologyForm extends Component
{
    use WithAlerts;

    public bool $showModal = false;

    public ?IncomingMaterial $material = null;

    public ?int $incomingMaterialId = null;

    public ?float $tpc = null;

    public ?float $yeast_mold = null;

    public ?float $coliform = null;

    public ?string $e_coli = null;

    public ?string $salmonella = null;

    public string $result = IncomingMaterialMicrobiologyTest::RESULT_PENDING;

    public ?string $notes = null;

    protected $listeners = [
        'openIncomingMaterialMicrobiologyForm' => 'openForm',
    ];

    public function openForm(int $id): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->reset([
            'material',
            'incomingMaterialId',
            'tpc',
            'yeast_mold',
            'coliform',
            'e_coli',
            'salmonella',
            'result',
            'notes',
        ]);

        $this->material = IncomingMaterial::with(['item', 'microbiologyTest'])->findOrFail($id);
        $this->incomingMaterialId = $this->material->id;

        if ($micro = $this->material->microbiologyTest) {
            $this->tpc = $micro->tpc !== null ? (float) $micro->tpc : null;
            $this->yeast_mold = $micro->yeast_mold !== null ? (float) $micro->yeast_mold : null;
            $this->coliform = $micro->coliform !== null ? (float) $micro->coliform : null;
            $this->e_coli = $micro->e_coli;
            $this->salmonella = $micro->salmonella;
            $this->result = $micro->result;
            $this->notes = $micro->notes;
        } else {
            $this->result = IncomingMaterialMicrobiologyTest::RESULT_PENDING;
        }

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'tpc' => ['nullable', 'numeric', 'min:0'],
            'yeast_mold' => ['nullable', 'numeric', 'min:0'],
            'coliform' => ['nullable', 'numeric', 'min:0'],
            'e_coli' => ['nullable', 'string', 'max:100'],
            'salmonella' => ['nullable', 'string', 'max:100'],
            'result' => ['required', 'in:PENDING,PASS,FAIL'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! $this->incomingMaterialId) {
            $this->showErrorToast('Data Incoming Material tidak valid.');

            return;
        }

        IncomingMaterialMicrobiologyTest::query()->updateOrCreate(
            ['incoming_material_id' => $this->incomingMaterialId],
            [
                'tpc' => $this->tpc,
                'yeast_mold' => $this->yeast_mold,
                'coliform' => $this->coliform,
                'e_coli' => $this->e_coli,
                'salmonella' => $this->salmonella,
                'result' => $this->result,
                'status' => $this->result === IncomingMaterialMicrobiologyTest::RESULT_PENDING
                    ? IncomingMaterialMicrobiologyTest::STATUS_DRAFT
                    : IncomingMaterialMicrobiologyTest::STATUS_COMPLETED,
                'notes' => $this->notes,
                'tested_by' => auth()->id(),
                'tested_at' => $this->result === IncomingMaterialMicrobiologyTest::RESULT_PENDING ? null : now(),
            ]
        );

        IncomingMaterial::whereKey($this->incomingMaterialId)->update([
            'lab_status' => $this->result === IncomingMaterialMicrobiologyTest::RESULT_PENDING
                ? 'WAITING_MICRO'
                : 'MICRO_COMPLETED',
            'updated_by' => auth()->id(),
        ]);

        $this->showSuccessToast('Hasil uji mikrobiologi incoming material berhasil disimpan.');
        $this->dispatch('incoming-material-microbiology:saved');
        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->material = null;
        $this->incomingMaterialId = null;
    }

    public function render()
    {
        return view('livewire.incoming-material.incoming-material-microbiology-form');
    }
}
