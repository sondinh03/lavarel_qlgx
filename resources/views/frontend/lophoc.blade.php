<?php
use App\Models\DiHoc;
?>
@extends('frontend.layout.main')

{{-- SEO --}}
@section('title', Str::title('Điểm danh đi học lớp ' . $lop->name))
<meta name="robots" content="noindex"/>

@section('main')
<div class="container-fluid">
    <div class="alert alert-success">
        <p class="mb-1"><span>Lưu ý các thông số sau khi bạn thực hiện Điểm danh đi học</span></p>
        <ul class="mb-0">
            <li>Đi học: +</li>
            <li>Vắng có phép: -</li>
            <li>Vắng: 0</li>
        </ul>
    </div>
    <div class="shadow bg-white mb-4 border rounded-4">
    	<div class="card border-0">
    		<div class="card-header bg-white">
    			<div class="card-title fw-semibold py-1 fs-5">
    				Điểm danh đi học kỳ I - {{$lop->schoolyear}} - {{$lop->name}}
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
                                  	@for ($i = 1; $i <= $weeks_ky1; $i++)
                                        <th scope="col" class="text-center">Tuần {{ $i }}</th>
                                    @endfor
                                </tr>
                          	</thead>
                          	<tbody class="fs-6">
                              	@php
                          			$checked_on = $checked_to = $checked_zo = '';
                          		@endphp
                          		@foreach($student as $key => $hocsinh)
                          		<tr data-id="{{$hocsinh->id}}" id="chuoi_{{$hocsinh->id}}">
                                  	<th scope="row">{{$hocsinh->stt}}</th>
                                  	<td>{{$hocsinh->mahv}}</td>
                                  	<td>{{$hocsinh->holy}}</td>
                                  	<td>{{$hocsinh->last_name}}</td>
                                  	<td class="sticky-col second-col">{{$hocsinh->name}}</td>
                                  	<td>{{$hocsinh->birthday}}</td>
                                  	@for ($i = 1; $i <= $weeks_ky1; $i++)
                                  		<?php
                                  		$hocsinh1 = DiHoc::where('idh', $hocsinh->id)->where('lophoc', $hocsinh->lop)->where('tuan', $i)->where('hocky', 1)->where('status', 1)->orderBy('created_at', 'asc')->get()->first();
                                  		$checked_on = $checked_to = $checked_zo = '';
                                  		if(!empty($hocsinh1)){
                                  		    if($hocsinh1->dihoc == 1){
                                  		        $checked_on = 'checked="checked"';
                                  		    }elseif($hocsinh1->dihoc == 2){
                                  		        $checked_to = 'checked="checked"';
                                  		    }elseif($hocsinh1->dihoc == 0){
                                  		        $checked_zo = 'checked="checked"';
                                  		    }
                                  		}
                                  		?>
                                  		<td>
                                  			<input type="hidden" name="idhv[]" value="{{$hocsinh->id}}">
                                  			<div class="text-center">
                                  				<div class="form-check form-check-inline" id="dihoc_content_{{$i}}_{{$hocsinh->id}}">
                                  					<input {{$checked_on}} class="form-check-input" type="radio" name="dihoc[{{$hocsinh->id}}][{{$i}}]" required="" id="dihoc_{{$i}}_{{$hocsinh->id}}" value="1">
                                  					<label class="form-check-label" for="dihoc_{{$i}}_{{$hocsinh->id}}">+</label>
                              					</div>
                              					<div class="form-check form-check-inline" id="dihoc_content_{{$i}}_{{$hocsinh->id}}">
                              						<input {{$checked_to}} class="form-check-input" type="radio" name="dihoc[{{$hocsinh->id}}][{{$i}}]" required="" id="dihoc_{{$i}}_{{$hocsinh->id}}" value="2">
                              						<label class="form-check-label" for="dihoc_{{$i}}_{{$hocsinh->id}}">-</label>
                          						</div>
                      							<div class="form-check form-check-inline" id="dihoc_content_{{$i}}_{{$hocsinh->id}}">
                                  					<input {{$checked_zo}} class="form-check-input" type="radio" name="dihoc[{{$hocsinh->id}}][{{$i}}]" required="" id="dihoc_{{$i}}_{{$hocsinh->id}}" value="0">
                                  					<label class="form-check-label" for="dihoc_{{$i}}_{{$hocsinh->id}}">0</label>
                              					</div>
                          					</div>
                                  		</td>
                                  	@endfor
                                </tr>
                          		@endforeach
                          	</tbody>
                          	<tfoot>
                                <tr>
                                    <td colspan="2" class="sticky-col second-col">
                                        <input type="submit" name="submit" class="btn btn-info text-white btn-submit1" value="Cập nhật">
                                    </td>
                                    <td colspan="2">
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
    				Điểm danh đi học kỳ II - {{$lop->schoolyear}} - {{$lop->name}}
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
                                  	@for ($i = 1; $i <= $weeks_ky2; $i++)
                                        <th scope="col" class="text-center">Tuần {{ $i }}</th>
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
                                  	@for ($i = 1; $i <= $weeks_ky2; $i++)
                                  		<?php
                                  		$hocsinh2 = DiHoc::where('idh', $hocsinh->id)->where('lophoc', $hocsinh->lop)->where('tuan', $i)->where('hocky', 2)->where('status', 1)->orderBy('created_at', 'asc')->get()->first();
                                  		$checked_on = $checked_to = $checked_zo = '';
                                  		if(!empty($hocsinh2)){
                                  		    if($hocsinh2->dihoc == 1){
                                  		        $checked_on = 'checked="checked"';
                                  		    }elseif($hocsinh2->dihoc == 2){
                                  		        $checked_to = 'checked="checked"';
                                  		    }elseif($hocsinh2->dihoc == 0){
                                  		        $checked_zo = 'checked="checked"';
                                  		    }
                                  		}
                                  		?>
                                  		<td>
                                  			<input type="hidden" name="idhv[]" value="{{$hocsinh->id}}">
                                  			<div class="text-center">
                                  				<div class="form-check form-check-inline" id="dihoc_content_{{$i}}_{{$hocsinh->id}}">
                                  					<input {{$checked_on}} class="form-check-input" type="radio" name="dihoc[{{$hocsinh->id}}][{{$i}}]" required="" id="dihoc_{{$i}}_{{$hocsinh->id}}" value="1">
                                  					<label class="form-check-label" for="dihoc_{{$i}}_{{$hocsinh->id}}">+</label>
                              					</div>
                              					<div class="form-check form-check-inline" id="dihoc_content_{{$i}}_{{$hocsinh->id}}">
                              						<input {{$checked_to}} class="form-check-input" type="radio" name="dihoc[{{$hocsinh->id}}][{{$i}}]" required="" id="dihoc_{{$i}}_{{$hocsinh->id}}" value="2">
                              						<label class="form-check-label" for="dihoc_{{$i}}_{{$hocsinh->id}}">-</label>
                          						</div>
                      							<div class="form-check form-check-inline" id="dihoc_content_{{$i}}_{{$hocsinh->id}}">
                                  					<input {{$checked_zo}} class="form-check-input" type="radio" name="dihoc[{{$hocsinh->id}}][{{$i}}]" required="" id="dihoc_{{$i}}_{{$hocsinh->id}}" value="0">
                                  					<label class="form-check-label" for="dihoc_{{$i}}_{{$hocsinh->id}}">0</label>
                              					</div>
                          					</div>
                                  		</td>
                                  	@endfor
                                </tr>
                          		@endforeach
                          	</tbody>
                          	<tfoot>
                                <tr>
                                    <td colspan="2" class="sticky-col second-col">
                                        <input type="submit" name="submit" class="btn btn-info text-white btn-submit2" value="Cập nhật">
                                    </td>
                                    <td colspan="2">
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
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
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
                url: "{{route('my-form')}}",
                type:'POST',
                headers: {
                	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {ky1:ky1, id:id, idhv:idhv, datamoi:$('#ky1').serialize()},
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
                url: "{{route('my-formhk2')}}",
                type:'POST',
                headers: {
                	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {ky2:ky2, id:id, idhv:idhv, datamoi:$('#ky2').serialize()},
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