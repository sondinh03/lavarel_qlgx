@extends('frontend.layout.main')

{{-- SEO --}}
@section('title', $meta_title)
@section('meta_keyword', $meta_keywords)
@section('meta_description', $meta_description)

@section('content_top')
    <div class="position-relative mb-3 mb-md-5 bg-mobile-gradient pt-4 pt-md-0">
    	<div class="d-none d-sm-none d-md-block">
        	<img src="/images/real_estate/real_estate.png" class="img-fluid w-100 h-auto" loading="lazy">
        </div>
        <div class="position-absolute top-50 start-0 translate-middle-y w-100 d-form-home">
        	<div class="container">
    			<div class="mb-3 fs-1 fw-semibold w-50">
            		<p class="mb-0">Vay vốn <span class="text-danger">dễ dàng</span></p> 
        			<p class="mb-3"><span class="text-danger">Mua nhà</span> khang trang</p>
        			<p class="fs-6 fw-normal lh-lg">Chúng tôi tư vấn và hỗ trợ Khách hàng vay vốn Ngân hàng với lãi suất tốt nhất cùng phương án vay vốn phù hợp với kế hoạch và khả năng tài chính của bạn.</p>
        			<p><span class="btn btn-danger p-2 ps-4 pe-4 fs-6">Tìm hiểu lãi xuất vay</span></p>
    			</div>
			</div>
        </div>
        <div class="d-block d-sm-block d-md-none mb-4 px-3">
        	<img src="/images/real_estate/real_estate.png" class="img-fluid w-100 h-auto" loading="lazy">
        </div>
    </div>
@endsection

@section('main')
	@if(!empty($benefits_advantages))
    	<div class="container mb-4">
    		<div class="pt-5 pb-3">
        		<div class="text-center w-75 m-auto">
        			<h2 class="fs-1 text-black mb-3">Tại sao lựa chọn Houzing - Vay mua nhà</h2>
        			<p class="text-secondary fs-7">Accelerate your wireframing workflow using pre-build components or combine your own.Accelerate your wireframing workflow using</p>
        		</div>
    		</div>
    	</div>
        <div class="container mb-5">
            <div class="row">
            	@foreach(json_decode($benefits_advantages) as $benefits)
            		<div class="col-12 col-sm-6 col-md-4 mb-4">
                    	<div class="bg-white p-5 rounded-3 border border-light-subtle shadow h-100 text-center">
                    		<div class="text-center mb-4">
                    			<img class="img-fluid w-auto h-auto" alt="{{$benefits->title_benefits_advantages}}" src="{{$benefits->image_benefits_advantages}}">
                    		</div>
                        	<h3 class="fs-4 mb-3 text-center font-bolder ps-4 pe-4">{!! $benefits->title_benefits_advantages !!}</h3>
                        	<div class="text-secondary fw-normal fs-7">{!! $benefits->note_benefits_advantages !!}</div>
                        </div>
                    </div>
            	@endforeach
            </div>
        </div>
    @endif
    
    @if(!empty($registration_steps))
    	<div class="bg-step py-0 py-md-5 mb-2 mb-md-5">
    		<div class="pt-5 pb-5">
                <div class="container">
                	<div class="text-center w-50 m-auto text-white d-none d-sm-none d-md-block">
                    	<h2>Vay vốn nhỏ siêu nhanh tại Houzing Bank chỉ 5 bước</h2>
                		<p class="fw-normal fs-7">Lãi suất vay thấp nhất thị trường chỉ có tại Housing Bank</p>
                	</div>
                	<div class="text-start m-auto text-white d-block d-sm-block d-md-none">
                    	<h2>Vay vốn nhỏ siêu nhanh tại Houzing Bank chỉ 5 bước</h2>
                		<p class="fw-normal fs-7">Lãi suất vay thấp nhất thị trường chỉ có tại Housing Bank</p>
                	</div>
                </div>
                <div class="container">
                	<div class="d-none d-sm-none d-md-block">
                        <div class="row">
                            @foreach(json_decode($registration_steps) as $key => $steps)
                            	<?php
                            	if($key <= 2){
                            	    $class ="col-md-4";
                            	}else{
                            	    $class ="col-md-6";
                            	}
                            	?>
                                <div class="{{$class}} mb-4">
                                	<div class="bg-white p-4 rounded-3 border border-light-subtle shadow h-100">
                                		<div class="mb-3">
                                			<div class="d-flex justify-content-between align-items-center">
                                				<img class="img-fluid w-auto h-auto" alt="{{$steps->title_registration_steps}}" src="{{$steps->image_registration_steps}}">
                                				<span class="border border-light-subtle rounded-pill bg-body-tertiary p-1 ps-3 pe-3">{{$steps->number_registration_steps}}</span>
                                			</div>
                                		</div>
                                		<h3 class="fs-5 mb-3 font-bolder">{!! $steps->title_registration_steps !!}</h3>
                                		<div class="text-secondary fw-normal fs-7">{!! $steps->note_registration_steps !!}</div>
                                	</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="d-block d-sm-block d-md-none">
                    	<div class="owl-project owl-carousel owl-theme">
                    		@foreach(json_decode($registration_steps) as $key => $steps)
                    			<div class="item h-100 p-1">
                    				<div class="bg-white p-4 rounded-3 border border-light-subtle shadow h-100">
                    					<div class="mb-3">
                                			<div class="d-flex justify-content-between align-items-center">
                                				<img class="img-fluid w-auto h-auto" alt="{{$steps->title_registration_steps}}" src="{{$steps->image_registration_steps}}">
                                				<span class="border border-light-subtle rounded-pill bg-body-tertiary p-1 ps-3 pe-3">{{$steps->number_registration_steps}}</span>
                                			</div>
                                		</div>
                                		<h3 class="fs-5 mb-3 font-bolder">{!! $steps->title_registration_steps !!}</h3>
                                		<div class="text-secondary fw-normal fs-7">{!! $steps->note_registration_steps !!}</div>
                    				</div>
                    			</div>
                    		@endforeach
                    	</div>
                    </div>
                </div>
            </div>
    	</div>
    @endif
    
    @if(!empty($plan_loan_option))
    	<div class="py-1 py-md-5">
        	<div class="container mb-4">
            	<div class="text-center w-50 m-auto text-white d-none d-sm-none d-md-block">
            		<h2 class="fs-2 text-black mb-3">Lựa chọn phương án vay phù hợp</h2>
            	</div>
            	<div class="text-center m-auto text-white d-block d-sm-block d-md-none">
            		<h2 class="fs-2 text-black mb-3">Lựa chọn phương án vay phù hợp</h2>
            	</div>
        	</div>
        	<div class="container mb-0 mb-md-5">
        		<div class="row">
        			@foreach(json_decode($plan_loan_option) as $key => $plan)
        				<div class="col-md-6 mb-4">
        					<div class="bg-white rounded-4 border border-light-subtle shadow h-100 p-4 pb-0">
        						<h3 class="fs-5 fw-semibold">{{$plan->title_plan_loan_option}}</h3>
        						<p class="fs-6 text-dark">{{$plan->note_plan_loan_option}}</p>
        						@if(!empty($plan->content_plan_loan_option))
        							@foreach(json_decode($plan->content_plan_loan_option) as $key => $cplan)
        								<div class="bg-body-tertiary rounded-2 p-3 mb-4">
        								@if($key % 2 == 0)
        									<h4 class="fs-6 text-xanh fw-medium">{{$cplan->name}}</h4>
        									<p class="fs-7 text-dark mb-0">{{$cplan->description}}</p>
        								@else
        									<h4 class="fs-6 text-do fw-medium">{{$cplan->name}}</h4>
        									<p class="fs-7 text-dark mb-0">{{$cplan->description}}</p>
        								@endif
        								</div>
        							@endforeach
        						@endif
        					</div>
        				</div>
        			@endforeach
    			</div>
        	</div>
    	</div>
    @endif
    
    @if(!empty($customer_story))
    	<div class="bg-body-secondary pt-5 pb-5 d-none d-sm-none d-md-block">
    		<div class="container-fluid pt-5 pb-5">
    			<div class="row">
    				<div class="col-md-2">
    				</div>
    				<div class="col-md-10">
    					<div class="w-50 mb-4 text-white">
                    		<h2 class="fs-2 text-black mb-3">Câu chuyện của những Khách hàng lần đầu sở hữu ngôi nhà mơ ước</h2>
                    	</div>
        				<div class="owl_post owl-carousel owl-theme">
            				@foreach(json_decode($customer_story) as $key => $story)
            					<div class="border border-light-subtle bg-white rounded-4">
            						<div class="row flex-md-row-reverse">
            							<div class="col-6">
            								<img class="img-fluid w-auto h-auto rounded-start-0" src="{{($story->image_customer_story)}}" loading="lazy"/>
            							</div>
            							<div class="col-6 d-flex align-items-center">
            								<div class="p-4">
            									<p class="mb-1 mb-md-3 fs-4"><i class="bi bi-chat-right-quote"></i></p>
            									<p class="fw-medium">{{$story->note_customer_story}}</p>
            									<p class="fs-8 fw-normal">{{$story->author_customer_story}}</p>
            								</div>
            							</div>
            						</div>
            					</div>
            				@endforeach
            			</div>
    				</div>
    			</div>
			</div>
    	</div>
    @endif
    
    <div class="container">
        <div class="pt-5 pb-5">
        	<p class="fs-3 mb-3 text-black fw-semibold">Tin tức, bài viết mới</p>
        	<div class="row">
        		<div class="col-md-9 mb-4">
            		<ul class="list-unstyled ps-0 mb-0 d-flex d-md-flex flex-nowrap overflow-x-auto">
            			<li class="me-2"><a class="btn btn-dark text-nowrap">Tất cả</a></li>
            			@foreach($category_post as $key => $item)
            				<li class="me-2"><a class="btn btn-light text-nowrap" title="{{$item->name}}" href="{{url($item->slug)}}">{{$item->name}}</a></li>
						@endforeach
            		</ul>
        		</div>
        		<div class="col-md-3 d-flex justify-content-end mb-4">
        			<a class="btn btn-danger p-2 ps-4 pe-4 fs-7 text-decoration-none fw-medium d-none d-sm-none d-md-block" href="/tin-tuc" title="Tin tức">
                        Xem thêm
                    </a>
        		</div>
        	</div>
            <div class="mb-4">
            	<?php
                	$array_first = array();
                	$array_last = array();
            	?>
                @forelse($postLoanHomes as $key => $postLoanHome)
                	<?php 
                        if($key == 0){
                        	$array_first = $postLoanHome;
                        }else{
                        	$array_last[] = $postLoanHome; 
                        }
                    ?>
                @empty
                @endforelse
                <div class="row">
                	<div class="col-md-7">
                		<div class="d-none d-sm-none d-md-block">
                    		<div class="mb-3">
                    			<a class="text-decoration-none" href="{{ url($array_first->slug) }}" title="{{ url($array_first->name) }}">
            						<img class="img-fluid w-100 h-auto rounded-3" src="{{ url($array_first->image) }}" loading="lazy"/>
            					</a>
        					</div>
        					<p class="mb-2"><span class="text-body-tertiary fs-8"><i class="fa-regular fa-clock me-2"></i>{{$array_first->updated_at}}</span></p>
        					<p class="mb-2"><a class="text-decoration-none text-dark fs-5 fw-semibold" href="{{ url($array_first->slug) }}" title="{{ $array_first->name ?? '' }}">{{ $array_first->name ?? '' }}</a></p>
        					<p class="fs-7 text-secondary mb-3">{{ substr($array_first->content ?? '', 0, 380) }} ...</p>
    					</div>
    					<div class="d-block d-sm-block d-md-none">
    						<div class="mb-3">
    							<div class="row">
    								<div class="col-5">
    									<a class="text-decoration-none" href="{{ url($array_first->slug) }}" title="{{ url($array_first->name) }}">
                    						<img class="img-fluid w-100 h-auto rounded-3" src="{{ url($array_first->image) }}" loading="lazy"/>
                    					</a>
    								</div>
    								<div class="col-7 d-flex align-items-center">
    									<div class="mb-0">
                    						<p class="text-body-tertiary fs-8 mb-2"><i class="fa-regular fa-clock me-2"></i>{{$array_first->updated_at}}</p>
                    						<p class="mb-2"><a class="text-decoration-none text-dark" href="{{ url($array_first->slug) }}">{{ $array_first->name ?? '' }}</a></p>
                    					</div>
    								</div>
    							</div>
    						</div>
    					</div>
                	</div>
                	<div class="col-md-5">
                		@foreach($array_last as $key => $last)
                    		<div class="mb-3">
                    			<div class="row">
                    				<div class="col-5">
                    					<a href="{{ url($last->slug) }}" class="text-decoration-none">
                    						<img class="img-fluid w-auto h-auto rounded-3" src="{{ url($last->image) }}" loading="lazy"/>
                    					</a>
                    				</div>
                    				<div class="col-7 d-flex align-items-center">
                    					<div class="mb-0">
                    						<p class="text-body-tertiary fs-8 mb-2"><i class="fa-regular fa-clock me-2"></i>{{$last->updated_at}}</p>
                    						<p class="mb-2"><a class="text-decoration-none text-dark" href="{{ url($last->slug) }}">{{ $last->name ?? '' }}</a></p>
                    					</div>
                    				</div>
                    			</div>
                            </div>
                        @endforeach
                	</div>
                </div>
            </div>
        </div>
    	@include('frontend.parts.register_phone')
    </div>
@endsection
@push('metas')
    @if($no_index)
        <meta name="robots" content="noindex"/>
    @endif
@endpush
