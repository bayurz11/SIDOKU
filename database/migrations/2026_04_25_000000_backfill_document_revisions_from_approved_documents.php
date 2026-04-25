<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('documents') || ! Schema::hasTable('document_revisions')) {
            return;
        }

        DB::table('documents')
            ->where('status', 'approved')
            ->orderBy('id')
            ->chunkById(100, function ($documents) {
                foreach ($documents as $document) {
                    $revisionNo = $document->revision_no ?? 0;

                    $exists = DB::table('document_revisions')
                        ->where('document_id', $document->id)
                        ->where('revision_no', $revisionNo)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    DB::table('document_revisions')->insert([
                        'document_id' => $document->id,
                        'revision_no' => $revisionNo,
                        'change_note' => 'Backfill histori revisi dari dokumen approved.',
                        'file_path' => $document->file_path ?: '',
                        'changed_by' => $document->updated_by ?: $document->created_by,
                        'changed_at' => $document->approved_at ?: $document->updated_at,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('document_revisions')) {
            return;
        }

        DB::table('document_revisions')
            ->where('change_note', 'Backfill histori revisi dari dokumen approved.')
            ->delete();
    }
};
