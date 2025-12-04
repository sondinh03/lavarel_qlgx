<?php
use App\Models\DiLe;
?>
@extends('frontend.layout.main')

{{-- SEO --}}
@section('title', Str::title('Điểm danh đi lễ lớp' . $lop->name))
<meta name="robots" content="noindex"/>

@section('main')
<div class="container-fluid">
    <div class="alert alert-success">
        <p class="mb-1"><span>Bạn cần tích chọn đi lễ hoặc vắng ở form bên dưới để tổng hợp ra số ngày đi lễ và không đi lễ một cách chính xác nhất</span></p>
    </div>
    <div class="shadow bg-white mb-4 border rounded-4">
    	<div class="card border-0">
    		<div class="card-header bg-white">
    			<div class="card-title fw-semibold py-1 fs-5">
    				DANH ĐI LỄ HỌC KỲ I - {{$lop->schoolyear}} - {{$lop->name}}
    			</div>
    		</div>
    		<div class="card-body">
    			<form action="" method="post" id="ky1">
    				<input type="hidden" name="ky1" value="1">
					<input type="hidden" name="id" value="{{$lop->id}}">
					<div class="table-responsive">
                        <div class="alert alert-success print-msg fs-8 mb-0" style="display:none">
                        	<p class="mb-0"></p>
                        </div>
                        <div class="alert alert-danger print-error-msg fs-8 mb-0" style="display:none">
                        	<ul class="mb-0"></ul>
                        </div>
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
                              	@php
                          			$checked_on = $checked_zo = '';
                          		@endphp
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
                                      		@php
                                          		$thang = $dt->format('n');
                                          		$ngay = $dt->format('j');
                                      		    $dile = DiLe::where('idh', $hocsinh->id)->where('lophoc', $hocsinh->lop)->where('hocky', 1)->where('thang', $thang)->where('ngay', $ngay)->where('status', 1)->orderBy('created_at', 'asc')->get()->first();
                                      		    if(!empty($dile)){
                                      		    	$giatri = $dile->dile;
                                      		    	if($dile->dile == 1){
                                              		    $checked_on = 'checked="checked"';
                                              		    $checked_zo = '';
                                              		    $checked_cp = '';
                                              		}elseif($dile->dile == 3){
                                              			$checked_on = '';
                                              		    $checked_zo = '';
                                              			$checked_cp = 'checked="checked"';
                                              		}else{
                                              			$checked_on = '';
                                              		    $checked_zo = 'checked="checked"';
                                              		    $checked_cp = '';
                                              		}
                                          		}else{
                                      		    	$checked_on = $checked_zo = $checked_cp = '';
                                      		    }                                  		    
                                      		@endphp
                                          	<td>
                                          		<div class="form-check form-check-inline">
                                                  	<input class="form-check-input" {{$checked_zo}} type="radio" name="dile[{{$hocsinh->id}}][{{$dt->format('n')}}][{{$dt->format('j')}}]" value="2">
                                                  	<label class="form-check-label pt-1 fs-8" for="radioDefault1">Vắng</label>
                                                </div>
                                                <div class="form-check form-check-inline me-0">
                                                  	<input class="form-check-input" {{$checked_on}} type="radio" name="dile[{{$hocsinh->id}}][{{$dt->format('n')}}][{{$dt->format('j')}}]" value="1">
                                                  	<label class="form-check-label pt-1 fs-8">Đi lễ</label>
                                                </div>
                                                <div class="form-check form-check-inline me-0">
                                                  	<input class="form-check-input" {{$checked_cp}} type="radio" name="dile[{{$hocsinh->id}}][{{$dt->format('n')}}][{{$dt->format('j')}}]" value="3">
                                                  	<label class="form-check-label pt-1 fs-8">CP</label>
                                                </div>
                                          	</td>
                                  		@endif
                                  	@endforeach
                                </tr>
                          		@endforeach
                          	</tbody>
                          	<tfoot>
                                <tr>
                                    <td colspan="2" class="sticky-col second-col">
                                        <input type="submit" name="submit" class="btn btn-info text-white btn-submit1" value="Cập nhật">
                                    </td>
                                    <td colspan="11">
                          				<div class="d-flex justify-content-end align-items-center py-2">
                          					{!! $pagination !!}
                          				</div>
                          			</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </form>
    		</div>
		</div>
	</div>
	<div class="shadow bg-white mb-4 border rounded-4">
    	<div class="card border-0">
    		<div class="card-header bg-white">
    			<div class="card-title fw-semibold py-1 fs-5">
    				DANH ĐI LỄ HỌC KỲ II - {{$lop->schoolyear}} - {{$lop->name}}
    			</div>
    		</div>
    		<div class="card-body">
    			<form action="" method="post" class="table-responsive" id="ky2">
    				<input type="hidden" name="ky2" value="2">
					<input type="hidden" name="id" value="{{$lop->id}}">
					<div class="table-responsive">
                        <div class="alert alert-success print-msg-bottom fs-8 mb-0" style="display:none">
                        	<p class="mb-0"></p>
                        </div>
                        <div class="alert alert-danger print-error-msg-bottom fs-8 mb-0" style="display:none">
                        	<ul class="mb-0"></ul>
                        </div>
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
                                      		@php
                                          		$thang = $dt->format('n');
                                          		$ngay = $dt->format('j');
                                      		    $dile = DiLe::where('idh', $hocsinh->id)->where('lophoc', $hocsinh->lop)->where('hocky', 2)->where('thang', $thang)->where('ngay', $ngay)->where('status', 1)->orderBy('created_at', 'asc')->get()->first();
                                      		    if(!empty($dile)){
                                      		    	$giatri = $dile->dile;
                                      		    	if($dile->dile == 1){
                                              		    $checked_on = 'checked="checked"';
                                              		    $checked_zo = '';
                                              		    $checked_cp = '';
                                          		    }elseif($dile->dile == 3){
                                          		    	$checked_on = '';
                                              		    $checked_zo = '';
                                              		    $checked_cp = 'checked="checked"';
                                              		}else{
                                              			$checked_on = '';
                                              		    $checked_zo = 'checked="checked"';
                                              		    $checked_cp = '';
                                              		}
                                          		}else{
                                      		    	$checked_on = $checked_zo = $checked_cp = '';
                                      		    }                                  		    
                                      		@endphp
                                          	<td>
                                          		<div class="form-check form-check-inline">
                                                  	<input class="form-check-input" {{$checked_zo}} type="radio" name="dile[{{$hocsinh->id}}][{{$dt->format('n')}}][{{$dt->format('j')}}]" value="2">
                                                  	<label class="form-check-label pt-1 fs-8" for="radioDefault1">Vắng</label>
                                                </div>
                                                <div class="form-check form-check-inline me-0">
                                                  	<input class="form-check-input" {{$checked_on}} type="radio" name="dile[{{$hocsinh->id}}][{{$dt->format('n')}}][{{$dt->format('j')}}]" value="1">
                                                  	<label class="form-check-label pt-1 fs-8">Đi lễ</label>
                                                </div>
                                                <div class="form-check form-check-inline me-0">
                                                  	<input class="form-check-input" {{$checked_cp}} type="radio" name="dile[{{$hocsinh->id}}][{{$dt->format('n')}}][{{$dt->format('j')}}]" value="3">
                                                  	<label class="form-check-label pt-1 fs-8">CP</label>
                                                </div>
                                          	</td>
                                  		@endif
                                  	@endforeach
                                </tr>
                          		@endforeach
                          	</tbody>
                          	<tfoot>
                                <tr>
                                    <td colspan="2" class="sticky-col second-col">
                                        <input type="submit" name="submit" class="btn btn-info text-white btn-submit2" value="Cập nhật">
                                    </td>
                                    <td colspan="11">
                          				<div class="d-flex justify-content-end align-items-center py-2">
                          					{!! $pagination !!}
                          				</div>
                          			</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </form>
    		</div>
		</div>
	</div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $(".btn-submit1").click(function(e){
            e.preventDefault();
            
            var ky1 = $("input[name='ky1']").val();
            var id = $("#ky1 input[name='id']").val();
            
            var idhv = $('#ky1 input[name="idhv[]"]').map(function (idx, ele) {
            	return $(ele).val().trim().length == 0 ? 0 : parseFloat($(ele).val().trim());
        	}).get();
        	
        	var dihoc = $('#ky1 input[name="dihoc[]"]').map(function (idx, ele) {
            	return $(ele).val().trim().length == 0 ? 0 : parseFloat($(ele).val().trim());
        	}).get();
            
            $.ajax({
                url: "{{route('my-form-dilehk1')}}",
                type:'POST',
                headers: {
                	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {ky1:ky1, id:id, datamoi:$('#ky1').serialize()},
                success: function(data) {
            		//console.log(data.success)
                 	if($.isEmptyObject(data.error)){
                     	printMsg(data.success);
                 	}else{
                     	printErrorMsg(data.error);
                 	}
                }
            });
        });
        function printMsg (msg) {
            $(".print-msg").find("p").html('');
            $(".print-msg").css('display','block');
            $(".print-msg").find("p").append('<span>'+msg+'</span>');
            $(".print-msg").delay(5000).hide(0);
        }
        function printErrorMsg (msg) {
            $(".print-error-msg").find("ul").html('');
            $(".print-error-msg").css('display','block');
            $.each( msg, function( key, value ) {
                $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
            });
            $(".print-error-msg").delay(5000).hide(0);
        }
    });
    
    $(document).ready(function() {
        $(".btn-submit2").click(function(e){
            e.preventDefault();
            
            var ky2 = $("input[name='ky2']").val();
            var id = $("#ky2 input[name='id']").val();
            
            var idhv = $('#ky2 input[name="idhv[]"]').map(function (idx, ele) {
            	return $(ele).val().trim().length == 0 ? 0 : parseFloat($(ele).val().trim());
        	}).get();
        	
        	var dihoc = $('#ky2 input[name="dihoc[]"]').map(function (idx, ele) {
            	return $(ele).val().trim().length == 0 ? 0 : parseFloat($(ele).val().trim());
        	}).get();
            
            $.ajax({
                url: "{{route('my-form-dilehk2')}}",
                type:'POST',
                headers: {
                	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {ky2:ky2, id:id, datamoi:$('#ky2').serialize()},
                success: function(data) {
            		//console.log(data.success)
                 	if($.isEmptyObject(data.error)){
                     	printMsg(data.success);
                 	}else{
                     	printErrorMsg(data.error);
                 	}
                }
            });
        });
        function printMsg (msg) {
            $(".print-msg-bottom").find("p").html('');
            $(".print-msg-bottom").css('display','block');
            $(".print-msg-bottom").find("p").append('<span>'+msg+'</span>');
            $(".print-msg-bottom").delay(5000).hide(0);
        }
        function printErrorMsg (msg) {
            $(".print-error-msg-bottom").find("ul").html('');
            $(".print-error-msg-bottom").css('display','block');
            $.each( msg, function( key, value ) {
                $(".print-error-msg-bottom").find("ul").append('<li>'+value+'</li>');
            });
            $(".print-error-msg-bottom").delay(5000).hide(0);
        }
    });
</script>
@endsection