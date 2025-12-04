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
		<form class="form-search">
    		<div class="shadow-sm border bg-white p-3 mb-3">
    			<div class="row">
    				<div class="form-group col-6 col-md-2 mb-2 mb-md-0">
    					<label class="mb-1">Tìm kiếm</label>
    					<input type="text" name="keyword" class="form-control" value="<?php if(!empty($_GET['keyword'])){ echo $_GET['keyword']; }?>">
    				</div>
    				
    				@if(!empty($form))
    				<div class="form-group col-6 col-md-2">
    					<label class="mb-1">Giáo phận</label>
    					<select class="form-select" name="giaophan" required="required">
    						<option></option>
    						@foreach($giaophan as $key => $item)
    							@if(!empty($_GET['giaophan'])) {
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
    						<option></option>			
    						@foreach($giaohat as $key => $item)
    							@if(!empty($_GET['giaohat']))
        							@if($item['id'] == $_GET['giaohat'])
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
    					<label class="mb-1">Giáo xứ</label>
    					<select class="form-select" name="giaoxu" required="required">
    						<option></option>
    						@foreach($giaoxu as $key => $item)
    							@if(!empty($_GET['giaoxu']))
        							@if($item['id'] == $_GET['giaoxu'])
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
    				@endif
    				<div class="form-group col-6 col-md-2 mb-3">
    					<label class="mb-1">Giáo họ</label>
    					<select class="form-select" name="giaoho">
    						<option></option>
    						@if(!empty($giaohos))
        						@foreach($giaohos as $key => $item)
        							@if(!empty($_GET['giaoho']))
            							@if($item['id'] == $_GET['giaoho'])
            								<option selected="selected" value="{{$item['id']}}">{{$item['name']}}</option>
            							@else
            								<option value="{{$item['id']}}">{{$item['name']}}</option>
            							@endif
        							@else
        								<option value="{{$item['id']}}">{{$item['name']}}</option>
        							@endif
        						@endforeach
    						@endif
    					</select>
    				</div>
    				<div class="group-form col-6 col-md-2 mb-2 mb-md-0">
    					<div class="mb-3">
    						<label for="formFile" class="form-label mb-1">Chọn khóa học</label>
    						<select class="form-select" name="schoolyear" required="required">
    							<option></option>
    							@if(!empty($array_year))
    								@foreach($array_year as $year)
    									@if(!empty($_GET['schoolyear']))
    										@if($year['id'] == $_GET['schoolyear'])
    											<option value="{{$year['id']}}" selected="selected">{{$year['name']}}</option>
    										@else
    											<option value="{{$year['id']}}">{{$year['name']}}</option>
    										@endif
    									@else
    										
    										<option value="{{$year['id']}}">{{$year['name']}}</option>
    									@endif    									
    								@endforeach
    							@endif
    						</select>
    					</div>
    				</div>
    				@if(empty($form))
    				<div class="form-group col-6 col-md-2 mb-2 mb-md-0">
    					<label class="mb-1">Khối/Ngành</label>
    					<select class="form-select" name="block" required="required">
    						@if(!empty($block))
        						@foreach($block as $item)
            						@if(!empty($_GET['block']))
            							@if($item['id'] == $_GET['block'])
            								<option selected="selected" value="{{$item['id']}}">{{$item['name']}}</option>
                						@else
            								<option value="{{$item['id']}}">{{$item['name']}}</option>
            							@endif
            						@else
            							<option value="{{$item['id']}}">{{$item['name']}}</option>
            						@endif
        						@endforeach
    						@endif
						</select>
    				</div>
    				<div class="form-group col-6 col-md-2 mb-2 mb-md-0">
    					<label class="mb-1">Lớp/Chi đoàn</label>
    					<select class="form-select" name="lop" required="required">
                				@if(!empty($lop))
            						@forelse($lop as $item)
                						@if(!empty($_GET['lop']))
                							@if($item['id'] == $_GET['lop'])
                								<option selected="selected" value="{{$item['id']}}">{{$item['name']}}</option>
                    						@else
                								<option value="{{$item['id']}}">{{$item['name']}}</option>
                							@endif
                							
                						@else
                							<option value="{{$item['id']}}">{{$item['name']}}</option>
                						@endif	
            						@empty
                						<option></option>
            						@endforelse
                				@endif
						</select>
    				</div>
    				@endif
    				<div class="group-form col-6 col-md-2">
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
                                  	<th scope="col">Giáo họ</th>
                                  	<th scope="col">Mã QR</th>
                                  	<th scope="col">Tính điểm</th>
                                  	<th scope="col">Kết quả</th>
                                  	<th scope="col"></th>
                                </tr>
                          	</thead>
                          	<tbody>
                          		@foreach($student as $key => $item)
                              		<tr>
                                      	<th scope="row">{{$item->stt}}</th>
                                  		<td>{{$item->mahv}}</td>
                                  		<td>{{$item->holy}}</td>
                                  		<td>{{$item->last_name}}</td>
                                      	<td class="sticky-col second-col"><a class="text-decoration-none" title="{{$item->name}}" href="{{$item->slug}}">{{$item->name}}</a></td>
                                      	<td>{{$item->birthday}}</td>
                                      	<td>{{$item->father}}</td>
                                      	<td>{{$item->mother}}</td>
                                      	<td>{{$item->paid}}, {{$item->pid}}, {{$item->deid}}</td>
                                      	<td><button type="button" class="btn btn btn-info text-white btn-qr" data-id="{{$item->id}}" data-lop="{{$item->lop}}">Lấy mã QR</button></td>
                                      	<td><button type="button" class="btn btn-danger btn-ky" data-id="{{$item->id}}" data-lop="{{$item->lop}}">Điểm học kỳ</button></td>
                                      	<td><button type="button" class="btn btn-primary btn-kq" data-id="{{$item->id}}" data-lop="{{$item->lop}}">Kết quả</button></td>
                                      	<td><a title="Thư giới thiệu" href="{{$item->thugioithieu}}" class="text-decoration-none btn btn-warning">Thư giới thiệu</a></td>
                                      	<td><a title="Sửa" href="{{$item->edit}}" class="text-decoration-none btn btn-primary">Sửa</a></td>
                                    </tr>
                          		@endforeach
                          	</tbody>
                          	<tfoot>
                          		<tr>
                          			<td colspan="14">
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
		$("select[name='schoolyear']").change(function() {
            var $option = $(this).find('option:selected');
            var schoolyear = $option.val();//to get content of "value" attrib
            var text = $option.text();//to get <option>Text</option> content
            $.ajax({
                url:"{{ route('search') }}",
                type:'POST',
                headers: {
                	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {schoolyear:schoolyear},
                beforeSend: function(){
                    $("select[name='block'] option[value]").remove();
                    $("select[name='lop'] option[value]").remove();
                },
                success: function(data) {
            		$.each(data, function(key, value){
            			$("select[name='block']").append(
                            "<option value=" + value.id + ">" + value.name + "</option>"
                        );
                    });
                }
            });
        });
        
        $("select[name='block']").change(function() {
            var $option = $(this).find('option:selected');
            var block = $option.val();//to get content of "value" attrib
            var text = $option.text();//to get <option>Text</option> content
            $.ajax({
                url:"{{ route('search') }}",
                type:'POST',
                headers: {
                	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {block:block},
                beforeSend: function(){
                    $("select[name='lop'] option[value]").remove();
                },
                success: function(data) {
            		$.each(data, function(key, value){
                        $("select[name='lop']").append(
                            "<option value=" + value.id + ">" + value.name + "</option>"
                        );
                    });
                }
            });
        });
        
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
                    $("select[name='block'] option[value]").remove();
                },
                success: function(data) {
            		$.each(data, function(key, value){
                        $("select[name='giaoho']").append(
                            "<option value=" + value.id + ">" + value.name + "</option>"
                        );
                        $("select[name='block']").append(
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
        $(".btn-lop").click(function(e){
            e.preventDefault();
            var id = $(this).attr("data-id");
            var scrollTop = false;
            $.ajax({
                url: "{{route('my-form-qrlop')}}",
                type:'POST',
                headers: {
                	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {id:id},
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
                        //location.reload();
                    });
                },
                success: function(response) {
                	$("#sitemodal .modal-dialog").addClass("modal-xl");
                	$("#sitemodal").find(".modal-title").html('Mã QR : ' + response.lophoc.lop);
                    var chtml = '';
                    
                    chtml += '<p id="tenlop">Tên lớp: <strong id="hoten">' + response.lophoc.name + '</strong><br></p>';
                    chtml += '<p>Khối: <strong>' + response.lophoc.khoi + '</strong><br></p>';
                    chtml += '<p>Khóa: <strong>' + response.lophoc.schoolyear + '</strong><br></p>';
                    chtml += '<p>Ký hiệu: <strong>' + response.lophoc.symbol + '</strong><br></p>';                     
                    chtml += '<p>Tải file để in - <button class="btn btn-warning" onclick="return PrintLop(printableArea);">In mã QR</button></p>';
                    chtml += '<div id="printableArea" class="d-none">';
                    chtml += '<div class="row" style="display: flex; flex-wrap: wrap; margin-left: 0px; margin-right: 0px;">';
                    	$.each(response.thanhvien, function(key, value) {
                        	chtml += '<div class="col-6 mb-4" style="width: 46%; margin-bottom: 40px; padding-left: 12px; padding-right: 12px;">';
                        		chtml += '<div class="border border-black border-2" style="border: 2px solid #000;">';
                        			chtml += '<div class="p-3" style="padding: 1rem;">';
                        				chtml += '<div class="text-center" style="text-align: center">';
                        					chtml += '<p class="mb-0" style="margin-bottom: 5px; margin-top: 0;">' + value["pid"] + '</p>';
                        					chtml += '<strong style="margin-bottom:5px;">THẺ HỌC VIÊN</strong>';
                        					chtml += '<p class="mb-0" style="margin-top: 0; margin-bottom: 0;">------------------</p>';
                        					chtml += '<strong>' + value["name"] + '</strong>';
                        				chtml += '</div>';
                        				chtml += '<br/>';
                        				chtml += '<br/>';
                        				chtml += '<div class="row d-flex justify-content-between" style="justify-content: space-between; display: flex; flex-wrap: wrap; width: 100%">';
                        					chtml += '<div class="col-6" style="width: 50%;">';
                        						chtml += '<div class="text-start">';
                        							chtml += '<span>Lớp</span>';
                        							chtml += '<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;</span>';
                        							chtml += '<strong>' + value["lop"] + '</strong>';
                    							chtml += '</div>';
                    							chtml += '<div class="text-start">';
                        							chtml += '<span>Khối</span>';
                        							chtml += '<span>&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;</span>';
                        							chtml += '<strong>' + value["khoi"] + '</strong>';
                    							chtml += '</div>';
                    							chtml += '<div class="text-start">';
                        							chtml += '<span>Mã số</span>';
                        							chtml += '<span>&nbsp;:&nbsp;</span>';
                        							chtml += '<strong>' + value["mahv"] + '</strong>';
                        						chtml += '</div>';
                        					chtml += '</div>';
                        					chtml += '<div class="col-6 text-end">';
                            					chtml += '<div class="qrcode">';
                            						chtml += value["qr"];
                            					chtml += '</div>';
                        					chtml += '</div>';
                        				chtml += '</div>';
                        			chtml += '</div>';
                        		chtml += '</div>';
                        	chtml += '</div>';
                        });
                    chtml += '</div>';
                    chtml += '</div>';
                    
                    $("#sitemodal").find(".modal-body").html(chtml);
                }
            });
        });
    });
    
    function PrintLop(elem) {
        var mywindow = window.open('', 'PRINT', 'height=400,width=600');
        

        mywindow.document.write('<html><head><title>Tên lớp: ' + document.title  + '</title>');
        mywindow.document.write('</head><body >');
        mywindow.document.write(document.getElementById("printableArea").innerHTML);
        mywindow.document.write('</body></html>');
    
        mywindow.document.close(); // necessary for IE >= 10
        mywindow.focus(); // necessary for IE >= 10*/
    
        mywindow.print();
        mywindow.close();
    
        return true;
    }
    
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
        $(".btn-qr").click(function(e){
            e.preventDefault();
            
            var id = $(this).attr("data-id");
            
            var lop = $(this).attr("data-lop");
            
            var scrollTop = false;
            
            $.ajax({
                url: "{{route('my-form-qr')}}",
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
                        //location.reload();
                    });
                },
                success: function(response) {
                	$("#sitemodal").find(".modal-title").html('Mã QR : ' + response.holy + ' ' + response.last_name + ' ' + response.name);
                    var chtml = '';
                    
                    chtml += '<p id="fullname">Họ và tên: <strong id="hoten">' + response.holy + ' ' + response.last_name + ' ' + response.name + '</strong><br></p>';
                    chtml += '<p id="mahv">Mã thiếu nhi: <strong>' + response.mahv + '</strong><br></p>';
                    chtml += '<p id="namsinh">Năm sinh: <strong>' + response.birthday + '</strong><br></p>';                    
                    chtml += '<p>Mã QR - <button class="btn btn-warning" onclick="return PrintElem(printableArea);">In mã QR</button></p>';
                    chtml += '<div id="printableArea">';
                    chtml += '<p>' + response.qr + '</p>';
                    chtml += '</div>';
                    
                    chtml += '<div class="alert alert-success print-msg fs-8 mb-0" style="display:none">';
                    	chtml += '<p class="mb-0"></p>';
                    chtml += '</div>';
                    chtml += '<div class="alert alert-danger print-error-msg fs-8 mb-0" style="display:none">';
                    	chtml += '<ul class="mb-0"></ul>';
                    chtml += '</div>';
                    //chtml += "<div class=\"alert\"></div>";
                    
                    $("#sitemodal").find(".modal-body").html(chtml);
                }
            });
        });
    });
    
    function PrintElem(elem) {
    	console.log(elem);
        var mywindow = window.open('', 'PRINT', 'height=400,width=600');
        
        var fullname = $("#hoten").text();

        mywindow.document.write('<html><head><title>' + fullname + ' - ' + document.title  + '</title>');
        mywindow.document.write('</head><body >');
        mywindow.document.write('<h4 style="margin-bottom: 5px">Lớp: ' + document.title  + '</h4>');
        mywindow.document.write(document.getElementById("fullname").innerHTML);
        mywindow.document.write(document.getElementById("mahv").innerHTML);
        mywindow.document.write(document.getElementById("namsinh").innerHTML);
        mywindow.document.write(document.getElementById("printableArea").innerHTML);
        mywindow.document.write('</body></html>');
    
        mywindow.document.close(); // necessary for IE >= 10
        mywindow.focus(); // necessary for IE >= 10*/
    
        mywindow.print();
        mywindow.close();
    
        return true;
    }

	$(document).ready(function() {
        $(".btn-ky").click(function(e){
            e.preventDefault();
            
            var id = $(this).attr("data-id");
            
            var lop = $(this).attr("data-lop");
            
            var scrollTop = false;
            
            $.ajax({
                url: "{{route('my-form-diemthi')}}",
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
                    chtml += "<form method=\"post\" class=\"diemthi\" action=\"\" onsubmit=\"return DiemForm(this);\" novalidate enctype=\"multipart/form-data\">";
                    chtml += "<input type=\"hidden\" name=\"ihv\" value=\"" + response.id + "\"> ";
                    chtml += "<input type=\"hidden\" name=\"lop\" value=\"" + response.lop + "\"> ";  
                    
                    chtml += '<p>Họ và tên: <strong>' + response.holy + ' ' + response.last_name + ' ' + response.name + '</strong></p>';
                    chtml += '<p>Mã thiếu nhi: <strong>' + response.mahv + '</strong></p>';
                    chtml += '<p>Năm sinh: <strong>' + response.birthday + '</strong></p>';
                    chtml += '<p>Lưu ý: thang điểm 10</p>';
                    
                    chtml += "<div class=\"table-responsive\">";
                    chtml += "<table class=\"table table-striped table-bordered table-hover\">"; 
                    chtml += "<thead>";
                    chtml += "  <tr>";
                    chtml += "      <th colspan=\"4\">";
                    chtml += "          KỲ I";
                    chtml += "      </th>";
                    chtml += "      <th colspan=\"4\">";
                    chtml += "          KỲ II";
                    chtml += "      </th>";
                    chtml += "      <th rowspan=\"2\" style=\"vertical-align: middle;\">";
                    chtml += "          TB Năm";
                    chtml += "      </th>";
                    chtml += "      <th rowspan=\"2\" style=\"vertical-align: middle;\">";
                    chtml += "          Xếp loại";
                    chtml += "      </th>";
                    chtml += "  </tr>";
                    chtml += "  <tr>";
                    chtml += "      <th>";
                    chtml += "          8 T";
                    chtml += "      </th>";
                    chtml += "      <th>";
                    chtml += "          K I";
                    chtml += "      </th>";
                    chtml += "      <th>";
                    chtml += "          Kinh";
                    chtml += "      </th>";
                    chtml += "      <th>";
                    chtml += "          Xếp loại";
                    chtml += "      </th>";
                    chtml += "      <th>";
                    chtml += "          8 T";
                    chtml += "      </th>";
                    chtml += "      <th>";
                    chtml += "          K II";
                    chtml += "      </th>";
                    chtml += "      <th>";
                    chtml += "          Kinh";
                    chtml += "      </th>";
                    chtml += "      <th>";
                    chtml += "          Xếp loại";
                    chtml += "      </th>";
                    chtml += "  </tr>";
                    chtml += "</thead>";
                    chtml += "<tbody>";
                    chtml += "<tr>";
                    chtml += '<td><input type="number" name="tuan1" class="form-control" step="0.1" min="0" max="10" maxlength="2" value="' + response.diem.tuan1 + '" /></td>';
                    chtml += '<td><input type="number" name="k1" class="form-control" step="0.1" min="0" max="10" maxlength="2" value="' + response.diem.k1 + '"/></td>';
                    chtml += '<td><input type="number" name="kinh1" class="form-control" step="0.1" min="0" max="10" maxlength="2" value="' + response.diem.kinh1 + '"/></td>';
                    chtml += '<td style="width: 120px"><input type="text" name="kq1" class="form-control" step="0.1" min="0" max="10" maxlength="2" disabled value="' + response.diem.kq1 + '"/></td>';
                    
                    chtml += '<td><input type="number" name="tuan2" class="form-control" step="0.1" min="0" max="10" maxlength="2" value="' + response.diem.tuan2 + '"/></td>';
                    chtml += '<td><input type="number" name="k2" class="form-control" step="0.1" min="0" max="10" maxlength="2" value="' + response.diem.k2 + '"/></td>';
                    chtml += '<td><input type="number" name="kinh2" class="form-control" step="0.1" min="0" max="10" maxlength="2" value="' + response.diem.kinh2 + '"/></td>';
                    chtml += '<td style="width: 120px"><input type="text" name="kq2" class="form-control" step="0.1" min="0" max="10" maxlength="2" disabled value="' + response.diem.kq2 + '"/></td>';
                    chtml += '<td style="width: 120px"><input type="text" name="canam" class="form-control" disabled value="' + response.diem.canam + '"/></td>';
                    chtml += '<td style="width: 120px"><input type="text" name="seploai" class="form-control" disabled value="' + response.diem.seploai + '"/></td>';
                    chtml += "</tr>";
                    
                    chtml += "<tr>";
                    chtml += '<td colspan="2" style="vertical-align: middle;"><label>Nghỉ lễ</label></td>';
                    chtml += '<td colspan="5"><input type="text" name="nghile" class="form-control" disabled placeholder="Nghỉ lễ" value="' + response.nghile + '"/></td>';
                    chtml += '<td colspan="5"><input type="text" name="nghile" class="form-control" disabled placeholder="" value="' + response.nghile + '/' + response.ledu + '"/></td>';
                    chtml += "</tr>";
                    
                    chtml += "<tr>";
                    chtml += '<td colspan="2" style="vertical-align: middle;"><label>Bỏ học</label></td>';
                    chtml += '<td colspan="5"><input type="text" name="bohoc" class="form-control" disabled placeholder="Bỏ học" value="' + response.bohoc + '"/></td>';
                    chtml += '<td colspan="5"><input type="text" class="form-control" disabled placeholder="" value="' + response.bohoc + '/' + response.hocdu + '"/></td>';
                    chtml += "</tr>";
                    
                    chtml += "<tr>";
                    chtml += '<td colspan="2" style="vertical-align: middle;"><label>Hạnh kiểm</label></td>';
                    chtml += '<td colspan="10"><input type="text" name="hanhkiem" class="form-control" disabled placeholder="Ghi chú" value="' + response.hanhkiem + '"/></td>';
                    chtml += "</tr>";
                    
                    chtml += "<tr>";
                    chtml += '<td colspan="2" style="vertical-align: middle;"><label>Ghi chú</label></td>';
                    chtml += '<td colspan="10"><input type="text" name="ghichu" class="form-control" placeholder="Ghi chú" value="' + response.diem.ghichu + '"/></td>';
                    chtml += "</tr>";
                    
                    chtml += "</tbody>";
                    chtml += "<tfoot>";
                	chtml += '<tr><th colspan="10"><div class="form-group" id="submit"><input class="btn btn-primary" name="submit" id="luudiem" type="submit" value="Lưu điểm" /></div></th></tr>';
                  	chtml += "</tfoot>";
                    chtml += "</table>"; 
                    chtml += "</div>";
                    
                    chtml += "</form>";
                    
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
        /*
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
        */
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
    
    function change_captcha(a) {
        if ($('[data-toggle=recaptcha]').length) {
            "undefined" != typeof grecaptcha ? reCaptcha2OnLoad() : reCaptcha2ApiLoad()
        } else if ($("[data-recaptcha3]").length) {
            "undefined" != typeof grecaptcha ? reCaptcha3OnLoad() : reCaptcha3ApiLoad()
        }
    
        if ($("img.captchaImg").length) {
            $("img.captchaImg").attr("src", nv_base_siteurl + "index.php?scaptcha=captcha&nocache=" + nv_randomPassword(10));
            "undefined" != typeof a && "" != a && $(a).val("");
        }
        return !1
    }
    
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