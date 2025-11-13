@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <livewire:document.document-type-list />
        <livewire:document.document-type-form />
    </div>
@endsection
