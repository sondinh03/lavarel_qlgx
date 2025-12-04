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
	<div class="container">
		<form class="form-search">
    		<div class="shadow-sm border bg-white p-3 mb-3">
    			<div class="row">
    				<div class="form-group col-6 col-md-3">
    					<label class="mb-1">Từ khóa tìm kiếm</label>
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
    						@foreach($giaohat as $key => $item)
    							@if(!empty($_GET['giaohat'])) {
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
    				<div class="group-form col-6 col-md-2 mb-2 mb-md-0">
    					<div class="mb-3">
    						<label for="formFile" class="form-label mb-1">Chọn khóa học</label>
    						<select class="form-select" name="namhoc" required="required">
    							<option></option>
    							@if(!empty($array_year))
    								@foreach($array_year as $year)
    									@if(!empty($_GET['namhoc']))
    										@if($year['id'] == $_GET['namhoc'])
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
    				<div class="group-form col-12 col-md-3">
    					<label class="mb-1">Tìm kiếm</label>
    					<button class="btn btn-primary w-100">Lọc thông tin</button>
    				</div>
    			</div>
    		</div>
		</form>
		@if(!empty($block))
		<div class="shadow border border-light-subtle bg-white rounded-3">	
			<div class="card">
				<div class="card-header border-light-subtle bg-white">
					<h3 class="card-title fs-6 fw-semibold py-2 mb-0">Danh sách khối học</h3>
				</div>
				<div class="card-body">
					<div class="table-responsive">
    					<table class="table table-vcenter text-nowrap mb-0 table-striped table-bordered border-top">
                          	<thead>
                            	<tr>
                                  	<th scope="col" class="fw-medium">#</th>
                                  	<th scope="col" class="fw-medium">Khối</th>
                                  	<th scope="col" class="fw-medium">Năm học</th>
                                  	<th scope="col" class="fw-medium">Giáo xứ</th>
                                  	<th scope="col" class="fw-medium">Giáo hạt</th>
                                  	<th scope="col" class="fw-medium">Giáo phận</th>
                                </tr>
                          	</thead>
                          	<tbody>
                          		@forelse($block as $key => $item)
                          			<tr>
                                      	<th scope="row">{{$key+1}}</th>
                                      	<td class="border-top-0 border-bottom-0 sticky-col second-col"><a class="text-decoration-none" href="{{$item->slug}}" title="{{$item->holy}}&nbsp;{{$item->name}}">{{$item->holy}}&nbsp;{{$item->name}}</a></td>
                                      	<td class="border-top-0 border-bottom-0">{{$item->namhoc}}</td>
                                      	<td class="border-top-0 border-bottom-0">{{$item->pid}}</td>
                                      	<td class="border-top-0 border-bottom-0">{{$item->deid}}</td>
                                      	<td class="border-top-0 border-bottom-0">{{$item->did}}</td>
                                    </tr>
                                @empty
                                	<tr>
                                		<td colspan="6">
                                            <p class="mb-3 text-center">
                                                Đang cập nhật
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse                            
                          	</tbody>
                          	@if(!empty($block))
                          	<tfoot>
                          		<tr>
                          			<td colspan="6">
                          				<div class="d-flex justify-content-center align-items-center py-2">
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