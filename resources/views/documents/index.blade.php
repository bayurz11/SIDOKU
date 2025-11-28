@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <livewire:document.document-list />
        <livewire:document.document-form />
        <livewire:document.document-import-form />
        <livewire:document.document-detail-form />
    </div>
@endsection
