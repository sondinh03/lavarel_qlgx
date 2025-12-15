@extends('frontend.layout.main')

@section('content')
{{-- <livewire:lop :id="$lopId" /> --}}
<livewire:pages.student.student-list :id="$lopId" />
@endsection