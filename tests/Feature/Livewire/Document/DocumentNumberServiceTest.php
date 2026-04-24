<?php

namespace Tests\Feature\Livewire\Document;

use App\Domains\Department\Models\Department;
use App\Domains\Document\Models\DocumentPrefixSetting;
use App\Domains\Document\Models\DocumentType;
use App\Domains\Document\Services\DocumentNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentNumberServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_document_number_from_double_brace_prefix_and_global_fallback(): void
    {
        $type = DocumentType::query()->create([
            'name' => 'SOP',
            'description' => 'Standard Operating Procedure',
            'is_active' => true,
        ]);

        $department = Department::query()->create([
            'name' => 'QC',
            'description' => 'Quality Control',
            'is_active' => true,
        ]);

        $prefix = DocumentPrefixSetting::query()->create([
            'company_prefix' => 'PRP',
            'document_type_id' => null,
            'department_id' => null,
            'format_nomor' => '{{COMP}}/{{MAIN}}/{{DEPT}}/{{SEQ}}',
            'last_sequence' => 0,
            'reset_interval' => 1,
            'is_active' => true,
        ]);

        $generated = DocumentNumberService::generate($type->id, $department->id);

        $this->assertSame('PRP/SOP/QC/001', $generated['code']);
        $this->assertSame($prefix->id, $generated['prefix_setting_id']);
        $this->assertDatabaseHas('document_prefix_settings', [
            'id' => $prefix->id,
            'last_sequence' => 1,
        ]);
    }
}
