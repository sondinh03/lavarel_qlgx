@extends('frontend.layout.main')

{{-- SEO --}}
@section('title', config('settings.web_name'))
@section('meta_description', config('settings.meta_description'))

@section('main')
<div class="bg-body-tertiary p-4">
	<div class="container">
		<div class="row">
			<div class="col-md-3 mb-4">
    			<div class="rounded-3 p-0 bg-white border border-light-subtle">
    				<div class="card overflow-hidden border-0">
    					<div class="card-body">
    						<p class="mb-1 fs-7">Tổng số giáo dân</p>
    						<p class="mb-1 d-flex align-items-center"><span class="fw-semibold fs-2">{{$giaodan}}</span>&nbsp;gia đình</p>
    						<small class="fs-9 text-muted">So với tháng trước</small>
    						<span class="bg-warning text-white fw-semibold fs-8 rounded-start-pill position-absolute top-20">76%</span>
    						<span class="ratio-text text-mute position-absolute">Mục tiêu đã đạt được</span>
    					</div>
    					<div id="spark1"></div>
    				</div>
    			</div>
			</div>
			<div class="col-md-3 mb-4">
    			<div class="rounded-3 bg-white border border-light-subtle">
    				<div class="card overflow-hidden border-0">
						<div class="card-body">
							<p class="mb-1 fs-7">Tổng số gia đình</p>
							<p class="mb-1 d-flex align-items-center"><span class="fw-semibold fs-2">{{$family}}</span>&nbsp;gia đình</p>
							<small class="fs-9 text-muted">So với tháng trước</small>
							<span class="bg-info text-white fw-semibold fs-8 rounded-start-pill position-absolute top-20">85%</span>
							<span class="ratio-text text-muted position-absolute">Mục tiêu đã đạt được</span>
						</div>
						<div id="spark2"></div>
					</div>
    			</div>
			</div>
			<div class="col-md-3 mb-4">
    			<div class="rounded-3 bg-white border border-light-subtle">
    				<div class="card overflow-hidden border-0">
						<div class="card-body">
							<p class="mb-1 fs-7">Tổng số hội đoàn</p>
    						<p class="mb-1 d-flex align-items-center"><span class="fw-semibold fs-2">{{$association}}</span>&nbsp;hội đoàn</p>
							<small class="fs-9 text-muted">So với tháng trước</small>
							<span class="bg-danger text-white fw-semibold fs-8 rounded-start-pill position-absolute top-20">62%</span>
							<span class="ratio-text text-muted position-absolute">Mục tiêu đã đạt được</span>
						</div>
						<div id="spark3"></div>
					</div>
    			</div>
			</div>
			<div class="col-md-3 mb-4">
    			<div class="rounded-3 bg-white border border-light-subtle">
    				<div class="card overflow-hidden border-0">
						<div class="card-body">
							<p class="mb-1 fs-7">Tổng số rao hôn phối</p>
							<p class="mb-1 d-flex align-items-center"><span class="fw-semibold fs-2">{{$marriage_announcements}}</span>&nbsp;hôn phối</p>
							<small class="fs-9 text-muted">So với tháng trước</small>
							<span class="bg-success text-white fw-semibold fs-8 rounded-start-pill position-absolute top-20">53%</span>
							<span class="ratio-text text-muted position-absolute">Mục tiêu đã đạt được</span>
						</div>
						<div id="spark4"></div>
					</div>
    			</div>
    		</div>
		</div>
	</div>
</div>

<script src="{{mix('js/char.js')}}"></script>
<script src="{{mix('js/apexcharts.min.js')}}"></script>
@endsection
