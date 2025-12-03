@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <livewire:ipc.ipc-product-check-list />
        <livewire:ipc.ipc-product-check-form />
        <livewire:ipc.ipc-product-detail />
    </div>
@endsection
