<?php $__env->startSection('title', Str::title('Điểm danh đi lễ và đi học ' . $student->name)); ?>
<meta name="robots" content="noindex"/>

<?php $__env->startSection('main'); ?>
<div class="container-fluid">
	<div class="shadow bg-white mb-4 border rounded-4">
    	<div class="card border-0">
    		<div class="card-header bg-white">
    			<div class="card-title fw-semibold py-1 fs-5">
    				Thông tin thiếu nhi
    			</div>
    		</div>
    		<div class="card-body">
    			<div class="row">
    				<div class="col-md-4">
    					<div class="border-bottom border-light-subtle pb-2 mb-2">
    						<div class="row">
    							<div class="col-12 col-md-3">
    								Họ và tên
    							</div>
    							<div class="col-12 col-md-9">
    								<strong><?php echo e($student->holy); ?> <?php echo e($student->last_name); ?> <?php echo e($student->name); ?></strong>
    								<span>(<a title="Sửa" href="<?php echo e($student->edit); ?>" class="text-decoration-none">Sửa</a>)</span>
    							</div>
    						</div>
    					</div>
    				</div>
    				<div class="col-md-4">
    					<div class="border-bottom border-light-subtle pb-2 mb-2">
    						<div class="row">
    							<div class="col-12 col-md-3">
    								Mã thiếu nhi
    							</div>
    							<div class="col-12 col-md-9">
    								<strong><?php echo e($student->mahv); ?></strong>
    							</div>
    						</div>
    					</div>
    				</div>
    				<div class="col-md-4">
    					<div class="border-bottom border-light-subtle pb-2 mb-2">
    						<div class="row">
    							<div class="col-12 col-md-3">
    								Ngày sinh
    							</div>
    							<div class="col-12 col-md-9">
    								<strong><?php echo e($student->birthday); ?></strong>
    							</div>
    						</div>
    					</div>
    				</div>
    				<div class="col-md-4">
    					<div class="border-bottom border-light-subtle pb-2 mb-2">
    						<div class="row">
    							<div class="col-12 col-md-3">
    								Họ tên Cha
    							</div>
    							<div class="col-12 col-md-9">
    								<strong><?php echo e($student->father); ?></strong>
    							</div>
    						</div>
    					</div>
    				</div>
    				<div class="col-md-4">
    					<div class="border-bottom border-light-subtle pb-2 mb-2">
    						<div class="row">
    							<div class="col-12 col-md-3">
    								Họ tên Mẹ
    							</div>
    							<div class="col-12 col-md-9">
    								<strong><?php echo e($student->mother); ?></strong>
    							</div>
    						</div>
    					</div>
    				</div>
    				<div class="col-md-4">
    					<div class="border-bottom border-light-subtle pb-2 mb-2">
    						<div class="row">
    							<div class="col-12 col-md-3">
    								Giáo họ
    							</div>
    							<div class="col-12 col-md-9">
    								<strong><?php echo e($student->paid); ?><?php echo e($student->pid); ?><?php echo e($student->deid); ?><?php echo e($student->did); ?></strong>
    							</div>
    						</div>
    					</div>
    				</div>
    			</div>
    		</div>
    	</div>
    </div>
    <div class="row">
    	<div class="col-md-6">
    		<div class="shadow bg-white mb-4 border rounded-4">
            	<div class="card border-0">
            		<div class="card-header bg-white">
            			<div class="card-title fw-semibold py-1 fs-5">
            				Điểm danh đi học
            			</div>
            		</div>
            		<div class="card-body">
            			<?php
            			if(!empty($lop->start_date_one) AND !empty($lop->end_date_one)){
                			if (($date >= $lop->start_date_one) && ($date <= $lop->end_date_one)){
                			    if(!empty($dihoc)){
                			        ?>
                    			    	<p>Điểm danh đi học kỳ 1</p>
                    			    	<p>Thời gian: Từ <strong><?php echo date('d-m-Y', strtotime($lop->start_date_two)); ?></strong> đến <strong><?php echo date('d-m-Y', strtotime($lop->end_date_two)); ?></strong></p>
                    			    	<p class="alert alert-info">Bạn đã điểm danh rồi</p>
                    			    <?php
                			    }else{
            			            ?>
                        			    <p>Điểm danh đi học kỳ 1</p>
                        			    <p>Thời gian: Từ <strong><?php echo date('d-m-Y', strtotime($lop->start_date_one)); ?></strong> đến <strong><?php echo date('d-m-Y', strtotime($lop->end_date_one)); ?></strong></p>
                        			    <form action="" method="post" id="ky1">
                							<input type="hidden" name="lop" value="<?php echo e($student->lop); ?>">
                							<input type="hidden" name="id" value="<?php echo e($student->id); ?>">
                							<input type="hidden" name="hocky" value="1">
                                            <input type="submit" name="submit" class="btn btn-info text-white btn-submit1" value="Điểm danh">
                            			</form>
                    			    <?php
                			    }
                			}
            			}
            			
            			if(!empty($lop->start_date_two) AND !empty($lop->end_date_two)){
                			if (($date >= $lop->start_date_two) && ($date <= $lop->end_date_two)){
                			    if(!empty($dihoc)){
                    			    ?>
                    			    	<p>Điểm danh đi học kỳ 2</p>
                    			    	<p>Thời gian: Từ <strong><?php echo date('d-m-Y', strtotime($lop->start_date_two)); ?></strong> đến <strong><?php echo date('d-m-Y', strtotime($lop->end_date_two)); ?></strong></p>
                    			    	<p class="alert alert-info">Bạn đã điểm danh rồi</p>
                    			    <?php
                			    }else{
                			        ?>
                			    	<p>Điểm danh đi học kỳ 2</p>
                			    	<p>Thời gian: Từ <strong><?php echo date('d-m-Y', strtotime($lop->start_date_two)); ?></strong> đến <strong><?php echo date('d-m-Y', strtotime($lop->end_date_two)); ?></strong></p>
                			    	
                    			    <form action="" method="post" id="ky2">
            							<input type="hidden" name="lop" value="<?php echo e($student->lop); ?>">
            							<input type="hidden" name="id" value="<?php echo e($student->id); ?>">
            							<input type="hidden" name="hocky" value="2">
                                        <input type="submit" name="submit" class="btn btn-info text-white btn-submit1" value="Điểm danh">
                        			</form>
                			        <?php 
                			    }
                			}
            			}
            			?>
            			<div class="alert alert-success print-msg fs-8 mb-0" style="display:none">
                        	<p class="mb-0"></p>
                        </div>
                        <div class="alert alert-danger print-error-msg fs-8 mb-0" style="display:none">
                        	<ul class="mb-0"></ul>
                        </div>
            		</div>
        		</div>
        	</div>
    	</div>
    	<div class="col-md-6">
    		<div class="shadow bg-white mb-4 border rounded-4">
            	<div class="card border-0">
            		<div class="card-header bg-white">
            			<div class="card-title fw-semibold py-1 fs-5">
            				Điểm danh đi lễ
            			</div>
            		</div>
            		<div class="card-body">
            			<?php
            			if(!empty($lop->start_date_one) AND !empty($lop->end_date_one)){
                			if (($date >= $lop->start_date_one) && ($date <= $lop->end_date_one)){
                			    if(!empty($dile)){
                			        ?>
                			        <p>Điểm danh đi lễ kỳ 1</p>
                			    	<p>Thời gian: Từ <strong><?php echo date('d-m-Y', strtotime($lop->start_date_two)); ?></strong> đến <strong><?php echo date('d-m-Y', strtotime($lop->end_date_two)); ?></strong></p>
                			    	<p class="alert alert-info">Bạn đã điểm danh rồi</p>
                			        <?php
                			    }else{
                    			    ?>
                    			    <p>Điểm danh đi lễ kỳ 1</p>
                    			    <p>Thời gian: Từ <strong><?php echo date('d-m-Y', strtotime($lop->start_date_one)); ?></strong> đến <strong><?php echo date('d-m-Y', strtotime($lop->end_date_one)); ?></strong></p>
                    			    <?php
                    			    if(date('l', strtotime($date)) == 'Thursday' OR date('l', strtotime($date)) == 'Sunday'){
                    			        ?>
                        			    <form action="" method="post" id="ky1">
                							<input type="hidden" name="lop" value="<?php echo e($student->lop); ?>">
                							<input type="hidden" name="id" value="<?php echo e($student->id); ?>">
                							<input type="hidden" name="hocky" value="1">
                                            <input type="submit" name="submit" class="btn btn-info text-white btn-submit2" value="Điểm danh">
                            			</form>
                        			    <?php
                    			    }else{
                    			        ?>
                    			        <p class="alert alert-danger">Hôm nay không phải ngày điểm danh</p>
                    			        <?php
                    			    }
                			    }
                			}
            			}
            			if(!empty($lop->start_date_two) AND !empty($lop->end_date_two)){
                			if (($date >= $lop->start_date_two) && ($date <= $lop->end_date_two)){
                			    if($dile > 2){
                			        ?>
                			        <p>Điểm danh đi lễ kỳ 2</p>
                			    	<p>Thời gian: Từ <strong><?php echo date('d-m-Y', strtotime($lop->start_date_two)); ?></strong> đến <strong><?php echo date('d-m-Y', strtotime($lop->end_date_two)); ?></strong></p>
                			    	<p class="alert alert-info">Bạn đã điểm danh rồi</p>
                			        <?php
                			    }else{
                    			    ?>
                    			    <p>Điểm danh đi lễ kỳ 2</p>
                    			    <p>Thời gian: Từ <strong><?php echo date('d-m-Y', strtotime($lop->start_date_two)); ?></strong> đến <strong><?php echo date('d-m-Y', strtotime($lop->end_date_two)); ?></strong></p>
                    			    <?php
                    			    if(date('l', strtotime($date)) == 'Thursday' OR date('l', strtotime($date)) == 'Sunday'){
                    			        ?>
                    			        <form action="" method="post" id="ky2">
                							<input type="hidden" name="lop" value="<?php echo e($student->lop); ?>">
                							<input type="hidden" name="id" value="<?php echo e($student->id); ?>">
                							<input type="hidden" name="hocky" value="2">
                                            <input type="submit" name="submit" class="btn btn-info text-white btn-submit2" value="Điểm danh">
                            			</form>
                    			        <?php
                    			    }else{
                    			        ?>
                    			        <p class="alert alert-danger">Hôm nay không phải ngày điểm danh</p>
                    			        <?php
                    			    }
                			    }
                			}
            			}
            			?>
            		</div>
        		</div>
        	</div>
    	</div>
    </div>
    <div class="shadow bg-white mb-4 border rounded-4">
    	<div class="card border-0">
    		<div class="card-header bg-white">
    			<div class="card-title fw-semibold py-1 fs-5">
    				Kết quả học tập
    			</div>
    		</div>
    		<div class="card-body">
    			<div class="table-responsive">
    				<table class="table table-striped table-bordered table-hover text-nowrap">
    					<thead>
                      		<tr>
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
                        <tbody>
                        	<?php if(!empty($diemthi)): ?>
                        	<tr>
                            	<td><?php echo e($diemthi->tuan1); ?></td>
                            	<td><?php echo e($diemthi->k1); ?></td>
                            	<td><?php echo e($diemthi->kinh1); ?></td>
                            	<td><?php echo e($diemthi->kq1); ?></td>
                            	<td><?php echo e($diemthi->tuan2); ?></td>
                            	<td><?php echo e($diemthi->k2); ?></td>
                            	<td><?php echo e($diemthi->kinh2); ?></td>
                            	<td><?php echo e($diemthi->kq2); ?></td>
                            	<td><?php echo e($diemthi->canam); ?></td>
                            	<td><?php echo e($diemthi->seploai); ?></td>
                            	<td><?php echo e($diemthi->nghile); ?></td>
                            	<td><?php echo e($diemthi->bohoc); ?></td>
                            	<td><?php echo e($diemthi->hanhkiem); ?></td>
                            	<td><?php echo e($diemthi->ghichu); ?></td>
                            </tr>
                            <?php endif; ?>
                    	</tbody>
    				</table>
    			</div>
    		</div>
		</div>
	</div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $(".btn-submit1").click(function(e){
            e.preventDefault();
            
            var hocky = $(this).prev('input[name="hocky"]').val();
            var id = $(this).prev().prev('input[name="id"]').val();
            var lop = $(this).prev().prev().prev('input[name="lop"]').val();
            
            $.ajax({
                url: "<?php echo e(route('my-form-dihoc')); ?>",
                type:'POST',
                headers: {
                	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {hocky:hocky, id:id, lop:lop},
                success: function(data) {
            		//console.log(data.success)
                 	if($.isEmptyObject(data.error)){
                     	printMsg(data.success);
                 		$('.btn-submit1').css('display','none');
                     	//window.location.reload();
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
        
        
        $(".btn-submit2").click(function(e){
            e.preventDefault();
            
            var hocky = $(this).prev('input[name="hocky"]').val();
            var id = $(this).prev().prev('input[name="id"]').val();
            var lop = $(this).prev().prev().prev('input[name="lop"]').val();
            
            $.ajax({
                url: "<?php echo e(route('my-form-dile')); ?>",
                type:'POST',
                headers: {
                	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {hocky:hocky, id:id, lop:lop},
                success: function(data) {
            		//console.log(data.success)
                 	if($.isEmptyObject(data.error)){
                     	printMsg(data.success);
                 		$('.btn-submit2').css('display','none');
                     	//window.location.reload();
                 	}else{
                     	printErrorMsg(data.error);
                 	}
                }
            });
        });
        function printMsg (msg) {
            $(".print-msg-dile").find("p").html('');
            $(".print-msg-dile").css('display','block');
            $(".print-msg-dile").find("p").append('<span>'+msg+'</span>');
            $(".print-msg-dile").delay(5000).hide(0);
        }
        function printErrorMsg (msg) {
            $(".print-error-msg-dile").find("ul").html('');
            $(".print-error-msg-dile").css('display','block');
            $.each( msg, function( key, value ) {
                $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
            });
            $(".print-error-msg").delay(5000).hide(0);
        }
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('frontend.layout.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/frontend/student.blade.php ENDPATH**/ ?>