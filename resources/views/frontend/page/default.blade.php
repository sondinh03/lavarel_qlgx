@extends('frontend.layout.main')

{{-- SEO --}}
@section('title', $meta_title)
@section('meta_keyword', $meta_keywords)
@section('meta_description', $meta_description)

@push('metas')
    @if($no_index)
        <meta name="robots" content="noindex"/>
    @endif
@endpush

@section('main')
<div class="bg-body-tertiary py-4">
	<div class="container">
		<div class="shadow bg-background">
		</div>
	</div>
</div>
@endsection
