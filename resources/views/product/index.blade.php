@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <livewire:ipc.in-preces-controlel-form />
        <livewire:ipc.in-preces-controlel-list />
        <livewire:ipc.in-proces-controlel-detail />
        {{-- <livewire:ipc.ipc-product-import /> --}}
    </div>
@endsection
