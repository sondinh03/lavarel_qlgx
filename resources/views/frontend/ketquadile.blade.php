<?php
use App\Models\DiLe;
?>
@extends('frontend.layout.main')

{{-- SEO --}}
@section('title', Str::title('Kết quả điểm danh đi lễ lớp ' . $lop->name))
<meta name="robots" content="noindex"/>

@section('main')
<div class="container-fluid">
	<div class="shadow bg-white mb-4 border rounded-4">
    	<div class="card border-0">
    		<div class="card-header bg-white">
    			<div class="card-title fw-semibold py-1 fs-5">
    				Kết quả điểm danh đi lễ kỳ 1 - {{$lop->schoolyear}} - {{$lop->name}}
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
                              	@foreach($period as $dt)
                              		<?php
                              		$date_one = $dt->format("l");
                              		$date_time = $dt->format("d/m/Y");
                              		if($date_one == 'Thursday' OR $date_one == 'Sunday'){
                              		    //print_r($dt);die;
                              		    if($date_one == 'Thursday'){
                              		        $date_one_vi = 'Thứ 5';
                              		    }else{
                              		        $date_one_vi = 'Chủ nhật';
                          		        }
                              		    ?>
                              		    <th class="text-center">{{$date_one_vi}}<br>{{$date_time}}</th>
                              		    <?php 
                              		}
                              		?>
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
                              	@foreach($period as $key => $dt)
                              		@php
                                  		$date_one = $dt->format("l");
                                  		$date_time = $dt->format("d/m/Y");
                              		@endphp
                              		@if($date_one == 'Thursday' OR $date_one == 'Sunday')
                              			<?php
                              			$thang = $dt->format('n');
                              			$ngay = $dt->format('j');
                              			$dile = DiLe::where('idh', $hocsinh->id)->where('lophoc', $hocsinh->lop)->where('hocky', 1)->where('thang', $thang)->where('ngay', $ngay)->where('status', 1)->orderby('ngay', 'asc')->get()->first();
                              			?>
                                  		<td>
                                  		@if(!empty($dile->dile))
                              				@if($dile->dile == 1)
                              					Đi lễ
                              				@elseif($dile->dile == 3)
                              					CP 
                              				@else
                              					Vắng
                              				@endif
                              			@else
                              				--
                                  		@endif
                                  		</td>
                          			@endif
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
    				Kết quả điểm danh đi lễ kỳ 2 - {{$lop->schoolyear}} - {{$lop->name}}
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
                              	@foreach($period_hk2 as $dt)
                              		<?php
                              		$date_one = $dt->format("l");
                              		$date_time = $dt->format("d/m/Y");
                              		if($date_one == 'Thursday' OR $date_one == 'Sunday'){
                              		    //print_r($dt);die;
                              		    if($date_one == 'Thursday'){
                              		        $date_one_vi = 'Thứ 5';
                              		    }else{
                              		        $date_one_vi = 'Chủ nhật';
                          		        }
                              		    ?>
                              		    <th class="text-center">{{$date_one_vi}}<br>{{$date_time}}</th>
                              		    <?php 
                              		}
                              		?>
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
                              	@foreach($period_hk2 as $key => $dt)
                              		@php
                                  		$date_one = $dt->format("l");
                                  		$date_time = $dt->format("d/m/Y");
                              		@endphp
                              		@if($date_one == 'Thursday' OR $date_one == 'Sunday')
                              			<?php
                              			$thang = $dt->format('n');
                              			$ngay = $dt->format('j');
                              			$dile = DiLe::where('idh', $hocsinh->id)->where('lophoc', $hocsinh->lop)->where('hocky', 2)->where('thang', $thang)->where('ngay', $ngay)->where('status', 1)->orderby('ngay', 'asc')->get()->first();
                              			?>
                              			<td>
                              			@if(!empty($dile->dile))
                              				@if($dile->dile == 1)
                              					Đi lễ
                          					@elseif($dile->dile == 3)
                              					CP 
                              				@else
                              					Vắng
                              				@endif
                              			@else
                              				--
                                  		@endif
                                  		</td>
                          			@endif
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