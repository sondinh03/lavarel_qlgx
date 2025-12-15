<?php
use App\Models\KhaoKinh;
?>



<?php $__env->startSection('title', Str::title('Khảo kinh lớp ' . $lop->name)); ?>
<meta name="robots" content="noindex"/>

<?php $__env->startSection('main'); ?>
<div class="container-fluid">
    <div class="alert alert-success">
        <p class="mb-1"><span>Lưu ý các thông số sau khi bạn thực hiện khảo kinh để tính điểm khảo kinh</span></p>
        <ul class="mb-0">
            <li>Thuộc bài: +</li>
            <li>Ấp úng: -</li>
            <li>Không thuộc: 0</li>
        </ul>
    </div>
    <div class="shadow bg-white mb-4 border rounded-4">
    	<div class="card border-0">
    		<div class="card-header bg-white">
    			<div class="card-title fw-semibold py-1 fs-5">
    				Khảo kinh học kỳ I - <?php echo e($lop->schoolyear); ?> - <?php echo e($lop->name); ?>

    			</div>
    		</div>
    		<div class="card-body">
    			<form action="" method="post" id="ky1">
    				<input type="hidden" name="ky1" value="1">
					<input type="hidden" name="id" value="<?php echo e($lop->id); ?>">
					<div class="table-responsive">
                        <div class="alert alert-success print-msg fs-8 mb-0" style="display:none">
                        	<p class="mb-0"></p>
                        </div>
                        <div class="alert alert-danger print-error-msg fs-8 mb-0" style="display:none">
                        	<ul class="mb-0"></ul>
                        </div>
            			<table class="table table-striped table-bordered table-hover text-nowrap">
                          	<thead class="fs-6">
                                <tr>
                                  	<th scope="col">#</th>
                                  	<th scope="col">Mã thiếu nhi</th>
                                  	<th scope="col">Tên thánh</th>
                                  	<th scope="col">Họ tên đệm</th>
                                  	<th scope="col">Tên</th>
                                  	<th scope="col">Ngày sinh</th>
                                  	<?php for($i = 1; $i <= $hk1; $i++): ?>
                                        <th scope="col" class="text-center">Ngày <?php echo e($i); ?></th>
                                    <?php endfor; ?>
                                </tr>
                          	</thead>
                          	<tbody class="fs-6">
                              	<?php
                          			$checked_on = $checked_to = $checked_zo = '';
                          		?>
                          		<?php $__currentLoopData = $student; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $hocsinh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                          		<tr data-id="<?php echo e($hocsinh->id); ?>" id="chuoi_<?php echo e($hocsinh->id); ?>">
                                  	<th scope="row"><?php echo e($hocsinh->stt); ?></th>
                                  	<td><?php echo e($hocsinh->mahv); ?></td>
                                  	<td><?php echo e($hocsinh->holy); ?></td>
                                  	<td><?php echo e($hocsinh->last_name); ?></td>
                                  	<td class="sticky-col second-col"><?php echo e($hocsinh->name); ?></td>
                                  	<td><?php echo e($hocsinh->birthday); ?></td>
                                  	<?php for($i = 1; $i <= $hk1; $i++): ?>
                                  	<td>
                              			<input type="hidden" name="idhv[]" value="<?php echo e($hocsinh->id); ?>">
                              			<div class="text-center">
                              				<?php
                                  				$checked_on = $checked_to = $checked_zo = '';
                                  				$khaokinh = KhaoKinh::where('idh', $hocsinh->id)->where('lophoc', $hocsinh->lop)->where('hocky', 1)->where('ngay', $i)->where('status', 1)->orderBy('created_at', 'asc')->get()->first();
                                  				$checked_on = $checked_to = $checked_zo = '';
                                  				if(!empty($khaokinh)){
                                  				    if($khaokinh->khaokinh == 1){
                                  				        $checked_on = 'checked="checked"';
                                  				    }elseif($khaokinh->khaokinh == 2){
                                  				        $checked_to = 'checked="checked"';
                                  				    }elseif($khaokinh->khaokinh == 3){
                                  				        $checked_zo = 'checked="checked"';
                                  				    }
                                  				}
                          				    ?>
                          				    <div class="form-check form-check-inline">
                              					<input <?php echo e($checked_on); ?> class="form-check-input" type="radio" name="khaokinh[<?php echo e($hocsinh->id); ?>][<?php echo e($i); ?>]" required="" id="khaokinh_<?php echo e($i); ?>_<?php echo e($hocsinh->id); ?>" value="1">
                              					<label class="form-check-label">+</label>
                          					</div>
                          					<div class="form-check form-check-inline">
                          						<input <?php echo e($checked_to); ?> class="form-check-input" type="radio" name="khaokinh[<?php echo e($hocsinh->id); ?>][<?php echo e($i); ?>]" required="" id="khaokinh_<?php echo e($i); ?>_<?php echo e($hocsinh->id); ?>" value="2">
                          						<label class="form-check-label">-</label>
                      						</div>
                  							<div class="form-check form-check-inline">
                              					<input <?php echo e($checked_zo); ?> class="form-check-input" type="radio" name="khaokinh[<?php echo e($hocsinh->id); ?>][<?php echo e($i); ?>]" required="" id="khaokinh_<?php echo e($i); ?>_<?php echo e($hocsinh->id); ?>" value="3">
                              					<label class="form-check-label">0</label>
                          					</div>
                              			</div>
                          			</td>
                                  	<?php endfor; ?>
                                </tr>
                          		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                          	</tbody>
                          	<tfoot>
                                <tr>
                                    <td colspan="3" class="sticky-col second-col">
                                        <input type="button" class="btn btn-info text-white" onclick="add_khaokinh_k1('<?php echo e($lop->id); ?>')" value="Thêm Ngày" />
                                        <input type="submit" name="submit" class="btn btn-info text-white btn-submit1" value="Cập nhật">
                                    </td>
                                    <td colspan="7">
                          				<div class="d-flex justify-content-end align-items-center py-2">
                          					<?php echo $pagination; ?>

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
	<div class="shadow bg-white mb-4 border rounded-4">
    	<div class="card border-0">
    		<div class="card-header bg-white">
    			<div class="card-title fw-semibold py-1 fs-5">
    				Khảo kinh học kỳ II - <?php echo e($lop->schoolyear); ?> - <?php echo e($lop->name); ?>

    			</div>
    		</div>
    		<div class="card-body">
    			<form action="" method="post" class="table-responsive" id="ky2">
    				<input type="hidden" name="ky2" value="2">
					<input type="hidden" name="id" value="<?php echo e($lop->id); ?>">
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
                                  	<th scope="col">#</th>
                                  	<th scope="col">Mã thiếu nhi</th>
                                  	<th scope="col">Tên thánh</th>
                                  	<th scope="col">Họ tên đệm</th>
                                  	<th scope="col">Tên</th>
                                  	<th scope="col">Ngày sinh</th>
                                  	<?php for($i = 1; $i <= $hk2; $i++): ?>
                                        <th scope="col" class="text-center">Ngày <?php echo e($i); ?></th>
                                    <?php endfor; ?>
                                </tr>
                          	</thead>
                          	<tbody class="fs-6">
                          		<?php $__currentLoopData = $student; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $hocsinh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                          		<tr data-id="<?php echo e($hocsinh->id); ?>" id="chuoi_<?php echo e($hocsinh->id); ?>">
                                  	<th scope="row"><?php echo e($hocsinh->stt); ?></th>
                                  	<td><?php echo e($hocsinh->mahv); ?></td>
                                  	<td><?php echo e($hocsinh->holy); ?></td>
                                  	<td><?php echo e($hocsinh->last_name); ?></td>
                                  	<td class="sticky-col second-col"><?php echo e($hocsinh->name); ?></td>
                                  	<td><?php echo e($hocsinh->birthday); ?></td>
                                  	<?php for($i = 1; $i <= $hk2; $i++): ?>
                                  		<td>
                                  			<input type="hidden" name="idhv[]" value="<?php echo e($hocsinh->id); ?>">
                                  			<div class="text-center">
                                  				<?php
                                  				$checked_on = $checked_to = $checked_zo = '';
                                  				$khaokinh = KhaoKinh::where('idh', $hocsinh->id)->where('lophoc', $hocsinh->lop)->where('hocky', 2)->where('ngay', $i)->where('status', 1)->orderBy('created_at', 'asc')->get()->first();
                                  				$checked_on = $checked_to = $checked_zo = '';
                                  				if(!empty($khaokinh)){
                                  				    if($khaokinh->khaokinh == 1){
                                  				        $checked_on = 'checked="checked"';
                                  				    }elseif($khaokinh->khaokinh == 2){
                                  				        $checked_to = 'checked="checked"';
                                  				    }elseif($khaokinh->khaokinh == 3){
                                  				        $checked_zo = 'checked="checked"';
                                  				    }
                                  				}
                              				    ?>
                              				    <div class="form-check form-check-inline">
                                  					<input <?php echo e($checked_on); ?> class="form-check-input" type="radio" name="khaokinh[<?php echo e($hocsinh->id); ?>][<?php echo e($i); ?>]" required="" id="khaokinh_<?php echo e($i); ?>_<?php echo e($hocsinh->id); ?>" value="1">
                                  					<label class="form-check-label">+</label>
                              					</div>
                              					<div class="form-check form-check-inline">
                              						<input <?php echo e($checked_to); ?> class="form-check-input" type="radio" name="khaokinh[<?php echo e($hocsinh->id); ?>][<?php echo e($i); ?>]" required="" id="khaokinh_<?php echo e($i); ?>_<?php echo e($hocsinh->id); ?>" value="2">
                              						<label class="form-check-label">-</label>
                          						</div>
                      							<div class="form-check form-check-inline">
                                  					<input <?php echo e($checked_zo); ?> class="form-check-input" type="radio" name="khaokinh[<?php echo e($hocsinh->id); ?>][<?php echo e($i); ?>]" required="" id="khaokinh_<?php echo e($i); ?>_<?php echo e($hocsinh->id); ?>" value="3">
                                  					<label class="form-check-label">0</label>
                              					</div>
                          					</div>
                                  		</td>
                                  	<?php endfor; ?>
                                </tr>
                          		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                          	</tbody>
                          	<tfoot>
                                <tr>
                                    <td colspan="3"  class="sticky-col second-col">
                                        <input type="button" class="btn btn-info text-white" onclick="add_khaokinh_k2('<?php echo e($lop->id); ?>')" value="Thêm Ngày" />
                                        <input type="submit" name="submit" class="btn btn-info text-white btn-submit2" value="Cập nhật">
                                    </td>
                                    <td colspan="7">
                          				<div class="d-flex justify-content-end align-items-center py-2">
                          					<?php echo $pagination; ?>

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
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script type="text/javascript">
    var file_items_k1 = '<?php echo e($items_k1); ?>';
    var file_items_k2 = '<?php echo e($items_k2); ?>';
</script>
<script type="text/javascript">
    function add_khaokinh_k1(a) {
        var newitem = '';
        file_items_k1++;
        $('#ky1 thead tr').append('<th class="text-center">Ngày ' + file_items_k1 + '</th>');
        $('#ky1 tbody tr').each(function(idx, el){
            var b = $(el).attr('data-id');
            newitem += '<td>';
                newitem += '<input type="hidden" name="idhv[]" value="' + b + '">';
                newitem += '<div class="text-center">';
                    newitem += "<div class=\"form-check form-check-inline\" id=\"khaokinh_content_" + file_items_k1 + "_" + b + "\">";
                        newitem += "<input class=\"form-check-input\" type=\"radio\" name=\"khaokinh[" + b + "]["+ file_items_k1 +"]\" required id=\"khaokinh_" + file_items_k2 + "_" + b + "\" value=\"1\" />";
                        newitem += '<label class="form-check-label" for="khaokinh_' + file_items_k1 + '_' + b + '">+</label>';
                    newitem += "</div>";
                    newitem += "<div class=\"form-check form-check-inline\" id=\"khaokinh_content_" + file_items_k1 + "_" + b + "\">";
                        newitem += "<input class=\"form-check-input\" type=\"radio\" name=\"khaokinh[" + b + "]["+ file_items_k1 +"]\" required id=\"khaokinh_" + file_items_k2 + "_" + b + "\" value=\"2\" />";
                        newitem += '<label class="form-check-label" for="khaokinh_' + file_items_k1 + '_' + b + '">-</label>';
                    newitem += "</div>";
                    newitem += "<div class=\"form-check form-check-inline\" id=\"khaokinh_content_" + file_items_k1 + "_" + b + "\">";
                        newitem += "<input class=\"form-check-input\" type=\"radio\" name=\"khaokinh[" + b + "]["+ file_items_k1 +"]\" required id=\"khaokinh_" + file_items_k2 + "_" + b + "\" value=\"3\" />";
                        newitem += '<label class="form-check-label" for="khaokinh_' + file_items_k1 + '_' + b + '">0</label>';
                    newitem += "</div>";
                newitem += '</div>';
            newitem += '</td>';
            $('#ky1 tbody tr#chuoi_'+b).append(newitem);
            var newitem = '';
        });
        return false;
    }
    
    function add_khaokinh_k2(a) {
        var newitem = '';
        file_items_k2++;
        $('#ky2 thead tr').append('<th class="text-center">Ngày ' + file_items_k2 + '</th>');
        $('#ky2 tbody tr').each(function(idx, el){
            var b = $(el).attr('data-id');
            newitem += '<td>';
                newitem += '<input type="hidden" name="idhv[]" value="' + b + '">';
                newitem += '<div class="text-center">';
                    newitem += "<div class=\"form-check form-check-inline\" id=\"khaokinh_content_" + file_items_k2 + "_" + b + "\">";
                        newitem += "<input class=\"form-check-input\" type=\"radio\" name=\"khaokinh[" + b + "]["+ file_items_k2 +"]\" required id=\"khaokinh_" + file_items_k2 + "_" + b + "\" value=\"1\" />";
                        newitem += '<label class="form-check-label" for="khaokinh_' + file_items_k2 + '_' + b + '">+</label>';
                    newitem += "</div>";
                    newitem += "<div class=\"form-check form-check-inline\" id=\"khaokinh_content_" + file_items_k2 + "_" + b + "\">";
                        newitem += "<input class=\"form-check-input\" type=\"radio\" name=\"khaokinh[" + b + "]["+ file_items_k2 +"]\" required id=\"khaokinh_" + file_items_k2 + "_" + b + "\" value=\"2\" />";
                        newitem += '<label class="form-check-label" for="khaokinh_' + file_items_k2 + '_' + b + '">-</label>';
                    newitem += "</div>";
                    newitem += "<div class=\"form-check form-check-inline\" id=\"khaokinh_content_" + file_items_k2 + "_" + b + "\">";
                        newitem += "<input class=\"form-check-input\" type=\"radio\" name=\"khaokinh[" + b + "]["+ file_items_k2 +"]\" required id=\"khaokinh_" + file_items_k2 + "_" + b + "\" value=\"3\" />";
                        newitem += '<label class="form-check-label" for="khaokinh_' + file_items_k2 + '_' + b + '">0</label>';
                    newitem += "</div>";
                newitem += '</div>';
            newitem += '</td>';
            $('#ky2 tbody tr#chuoi_'+b).append(newitem);
            var newitem = '';
        });
        return false;
    }
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $(".btn-submit1").click(function(e){
            e.preventDefault();
            
            var ky1 = $("input[name='ky1']").val();
            var id = $("#ky1 input[name='id']").val();
            
            var idhv = $('#ky1 input[name="idhv[]"]').map(function (idx, ele) {
            	return $(ele).val().trim().length == 0 ? 0 : parseFloat($(ele).val().trim());
        	}).get();
        	
        	var khaokinh = $('#ky1 input[name="khaokinh[]"]').map(function (idx, ele) {
            	return $(ele).val().trim().length == 0 ? 0 : parseFloat($(ele).val().trim());
        	}).get();
            
            $.ajax({
                url: "<?php echo e(route('my-form-khaokinhhk1')); ?>",
                type:'POST',
                headers: {
                	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {ky1:ky1, id:id, idhv:idhv, datamoi:$('#ky1').serialize()},
                success: function(data) {
            		//console.log(data.success)
                 	if($.isEmptyObject(data.error)){
                     	printMsg(data.success);
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
    });
    
    $(document).ready(function() {
        $(".btn-submit2").click(function(e){
            e.preventDefault();
            
            var ky2 = $("input[name='ky2']").val();
            var id = $("#ky2 input[name='id']").val();
            
            var idhv = $('#ky2 input[name="idhv[]"]').map(function (idx, ele) {
            	return $(ele).val().trim().length == 0 ? 0 : parseFloat($(ele).val().trim());
        	}).get();
        	
        	var khaokinh = $('#ky2 input[name="khaokinh[]"]').map(function (idx, ele) {
            	return $(ele).val().trim().length == 0 ? 0 : parseFloat($(ele).val().trim());
        	}).get();
            
            $.ajax({
                url: "<?php echo e(route('my-form-khaokinhhk2')); ?>",
                type:'POST',
                headers: {
                	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {ky2:ky2, id:id, idhv:idhv, datamoi:$('#ky2').serialize()},
                success: function(data) {
            		//console.log(data.success)
                 	if($.isEmptyObject(data.error)){
                     	printMsg(data.success);
                 	}else{
                     	printErrorMsg(data.error);
                 	}
                }
            });
        });
        function printMsg (msg) {
            $(".print-msg-bottom").find("p").html('');
            $(".print-msg-bottom").css('display','block');
            $(".print-msg-bottom").find("p").append('<span>'+msg+'</span>');
            $(".print-msg-bottom").delay(5000).hide(0);
        }
        function printErrorMsg (msg) {
            $(".print-error-msg-bottom").find("ul").html('');
            $(".print-error-msg-bottom").css('display','block');
            $.each( msg, function( key, value ) {
                $(".print-error-msg-bottom").find("ul").append('<li>'+value+'</li>');
            });
            $(".print-error-msg-bottom").delay(5000).hide(0);
        }
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('frontend.layout.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/frontend/khaokinh.blade.php ENDPATH**/ ?>