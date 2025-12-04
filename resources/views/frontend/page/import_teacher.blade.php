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
		<div class="alert alert-info">
			Thêm danh sách giáo viên bằng excel
		</div>
		@if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </div>
        @endif
		<form class="form-search" action="{{ route('importgv') }}" method="post" enctype="multipart/form-data">
			@csrf
    		<div class="shadow-sm border bg-white p-3 mb-3">
    			<div class="row">
    				<div class="form-group col-6 col-md-2 mb-3">
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
    				<div class="form-group col-6 col-md-2 mb-3">
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
    				<div class="form-group col-6 col-md-2 mb-3">
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
    				<div class="group-form col-12 col-md-2 mb-3">
    					<div class="mb-3">
    						<label for="formFile" class="form-label mb-1">Chọn khóa học</label>
    						<select class="form-select" name="schoolyear" required="required">
    							<option></option>
    							@if(!empty($array_year))
    								@foreach($array_year as $year)
    									<option value="{{$year['id']}}">{{$year['name']}}</option>
    								@endforeach
    							@endif
    						</select>
    					</div>
    				</div>
    				<div class="group-form col-12 col-md-2 mb-3">
    					<div class="mb-3">
                          	<label for="formFile" class="form-label mb-1">Chọn file excel</label>
                          	<input class="form-control" type="file" name="file" id="file">
                        </div>
    				</div>
    				<div class="group-form col-12 col-md-2 mb-3">
    					<label class="mb-1">&nbsp;</label>
    					<button type="submit" class="btn btn-primary w-100">Thực hiện import</button>
    				</div>
    			</div>
    		</div>
		</form>
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
	});
</script>
@endsection