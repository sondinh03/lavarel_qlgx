@extends('frontend.layout.main')

{{-- SEO --}}
@section('title', Str::title('Kết quả học tập lớp ' . $lop->name))
<meta name="robots" content="noindex"/>

@section('main')
<div class="container-fluid">
	<div class="shadow bg-white mb-4 border rounded-4">
    	<div class="card border-0">
    		<div class="card-header bg-white">
    			<div class="card-title fw-semibold py-1 fs-5">
    				Kết quả học tập - {{$lop->schoolyear}} - {{$lop->name}}
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
                                  	<th scope="col" rowspan="2">#</th>
                                  	<th scope="col" rowspan="2">Mã thiếu nhi</th>
                                  	<th scope="col" rowspan="2">Tên thánh</th>
                                  	<th scope="col" rowspan="2">Họ tên đệm</th>
                                  	<th scope="col" rowspan="2">Tên</th>
                                  	<th scope="col" rowspan="2">Ngày sinh</th>
                                  	<th colspan="4">
                                        KỲ I
                                    </th>
                                    <th colspan="4">
                                        KỲ II
                                    </th>
                                    <th rowspan="2" style="vertical-align: middle;">
                                        TB Năm
                                    </th>
                                    <th rowspan="2" style="vertical-align: middle;">
                                        Xếp loại
                                    </th>
                                    <th rowspan="2" style="vertical-align: middle;">
                                        Nghỉ lễ
                                    </th>
                                    <th rowspan="2" style="vertical-align: middle;">
                                        Bỏ học
                                    </th>
                                    <th rowspan="2" style="vertical-align: middle;">
                                        Hạnh kiểm
                                    </th>
                                    <th rowspan="2" style="vertical-align: middle;">
                                        Ghi chú
                                    </th>
                                </tr>
                                <tr>
                                    <th>8 T</th>
                                    <th>K I</th>
                                    <th>Kinh</th>
                                    <th>Xếp loại</th>
                                    <th>8 T</th>
                                    <th>K II</th>
                                    <th>Kinh</th>
                                    <th>Xếp loại</th>
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
                                  	@if($hocsinh->diem)
                                  	<td>{{$hocsinh->diem->tuan1}}</td>
                                  	<td>{{$hocsinh->diem->k1}}</td>
                                  	<td>{{$hocsinh->diem->kinh1}}</td>
                                  	<td>{{$hocsinh->diem->kq1}}</td>
                                  	<td>{{$hocsinh->diem->tuan2}}</td>
                                  	<td>{{$hocsinh->diem->k2}}</td>
                                  	<td>{{$hocsinh->diem->kinh2}}</td>
                                  	<td>{{$hocsinh->diem->kq2}}</td>
                                  	<td>{{$hocsinh->diem->canam}}</td>
                                  	<td>{{$hocsinh->diem->seploai}}</td>
                                  	<td>{{$hocsinh->diem->nghile}} / {{$hocsinh->days}} </td>
                                  	<td>{{$hocsinh->diem->bohoc}} / {{$hocsinh->weeks}} </td>
                                  	<td>{{$hocsinh->diem->hanhkiem}}</td>
                                  	<td>{{$hocsinh->diem->ghichu}}</td>
                                  	@endif
                                </tr>
                          		@endforeach
                          	</tbody>
                          	<tfoot>
                          		<tr>
                          			<td colspan="18">
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
@endsection