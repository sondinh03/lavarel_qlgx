

<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-800">Bảng Điều Khiển</h1>
            <p class="text-sm text-gray-500">Chào mừng trở lại!</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
            <div class="p-4 bg-indigo-50 rounded-lg">
                <div class="text-sm text-gray-500">Tổng số lớp</div>
                <div class="text-3xl font-bold text-indigo-700"><?php echo e(\App\Models\Lop::count()); ?></div>
            </div>

            <div class="p-4 bg-green-50 rounded-lg">
                <div class="text-sm text-gray-500">Tổng số học sinh</div>
                <div class="text-3xl font-bold text-green-700"><?php echo e(\App\Models\Student::count()); ?></div>
            </div>

            <div class="p-4 bg-yellow-50 rounded-lg">
                <div class="text-sm text-gray-500">Báo cáo nhanh</div>
                <div class="text-3xl font-bold text-yellow-700">--</div>
            </div>
        </div>

        <div class="mt-6 text-sm text-gray-600">
            Đây là trang dashboard mẫu. Bạn có muốn tôi thêm các thành phần (biểu đồ, bảng, thống kê) cụ thể không?
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layout.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/frontend/dashboard.blade.php ENDPATH**/ ?>