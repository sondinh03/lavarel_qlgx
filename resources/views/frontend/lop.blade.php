@extends('frontend.layout.main')

@section('content')
<livewire:student.student-list :id="$lopId" />
@endsection