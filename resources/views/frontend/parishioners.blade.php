@extends('frontend.layout.main')

{{-- SEO --}}
@section('title', Str::title(optional($parishioners->metas)->meta_title ?? $parishioners->name))
<meta name="robots" content="noindex"/>

{{-- @section('main') --}}
@section('content')
<div class="bg-body-tertiary py-4">
	<div class="container-fluid">
		<div class="shadow bg-white mb-4 border rounded-4">
			<div class="card border-0">
				<div class="card-header bg-white">
					<div class="card-title fw-semibold py-1  fs-5">Thông tin cơ bản</div>
				</div>
				<div class="card-body">
					<div class="row">
    					<div class="col-12 col-md-4">
    						<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-5">
                						Mã giáo dân
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$parishioners->id}}</strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-5">
                						Họ tên
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$parishioners->holy}} {{$parishioners->last_name}} {{$parishioners->name}}</strong>
                						<span>(<a title="Sửa" href="{{$parishioners->edit}}" class="text-decoration-none">Sửa</a>)</span>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-5">
                						Giới tính
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$parishioners->sex}}</strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-5">
                						Ngày sinh
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$parishioners->birthday}}</strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-5">
                						Cccd
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$parishioners->cccd}}</strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-5">
                						Cha
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$parishioners->father}}</strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-5">
                						Mẹ
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$parishioners->mother}}</strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-5">
                						Số điện thoại
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$parishioners->phone}}</strong>
                					</div>
                				</div>
            				</div>
    					</div>
    					<div class="col-12 col-md-5">
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-3">
                						Email
                					</div>
                					<div class="col-12 col-md-9">
                						<strong>{{$parishioners->email}}</strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-3">
                						Giáo họ
                					</div>
                					<div class="col-12 col-md-9">
                						<strong>{{$parishioners->paid}}</strong>
                						<strong>{{$parishioners->pid}}</strong>
                						<strong>{{$parishioners->deid}}</strong>
                						<strong>{{$parishioners->did}}</strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-3">
                						Hội đoàn
                					</div>
                					<div class="col-12 col-md-9">
                						<strong>{{$parishioners->assid}}</strong>
                					</div>
                				</div>
            				</div>
    						<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-3">
                						Nguyên quán
                					</div>
                					<div class="col-12 col-md-9">
                						<strong>{{$parishioners->origin}}, {{$parishioners->ward}}, {{$parishioners->province}}</strong>
                					</div>
            					</div>
            				</div>		
            				<div class="border-bottom border-light-subtle pb-2 mb-2">		
                				<div class="row">
                					<div class="col-12 col-md-3">
                						Trú quán
                					</div>
                					<div class="col-12 col-md-9">
                						<strong>{{$parishioners->residence}}, {{$parishioners->resi_ward}}, {{$parishioners->resi_province}}</strong>						
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-3">
                						Dân tộc
                					</div>
                					<div class="col-12 col-md-9">
                						<strong>{{$parishioners->ethnic}}</strong>					
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-3">
                						Ngôn ngữ
                					</div>
                					<div class="col-12 col-md-9">
                						<strong>{{$parishioners->language}}</strong>				
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-3">
                						Trình độ
                					</div>
                					<div class="col-12 col-md-9">
                						<strong>{{$parishioners->level}}</strong>		
                					</div>
                				</div>
            				</div>
    					</div>
    					<div class="col-3">
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
                				<div class="row">
                					<div class="col-12 col-md-5">
                						Nghề nghiệp
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$parishioners->career}}</strong>			
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-5">
                						Chức vụ
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$parishioners->position}}</strong>		
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-5">
                						Trình độ chuyên môn
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$parishioners->professional_level}}</strong>		
                					</div>
                				</div>
            				</div>
    						<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-5">
                						Giáo dục
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{{$parishioners->study}}</strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-5">
                						Tân tòng
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{!! $parishioners->new_convert !!}</strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-5">
                						Có gia đình
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{!! $parishioners->married !!}</strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-5">
                						Thống kê
                					</div>
                					<div class="col-12 col-md-7">
                						<strong>{!! $parishioners->statistical !!}</strong>
                					</div>
                				</div>
            				</div>
            				<div class="row">
            					<div class="col-12 col-md-5">
                					Mô tả thêm
                				</div>
            					<div class="col-12 col-md-7">
            						<strong>{{$parishioners->note}}</strong>
            					</div>
            				</div>
        				</div>
    				</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 mb-4">
				<div class="shadow bg-white border rounded-4 h-100">
        			<div class="card border-0 rounded-4">
        				<div class="card-header bg-transparent rounded-top-4">
        					<div class="card-title fw-semibold py-1  fs-5">Rửa tội</div>
        				</div>
        				<div class="card-body">
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
            					<div class="row">
            						<div class="col-12 col-md-4">
            							Ngày rửa tội
            						</div>
            						<div class="col-12 col-md-8">
            							<strong>{{$parishioners->baptism_date}}</strong>
            						</div>
            					</div>
        					</div>
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
            					<div class="row">
            						<div class="col-12 col-md-4">
            							Số rửa tội
            						</div>
            						<div class="col-12 col-md-8">
            							<strong>{{$parishioners->baptism_number}}</strong>
            						</div>
            					</div>
        					</div>
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
            					<div class="row">
            						<div class="col-12 col-md-4">
            							Người ban bí tích
            						</div>
            						<div class="col-12 col-md-8">
            							<strong>{{$parishioners->baptism_giver}}</strong>
            						</div>
            					</div>
        					</div>
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
            					<div class="row">
            						<div class="col-12 col-md-4">
            							Người đỡ đầu
            						</div>
            						<div class="col-12 col-md-8">
            							<strong>{{$parishioners->baptism_sponsor}}</strong>
            						</div>
            					</div>
        					</div>
        					<div class="row">
        						<div class="col-12 col-md-4">
        							Giáo xứ
        						</div>
        						<div class="col-12 col-md-8">
        							<strong>{{$parishioners->baptism_parish}}</strong>
        							<strong>{{$parishioners->baptism_deanerys}}</strong> 
        							<strong>{{$parishioners->baptism_dioceses}}</strong>
        						</div>
        					</div>
        				</div>
        			</div>
        		</div>
			</div>
			<div class="col-md-3 mb-4">
				<div class="shadow bg-white border rounded-4 h-100">
        			<div class="card border-0 rounded-4">
        				<div class="card-header bg-transparent rounded-top-4">
        					<div class="card-title fw-semibold py-1  fs-5">Thêm sức</div>
        				</div>
        				<div class="card-body">
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
            					<div class="row">
            						<div class="col-12 col-md-4">
            							Ngày thêm sức
            						</div>
            						<div class="col-12 col-md-8">
            							<strong>{{$parishioners->more_power_date}}</strong>
            						</div>
            					</div>
        					</div>
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
            					<div class="row">
            						<div class="col-12 col-md-4">
            							Số thêm sức
            						</div>
            						<div class="col-12 col-md-8">
            							<strong>{{$parishioners->more_power_number}}</strong>
            						</div>
            					</div>
        					</div>
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
            					<div class="row">
            						<div class="col-12 col-md-4">
            							Người ban bí tích
            						</div>
            						<div class="col-12 col-md-8">
            							<strong>{{$parishioners->more_power_giver}}</strong>
            						</div>
            					</div>
        					</div>
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
            					<div class="row">
            						<div class="col-12 col-md-4">
            							Người đỡ đầu
            						</div>
            						<div class="col-12 col-md-8">
            							<strong>{{$parishioners->more_power_sponsor}}</strong>            							
            						</div>
            					</div>
        					</div>
        					<div class="row">
        						<div class="col-12 col-md-4">
        							Giáo xứ
        						</div>
        						<div class="col-12 col-md-8">
        							<strong>{{$parishioners->more_power_parish}}</strong>
        							<strong>{{$parishioners->more_power_deanerys}}</strong>
        							<strong>{{$parishioners->more_power_dioceses}}</strong>
        						</div>
        					</div>
        				</div>
        			</div>
        		</div>
			</div>
			<div class="col-md-3 mb-4">
				<div class="shadow bg-white border rounded-4 h-100">
        			<div class="card border-0 rounded-4">
        				<div class="card-header bg-transparent rounded-top-4">
        					<div class="card-title fw-semibold py-1  fs-5">Rước lễ</div>
        				</div>
        				<div class="card-body">
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
            					<div class="row">
            						<div class="col-12 col-md-4">
            							Ngày rước lễ
            						</div>
            						<div class="col-12 col-md-8">
            							<strong>{{$parishioners->communion_date}}</strong>        
            						</div>
            					</div>
        					</div>
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
            					<div class="row">
            						<div class="col-12 col-md-4">
            							Số rước lễ
            						</div>
            						<div class="col-12 col-md-8">
            							<strong>{{$parishioners->communion_number}}</strong>        
            						</div>
            					</div>
        					</div>
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
            					<div class="row">
            						<div class="col-12 col-md-4">
            							Người ban bí tích
            						</div>
            						<div class="col-12 col-md-8">
            							<strong>{{$parishioners->communion_giver}}</strong>        
            						</div>
            					</div>
        					</div>
        					<div class="row">
        						<div class="col-12 col-md-4">
        							Giáo xứ
        						</div>
        						<div class="col-12 col-md-8">
        							<strong>{{$parishioners->communion_parish}}</strong>
        							<strong>{{$parishioners->communion_deanerys}}</strong>
        							<strong>{{$parishioners->communion_dioceses}}</strong>
        						</div>
        					</div>
        				</div>
        			</div>
        		</div>
			</div>
			<div class="col-md-3 mb-4">
				<div class="shadow bg-white border rounded-4 h-100">
        			<div class="card border-0 rounded-4">
        				<div class="card-header bg-transparent rounded-top-4">
        					<div class="card-title fw-semibold py-1  fs-5">Xức dầu</div>
        				</div>
        				<div class="card-body">
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
            					<div class="row">
            						<div class="col-12 col-md-4">
            							Ngày xúc dầu
            						</div>
            						<div class="col-12 col-md-8">
            							<strong>{{$parishioners->anoint_date}}</strong>
            						</div>
            					</div>
        					</div>
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
            					<div class="row">
            						<div class="col-12 col-md-4">
            							Tình trạng
            						</div>
            						<div class="col-12 col-md-8">
            							<strong>{{$parishioners->anoint_status}}</strong>
            						</div>
            					</div>
        					</div>
        					<div class="border-bottom border-light-subtle pb-2 mb-2">
            					<div class="row">
            						<div class="col-12 col-md-4">
            							Người ban bí tích
            						</div>
            						<div class="col-12 col-md-8">
            							<strong>{{$parishioners->anoint_giver}}</strong>
            						</div>
            					</div>
        					</div>
        					<div class="row">
        						<div class="col-12 col-md-4">
        							Ghi chú
        						</div>
        						<div class="col-12 col-md-8">
        							<strong>{{$parishioners->anoint_note}}</strong>
        						</div>
        					</div>
        				</div>
        			</div>
        		</div>
			</div>
		</div>
	 	@if($parishioners->die_status === 1)
    		<div class="shadow bg-white mb-4 border rounded-4">
    			<div class="card border-0">
    				<div class="card-header bg-white">
    					<div class="card-title fw-semibold py-1  fs-5">Thông tin khác</div>
    				</div>
    				<div class="card-body">
    					<div class="border-bottom border-light-subtle pb-2 mb-2">
        					<div class="row">
        						<div class="col-12 col-md-4">
        							Trạng thái sống còn
        						</div>
        						<div class="col-12 col-md-8">
        							<strong>{!! $parishioners->die_status !!}</strong>
        						</div>
        					</div>
    					</div>
    					<div class="border-bottom border-light-subtle pb-2 mb-2">
        					<div class="row">
        						<div class="col-12 col-md-4">
        							Thời gian mất
        						</div>
        						<div class="col-12 col-md-8">
        							<strong>{{$parishioners->die_time}}</strong>
        						</div>
        					</div>
    					</div>
    					<div class="border-bottom border-light-subtle pb-2 mb-2">
        					<div class="row">
        						<div class="col-12 col-md-4">
        							Số xổ mất
        						</div>
        						<div class="col-12 col-md-8">
        							<strong>{{$parishioners->die_lottery}}</strong>
        						</div>
        					</div>
    					</div>
    					<div class="border-bottom border-light-subtle pb-2 mb-2">
        					<div class="row">
        						<div class="col-12 col-md-4">
        							Nơi qua đời
        						</div>
        						<div class="col-12 col-md-8">
        							<strong>{{$parishioners->die_death}}</strong>
        						</div>
        					</div>
    					</div>
    					<div class="border-bottom border-light-subtle pb-2 mb-2">
        					<div class="row">
        						<div class="col-12 col-md-4">
    								Nơi an táng
        						</div>
        						<div class="col-12 col-md-8">
        							<strong>{{$parishioners->die_burial}}</strong>
        						</div>
        					</div>
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
							<a title="Lý lịch cá nhân" href="{{$parishioners->lylichcanhan}}" class="text-decoration-none btn btn-info"><span class="me-2"><i class="bi bi-cloud-download"></i></span>Lý lịch cá nhân</a>
        				</div>
        				<div class="py-2 mb-2 px-3">
        					<a title="Bí tích" href="{{$parishioners->bitich}}" class="text-decoration-none btn btn-info"><span class="me-2"><i class="bi bi-cloud-download"></i></span>Bí tích</a>
        				</div>
        				<div class="py-2 mb-2 px-3">
        					<a title="Giấy giới thiệu học giáo lý hôn phối" href="{{$parishioners->giaygioithieugiaolyhonphoi}}" class="text-decoration-none btn btn-info"><span class="me-2"><i class="bi bi-cloud-download"></i></span>Giấy giới thiệu học giáo lý hôn phối</a>
        				</div>
        				<div class="py-2 mb-2 px-3">
        					<a title="Giấy giới thiệu hôn phối" href="{{$parishioners->giaygioithieuhonphoi}}" class="text-decoration-none btn btn-info"><span class="me-2"><i class="bi bi-cloud-download"></i></span>Giấy giới thiệu hôn phối</a>
        				</div>
        				<div class="py-2 mb-2 px-3">
        					<a title="Giấy điều tra hôn phối" href="{{$parishioners->giaydieutrahonphoi}}" class="text-decoration-none btn btn-info"><span class="me-2"><i class="bi bi-cloud-download"></i></span>Giấy điều tra hôn phối</a>
        				</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection