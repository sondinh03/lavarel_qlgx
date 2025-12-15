
<footer class="text-center bg-white py-3 shadow fs-7">
	<p class="mb-1"><?php echo e(config('settings.copyright')); ?></p>
	<p class="mb-0"><?php echo e(config('settings.address')); ?></p>
</footer>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>    


<!-- 
<footer class="bg-white/95 backdrop-blur-sm border-t border-gray-200 py-6 text-center text-sm text-gray-600">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<p class="mb-1 font-medium"><?php echo e(config('settings.copyright')); ?></p>
		<p class="mb-0 text-gray-500"><?php echo e(config('settings.address')); ?></p>
	</div>
</footer>


<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<?php $__env->startPush('footer_scripts'); ?>
<script>
	document.addEventListener('DOMContentLoaded', function() {
		// Khởi tạo Flatpickr cho mọi input có class .datepicker
		document.querySelectorAll('.datepicker').forEach(function(element) {
			flatpickr(element, {
				dateFormat: "d/m/Y",
				locale: {
					firstDayOfWeek: 1,
					weekdays: {
						shorthand: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
						longhand: ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy']
					},
					months: {
						shorthand: ['Th1', 'Th2', 'Th3', 'Th4', 'Th5', 'Th6', 'Th7', 'Th8', 'Th9', 'Th10', 'Th11', 'Th12'],
						longhand: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12']
					}
				},
				theme: "light", // iOS-like
				allowInput: true,
				clickOpens: true
			});
		});
	});
</script>
<?php $__env->stopPush(); ?>


 --><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/frontend/layout/footer.blade.php ENDPATH**/ ?>