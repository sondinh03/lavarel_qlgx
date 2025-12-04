@extends('frontend.layout.main')

{{-- SEO --}}
@section('title', Str::title(optional($block->metas)->meta_title ?? $block->name))
<meta name="robots" content="noindex"/>

@section('main')
<div class="container-fluid">
    <div class="shadow bg-white mb-4 border rounded-4">
    	<div class="card border-0">
    		<div class="card-header bg-white">
    			<div class="card-title fw-semibold py-1 fs-5">
    				khối {{$block->name}}
    			</div>
    		</div>
    		<div class="card-body">
    			<div class="row">
    				<div class="col-md-4">
    					<div class="border-bottom border-light-subtle pb-2 mb-2">
    						<div class="row">
    							<div class="col-12 col-md-4">
    								Khối
    							</div>
    							<div class="col-12 col-md-8">
    								<strong>{{$block->block}}</strong>
    							</div>
    						</div>
    					</div>
    				</div>
    				<div class="col-md-8">
    					<div class="border-bottom border-light-subtle pb-2 mb-2">
    						<div class="row">
    							<div class="col-12 col-md-3">
    								Địa chỉ
    							</div>
    							<div class="col-12 col-md-9">
    								<strong>{{$block->paid}}{{$block->pid}}{{$block->deid}}{{$block->did}}</strong>
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
    			<div class="card-title fw-semibold py-1 fs-5">
    				Danh sách lớp
    			</div>
    		</div>
    		<div class="card-body">
    			<div class="table-responsive">
            		<table class="table table-striped table-bordered table-hover text-nowrap">
                      	<thead>
                            <tr>
                              	<th scope="col">#</th>
                              	<th scope="col">Tên lớp</th>
                              	<th scope="col">Khối</th>
                              	<th scope="col">Khóa học</th>
                              	<th scope="col">Học kỳ 1</th>
                              	<th scope="col">Học kỳ 2</th>
                              	<th scope="col">Ký hiệu</th>
                              	<th scope="col">Giáo viên</th>
                              	<th scope="col">Địa chỉ</th>
                              	<th scope="col">Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                        	@foreach($lop as $key => $item)
                      		<tr>
                              	<th scope="row">{{$key+1}}</th>
                              	<td><a href="{{$item->slug}}" title="{{$item->name}}"><strong>{{$item->name}}</strong></a></td>
                              	<td>{{$item->block}}</td>
                              	<td>{{$item->schoolyear}}</td>
                              	<td>{{$item->start_date_one}} đến {{$item->end_date_one}}</td>
                              	<td>{{$item->start_date_two}} đến {{$item->end_date_two}}</td>
                              	<td>{{$item->symbol}}</td>
                              	<td>
                              		@if(!empty($item->teach))
                                  		@foreach($item->teach as $key => $teach)
                                  			{{$teach}}
                                  			@if($key + 1 < count($item->teach))
                                  				,
                              				@endif
                                  		@endforeach
                              		@endif
                              	</td>
                              	<td>{{$item->paid}}{{$item->pid}}{{$item->deid}}{{$item->did}}</td>
                              	<td></td>
                            </tr>
                      		@endforeach
                        </tbody>
                        <tfoot>
                      		<tr>
                      			<td colspan="11">
                      				<div class="d-flex justify-content-end align-items-center py-2">
                      					{!! $pagination !!}
                      				</div>
                      			</td>
                      		</tr>
                      	</tfoot>
                    </table>
                </div>
    		</div>
		</div>
	</div>
</div>
@endsection