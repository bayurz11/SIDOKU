@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <livewire:document.approval-queue />
        <livewire:document.document-detail-form />
    </div>
@endsection
