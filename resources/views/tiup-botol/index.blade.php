@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <livewire:ipc.tiup-botol-list />
        <livewire:ipc.tiup-botol-form />
        {{-- <livewire:ipc.ipc-product-detail /> --}}
    </div>
@endsection
