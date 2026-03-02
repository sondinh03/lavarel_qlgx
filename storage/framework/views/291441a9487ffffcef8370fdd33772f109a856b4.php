<!DOCTYPE html>
<html lang="vi" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title><?php echo $__env->yieldContent('title', config('settings.web_name', 'Quản Lý Giáo Xứ')); ?></title>

    <?php if ($__env->exists('frontend.layout.meta')) echo $__env->make('frontend.layout.meta', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <link href="<?php echo e(mix('css/app.css')); ?>" rel="stylesheet">
    <?php echo \Livewire\Livewire::styles(); ?>

    <?php echo $__env->yieldPushContent('styles'); ?>

    <?php if(\App\Support\AuthUser::user()?->isCatechist()): ?>
    <style>
        /* Mobile-first styles for catechist */
        @media  screen and (-webkit-min-device-pixel-ratio:0) {

            select,
            textarea,
            input[type="text"],
            input[type="password"],
            input[type="datetime"],
            input[type="datetime-local"],
            input[type="date"],
            input[type="month"],
            input[type="time"],
            input[type="week"],
            input[type="number"],
            input[type="email"],
            input[type="url"] {
                font-size: 16px;
            }
        }

        .bottom-nav-safe {
            padding-bottom: calc(env(safe-area-inset-bottom) + 1rem);
        }

        .touch-feedback:active {
            transform: scale(0.97);
            opacity: 0.8;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
    <?php endif; ?>
</head>

<body class="min-h-screen bg-slate-50 text-slate-800 antialiased flex flex-col 
             <?php if(\App\Support\AuthUser::user()?->isCatechist()): ?> pb-20 <?php endif; ?>"
    x-data="{open:false, showMenu:false}">

    
    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.loading-indicator','data' => ['target' => 'selectedNamHoc,selectedKhoi,resetFilters']]); ?>
<?php $component->withName('loading-indicator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['target' => 'selectedNamHoc,selectedKhoi,resetFilters']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

    <?php if(\App\Support\AuthUser::user()?->isCatechist()): ?>
    
    <header class="sticky top-0 z-40 bg-gradient-to-r from-primary-500 to-primary-600 shadow-sm">
        <div class="flex items-center justify-between px-4 h-14">
            
            <div class="flex items-center gap-2.5">
                <img src="<?php echo e(url(config('settings.logo'))); ?>" class="h-7 w-auto" alt="Logo">
                
            </div>

            
            <div class="flex items-center gap-1">
                
                <button class="relative p-2 rounded-full text-white hover:bg-white/20 active:bg-white/30 transition touch-feedback">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </button>

                
                <button @click="showMenu = !showMenu"
                    class="w-7 h-7 rounded-full bg-white/20 border border-white/30 flex items-center justify-center text-white text-xs font-semibold hover:bg-white/30 active:bg-white/40 transition touch-feedback">
                    <?php echo e(strtoupper(substr(Auth::user()->name, 0, 1))); ?>

                </button>
            </div>
        </div>
    </header>

    
    <div x-cloak x-show="showMenu"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="showMenu = false"
        class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm">

        <div @click.stop
            x-show="showMenu"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="absolute right-0 top-0 bottom-0 w-80 max-w-[85vw] bg-white shadow-2xl">

            
            <div class="bg-gradient-to-r from-primary-500 to-primary-600 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-white">Menu</h2>
                    <button @click="showMenu = false"
                        class="p-2 rounded-full text-white hover:bg-white/20 active:bg-white/30 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-lg">
                        <?php echo e(substr(Auth::user()->name, 0, 1)); ?>

                    </div>
                    <div class="text-white">
                        <p class="font-semibold"><?php echo e(Auth::user()->name); ?></p>
                        <p class="text-xs text-primary-100">Giáo lý viên</p>
                    </div>
                </div>
            </div>

            
            <nav class="p-4 space-y-1">
                <a href="<?php echo e(route('dashboard')); ?>"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-primary-50 hover:text-primary-700 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="font-medium">Trang chủ</span>
                </a>

                <a href="<?php echo e(route('students.index')); ?>"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-primary-50 hover:text-primary-700 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="font-medium">Học sinh lớp tôi</span>
                </a>

                <a href="<?php echo e(route('attendance.show')); ?>"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-primary-50 hover:text-primary-700 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium">Lịch sử điểm danh</span>
                </a>

                <div class="my-4 border-t border-slate-200"></div>

                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span class="font-medium">Đăng xuất</span>
                    </button>
                </form>
            </nav>
        </div>
    </div>

    
    <div>
        <nav class="fixed bottom-0 left-0 right-0 z-30 bg-white border-t border-slate-200 shadow-lg bottom-nav-safe">
            <div class="grid grid-cols-4 gap-0 px-1 pt-1.5 pb-1">
                
                <a href="<?php echo e(route('dashboard')); ?>"
                    class="flex flex-col items-center gap-0.5 py-1 rounded-lg transition touch-feedback
                        <?php echo e(request()->routeIs('dashboard')
                            ? 'text-primary-600 bg-primary-50'
                            : 'text-slate-400 hover:text-primary-600 active:bg-slate-100 active:scale-95'); ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-[10px] font-medium leading-none">Trang chủ</span>
                </a>

                
                <a href="<?php echo e(route('students.index')); ?>"
                    class="flex flex-col items-center gap-0.5 py-1 rounded-lg transition touch-feedback
                        <?php echo e(request()->routeIs('students.*') ? 'text-primary-600 bg-primary-50'
                            : 'text-slate-400 hover:text-primary-600 active:bg-slate-100 active:scale-95'); ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="text-[10px] font-medium leading-none">Học sinh</span>
                </a>

                
                <a href="<?php echo e(route('attendance.show')); ?>"
                    class="flex flex-col items-center gap-0.5 py-1 rounded-lg transition touch-feedback
                        <?php echo e(request()->routeIs('attendance.*') ? 'text-primary-600 bg-primary-50'
                            : 'text-slate-400 hover:text-primary-600 active:bg-slate-100 active:scale-95'); ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span class="text-[10px] font-medium leading-none">Điểm danh</span>
                </a>

                
                <a href="<?php echo e(route('attendance.show')); ?>"
                    class="flex flex-col items-center gap-0.5 py-1 rounded-lg transition touch-feedback
                        <?php echo e(request()->routeIs('session.*') ? 'text-primary-600 bg-primary-50'
                            : 'text-slate-400 hover:text-primary-600 active:bg-slate-100 active:scale-95'); ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-[10px] font-medium leading-none">Lịch sử</span>
                </a>
            </div>
        </nav>
    </div>

    <?php else: ?>
    
    
    

    
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur border-b border-slate-200">
        <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="h-16 flex items-center justify-between">

                
                <a href="/" class="flex items-center gap-3">
                    <img src="<?php echo e(url(config('settings.logo'))); ?>" class="h-10 w-auto" alt="Logo">
                    <span class="hidden sm:block text-lg font-semibold text-primary-700">
                        <?php echo e(config('settings.web_name')); ?>

                    </span>
                </a>

                
                <nav class="hidden lg:flex items-center gap-2 flex-1 justify-center">

                    
                    <a href="/"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-slate-700 
                                  hover:bg-primary-50 hover:text-primary-700 transition-all whitespace-nowrap">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Trang chủ
                    </a>

                    
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            @click.outside="open = false"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-slate-700 
                                           hover:bg-primary-50 hover:text-primary-700 transition-all whitespace-nowrap">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Học Sinh
                            <svg class="w-4 h-4 transition-transform" :class="{'rotate-180': open}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute left-0 mt-2 w-64 bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden z-50">

                            <a href="<?php echo e(route('scores.index')); ?>"
                                class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-primary-50 hover:text-primary-700 transition-all">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <span class="font-medium">Kết quả học tập</span>
                            </a>

                            <a href="<?php echo e(route('attendance.show')); ?>"
                                class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-primary-50 hover:text-primary-700 transition-all">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <span class="font-medium">Điểm danh</span>
                            </a>

                            <a href="<?php echo e(route('classes.index')); ?>"
                                class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-primary-50 hover:text-primary-700 transition-all">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <span class="font-medium">Danh sách lớp</span>
                            </a>

                            <div class="border-t border-slate-200"></div>

                            <a href="<?php echo e(route('students.index')); ?>"
                                class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-primary-50 hover:text-primary-700 transition-all">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span class="font-medium">Quản lý học sinh</span>
                            </a>

                            <a href="<?php echo e(route('session.index')); ?>"
                                class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-primary-50 hover:text-primary-700 transition-all">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span class="font-medium">Quản lý phiên điểm danh</span>
                            </a>
                        </div>
                    </div>

                    
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            @click.outside="open = false"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-slate-700 
                                           hover:bg-primary-50 hover:text-primary-700 transition-all whitespace-nowrap">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Nhân Sự
                            <svg class="w-4 h-4 transition-transform" :class="{'rotate-180': open}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute left-0 mt-2 w-64 bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden z-50">

                            <a href="<?php echo e(route('catechists.index')); ?>"
                                class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-primary-50 hover:text-primary-700 transition-all">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="font-medium">Giáo lý viên</span>
                            </a>

                            <a href="<?php echo e(route('parishioners.index')); ?>"
                                class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-primary-50 hover:text-primary-700 transition-all">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="font-medium">Giáo dân</span>
                            </a>

                            <a href="<?php echo e(route('catechists.import')); ?>"
                                class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-primary-50 hover:text-primary-700 transition-all">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="font-medium">Import giáo lý viên</span>
                            </a>
                        </div>
                    </div>

                    
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            @click.outside="open = false"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-slate-700 
                                           hover:bg-primary-50 hover:text-primary-700 transition-all whitespace-nowrap">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Hệ Thống
                            <svg class="w-4 h-4 transition-transform" :class="{'rotate-180': open}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute left-0 mt-2 w-64 bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden z-50">

                            <a href="<?php echo e(route('school-years.index')); ?>"
                                class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-primary-50 hover:text-primary-700 transition-all">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="font-medium">Năm học</span>
                            </a>

                            <a href="<?php echo e(route('grades.index')); ?>"
                                class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-primary-50 hover:text-primary-700 transition-all">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <span class="font-medium">Khối</span>
                            </a>

                            <a href="<?php echo e(route('classes.index')); ?>"
                                class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-primary-50 hover:text-primary-700 transition-all">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                </svg>
                                <span class="font-medium">Lớp</span>
                            </a>

                            <div class="border-t border-slate-200"></div>

                            <a href="<?php echo e(route('parish-children.index')); ?>"
                                class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-primary-50 hover:text-primary-700 transition-all">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span class="font-medium">Giáo họ</span>
                            </a>

                            <a href="<?php echo e(route('holy-names.index')); ?>"
                                class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-primary-50 hover:text-primary-700 transition-all">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                                <span class="font-medium">Tên thánh</span>
                            </a>

                            <a href="<?php echo e(route('parishioners.index')); ?>"
                                class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-primary-50 hover:text-primary-700 transition-all">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                                <span class="font-medium">Giáo dân</span>
                            </a>
                        </div>
                    </div>

                    
                    <?php if(auth()->guard()->guest()): ?>
                    <a href="<?php echo e(route('login')); ?>"
                        class="hidden md:inline-flex items-center gap-2 px-4 py-2 rounded-xl
                  text-sm font-semibold text-white
                  bg-primary-600 hover:bg-primary-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 3h4a2 2 0 012 2v4m0 6v4a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3" />
                        </svg>
                        Đăng nhập
                    </a>
                    <?php endif; ?>

                    <?php if(auth()->guard()->check()): ?>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            @click.outside="open = false"
                            class="flex items-center gap-2 px-3 py-2 rounded-xl
                           text-sm font-semibold text-slate-700
                           hover:bg-slate-100 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <?php echo e(Auth::user()->name ?? 'Tài khoản'); ?>

                            <svg class="w-4 h-4" :class="{ 'rotate-180': open }"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open"
                            x-transition
                            class="absolute right-0 mt-2 w-48 bg-white border border-slate-200
                        rounded-xl shadow-lg overflow-hidden z-50">

                            <a href="<?php echo e(route('dashboard')); ?>"
                                class="block px-4 py-3 text-sm hover:bg-slate-100">
                                Trang quản trị
                            </a>

                            <form method="POST" action="<?php echo e(route('logout')); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit"
                                    class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50">
                                    Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>

                </nav>

                
                <div class="flex items-center gap-3">
                    <button id="fullscreen-button" class="p-2 rounded-lg hover:bg-slate-100 text-slate-600 hover:text-primary-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                    </button>

                    <button @click="open = !open" class="md:hidden p-2 rounded-lg hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        
        <div x-show="open" x-transition @click.outside="open=false" class="md:hidden bg-white border-t border-slate-200">
            <nav class="px-4 py-4 space-y-2">
                <a href="/" class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-primary-50 hover:text-primary-700">
                    Trang chủ
                </a>
                <a href="<?php echo e(route('students.index')); ?>" class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-primary-50 hover:text-primary-700">
                    Học sinh
                </a>

                <?php if(auth()->guard()->guest()): ?>
                <a href="<?php echo e(route('login')); ?>"
                    class="block px-3 py-2 rounded-lg text-white bg-primary-600 hover:bg-primary-700">
                    Đăng nhập
                </a>
                <?php endif; ?>

                <?php if(auth()->guard()->check()): ?>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit"
                        class="block w-full text-left px-3 py-2 rounded-lg text-red-600 hover:bg-red-50">
                        Đăng xuất
                    </button>
                </form>
                <?php endif; ?>

            </nav>
        </div>
    </header>

    
    <button
        onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
        class="fixed bottom-6 right-6 z-40 w-12 h-12 
               bg-gradient-to-r from-primary-500 to-primary-600 
               hover:from-primary-600 hover:to-primary-700
               text-white rounded-full shadow-lg
               flex items-center justify-center
               transition-all hover:scale-110 active:scale-95
               focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
               <?php if(\App\Support\AuthUser::user()?->isCatechist()): ?> bottom-24 <?php endif; ?>"
        aria-label="Scroll to top">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </button>
    <?php endif; ?>

    
    <main class="flex-1">
        <div class="w-full mx-auto px-0 sm:px-6 lg:px-8 py-2 space-y-6">
            <?php echo $__env->yieldContent('content_top'); ?>
            <div class="w-full">
                <section>
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-0 sm:p-6">
                        <?php echo $__env->yieldContent('content'); ?>
                    </div>
                </section>
            </div>
            <?php echo $__env->yieldContent('content_bottom'); ?>
        </div>
    </main>



    
    <?php echo $__env->make('frontend.layout.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    

    
    <script src="<?php echo e(mix('js/manifest.js')); ?>"></script>
    <script src="<?php echo e(mix('js/vendor.js')); ?>"></script>
    <script src="<?php echo e(mix('js/app.js')); ?>"></script>

    
    <script>
        document.getElementById('fullscreen-button')?.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.log('Fullscreen error:', err);
                });
            } else {
                document.exitFullscreen();
            }
        });
    </script>

    <?php echo $__env->yieldPushContent('scripts'); ?>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <?php echo \Livewire\Livewire::scripts(); ?>

</body>

</html><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/frontend/layout/main.blade.php ENDPATH**/ ?>