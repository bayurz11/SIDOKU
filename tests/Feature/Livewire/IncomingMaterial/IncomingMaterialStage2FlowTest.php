<?php

namespace Tests\Feature\Livewire\IncomingMaterial;

use App\Livewire\IncomingMaterial\IncomingMaterialForm;
use App\Livewire\IncomingMaterial\IncomingMaterialMicrobiologyForm;
use App\Livewire\IncomingMaterial\IncomingMaterialStage2Form;
use App\Models\Domains\IncomingMaterial\Models\IncomingMaterial;
use App\Models\Domains\IncomingMaterial\Models\IncomingMaterialItem;
use App\Models\Domains\IncomingMaterial\Models\IncomingMaterialMicrobiologyTest;
use App\Models\Domains\IncomingMaterial\Models\IncomingMaterialStage2Check;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Livewire\Document\Concerns\BuildsDocumentFixtures;
use Tests\TestCase;

class IncomingMaterialStage2FlowTest extends TestCase
{
    use BuildsDocumentFixtures;
    use RefreshDatabase;

    public function test_stage_1_uses_master_item_and_enables_microbiology_for_tea(): void
    {
        $user = $this->createUserWithAccess(['incoming_material.create']);
        $item = IncomingMaterialItem::query()->create([
            'name' => 'Teh Hitam BOP',
            'category' => IncomingMaterialItem::CATEGORY_TEA,
            'default_unit' => 'KG',
            'requires_microbiology' => true,
            'stage2_fields' => ['Warna daun teh', 'Aroma'],
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(IncomingMaterialForm::class)
            ->call('openForm')
            ->set('incoming_material_item_id', $item->id)
            ->assertSet('name_of_goods', 'Teh Hitam BOP')
            ->assertSet('quantity_unit', 'KG')
            ->assertSet('test_microbiology', true)
            ->set('supplier_name', 'PT Supplier Teh')
            ->set('receipt_date', now()->toDateString())
            ->set('batch_number', 'BATCH-TEA-001')
            ->set('quantity', 100)
            ->set('inspectionItems.0.parameter', 'Kemasan')
            ->set('inspectionItems.0.standard', 'Utuh')
            ->set('inspectionItems.0.test_result', 'ok')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('incoming_materials', [
            'incoming_material_item_id' => $item->id,
            'material_name' => 'Teh Hitam BOP',
            'test_microbiology' => true,
            'lab_status' => 'WAITING_TEST',
        ]);
    }

    public function test_microbiology_result_is_used_by_stage_2_decision(): void
    {
        $user = $this->createUserWithAccess(['incoming_material.edit', 'microbiology.edit']);
        $item = IncomingMaterialItem::query()->create([
            'name' => 'Teh Hijau Fanning',
            'category' => IncomingMaterialItem::CATEGORY_TEA,
            'default_unit' => 'KG',
            'requires_microbiology' => true,
            'stage2_fields' => ['Warna daun teh', 'Aroma'],
            'is_active' => true,
        ]);
        $material = IncomingMaterial::query()->create([
            'date' => now()->toDateString(),
            'supplier' => 'PT Supplier Teh',
            'material_name' => $item->name,
            'incoming_material_item_id' => $item->id,
            'batch_number' => 'BATCH-TEA-002',
            'quantity' => 50,
            'quantity_unit' => 'KG',
            'status' => 'ACCEPTED',
            'test_microbiology' => true,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test(IncomingMaterialStage2Form::class)
            ->call('openForm', $material->id)
            ->set('fieldResults.0.result', 'OK')
            ->set('fieldResults.1.result', 'OK')
            ->call('save');

        $this->assertDatabaseHas('incoming_material_stage2_checks', [
            'incoming_material_id' => $material->id,
            'physical_result' => IncomingMaterialStage2Check::RESULT_OK,
            'microbiology_result' => IncomingMaterialStage2Check::MICRO_WAITING,
            'final_decision' => IncomingMaterialStage2Check::DECISION_HOLD,
        ]);

        Livewire::test(IncomingMaterialMicrobiologyForm::class)
            ->call('openForm', $material->id)
            ->set('tpc', 100)
            ->set('yeast_mold', 10)
            ->set('coliform', 0)
            ->set('e_coli', 'Negatif')
            ->set('salmonella', 'Negatif')
            ->set('result', IncomingMaterialMicrobiologyTest::RESULT_PASS)
            ->call('save');

        Livewire::test(IncomingMaterialStage2Form::class)
            ->call('openForm', $material->id)
            ->call('save');

        $this->assertDatabaseHas('incoming_material_stage2_checks', [
            'incoming_material_id' => $material->id,
            'microbiology_result' => IncomingMaterialStage2Check::RESULT_OK,
            'final_decision' => IncomingMaterialStage2Check::DECISION_ACCEPTED,
        ]);
    }
}
