<?php $__env->startSection('title', config('settings.web_name')); ?>
<?php $__env->startSection('meta_description', config('settings.meta_description')); ?>

<?php $__env->startSection('main'); ?>
<div class="bg-body-tertiary p-4">
	<div class="container">
		<div class="text-center">
			<div class="bg-white border p-5">
				<h1 class="fs-3 fw-bolder mb-3">MỤC VỤ QUẢN LÝ <span class="text-uppercase text-danger"><?php echo e($user->name); ?></span></h1>
				<p>Để thêm các thông tin của xứ bạn đang quản lý, xin vui lòng <a class="text-decoration-none" href="/admin" title="quản trị">click vào đây</a> để truy cập trang quản trị</p>
				
				<div class="d-block d-md-flex justify-content-center">
					<?php if(!empty($loginroi)): ?>
					<a class="text-decoration-none mx-2 btn btn-primary mb-3" href="/admin/dashboard" title="Quản lý giáo xứ">Bảng điều khiển</a>
					<?php endif; ?>
					<?php if(!empty($gx)): ?>
					<a class="text-decoration-none mx-2 btn btn-success mb-3" href="/admin/parishioners" title="Quản lý giáo xứ">Quản lý giáo xứ</a>
					<?php endif; ?>
					<?php if(!empty($hs)): ?>
					<a class="text-decoration-none mx-2 btn btn-info mb-3" href="/admin/student" title="Quản lý giáo lý">Quản lý giáo lý</a>
					<?php endif; ?>
				</div>
				
				<div class="my-4">
					<img src="<?php echo e(asset('/uploads/chua chien lanh.jpg')); ?>" 
					     alt="Welcome Parish System"
					     class="img-fluid rounded shadow"
					     style="max-width: 500px;">
				</div>
				
				<h1 class="fs-3 fw-bolder mb-3">Xin đọc kỹ các hướng dẫn để import chuẩn dữ liệu. </h1>
				<div class="d-block d-md-flex justify-content-center">
					
					<a class="text-decoration-none mx-2 btn btn-info mb-3" href="/tai-lieu/huong-dan-import-quan-ly-giao-xu.docx">Hướng dẫn quản lý giáo xứ</a>
					<a class="text-decoration-none mx-2 btn btn-primary mb-3" href="/tai-lieu/huong-dan-import-danh-sach-lop.docx">Hướng dẫn quản lý thiếu nhi</a>
					<a class="text-decoration-none mx-2 btn btn-success mb-3" href="/tai-lieu/file-mau.zip">File nhập dữ liệu mẫu</a>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo e(mix('js/char.js')); ?>"></script>
<script src="<?php echo e(mix('js/apexcharts.min.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layout.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/frontend/helo.blade.php ENDPATH**/ ?>