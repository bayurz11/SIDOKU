<?php

namespace App\Livewire\IncomingMaterial;

use App\Models\Domains\IncomingMaterial\Models\IncomingMaterial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class IncomingMaterialList extends Component
{
    public function showIncomingMaterialDetail($id)
    {
        $this->dispatch('openIncomingMaterialDetail', id: $id);
    }

    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $material = IncomingMaterial::findOrFail($id);

            // Hapus file terkait
            foreach ($material->files as $file) {
                // Pastikan file ada di storage sebelum dihapus
                if (Storage::disk('public')->exists($file->file_path)) {
                    Storage::disk('public')->delete($file->file_path);
                }
                // Hapus record di database
                $file->delete();
            }

            // Hapus inspection terkait
            $material->inspections()->delete();

            // Hapus material
            $material->delete();

            DB::commit();

            $this->dispatch('show-toast', [
                'type' => 'success',
                'title' => 'Data berhasil dihapus!'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('show-toast', [
                'type' => 'error',
                'title' => 'Gagal menghapus data!'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.incoming-material.incoming-material-list', [
            'incomingData' => IncomingMaterial::latest('date')->get()
        ]);
    }
}
