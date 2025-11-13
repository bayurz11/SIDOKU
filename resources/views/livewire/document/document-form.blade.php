@php
    use Illuminate\Support\Str;
@endphp

<div>
    @if ($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center"
            wire:click.self="closeModal">
            <div class="relative top-8 mx-auto p-6 border w-full max-w-4xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    {{-- HEADER --}}
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">
                                {{ $isEditing ? 'Edit Document' : 'Add Document' }}
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Input data dokumen dan sistem akan menghasilkan nomor sesuai prefix setting.
                            </p>
                        </div>

                        <div class="flex flex-col items-end space-y-2">
                            @if ($document_code)
                                <span
                                    class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                    No. Dokumen: <span class="ml-1 font-mono">{{ $document_code }}</span>
                                </span>
                            @endif

                            <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- FORM --}}
                    <form wire:submit.prevent="save" class="space-y-6">
                        {{-- Row 1: Type, Dept, Level --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Document Type --}}
                            <div>
                                <label for="document_type_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Document Type <span class="text-red-500">*</span>
                                </label>
                                <select wire:model.defer="document_type_id" id="document_type_id"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <option value="">-- Pilih Type --</option>
                                    @foreach ($documentTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('document_type_id')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Department --}}
                            <div>
                                <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Department
                                </label>
                                <select wire:model.defer="department_id" id="department_id"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <option value="">-- Semua Department --</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Level --}}
                            <div>
                                <label for="level" class="block text-sm font-medium text-gray-700 mb-2">
                                    Level Dokumen
                                </label>
                                <select wire:model.defer="level" id="level"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm  focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">

                                    {{-- LEVEL 1 (Paling tinggi) --}}
                                    <option value="1">Level 1 — Manual Keamanan Pangan / Food Safety Manual
                                    </option>

                                    {{-- LEVEL 2 --}}
                                    <option value="2">Level 2 — SOP (Standard Operating Procedure)</option>

                                    {{-- LEVEL 3 --}}
                                    <option value="3">Level 3 — WI (Work Instruction)</option>

                                    {{-- LEVEL 4 --}}
                                    <option value="4">Level 4 — FORM & DOC (Dokumen Turunan)</option>

                                </select>

                                @error('level')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Parent Document --}}
                        <div>
                            <label for="parent_document_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Parent Document (opsional)
                            </label>
                            <select wire:model.defer="parent_document_id" id="parent_document_id"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                       focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">-- Tanpa Parent --</option>
                                @foreach ($parentDocuments as $p)
                                    <option value="{{ $p->id }}">
                                        {{ $p->document_code }} — {{ Str::limit($p->title, 60) }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Parent digunakan untuk WI turunan SOP, FORM turunan WI, atau DOC turunan lainnya
                                sehingga
                                placeholder PARENT_REF di prefix bisa terisi otomatis.
                            </p>
                            @error('parent_document_id')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Judul & Summary --}}
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                Judul Dokumen <span class="text-red-500">*</span>
                            </label>
                            <input wire:model.defer="title" type="text" id="title"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                       focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Contoh: Prosedur Pemeriksaan Bahan Baku Masuk">
                            @error('title')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="summary" class="block text-sm font-medium text-gray-700 mb-2">
                                Ringkasan (opsional)
                            </label>
                            <textarea wire:model.defer="summary" id="summary" rows="3"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                       focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                placeholder="Deskripsi singkat mengenai isi dokumen..."></textarea>
                            @error('summary')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Tanggal & Status --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="effective_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tanggal Efektif
                                </label>
                                <input wire:model.defer="effective_date" type="date" id="effective_date"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                @error('effective_date')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="expired_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tanggal Kadaluarsa (opsional)
                                </label>
                                <input wire:model.defer="expired_date" type="date" id="expired_date"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                @error('expired_date')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                    Status
                                </label>
                                <select wire:model.defer="status" id="status"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <option value="draft">Draft</option>
                                    <option value="in_review">In Review</option>
                                    <option value="approved">Approved</option>
                                    <option value="obsolete">Obsolete</option>
                                </select>
                                @error('status')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- File Upload --}}
                        <div>
                            <label for="uploaded_file" class="block text-sm font-medium text-gray-700 mb-2">
                                File Dokumen (PDF/DOC/XLS)
                            </label>
                            <input wire:model="uploaded_file" type="file" id="uploaded_file"
                                class="block w-full text-sm text-gray-900 border border-gray-300 rounded-md cursor-pointer
                                       focus:outline-none focus:ring-blue-500 focus:border-blue-500 file:px-4 file:py-2
                                       file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                       hover:file:bg-blue-100">
                            @error('uploaded_file')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror

                            @if ($existing_file_path && !$uploaded_file)
                                <p class="mt-2 text-xs text-gray-600">
                                    File saat ini:
                                    <a href="{{ asset('storage/' . ltrim($existing_file_path, '/')) }}"
                                        target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                                        Lihat / download
                                    </a>
                                </p>
                            @endif
                        </div>

                        {{-- Status aktif --}}
                        <div>
                            <label class="flex items-center">
                                <input wire:model="is_active" type="checkbox"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700 font-medium">
                                    Aktif
                                </span>
                            </label>
                        </div>

                        {{-- FOOTER BUTTONS --}}
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                            <button type="button" wire:click="closeModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md
                                       hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md
                                       hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500
                                       flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $isEditing ? 'Update Document' : 'Save Document' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
