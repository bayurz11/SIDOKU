@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <livewire:incoming-material.incoming-material-list />
        <livewire:incoming-material.incoming-material-form />
        <livewire:incoming-material.incoming-material-detail />
        {{-- <livewire:ipc.ipc-product-import /> --}}
    </div>
@endsection
