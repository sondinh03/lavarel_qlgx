<?php
use App\Models\DiHoc;
?>
@extends('frontend.layout.main')

{{-- SEO --}}
@section('title', Str::title('Kết quả điểm danh đi học lớp ' . $lop->name))
<meta name="robots" content="noindex"/>

@section('main')
<div class="container-fluid">
	<div class="shadow bg-white mb-4 border rounded-4">
    	<div class="card border-0">
    		<div class="card-header bg-white">
    			<div class="card-title fw-semibold py-1 fs-5">
    				Kết quả đi học kỳ 1 - {{$lop->schoolyear}} - {{$lop->name}}
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
                              	@foreach($weeks_ky1 as $key => $row)
                              	<th>Tuần {{$key}}</th>
                              	@endforeach
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
                              	@foreach($weeks_ky1 as $key => $row)
                              		<td>
                              		<?php
                              		$dihoc = DiHoc::where('idh', $hocsinh->id)->where('lophoc', $hocsinh->lop)->where('hocky', 1)->where('tuan', $key)->where('status', 1)->orderby('tuan', 'asc')->get();
                              		?>
                              		@foreach($dihoc as $_key => $row)
                                  		@if($row->dihoc == 1)
                                  			Đi học
                                  		@elseif($row->dihoc == 2)
                                  			Vắng có phép
                                  		@elseif($row->dihoc == 0)
                                  			Vắng
                              			@else
                              				---
                                  		@endif
                              		@endforeach
                              		</td>
                              	@endforeach
                            </tr>
                      		@endforeach
                      	</tbody>
                      	<tfoot>
                      		<tr>
                      			<td colspan="4">
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
    				Kết quả đi học kỳ 2 - {{$lop->schoolyear}} - {{$lop->name}}
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
                              	@foreach($weeks_ky2 as $key => $row)
                              	<th>Tuần {{$key}}</th>
                              	@endforeach
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
                              	@foreach($weeks_ky2 as $key => $row)
                              		<td>
                              		<?php
                              		$dihoc = DiHoc::where('idh', $hocsinh->id)->where('lophoc', $hocsinh->lop)->where('hocky', 2)->where('tuan', $key)->where('status', 1)->orderby('tuan', 'asc')->get();
                              		?>
                              		@foreach($dihoc as $_key => $row)
                                  		@if($row->dihoc == 1)
                                  			Đi học
                                  		@elseif($row->dihoc == 2)
                                  			Vắng có phép
                                  		@elseif($row->dihoc == 0)
                                  			Vắng
                              			@else
                              				---
                                  		@endif
                              		@endforeach
                              		</td>
                              	@endforeach
                            </tr>
                      		@endforeach
                      	</tbody>
                      	<tfoot>
                      		<tr>
                      			<td colspan="4">
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