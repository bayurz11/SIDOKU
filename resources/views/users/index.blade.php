@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <livewire:users.user-list />
    <livewire:users.user-form />
</div>
@endsection