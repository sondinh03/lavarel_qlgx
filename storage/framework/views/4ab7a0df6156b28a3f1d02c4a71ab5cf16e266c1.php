<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
<meta http-equiv='content-language' content="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>"/>

<link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
<link rel="icon" type="image/svg+xml" href="/favicon.svg" />
<link rel="shortcut icon" href="/favicon.ico" />
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
<meta name="apple-mobile-web-app-title" content="MVGX" />
<link rel="manifest" href="/site.webmanifest" />

<meta name="msapplication-TileColor" content="#ed4238">
<meta name="theme-color" content="#ffffff">
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

<meta name="google-site-verification" content="<?php echo e(config('settings.google_site_verification')); ?>"/>
<meta name='description' content="<?php echo $__env->yieldContent('meta_description'); ?>"/>
<meta name='keyword' content="<?php echo $__env->yieldContent('meta_keyword'); ?>"/>
<meta property='og:locale' content="<?php echo e(app()->getLocale() ?: 'vi_VN'); ?>"/>
<meta property='og:type' content="<?php echo $__env->yieldContent('meta_type', 'website'); ?>"/>
<meta property='og:title' content="<?php echo $__env->yieldContent('title'); ?>"/>
<meta property='og:description' content="<?php echo $__env->yieldContent('meta_description'); ?>"/>
<meta property='og:url' content="<?php echo e(url()->current()); ?>"/>
<meta property='og:site_name' content="<?php echo e(config('settings.web_name')); ?>"/>

<meta property='og:image' content="<?php echo $__env->yieldContent('meta_image'); ?>"/>

<meta name="twitter:card" content="summary">
<meta name="twitter:site" content="@">
<meta name="twitter:title" content="<?php echo $__env->yieldContent('title'); ?>">
<meta name="twitter:description" content="<?php echo $__env->yieldContent('meta_description'); ?>">
<meta name="twitter:image" content="<?php echo $__env->yieldContent('meta_image'); ?>">
<?php echo $__env->yieldPushContent('metas'); ?>

<link rel="preconnect" href="//fonts.googleapis.com">
<link rel="preconnect" href="//fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="//cdn.datatables.net">
<link rel="preconnect" href="//cdnjs.cloudflare.com">
<link rel="preconnect" href="//ajax.googleapis.com">
<link rel="preconnect" href="//staticxx.facebook.com">

<link rel="preload" href="<?php echo e(mix('css/style.css')); ?>" as="style">
<link rel="preload" href="<?php echo e(mix('js/manifest.js')); ?>" as="script">
<link rel="preload" href="<?php echo e(mix('js/vendor.js')); ?>" as="script">
<link rel="preload" href="<?php echo e(mix('js/main.js')); ?>" as="script">
<link rel="preload" href="<?php echo e(mix('js/char.js')); ?>" as="script">
<link rel="preload" href="<?php echo e(mix('js/custom.js')); ?>" as="script">

<link rel="stylesheet" href="<?php echo e(mix('css/app.css')); ?>">
<link rel="stylesheet" href="<?php echo e(mix('css/style.css')); ?>">
<link rel="stylesheet" href="<?php echo e(mix('css/responsive.css')); ?>">

<link rel="stylesheet" href="<?php echo e(mix('assets/bootstrap-icons/font/bootstrap-icons.min.css')); ?>">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="<?php echo e(asset('js/jquery.min.js')); ?>" type="text/javascript" defer></script><?php /**PATH D:\Document\WORKING\lavarel_qlgx\resources\views/frontend/layout/meta.blade.php ENDPATH**/ ?>