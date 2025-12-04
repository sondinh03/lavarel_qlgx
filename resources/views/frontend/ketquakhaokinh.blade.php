<?php
use App\Models\DiLe;
use App\Models\KhaoKinh;
?>
@extends('frontend.layout.main')

{{-- SEO --}}
@section('title', Str::title('Kết quả khảo kinh lớp ' . $lop->name))
<meta name="robots" content="noindex"/>

@section('main')
<div class="container-fluid">
	<div class="shadow bg-white mb-4 border rounded-4">
    	<div class="card border-0">
    		<div class="card-header bg-white">
    			<div class="card-title fw-semibold py-1 fs-5">
    				Kết quả khảo kinh kỳ 1 - {{$lop->schoolyear}} - {{$lop->name}}
    			</div>
    		</div>
    		<div class="card-body">
				<div class="table-responsive">
        			<table class="table table-striped table-bordered table-hover text-nowrap">
                      	<thead class="fs-6">
                            <tr>
                              	<th scope="col">#</th>
                              	<th scope="col">Mã thiếu nhi</th>
                              	<th scope="col">Tên thánh</th>
                              	<th scope="col">Họ tên đệm</th>
                              	<th scope="col">Tên</th>
                              	<th scope="col">Ngày sinh</th>
                              	@for ($i = 1; $i <= $hk1; $i++)
                              	<th>Lần {{$i}}</th>
                              	@endfor
                            </tr>
                      	</thead>
                      	<tbody class="fs-6">
                      		@foreach($student as $key => $hocsinh)
                      		<tr data-id="{{$hocsinh->id}}" id="chuoi_{{$hocsinh->id}}">
                              	<th scope="row">{{$hocsinh->stt}}</th>
                              	<td>{{$hocsinh->mahv}}</td>
                              	<td>{{$hocsinh->holy}}</td>
                              	<td>{{$hocsinh->last_name}}</td>
                              	<td class="sticky-col second-col">{{$hocsinh->name}}</td>
                              	<td>{{$hocsinh->birthday}}</td>
                              	@for ($i = 1; $i <= $hk1; $i++)
                                  	<?php
                                  	$khaokinh = KhaoKinh::where('idh', $hocsinh->id)->where('lophoc', $hocsinh->lop)->where('hocky', 1)->where('ngay', $i)->where('status', 1)->orderBy('created_at', 'asc')->get()->first();
                                  	?>
                                  	<td>
                                      	<?php
                                      	if(!empty($khaokinh->khaokinh)){
                                      	    if($khaokinh->khaokinh == 1){
                                          	    echo 'Thuộc bài';
                                      	    }elseif($khaokinh->khaokinh == 2){
                                          	    echo 'Ấp úng';
                                      	    }elseif($khaokinh->khaokinh == 3){
                                          	    echo 'Không thuộc';
                                          	}else{
                                          	    echo ' -- ';
                                          	}
                                      	}else{
                                      	    echo ' -- ';
                                      	}
                                      	?>
                                  	</td>
                              	@endfor
                            </tr>
                      		@endforeach
                      	</tbody>
                      	<tfoot>
                      		<tr>
                      			<td colspan="7">
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
	<div class="shadow bg-white mb-4 border rounded-4">
    	<div class="card border-0">
    		<div class="card-header bg-white">
    			<div class="card-title fw-semibold py-1 fs-5">
    				Kết quả khảo kinh kỳ 2 - {{$lop->schoolyear}} - {{$lop->name}}
    			</div>
    		</div>
    		<div class="card-body">
				<div class="table-responsive">
        			<table class="table table-striped table-bordered table-hover text-nowrap">
                      	<thead class="fs-6">
                            <tr>
                              	<th scope="col">#</th>
                              	<th scope="col">Mã thiếu nhi</th>
                              	<th scope="col">Tên thánh</th>
                              	<th scope="col">Họ tên đệm</th>
                              	<th scope="col">Tên</th>
                              	<th scope="col">Ngày sinh</th>
                          		@for ($i = 1; $i <= $hk2; $i++)
                              	<th>Lần {{$i}}</th>
                              	@endfor
                            </tr>
                      	</thead>
                      	<tbody class="fs-6">
                      		@foreach($student as $key => $hocsinh)
                      		<tr data-id="{{$hocsinh->id}}" id="chuoi_{{$hocsinh->id}}">
                              	<th scope="row">{{$hocsinh->stt}}</th>
                              	<td>{{$hocsinh->mahv}}</td>
                              	<td>{{$hocsinh->holy}}</td>
                              	<td>{{$hocsinh->last_name}}</td>
                              	<td class="sticky-col second-col">{{$hocsinh->name}}</td>
                              	<td>{{$hocsinh->birthday}}</td>
                              	@for ($i = 1; $i <= $hk2; $i++)
                                  	<?php
                                  	$khaokinh = KhaoKinh::where('idh', $hocsinh->id)->where('lophoc', $hocsinh->lop)->where('hocky', 2)->where('ngay', $i)->where('status', 1)->orderBy('created_at', 'asc')->get()->first();
                                  	?>
                                  	<td>
                                      	<?php
                                      	if(!empty($khaokinh->khaokinh)){
                                      	    if($khaokinh->khaokinh == 1){
                                          	    echo 'Thuộc bài';
                                      	    }elseif($khaokinh->khaokinh == 2){
                                          	    echo 'Ấp úng';
                                      	    }elseif($khaokinh->khaokinh == 3){
                                          	    echo 'Không thuộc';
                                          	}else{
                                          	    echo ' -- ';
                                          	}
                                      	}else{
                                      	    echo ' -- ';
                                      	}
                                      	?>
                                  	</td>
                              	@endfor
                            </tr>
                      		@endforeach
                      	</tbody>
                      	<tfoot>
                      		<tr>
                      			<td colspan="7">
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