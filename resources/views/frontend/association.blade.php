@extends('frontend.layout.main')

{{-- SEO --}}
@section('title', Str::title(optional($association->metas)->meta_title ?? $association->name))
<meta name="robots" content="noindex"/>

@section('main')
<div class="bg-body-tertiary py-4">
	<div class="container-fluid">
		<div class="shadow bg-white mb-4 border rounded-4">
			<div class="card border-0">
				<div class="card-header bg-white">
					<div class="card-title fw-semibold py-1  fs-5">Hội đoàn</div>
				</div>
				<div class="card-body">
					<div class="row">
    					<div class="col-12 col-md-4">
    						<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-4">
                						Tên hội đoàn
                					</div>
                					<div class="col-12 col-md-8">
                						<strong>{{$association->name}}</strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-4">
                						Ngày bổn mạng
                					</div>
                					<div class="col-12 col-md-8">
                						<strong>{{$association->ngaybonmang}}</strong>
                					</div>
                				</div>
            				</div>
    					</div>
    					<div class="col-12 col-md-4">
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-4">
                						Ngày thành lập
                					</div>
                					<div class="col-12 col-md-8">
                						<strong>{{$association->ngaythanhlap}}</strong>
                					</div>
                				</div>
            				</div>
    						<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-4">
                						Thánh bổn mạng
                					</div>
                					<div class="col-12 col-md-8">
                						<strong>{{$association->thanhbonmang}}</strong>
                					</div>
                				</div>
            				</div>
    					</div>
    					<div class="col-12 col-md-4">
    						<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-2">
                						Giáo xứ
                					</div>
                					<div class="col-12 col-md-10">
                						<strong>{{$association->pid}}{{$association->deid}}{{$association->did}}</strong>
                					</div>
                				</div>
            				</div>
            				<div class="border-bottom border-light-subtle pb-2 mb-2">
        						<div class="row">
                					<div class="col-12 col-md-2">
                						Ghi chú
                					</div>
                					<div class="col-12 col-md-10">
                						<strong>{{$association->note}}</strong>
                					</div>
                				</div>
            				</div>
    					</div>
					</div>
				</div>
			</div>
		</div>
		@if(!empty($thanhvien))
		<div class="shadow bg-white mb-4 border rounded-4">
			<div class="card border-0">
				<div class="card-header bg-white">
					<div class="card-title fw-semibold py-1  fs-5">Thành viên hội đoàn</div>
				</div>
				<div class="card-body">
					<div class="table-responsive">
    					<table class="table table-vcenter text-nowrap mb-0 table-striped table-bordered border-top">
                          	<thead>
                            	<tr>
                                  	<th scope="col" class="fw-medium">STT</th>
                                  	<th scope="col" class="fw-medium">Tên thánh</th>
                                  	<th scope="col" class="fw-medium">Tên đệm</th>
                                  	<th scope="col" class="fw-medium">Tên</th>
                                  	<th scope="col" class="fw-medium">Giới tính</th>
                                  	<th scope="col" class="fw-medium">Ngày sinh</th>
                                  	<th scope="col" class="fw-medium">Giáo họ</th>
                                </tr>
                          	</thead>
                          	<tbody> 
                          	@foreach($thanhvien as $key => $row)
            					<tr>
            						<th scope="row">{{$row->stt}}</th>
            						<td class="border-top-0 border-bottom-0">{{$row->holy}}</td>
            						<td class="border-top-0 border-bottom-0">{{$row->last_name}}</td>
            						<td class="border-top-0 border-bottom-0"><a class="text-decoration-none" title="{{$row->holy}} {{$row->name}}" href="{{$row->slug}}">{{$row->name}}</a></td>
            						<td class="border-top-0 border-bottom-0">{{$row->sex}}</td>
                                  	<td class="border-top-0 border-bottom-0">{{$row->birthday}}</td>
                                  	<td class="border-top-0 border-bottom-0">{{$row->paid}}{{$row->pid}}{{$row->did}}</td>
            					</tr>
            					@endforeach   
                          	</tbody>
                          	@if(!empty($family))
                          	<tfoot>
                          		<tr>
                          			<td colspan="7">
                          				<div class="d-flex justify-content-end align-items-center py-2">
                          					{!! $pagination !!}
                          				</div>
                          			</td>
                          		</tr>
                          	</tfoot>
                          	@endif
                      	</table>
                  	</div>
				</div>
			</div>
		</div>
		@endif
	</div>
</div>
@endsection