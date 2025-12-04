@extends('frontend.layout.main')

{{-- SEO --}}
@section('title', $meta_title)
@section('meta_keyword', $meta_keywords)
@section('meta_description', $meta_description)

@push('metas')
    @if($no_index)
        <meta name="robots" content="noindex"/>
    @endif
@endpush

@section('main')
<div class="bg-body-tertiary py-4">
	<div class="container-fluid">
		<div class="alert alert-primary">
			Tìm kiếm thông tin của một học sinh bằng mã học sinh
		</div>
		<form class="form-search">
    		<div class="shadow-sm border bg-white p-3 mb-3">
    			<div class="row">
    				<div class="form-group col-6 col-md-3">
    					<label class="mb-1">Tìm kiếm bằng mã học sinh</label>
    					<input type="text" name="keyword" class="form-control" value="<?php if(!empty($_GET['keyword'])){ echo $_GET['keyword']; }?>">
    				</div>
    				<div class="form-group col-6 col-md-2">
    					<label class="mb-1">Giáo phận</label>
    					<select class="form-select" name="giaophan" required="required">
    						<option></option>
    						@foreach($giaophan as $key => $item)
    							@if(!empty($_GET)) {
                                    @if($item['id'] == $_GET['giaophan'])
        								<option selected="selected" value="{{$item['id']}}">{{$item['name']}}</option>
            						@else
        								<option value="{{$item['id']}}">{{$item['name']}}</option>
        							@endif
    							@else
    								<option value="{{$item['id']}}">{{$item['name']}}</option>
                                @endif		
    						@endforeach
    					</select>
    				</div>
    				<div class="form-group col-6 col-md-2">
    					<label class="mb-1">Giáo hạt</label>
    					<select class="form-select" name="giaohat" required="required">
    						@foreach($giaohat as $key => $item)
    							@if($item['id'] == $_GET['giaohat'])
    								<option selected="selected" value="{{$item['id']}}">{{$item['name']}}</option>
    							@else
    								<option value="{{$item['id']}}">{{$item['name']}}</option>
    							@endif
    						@endforeach
    					</select>
    				</div>
    				<div class="form-group col-6 col-md-2">
    					<label class="mb-1">Giáo xứ</label>
    					<select class="form-select" name="giaoxu" required="required">
    						@foreach($giaoxu as $key => $item)
    							@if($item['id'] == $_GET['giaoxu'])
    								<option selected="selected" value="{{$item['id']}}">{{$item['name']}}</option>
    							@else
    								<option value="{{$item['id']}}">{{$item['name']}}</option>
    							@endif
    						@endforeach
    					</select>
    				</div>
    				<div class="group-form col-12 col-md-2">
    					<label class="mb-1">Tìm kiếm</label>
    					<button class="btn btn-primary w-100">Lọc thông tin</button>
    				</div>
				</div>
			</div>
		</form>
		@if(!empty($student))
		<div class="shadow border border-light-subtle bg-white rounded-3">	
			<div class="card">
				<div class="card-header border-light-subtle bg-white">
					<h3 class="card-title fs-6 fw-semibold py-2 mb-0">Kết quả tìm kiếm theo mã học sinh</h3>
				</div>
				<div class="card-body">
					<div class="table-responsive">
    					<table class="table table-vcenter text-nowrap mb-0 table-striped table-bordered border-top">
                          	<thead>
                            	<tr>
                                  	<th scope="col">#</th>
                                  	<th scope="col">Mã thiếu nhi</th>
                                  	<th scope="col">Tên thánh</th>
                                  	<th scope="col">Họ tên đệm</th>
                                  	<th scope="col">Tên</th>
                                  	<th scope="col">Ngày sinh</th>
                                  	<th scope="col">Họ tên cha</th>
                                  	<th scope="col">Họ tên mẹ</th>
                                  	<th scope="col">Địa chỉ</th>
                                  	<th scope="col">Đi học</th>
                                  	<th scope="col">Đi lễ</th>
                                  	<th scope="col">Kết quả</th>
                                </tr>
                          	</thead>
                          	<tbody>
                          		@foreach($student as $key => $hocsinh)
                              		<tr>
                                      	<th scope="row">{{$hocsinh->stt}}</th>
                                      	<td>{{$hocsinh->mahv}}</td>
                                      	<td>{{$hocsinh->holy}}</td>
                                      	<td>{{$hocsinh->last_name}}</td>
                                      	<td class="sticky-col second-col">{{$hocsinh->name}}</td>
                                      	<td>{{$hocsinh->birthday}}</td>
                                      	<td>{{$hocsinh->father}}</td>
                                      	<td>{{$hocsinh->mother}}</td>
                                      	<td>{{$hocsinh->pid}}</td>
                                      	<td><button type="button" class="btn btn-primary btn-dihoc" data-id="{{$hocsinh->id}}" data-lop="{{$hocsinh->lop}}">Đi học</button></td>
                                      	<td><button type="button" class="btn btn-primary btn-dile" data-id="{{$hocsinh->id}}" data-lop="{{$hocsinh->lop}}">Đi lễ</button></td>
                                      	<td><button type="button" class="btn btn-primary btn-kq" data-id="{{$hocsinh->id}}" data-lop="{{$hocsinh->lop}}">Kết quả</button></td>
                                    </tr>
                          		@endforeach
                          	</tbody>
                          	<tfoot>
                          		<tr>
                          			<td colspan="10">
                          				<div class="d-flex justify-content-center align-items-center py-2">
                          					@if(!empty($student))
                          						{!! $pagination !!}
                          					@endif
                          				</div>
                          			</td>
                          		</tr>
                          	</tfoot>
                        </table>
                    </div>
				</div>
			</div>
		</div>
		@else
			<div class="alert alert-danger" role="alert">
    			Không có kết quả
            </div>
		@endif
	</div>
</div>
<div id="sitemodal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title fs-5">&nbsp;</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <em class="fa fa-spinner fa-spin">&nbsp;</em>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($){
        $("select[name='giaophan']").change(function() {
            var $option = $(this).find('option:selected');
            var dioceses = $option.val();//to get content of "value" attrib
            var text = $option.text();//to get <option>Text</option> content
            $.ajax({
                url:"{{ route('search') }}",
                type:'POST',
                headers: {
                	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {dioceses:dioceses},
                beforeSend: function(){
                    $("select[name='giaohat'] option[value]").remove();
                    $("select[name='giaoxu'] option[value]").remove();
                    $("select[name='giaoho'] option[value]").remove();
                },
                success: function(data) {
            		$.each(data, function(key, value){
                        $("select[name='giaohat']").append(
                            "<option value=" + value.id + ">" + value.name + "</option>"
                        );
                    });
                }
            });
        });
        
        $("select[name='giaohat']").change(function() {
            var $option = $(this).find('option:selected');
            var deanerys = $option.val();//to get content of "value" attrib
            var text = $option.text();//to get <option>Text</option> content
            $.ajax({
                url:"{{ route('search') }}",
                type:'POST',
                headers: {
                	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {deanerys:deanerys},
                beforeSend: function(){
                    $("select[name='giaoxu'] option[value]").remove();
                    $("select[name='giaoho'] option[value]").remove();
                },
                success: function(data) {
            		$.each(data, function(key, value){
                        $("select[name='giaoxu']").append(
                            "<option value=" + value.id + ">" + value.name + "</option>"
                        );
                    });
                }
            });
        });
        
        $("select[name='giaoxu']").change(function() {
            var $option = $(this).find('option:selected');
            var parish_managements = $option.val();//to get content of "value" attrib
            var text = $option.text();//to get <option>Text</option> content
            $.ajax({
                url:"{{ route('search') }}",
                type:'POST',
                headers: {
                	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {parish_managements:parish_managements},
                beforeSend: function(){
                    $("select[name='giaoho'] option[value]").remove();
                },
                success: function(data) {
            		$.each(data, function(key, value){
                        $("select[name='giaoho']").append(
                            "<option value=" + value.id + ">" + value.name + "</option>"
                        );
                    });
                }
            });
        });
	});
</script>
<script type="text/javascript">
	$(document).ready(function() {
        $(".btn-dihoc").click(function(e){
            e.preventDefault();
            
            var id = $(this).attr("data-id");
            
            var lop = $(this).attr("data-lop");
            
            var scrollTop = false;
            
            $.ajax({
                url: "{{route('my-form-kqdihoc')}}",
                type:'POST',
                headers: {
                	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {id:id, lop:lop},
                beforeSend: function(){
                    //$(".loading").show();
                },
                complete: function(){
                    $(".loading").hide();
                    if (scrollTop) {
                        $("html,body").animate({
                            scrollTop: 0
                        }, 200, function() {
                            $("#sitemodal").modal('toggle');
                        });
                        $('#sitemodal').on('hide.bs.modal', function() {
                            $("html,body").animate({
                                scrollTop: scrollTop
                            }, 200);
                        });
                    } else {

                        $("#sitemodal").modal('toggle');
                    }
                    $('#sitemodal').on('hidden.bs.modal', function() {
                        $("#sitemodal .modal-content").find(".modal-header .modal-title").empty();
                        $("#sitemodal .modal-content").find(".modal-body").empty();
                        $("#sitemodal .modal-dialog").removeClass("modal-xl");
                        //location.reload();
                    });
                },
                success: function(response) {
                	$("#sitemodal .modal-dialog").addClass("modal-xl");
                	$("#sitemodal").find(".modal-title").html('Bảng thống kê đi học thiếu nhi : ' + response.holy + ' ' + response.last_name + ' ' + response.name);
                    var chtml = '';
                    chtml += '<p>Họ và tên: <strong>' + response.holy + ' ' + response.last_name + ' ' + response.name + '</strong></p>';
                    chtml += '<p>Mã thiếu nhi: <strong>' + response.mahv + '</strong></p>';
                    chtml += '<p>Năm sinh: <strong>' + response.birthday + '</strong></p>';
                    chtml += '<div class="table-responsive">';
                	chtml += '	<table class="table table-striped table-bordered table-hover">';
                	chtml += '		<caption>KỲ I</caption>';
                	chtml += '		<thead>';
                    chtml += '			<tr>';
                    chtml += 				response.tuanhk1;
                    chtml += '			</tr>';
                    chtml += '		</thead>';
                    chtml += '		<tbody>';
                    chtml += '			<tr>';
                    chtml += 				response.dihochk1;
                    chtml += '			</tr>';
                    chtml += '		</tbody>';
                	chtml += '	</table>';
                	chtml += '</div>';
                	chtml += '<div class="table-responsive">';
                	chtml += '	<table class="table table-striped table-bordered table-hover">';
                	chtml += '		<caption>KỲ II</caption>';
                	chtml += '		<thead>';
                    chtml += '			<tr>';
                    chtml += 				response.tuanhk2;
                    chtml += '			</tr>';
                    chtml += '		</thead>';
                    chtml += '		<tbody>';
                    chtml += '			<tr>';
                    chtml += 				response.dihochk2;
                    chtml += '			</tr>';
                    chtml += '		</tbody>';
                	chtml += '	</table>';
                	chtml += '</div>';
                    $("#sitemodal").find(".modal-body").html(chtml);
                }
            });
        });
    });
</script>
<script type="text/javascript">
	$(document).ready(function() {
        $(".btn-dile").click(function(e){
            e.preventDefault();
            
            var id = $(this).attr("data-id");
            
            var lop = $(this).attr("data-lop");
            
            var scrollTop = false;
            
            $.ajax({
                url: "{{route('my-form-kqdile')}}",
                type:'POST',
                headers: {
                	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {id:id, lop:lop},
                beforeSend: function(){
                    //$(".loading").show();
                },
                complete: function(){
                    $(".loading").hide();
                    if (scrollTop) {
                        $("html,body").animate({
                            scrollTop: 0
                        }, 200, function() {
                            $("#sitemodal").modal('toggle');
                        });
                        $('#sitemodal').on('hide.bs.modal', function() {
                            $("html,body").animate({
                                scrollTop: scrollTop
                            }, 200);
                        });
                    } else {

                        $("#sitemodal").modal('toggle');
                    }
                    $('#sitemodal').on('hidden.bs.modal', function() {
                        $("#sitemodal .modal-content").find(".modal-header .modal-title").empty();
                        $("#sitemodal .modal-content").find(".modal-body").empty();
                        $("#sitemodal .modal-dialog").removeClass("modal-xl");
                        //location.reload();
                    });
                },
                success: function(response) {
                	console.log(response);
                	$("#sitemodal .modal-dialog").addClass("modal-xl");
                	$("#sitemodal").find(".modal-title").html('Bảng thống kê đi lễ thiếu nhi : ' + response.holy + ' ' + response.last_name + ' ' + response.name);
                    var chtml = '';
                    chtml += '<p>Họ và tên: <strong>' + response.holy + ' ' + response.last_name + ' ' + response.name + '</strong></p>';
                    chtml += '<p>Mã thiếu nhi: <strong>' + response.mahv + '</strong></p>';
                    chtml += '<p>Năm sinh: <strong>' + response.birthday + '</strong></p>';
                    chtml += '<div class="table-responsive">';
                	chtml += '	<table class="table table-striped table-bordered table-hover">';
                	chtml += '		<caption>KỲ I</caption>';
                	chtml += '		<thead>';
                    chtml += '			<tr>';
                    chtml += 				response.tuanhk1;
                    chtml += '			</tr>';
                    chtml += '		</thead>';
                    chtml += '		<tbody>';
                    chtml += '			<tr>';
                    chtml += 				response.dilehk1;
                    chtml += '			</tr>';
                    chtml += '		</tbody>';
                	chtml += '	</table>';
                	chtml += '</div>';
                	chtml += '<div class="table-responsive">';
                	chtml += '	<table class="table table-striped table-bordered table-hover">';
                	chtml += '		<caption>KỲ II</caption>';
                	chtml += '		<thead>';
                    chtml += '			<tr>';
                    chtml += 				response.tuanhk2;
                    chtml += '			</tr>';
                    chtml += '		</thead>';
                    chtml += '		<tbody>';
                    chtml += '			<tr>';
                    chtml += 				response.dilehk2;
                    chtml += '			</tr>';
                    chtml += '		</tbody>';
                	chtml += '	</table>';
                	chtml += '</div>';
                    
                    $("#sitemodal").find(".modal-body").html(chtml);
                }
            });
        });
    });
</script>
<script type="text/javascript">    
	$(document).ready(function() {
        $(".btn-kq").click(function(e){
            e.preventDefault();
            
            var id = $(this).attr("data-id");
            
            var lop = $(this).attr("data-lop");
            
            var scrollTop = false;
            
            $.ajax({
                url: "{{route('my-form-kq')}}",
                type:'POST',
                headers: {
                	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {id:id, lop:lop},
                beforeSend: function(){
                    //$(".loading").show();
                },
                complete: function(){
                    $(".loading").hide();
                    if (scrollTop) {
                        $("html,body").animate({
                            scrollTop: 0
                        }, 200, function() {
                            $("#sitemodal").modal('toggle');
                        });
                        $('#sitemodal').on('hide.bs.modal', function() {
                            $("html,body").animate({
                                scrollTop: scrollTop
                            }, 200);
                        });
                    } else {

                        $("#sitemodal").modal('toggle');
                    }
                    $('#sitemodal').on('hidden.bs.modal', function() {
                        $("#sitemodal .modal-content").find(".modal-header .modal-title").empty();
                        $("#sitemodal .modal-content").find(".modal-body").empty();
                        $("#sitemodal .modal-dialog").removeClass("modal-xl");
                        //location.reload();
                    });
                },
                success: function(response) {
                	$("#sitemodal .modal-dialog").addClass("modal-xl");
                	$("#sitemodal").find(".modal-title").html('Bảng điểm thiếu nhi : ' + response.holy + ' ' + response.last_name + ' ' + response.name);
                    var chtml = '';

                    chtml += '<p>Họ và tên: <strong>' + response.holy + ' ' + response.last_name + ' ' + response.name + '</strong></p>';
                    chtml += '<p>Mã thiếu nhi: <strong>' + response.mahv + '</strong></p>';
                    chtml += '<p>Năm sinh: <strong>' + response.birthday + '</strong></p>';
                    chtml += '<p>Lưu ý: thang điểm 10</p>';
                    
                    chtml += '<div class="table-responsive">';
                	chtml += '	<table class="table table-striped table-bordered table-hover">';
                	chtml += '		<thead>';
                    chtml += '			<tr>';
                    chtml += '				<th colspan="4">KỲ I</th>';
                    chtml += '				<th colspan="4">KỲ II</th>';
                    chtml += '				<th rowspan="2" style="vertical-align: middle;">TB Năm</th>';
                    chtml += '				<th rowspan="2" style="vertical-align: middle;">Xếp loại</th>';
                    chtml += '				<th rowspan="2" style="vertical-align: middle;">Nghỉ lễ</th>';
                    chtml += '				<th rowspan="2" style="vertical-align: middle;">Bỏ học</th>';
                    chtml += '				<th rowspan="2" style="vertical-align: middle;">Hạnh kiểm</th>';
                    chtml += '				<th rowspan="2" style="vertical-align: middle;">Ghi chú</th>';
                    chtml += '			</tr>';
                    chtml += '			<tr>';
                    chtml += '				<th>8 T</th>';
                    chtml += '				<th>K I</th>';
                    chtml += '				<th>Kinh</th>';
                    chtml += '				<th>Xếp loại</th>';
                    chtml += '				<th>8 T</th>';
                    chtml += '				<th>K II</th>';
                    chtml += '				<th>Kinh</th>';
                    chtml += '				<th>Xếp loại</th>';
                    chtml += '			</tr>';
                    chtml += '		</thead>';
                    chtml += '		<tbody>';
                    chtml += '			<tr>';
                    chtml += '				<td>' + response.diem.tuan1 + '</td>';
                    chtml += '				<td>' + response.diem.k1 + '</td>';
                    chtml += '				<td>' + response.diem.kinh1 + '</td>';
                    chtml += '				<td>' + response.diem.kq1 + '</td>';
                    chtml += '				<td>' + response.diem.tuan2 + '</td>';
                    chtml += '				<td>' + response.diem.k2 + '</td>';
                    chtml += '				<td>' + response.diem.kinh2 + '</td>';
                    chtml += '				<td>' + response.diem.kq2 + '</td>';
                    chtml += '				<td>' + response.diem.canam + '</td>';
                    chtml += '				<td>' + response.diem.seploai + '</td>';
                    chtml += '				<td>' + response.diem.nghile + '</td>';
                    chtml += '				<td>' + response.diem.bohoc + '</td>';
                    chtml += '				<td>' + response.diem.hanhkiem + '</td>';
                    chtml += '				<td>' + response.diem.ghichu + '</td>';
                    chtml += '			</tr>';
                    chtml += '		</tbody>';
                	chtml += '	</table>';
                	chtml += '</div>';
                    
                    
                    chtml += '<div class="alert alert-success print-msg fs-8 mb-0" style="display:none">';
                    	chtml += '<p class="mb-0"></p>';
                    chtml += '</div>';
                    chtml += '<div class="alert alert-danger print-error-msg fs-8 mb-0" style="display:none">';
                    	chtml += '<ul class="mb-0"></ul>';
                    chtml += '</div>';
                    //chtml += "<div class=\"alert\"></div>";
                    
                    $("#sitemodal").find(".modal-body").html(chtml);
                
            		//console.log(data.success)
            		/*
                 	if($.isEmptyObject(response.error)){
                     	printMsg(response.success);
                 	}else{
                     	printErrorMsg(response.error);
                 	}*/
                }
            });
        });
    });
    
    $(document).ready(function() {
        var calculate = function() {
        	var tuan1 = $('input[name="tuan1"]').val();
        	var k1 = $('input[name="k1"]').val();
        	var kinh1 = $('input[name="kinh1"]').val();
        	
        	var tong1 = parseFloat(tuan1) + parseFloat(k1) + parseFloat(kinh1);
        	var tongphu1 = parseFloat(tong1)/3;
        	
        	if($.isNumeric( tongphu1 )){
        		var ketqua1 = '';
            	if(tongphu1 >= 9.5){
            		var ketqua1 = 'Xuất sắc';
            	}else if(tongphu1 >= 8 && tongphu1 < 9.5){
            		var ketqua1 = 'Giỏi';
            	}else if(tongphu1 >= 6.5 && tongphu1 < 8){
            		var ketqua1 = 'Khá';
            	}else if(tongphu1 >= 5 && tongphu1 < 6.5){
            		var ketqua1 = 'Trung bình';
            	}else if(tongphu1 >= 3.5 && tongphu1 < 5){
            		var ketqua1 = 'Yếu';
            	}else if(tongphu1 >= 0 && tongphu1 < 3.5){
            		var ketqua1 = 'Kém';
            	}
        		$('input[name="kq1"]').val(ketqua1);
        	}
        	
        	var tuan2 = $('input[name="tuan2"]').val();
        	var k2 = $('input[name="k2"]').val();
        	var kinh2 = $('input[name="kinh2"]').val();
        	
        	var tong2 = parseFloat(tuan2) + parseFloat(k2) + parseFloat(kinh2);
        	var tongphu2 = parseFloat(tong2)/3;
        	
        	if($.isNumeric( tongphu2 )){
        		var ketqua2 = '';
            	if(tongphu2 >= 9.5){
            		var ketqua2 = 'Xuất sắc';
            	}else if(tongphu2 >= 8 && tongphu2 < 9.5){
            		var ketqua2 = 'Giỏi';
            	}else if(tongphu2 >= 6.5 && tongphu2 < 8){
            		var ketqua2 = 'Khá';
            	}else if(tongphu2 >= 5 && tongphu2 < 6.5){
            		var ketqua2 = 'Trung bình';
            	}else if(tongphu2 >= 3.5 && tongphu2 < 5){
            		var ketqua2 = 'Yếu';
            	}else if(tongphu2 >= 0 && tongphu2 < 3.5){
            		var ketqua2 = 'Kém';
            	}
        		$('input[name="kq2"]').val(ketqua2);
        	}
        	
        	
        	var ghichu = $('input[name="ghichu"]').val();
        };
		$("body").on('keyup', '#sitemodal', calculate);
    });
    
    function DiemForm(a) {
    	var formData  = new FormData();    
        formData.append('ihv', $('input[name=ihv]').val());
        formData.append('lop', $('input[name=lop]').val());
        formData.append('tuan1', $('input[name=tuan1]').val());
        formData.append('k1', $('input[name=k1]').val()); 
        formData.append('kinh1', $('input[name=kinh1]').val()); 
        formData.append('kq1', $('input[name=kq1]').val());
        formData.append('tuan2', $('input[name=tuan2]').val()); 
        formData.append('k2', $('input[name=k2]').val()); 
        formData.append('kinh2', $('input[name=kinh2]').val()); 
        formData.append('kq2', $('input[name=kq2]').val()); 
        formData.append('ghichu', $('input[name=ghichu]').val()); 
        formData.append('nghile', $('input[name=nghile]').val()); 
        formData.append('bohoc', $('input[name=bohoc]').val()); 
        formData.append('hanhkiem', $('input[name=hanhkiem]').val()); 
        formData.append('canam', $('input[name=canam]').val()); 
        formData.append('seploai', $('input[name=seploai]').val()); 
        
        $(".has-error", a).removeClass("has-error");
        var c = 0;    
        c || ($(a).find("[type='submit']").prop("disabled", !0), $.ajax({
            type: $(a).prop("method"),
            cache: !1,
            url: "{{route('my-form-updatediem')}}",
            type:'POST',
            headers: {
            	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            dataType: "json",
            cache : false,
            processData: false,
            contentType: false,
            success: function(b) {
                if (b.redirect) {
                    // data.redirect contains the string URL to redirect to
                    window.location.href = b.redirect;
                }
                "error" == b.success && "" != b.input ? ($(".tooltip-current", a).removeClass("tooltip-current"), $(a).find("[name=" + b.input + "]").each(function() {
                    $(this).addClass("tooltip-current").attr("data-current-mess", b.success);
                    nv_validErrorShow(this)
                }), setTimeout(function() {
                    $(a).find("[type='submit']").prop("disabled", !1)
                }, 1E3), (change_captcha())) : ($("input,select,button,textarea", a).prop("disabled", !0), "error" == b.status ? $(a).next().html(b.success).removeClass("alert-info").addClass("alert-danger").show() : $(a).next().html(b.success).removeClass("alert-danger").addClass("alert-info").show(), $("[data-mess]").tooltip("destroy"), setTimeout(function() {
                    $(a).next().hide();
                    $("input,select,button,textarea", a).not(".disabled").prop("disabled", !1);
                    //$("button", a).not(".disabled").prop("disabled", !1);
                }, 5E3))
            }
        }));
        return !1
    };
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
</script>
@endsection