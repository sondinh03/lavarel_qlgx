@extends('frontend.layout.main')

{{-- SEO --}}
@section('title', Str::title(optional($raohonphoi->metas)->meta_title ?? $raohonphoi->name))
<meta name="robots" content="noindex"/>

@section('main')
<div class="bg-body-tertiary py-4">
	<div class="container-fluid">
		<div class="shadow bg-white mb-4 border rounded-4">
			<div class="card border-0">
				<div class="card-header bg-white">
					<div class="card-title fw-semibold py-1  fs-5">Thông tin rao hôn phối</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<div class="border-bottom border-light-subtle pb-2 mb-2">
								<div class="row">
                					<div class="col-12 col-md-4">
                						Đôi rao
                					</div>
                					<div class="col-12 col-md-8">
                						<strong>{{$raohonphoi->name}}</strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
								<div class="row">
                					<div class="col-12 col-md-4">
                						Linh mục
                					</div>
                					<div class="col-12 col-md-8">
                						<strong>{{$raohonphoi->priest}}</strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
								<div class="row">
                					<div class="col-12 col-md-4">
                						Nơi rao
                					</div>
                					<div class="col-12 col-md-8">
                						<strong>{{$raohonphoi->pid}}{{$raohonphoi->deid}}{{$raohonphoi->did}}</strong>
                					</div>
                				</div>
            				</div>
						</div>
						<div class="col-md-6">
							<div class="border-bottom border-light-subtle pb-2 mb-2">
								<div class="row">
                					<div class="col-12 col-md-4">
                						Rao lần 1
                					</div>
                					<div class="col-12 col-md-8">
                						<strong>{{$raohonphoi->announcements_one}}</strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
								<div class="row">
                					<div class="col-12 col-md-4">
                						Rao lần 2
                					</div>
                					<div class="col-12 col-md-8">
                						<strong>{{$raohonphoi->announcements_two}}</strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
								<div class="row">
                					<div class="col-12 col-md-4">
                						Rao lần 3
                					</div>
                					<div class="col-12 col-md-8">
                						<strong>{{$raohonphoi->announcements_three}}</strong>
                					</div>
                				</div>
            				</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="shadow bg-white mb-4 border rounded-4">
			<div class="card border-0">
				<div class="card-header bg-white">
					<div class="card-title fw-semibold py-1  fs-5">Rao hôn phối</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-4">
                						Người thứ nhất
                					</div>
                					<div class="col-12 col-md-8">
                						<strong><a class="text-decoration-none" href="{{$nam->slug}}" title="{{$nam->holy}} {{$nam->last_name}} {{$nam->name}}">{{$nam->holy}} {{$nam->last_name}} {{$nam->name}}</a></strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-4">
                						Nguyên quán xứ
                					</div>
                					<div class="col-12 col-md-8">
                						<strong>{{$nam->parishsold}}{{$nam->parishmanagementsold}}{{$nam->deanerysold}}{{$nam->diocesesold}}</strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-4">
                						Hiện ở giáo xứ
                					</div>
                					<div class="col-12 col-md-8">
                						<strong>{{$nam->parishs}}{{$nam->parishmanagements}}{{$nam->deanerys}}{{$nam->dioceses}}</strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-4">
                						Trước ở giáo xứ
                					</div>
                					<div class="col-12 col-md-8">
                						<strong>{{$nam->parishsbefore}}{{$nam->parishmanagementsbefore}}{{$nam->deanerysbefore}}{{$nam->diocesesbefore}}</strong>
                					</div>
                				</div>
							</div>
						</div>
						<div class="col-md-6">
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-4">
                						Người thứ hai
                					</div>
                					<div class="col-12 col-md-8">
                						<strong><a class="text-decoration-none" href="{{$nu->slug}}" title="{{$nu->holy}} {{$nu->last_name}} {{$nu->name}}">{{$nu->holy}} {{$nu->last_name}} {{$nu->name}}</a></strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-4">
                						Nguyên quán xứ
                					</div>
                					<div class="col-12 col-md-8">
                						<strong>{{$nu->parishsold}}{{$nu->parishmanagementsold}}{{$nu->deanerysold}}{{$nu->diocesesold}}</strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-4">
                						Hiện ở giáo xứ
                					</div>
                					<div class="col-12 col-md-8">
                						<strong>{{$nu->parishs}}{{$nu->parishmanagements}}{{$nu->deanerys}}{{$nu->dioceses}}</strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-4">
                						Trước ở giáo xứ
                					</div>
                					<div class="col-12 col-md-8">
                						<strong>{{$nu->parishsbefore}}{{$nu->parishmanagementsbefore}}{{$nu->deanerysbefore}}{{$nu->diocesesbefore}}</strong>
                					</div>
                				</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="shadow bg-white mb-4 border rounded-4">
			<div class="card border-0">
				<div class="card-header bg-white">
					<div class="card-title fw-semibold py-1  fs-5">Download File Word</div>
				</div>
				<div class="card-body">
					<div class="d-block d-sm-block d-md-flex align-items-center justify-content-center">
						<div class="py-2 mb-2 px-3">
							<a title="Rao hôn phối (nam)" href="{{$nam->raohonphoi_nam}}" class="text-decoration-none btn btn-info"><span class="me-2"><i class="bi bi-cloud-download"></i></span>Rao hôn phối (nam)</a>
        				</div>
        				<div class="py-2 mb-2 px-3">
        					<a title="Rao hôn phối (nữ)" href="{{$nu->raohonphoi_nu}}" class="text-decoration-none btn btn-info"><span class="me-2"><i class="bi bi-cloud-download"></i></span>Rao hôn phối (nữ)</a>
        				</div>
        				<div class="py-2 mb-2 px-3">
        					<a title="Kết quả rao hôn phối (nam)" href="{{$nam->kqraohonphoi_nam}}" class="text-decoration-none btn btn-info"><span class="me-2"><i class="bi bi-cloud-download"></i></span>Kết quả rao hôn phối (nam)</a>
        				</div>
        				<div class="py-2 mb-2 px-3">
        					<a title="Kết quả rao hôn phối (nữ)" href="{{$nu->kqraohonphoi_nu}}" class="text-decoration-none btn btn-info"><span class="me-2"><i class="bi bi-cloud-download"></i></span>Kết quả rao hôn phối (nữ)</a>
        				</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection