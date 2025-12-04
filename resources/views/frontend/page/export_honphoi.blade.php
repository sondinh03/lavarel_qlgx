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
			Xuất file excel danh sách hôn phối
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
        <form class="form-search" action="{{ route('exporthonphoi') }}" method="post" enctype="multipart/form-data">
			@csrf
    		<div class="shadow-sm border bg-white p-3 mb-3">
    			<div class="row">
    				@if(!empty($form))
    				<div class="form-group col-6 col-md-2 mb-3">
    					<label class="mb-1">Giáo phận</label>
    					<select class="form-control" name="giaophan">
    						<option>-----</option>
    						@forelse($giaophan as $key => $item)
    							@if(!empty($_GET)) {
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
    				<div class="form-group col-6 col-md-2 mb-3">
    					<label class="mb-1">Giáo hạt</label>
    					<select class="form-control" name="giaohat">
    						<option>-----</option>			
    						@forelse($giaohat as $key => $item)
    							@if($item['id'] == $_GET['giaohat'])
    								<option selected="selected" value="{{$item['id']}}">{{$item['name']}}</option>
    							@else
    								<option value="{{$item['id']}}">{{$item['name']}}</option>
    							@endif
    						@empty
    							<option>-- Không có --</option>
    						@endforelse		
    					</select>
    				</div>
    				<div class="form-group col-6 col-md-2 mb-3">
    					<label class="mb-1">Giáo xứ</label>
    					<select class="form-control" name="giaoxu">
    						<option>-----</option>
    						@forelse($giaoxu as $key => $item)
    							@if($item['id'] == $_GET['giaoxu'])
    								<option selected="selected" value="{{$item['id']}}">{{$item['name']}}</option>
    							@else
    								<option value="{{$item['id']}}">{{$item['name']}}</option>
    							@endif
    						@empty
    							<option>-- Không có --</option>
    						@endforelse
    					</select>
    				</div>
    				@else
    				<div class="form-group col-6 col-md-2 mb-3">
    					<label class="mb-1">Giáo xứ</label>
    					<select class="form-control" name="giaoxu" required="required">
    						@if(!empty($giaoxu))
    							<option value="{{$giaoxu->id}}">{{$giaoxu->name}}</option>
    						@endif
    					</select>
    				</div>
    				@endif
    				<div class="group-form col-12 col-md-2 mb-3">
    					<label class="mb-1">Thời gian</label>
    					<input type="date" name="date" class="form-control" required="required">
    				</div>
    				<div class="group-form col-12 col-md-2 mb-3">
    					<label class="mb-1">&nbsp;</label>
    					<button type="submit" class="btn btn-primary w-100">Tải xuống</button>
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
                    $("select[name='lop'] option[value]").remove();
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
                    $("select[name='lop'] option[value]").remove();
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
            var giaoxu = $option.val();//to get content of "value" attrib
            var text = $option.text();//to get <option>Text</option> content
            $.ajax({
                url:"{{ route('search') }}",
                type:'POST',
                headers: {
                	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {giaoxu:giaoxu},
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
	});
</script>
@endsection