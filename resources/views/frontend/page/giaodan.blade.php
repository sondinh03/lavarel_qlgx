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
    				<div class="form-group col-6 col-md-2 mb-3">
    					<label class="mb-1">Từ khóa tìm kiếm</label>
    					<input type="text" name="keyword" class="form-control" value="<?php if(!empty($_GET['keyword'])){ echo $_GET['keyword']; }?>">
    				</div>
    				@if(!empty($form))
    				<div class="form-group col-6 col-md-2">
    					<label class="mb-1">Giáo phận</label>
    					<select class="form-control" name="giaophan">
    						<option>-----</option>
    						@forelse($giaophan as $key => $item)
    							@if(!empty($_GET['giaophan'])) {
                                    @if($item['id'] == $_GET['giaophan'])
        								<option selected="selected" value="{{$item['id']}}">{{$item['name']}}</option>
            						@else
        								<option value="{{$item['id']}}">{{$item['name']}}</option>
        							@endif
    							@else
    								<option value="{{$item['id']}}">{{$item['name']}}</option>
                                @endif		
    						@empty
    							<option>-- Không có --</option>
    						@endforelse
    					</select>
    				</div>
    				<div class="form-group col-6 col-md-2">
    					<label class="mb-1">Giáo hạt</label>
    					<select class="form-control" name="giaohat">
    						<option>-----</option>			
    						@forelse($giaohat as $key => $item)
    							@if(!empty($_GET['giaohat'])) {
        							@if($item['id'] == $_GET['giaohat'])
        								<option selected="selected" value="{{$item['id']}}">{{$item['name']}}</option>
        							@else
        								<option value="{{$item['id']}}">{{$item['name']}}</option>
        							@endif
    							@else
    								<option value="{{$item['id']}}">{{$item['name']}}</option>
    							@endif
    						@empty
    							<option>-- Không có --</option>
    						@endforelse		
    					</select>
    				</div>
    				<div class="form-group col-6 col-md-2">
    					<label class="mb-1">Giáo xứ</label>
    					<select class="form-control" name="giaoxu">
    						<option>-----</option>
    						@forelse($giaoxu as $key => $item)
    							@if(!empty($_GET['giaoxu']))
        							@if($item['id'] == $_GET['giaoxu'])
        								<option selected="selected" value="{{$item['id']}}">{{$item['name']}}</option>
        							@else
        								<option value="{{$item['id']}}">{{$item['name']}}</option>
        							@endif
    							@else
    								<option value="{{$item['id']}}">{{$item['name']}}</option>
    							@endif
    						@empty
    							<option>-- Không có --</option>
    						@endforelse
    					</select>
    				</div>    				
    				@endif
    				<div class="form-group col-6 col-md-2 mb-3">
    					<label class="mb-1">Giáo họ</label>
    					<select class="form-control" name="giaoho">
    						<option>-----</option>
    						@foreach($giaoho as $key => $item)
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
    					</select>
    				</div>
    				<div class="group-form col-12 col-md-2">
    					<label class="mb-1">Tìm kiếm</label>
    					<button class="btn btn-primary w-100">Lọc thông tin</button>
    				</div>
    			</div>
    		</div>
		</form>
		@if(!empty($parishioners))
		<div class="shadow border border-light-subtle bg-white rounded-3">	
			<div class="card">
				<div class="card-header border-light-subtle bg-white">
					<h3 class="card-title fs-6 fw-semibold py-2 mb-0">Danh sách giáo dân</h3>
				</div>
				<div class="card-body">
					<div class="table-responsive">
    					<table class="table table-vcenter text-nowrap mb-0 table-striped table-bordered border-top">
                          	<thead>
                            	<tr>
                                  	<th scope="col" class="fw-medium">#</th>
                                  	<th scope="col" class="fw-medium">Tên Thánh</th>
                                  	<th scope="col" class="fw-medium">Họ tên đệm</th>
                                  	<th scope="col" class="fw-medium">Tên</th>
                                  	<th scope="col" class="fw-medium">Giáo họ</th>
                                  	<th scope="col" class="fw-medium">Giới tính</th>
                                  	<th scope="col" class="fw-medium">Ngày sinh</th>
                                  	<th scope="col" class="fw-medium">Địa chỉ</th>
                                  	<th scope="col" class="fw-medium"></th>
                                </tr>
                          	</thead>
                          	<tbody>	                    		
                          		@forelse($parishioners as $key => $item)
                          			<tr>
                                      	<th scope="row">{{$item->stt}}</th>
                                      	<td class="border-top-0 border-bottom-0">{{$item->holy}}</td>
                                      	<td class="border-top-0 border-bottom-0">{{$item->last_name}}</td>
                                      	<td class="border-top-0 border-bottom-0 sticky-col second-col"><a class="text-decoration-none" href="{{$item->slug}}" title="{{$item->holy}}&nbsp;{{$item->name}}">{{$item->name}}</a></td>
                                      	<td class="border-top-0 border-bottom-0">{{$item->paid}}, {{$item->pid}}, {{$item->deid}}, {{$item->did}}</td>
                                      	<td class="border-top-0 border-bottom-0">{{$item->sex}}</td>
                                      	<td class="border-top-0 border-bottom-0">{{$item->birthday}}</td>
                                      	<td class="border-top-0 border-bottom-0">{{$item->residence}}, {{$item->resi_ward}}, {{$item->resi_province}}</td>
                                      	<td><a title="Sửa" href="{{$item->edit}}" class="text-decoration-none btn btn-primary">Sửa</a></td>
                                    </tr>
                                @empty
                                	<tr>
                                		<td colspan="9">
                                            <p class="mb-3 text-center">
                                                Đang cập nhật
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse                            
                          	</tbody>
                          	<tfoot>
                          		<tr>
                          			<td colspan="9">
                          				<div class="d-flex justify-content-center align-items-center py-2">
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
		@else
		<div class="alert alert-danger" role="alert">
			Không có kết quả
        </div>
		@endif
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
@endsection