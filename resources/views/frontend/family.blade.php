@extends('frontend.layout.main')

{{-- SEO --}}
@section('title', Str::title(optional($family->metas)->meta_title ?? $family->name))
<meta name="robots" content="noindex"/>

@section('main')
<div class="bg-body-tertiary py-4">
	<div class="container-fluid">
		@if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif
		<div class="shadow bg-white mb-4 border rounded-4">
			<div class="card border-0">
				<div class="card-header bg-white">
					<div class="card-title fw-semibold py-1  fs-5">Thông tin gia đình</div>
				</div>
				<div class="card-body">
					<div class="row">
    					<div class="col-12 col-md-4">
    						<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-5">
                						Mã gia đình
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$family->id}}</strong>
                					</div>
            					</div>
        					</div>
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-5">
                						Tên gia đình
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$family->name}}</strong>
                						<span>(<a title="Sửa" href="{{$family->edit}}" class="text-decoration-none">Sửa</a>)</span>
                					</div>
            					</div>
        					</div>
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-5">
                						Cha
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$family->father}}</strong>
                					</div>
            					</div>
        					</div>
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-5">
                						Mẹ
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$family->mother}}</strong>
                					</div>
            					</div>
        					</div>
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-5">
                						Giáo họ
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$family->paid}}</strong>
                					</div>
            					</div>
        					</div>
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-5">
                						Số hộ khẩu
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$family->household}}</strong>
                					</div>
            					</div>
        					</div>
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-5">
                						Diện
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$family->dien}}</strong>
                					</div>
            					</div>
        					</div>
    					</div>
    					<div class="col-12 col-md-4">        					
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-3">
                						Số điện thoại
                					</div>
                					<div class="col-12 col-md-8">
                						<strong>{{$family->phone}}</strong>
                					</div>
            					</div>
        					</div>        					
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-3">
                						Ghi chú
                					</div>
                					<div class="col-12 col-md-8">
                						<strong>{{$family->note}}</strong>
                					</div>
            					</div>
        					</div>
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-3">
                						Địa chỉ
                					</div>
                					<div class="col-12 col-md-8">
                						<strong>{{$family->origin}}{{$family->ward}}{{$family->province}}</strong>
                					</div>
            					</div>
        					</div>
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-3">
                						Giáo xứ
                					</div>
                					<div class="col-12 col-md-8">
                						<strong>{{$family->paid}}{{$family->pid}}{{$family->deid}}{{$family->did}}</strong>
                					</div>
            					</div>
        					</div>
    						<div class="border-bottom border-light-subtle pb-2 mb-2">
    							<div class="row">
                					<div class="col-12 col-md-3">
                						Ngày hôn phối
                					</div>
                					<div class="col-12 col-md-8">
                						<strong>{{$marriage->date}}</strong>
            						</div>
            					</div>
    						</div>
    						<div class="border-bottom border-light-subtle pb-2 mb-2">
    							<div class="row">
                					<div class="col-12 col-md-3">
                						Số hôn phối
                					</div>
                					<div class="col-12 col-md-8">
                						<strong>{{$marriage->sohonphoi}}</strong>
            						</div>
            					</div>
    						</div>
    					</div>
    					<div class="col-12 col-md-4">    						
    						<div class="border-bottom border-light-subtle pb-2 mb-2">
    							<div class="row">
                					<div class="col-12 col-md-5">
                						Nơi hôn phối
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$marriage->marriage_address}}{{$marriage->marriage_ward}}{{$marriage->marriage_province}}</strong>
                					</div>
            					</div>
    						</div>    						
    						<div class="border-bottom border-light-subtle pb-2 mb-2">
    							<div class="row">
                					<div class="col-12 col-md-5">
                						Linh mục
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$marriage->priest}}</strong>
                					</div>
            					</div>
    						</div>    						
    						<div class="border-bottom border-light-subtle pb-2 mb-2">
    							<div class="row">
                					<div class="col-12 col-md-5">
                						Người làm chứng 1
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$marriage->peopleone}}</strong>
                					</div>
            					</div>
    						</div>    						
    						<div class="border-bottom border-light-subtle pb-2 mb-2">
    							<div class="row">
                					<div class="col-12 col-md-5">
                						Người làm chứng 2
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$marriage->peopletwo}}</strong>
                					</div>
            					</div>
    						</div>    						
    						<div class="border-bottom border-light-subtle pb-2 mb-2">
    							<div class="row">
                					<div class="col-12 col-md-5">
                						Trạng thái
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$marriage->tinhtrang}}</strong>
                					</div>
            					</div>
    						</div>    						
    						<div class="border-bottom border-light-subtle pb-2 mb-2">
    							<div class="row">
                					<div class="col-12 col-md-5">
                						Ghi chú
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$marriage->marriage_note}}</strong>
                					</div>
            					</div>
    						</div>
    					</div>
					</div>					
				</div>
			</div>
		</div>
		@if(is_array($children))
			<div class="shadow bg-white mb-4 border rounded-4">
    			<div class="card border-0">
    				<div class="card-header bg-white">
    					<div class="card-title fw-semibold py-1  fs-5">Các thành viên trong gia đình</div>
    				</div>
    				<div class="card-body">
    					<div class="row">
        					@foreach($children as $row)
    						<div class="col-12 col-md-4">
    							<div class="border-bottom border-primary border-3 pb-3 mb-3">
            						<div class="border-bottom border-light-subtle pb-2 mb-2">
                						<div class="row">
                        					<div class="col-12 col-md-3">
                        						Họ và tên
                        					</div>
                        					<div class="col-12 col-md-9">
                        						<strong><a class="text-decoration-none" title="{{$row->name}}" href="{{$row->slug}}">{{$row->name}}</a></strong>
                        					</div>
                    					</div>
                					</div>
                					<div class="border-bottom border-light-subtle pb-2 mb-2">
                						<div class="row">
                        					<div class="col-12 col-md-3">
                        						Phái
                        					</div>
                        					<div class="col-12 col-md-9">
                        						<strong>{{$row->sex}}</strong>
                        					</div>
                    					</div>
                					</div>
                					<div class="border-bottom border-light-subtle pb-2 mb-2">
                						<div class="row">
                        					<div class="col-12 col-md-3">
                        						Ngày sinh
                        					</div>
                        					<div class="col-12 col-md-9">
                        						<strong>{{$row->birthday}}</strong>
                        					</div>
                    					</div>
                					</div>
                					<div class="clearfix">
                						<div class="row">
                        					<div class="col-12 col-md-3">
                        						Địa chỉ
                        					</div>
                        					<div class="col-12 col-md-9">
                        						<strong>{{$row->residence}}{{$row->resi_ward}}{{$row->resi_province}}</strong>
                        					</div>
                    					</div>
                					</div>
            					</div>
        					</div>
        					@endforeach
    					</div>
    				</div>
    			</div>
    		</div>
		@endif
		<div class="shadow bg-white mb-4 border rounded-4">
			<div class="card border-0">
				<div class="card-header bg-white">
					<div class="card-title fw-semibold py-1  fs-5">Download File Word</div>
				</div>
				<div class="card-body">
					<div class="d-block d-sm-block d-md-flex align-items-center justify-content-center">
						<div class="py-2 mb-2 px-3">
							<a title="Sổ gia đình công giáo" href="{{$family->sogiadinhconggiao}}" class="text-decoration-none btn btn-info"><span class="me-2"><i class="bi bi-cloud-download"></i></span>Sổ gia đình công giáo</a>
        				</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection