<?php $__env->startSection('content'); ?>
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 px-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

        
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-slate-900">
                Đăng nhập hệ thống
            </h1>
            <p class="text-slate-500 mt-1 text-sm">
                Quản lý giáo lý giáo xứ
            </p>
        </div>

        
        <form method="POST" action="<?php echo e(route('login')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>

            
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    Email hoặc số điện thoại
                </label>
                <input
                    type="text"
                    name="email"
                    value="<?php echo e(old('email')); ?>"
                    required
                    autofocus
                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                           focus:ring-2 focus:ring-primary-500 focus:outline-none">
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-sm text-red-500 mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    Mật khẩu
                </label>
                <input
                    type="password"
                    name="password"
                    required
                    class="w-full px-3 py-2 rounded-xl border border-slate-300
                           focus:ring-2 focus:ring-primary-500 focus:outline-none">
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-sm text-red-500 mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="remember"
                        class="rounded border-slate-300 text-primary-600">
                    <span class="text-slate-600">Ghi nhớ đăng nhập</span>
                </label>

                <?php if(Route::has('password.request')): ?>
                <a href="<?php echo e(route('password.request')); ?>"
                    class="text-primary-600 hover:underline">
                    Quên mật khẩu?
                </a>
                <?php endif; ?>
            </div>

            
            <button
                type="submit"
                class="w-full py-2.5 rounded-xl bg-primary-600 text-white font-semibold
                       hover:bg-primary-700 transition">
                Đăng nhập
            </button>
        </form>

        
        <div class="text-center mt-6">
            <a href="<?php echo e(route('landing')); ?>"
                class="text-sm text-slate-500 hover:text-primary-600">
                ← Quay về trang tra cứu
            </a>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('frontend.layout.landing', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/auth/login.blade.php ENDPATH**/ ?>