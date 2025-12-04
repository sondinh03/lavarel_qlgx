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
<div class="container">
	@if(!empty($thongke))
    	@foreach($thongke as $item)
    		<div class="bg-primary text-white py-3 px-3 mb-3">
    			<h3 class="fs-5 mb-0">{{$item['giaoxu']['name']}}</h3>
    		</div>
    		@if($item['giaoxu']['student']==1)
        		<div class="alert alert-primary fs-7 py-2" role="alert">
                  	Thống kê học sinh
                </div>
    			<div class="clearfix">
            		<div class="row">
            			<div class="col-md-3 mb-3 mb-md-0">
            				<div class="card border border-light-subtle">
                        		<div class="card-header bg-primary">
                        			<div class="card-title fw-semibold py-1 fs-6 mb-0 text-white">
                        				Thống kê khối
                        			</div>
                        		</div>
                        		<div class="card-body">
                        			<div class="clearfix">
                    					<div class="w-100 d-flex justify-content-between align-items-end">
                    						<p class=" mb-1 fs-14">Số khối</p>
                    						<h2 class="mb-0">
                    							<span class="number-font1">{{number_format($item['block']['block_count'])}}</span>
                    							<span class="ml-2 text-muted fs-11">
                    								<span class="text-success"><i class="bi bi-caret-up-fill"></i> {{number_format($item['block']['block_total'])}}%</span>
                								</span>
            								</h2>
                    					</div>
                    				</div>
                    				<div class="progress h-2 mt-1">
            							<div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" style="width: {{$item['block']['block_total']}}%"></div>
            						</div>
                        		</div>
                    		</div>
            			</div>
            			<div class="col-md-3 mb-3 mb-md-0">
            				<div class="card border border-light-subtle">
            					<div class="card-header bg-info">
                        			<div class="card-title fw-semibold py-1 fs-6 mb-0 text-white">
                        				Thống kê lớp
                        			</div>
                        		</div>
                    			<div class="card-body">
                    				<div class="clearfix">
                    					<div class="w-100 d-flex justify-content-between align-items-end">
                    						<p class=" mb-1 fs-14">Số lớp</p>
                    						<h2 class="mb-0">
                    							<span class="number-font1">{{number_format($item['lop']['lop_count'])}}</span>
                    							<span class="ml-2 text-muted fs-11">
                    								<span class="text-success"><i class="bi bi-caret-up-fill"></i> {{number_format($item['lop']['lop_total'])}}%</span>
                								</span>
            								</h2>
                    					</div>
                    				</div>
                    				<div class="progress h-2 mt-1">
            							<div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" style="width: {{$item['lop']['lop_total']}}%"></div>
            						</div>
                    			</div>
                    		</div>
            			</div>
            			<div class="col-md-3 mb-3 mb-md-0">
            				<div class="card border border-light-subtle">
            					<div class="card-header bg-warning">
                        			<div class="card-title fw-semibold py-1 fs-6 mb-0">
                        				Thống kê học sinh
                        			</div>
                        		</div>
                    			<div class="card-body">
                    				<div class="clearfix">
                    					<div class="w-100 d-flex justify-content-between align-items-end">
                    						<p class=" mb-1 fs-14">Số học sinh</p>
                    						<h2 class="mb-0">
                    							<span class="number-font1">{{number_format($item['hocsinh']['hocsinh_count'])}}</span>
                    							<span class="ml-2 text-muted fs-11">
                    								<span class="text-success"><i class="bi bi-caret-up-fill"></i> {{number_format($item['hocsinh']['hocsinh_total'])}}%</span>
                								</span>
            								</h2>
                    					</div>
                    				</div>
                    				<div class="progress h-2 mt-1">
            							<div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: {{$item['hocsinh']['hocsinh_total']}}%"></div>
            						</div>
                    			</div>
                    		</div>
            			</div>
            			<div class="col-md-3 mb-3 mb-md-0">
            				<div class="card border border-light-subtle">
            					<div class="card-header bg-success">
                        			<div class="card-title fw-semibold py-1 fs-6 mb-0 text-white">
                        				Thống kê giáo viên
                        			</div>
                        		</div>
                    			<div class="card-body">
                    				<div class="clearfix">
                    					<div class="w-100 d-flex justify-content-between align-items-end">
                    						<p class=" mb-1 fs-14">Số giáo viên</p>
                    						<h2 class="mb-0">
                    							<span class="fs-2 fw-normal">{{number_format($item['teacher']['teacher_count'])}}</span>
                    							<span class="ml-2 text-muted fs-11">
                    								<span class="text-success">
                    									<i class="bi bi-caret-up-fill"></i> {{number_format($item['teacher']['teacher_total'])}}%
                									</span>
            									</span>
            								</h2>
                    					</div>
                    				</div>
                    				<div class="progress h-2 mt-1">
            							<div class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: {{$item['teacher']['teacher_total']}}%"></div>
            						</div>
                    			</div>
                			</div>
            			</div>
            		</div>
            	</div>
    		@endif
    		<hr>
    		@if($item['giaoxu']['parish']==1)
    			<div class="alert alert-primary fs-7 py-2" role="alert">
                  	Thống kê giáo xứ
                </div>
            	<div class="clearfix">
            		<div class="row">
            			<div class="col-md-3 mb-3 mb-md-0">
            				<div class="card border border-light-subtle">
                        		<div class="card-header bg-warning">
                        			<div class="card-title fw-semibold py-1 fs-6 mb-0">
                        				Thống kê hội đoàn
                        			</div>
                        		</div>
                        		<div class="card-body">
                        			<div class="clearfix">
                    					<div class="w-100 d-flex justify-content-between align-items-end">
                    						<p class=" mb-1 fs-14">Hộ đoàn</p>
                    						<h2 class="mb-0">
                    							<span class="fs-2 fw-normal">{{number_format($item['association']['association_count'])}}</span>
                    							<span class="ml-2 text-muted fs-11">
                    								<span class="text-success">
                    									<i class="bi bi-caret-up-fill"></i> {{number_format($item['association']['association_total'])}}%
                									</span>
            									</span>
            								</h2>
                    					</div>
                    				</div>
                    				<div class="progress h-2 mt-1">
            							<div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" style="width: {{$item['association']['association_total']}}%"></div>
            						</div>
                        		</div>
                    		</div>
            			</div>
            			<div class="col-md-3 mb-3 mb-md-0">
            				<div class="card border border-light-subtle">
            					<div class="card-header bg-danger">
                        			<div class="card-title fw-semibold py-1 fs-6 mb-0 text-white">
                        				Thống kê giáo dân
                        			</div>
                        		</div>
                    			<div class="card-body">
                    				<div class="clearfix">
                    					<div class="w-100 d-flex justify-content-between align-items-end">
                    						<p class=" mb-1 fs-14">Giáo dân</p>
                    						<h2 class="mb-0">
                    							<span class="fs-2 fw-normal">{{number_format($item['parishioners']['parishioners_count'])}}</span>
                    							<span class="ml-2 text-muted fs-11">
                    								<span class="text-success">
                    									<i class="bi bi-caret-up-fill"></i> {{number_format($item['parishioners']['parishioners_total'])}}%
                									</span>
            									</span>
            								</h2>
                    					</div>
                    				</div>
                    				<div class="progress h-2 mt-1">
            							<div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" style="width: {{$item['parishioners']['parishioners_total']}}%"></div>
            						</div>
                    			</div>
                    		</div>
            			</div>
            			<div class="col-md-3 mb-3 mb-md-0">
            				<div class="card border border-light-subtle">
            					<div class="card-header bg-success">
                        			<div class="card-title fw-semibold py-1 fs-6 mb-0 text-white">
                        				Thống kê gia đình
                        			</div>
                        		</div>
                    			<div class="card-body">
                    				<div class="clearfix">
                    					<div class="w-100 d-flex justify-content-between align-items-end">
                    						<p class=" mb-1 fs-14">Gia đình</p>
                    						<h2 class="mb-0">
                    							<span class="fs-2 fw-normal">{{number_format($item['family']['family_count'])}}</span>
                    							<span class="ml-2 text-muted fs-11">
                    								<span class="text-success">
                    									<i class="bi bi-caret-up-fill"></i> {{number_format($item['family']['family_total'])}}%
                									</span>
            									</span>
            								</h2>
                    					</div>
                    				</div>
                    				<div class="progress h-2 mt-1">
            							<div class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: {{$item['family']['family_total']}}%"></div>
            						</div>
                    			</div>
                    		</div>
            			</div>
            			<div class="col-md-3 mb-3 mb-md-0">
            				<div class="card border border-light-subtle">
            					<div class="card-header bg-primary">
                        			<div class="card-title fw-semibold py-1 fs-6 mb-0 text-white">
                        				Thống kê rao hôn phối
                        			</div>
                        		</div>
                    			<div class="card-body">
                    				<div class="clearfix">
                    					<div class="w-100 d-flex justify-content-between align-items-end">
                    						<p class=" mb-1 fs-14">Rao hôn phối</p>
                    						<h2 class="mb-0">
                    							<span class="fs-2 fw-normal">{{number_format($item['marriage']['marriage_count'])}}</span>
                    							<span class="ml-2 text-muted fs-11">
                    								<span class="text-success">
                    									<i class="bi bi-caret-up-fill"></i> {{number_format($item['marriage']['marriage_total'])}}%
                									</span>
            									</span>
            								</h2>
                    					</div>
                    				</div>
                    				<div class="progress h-2 mt-1">
            							<div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: {{$item['marriage']['marriage_total']}}%"></div>
            						</div>
                    			</div>
                			</div>
            			</div>
            		</div>
            	</div>
    		@endif
    	@endforeach
	@endif
</div>
@endsection