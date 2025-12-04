<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
<meta http-equiv='content-language' content="{{ str_replace('_', '-', app()->getLocale()) }}"/>
<link rel="apple-touch-icon" sizes="76x76" href="/favicon/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
<link rel="manifest" href="/favicon/site.webmanifest">
<link rel="mask-icon" href="/favicon/safari-pinned-tab.svg" color="#ed4238">
<meta name="msapplication-TileColor" content="#ed4238">
<meta name="theme-color" content="#ffffff">
<meta name="csrf-token" content="{{ csrf_token() }}">
{{-- SEO --}}
<meta name="google-site-verification" content="{{config('settings.google_site_verification')}}"/>
<meta name='description' content="@yield('meta_description')"/>
<meta name='keyword' content="@yield('meta_keyword')"/>
<meta property='og:locale' content="{{ app()->getLocale() ?: 'vi_VN' }}"/>
<meta property='og:type' content="@yield('meta_type', 'website')"/>
<meta property='og:title' content="@yield('title')"/>
<meta property='og:description' content="@yield('meta_description')"/>
<meta property='og:url' content="{{ url()->current() }}"/>
<meta property='og:site_name' content="{{ config('settings.web_name') }}"/>

<meta property='og:image' content="@yield('meta_image')"/>

<meta name="twitter:card" content="summary">
<meta name="twitter:site" content="@">
<meta name="twitter:title" content="@yield('title')">
<meta name="twitter:description" content="@yield('meta_description')">
<meta name="twitter:image" content="@yield('meta_image')">
@stack('metas')

<link rel="preconnect" href="//fonts.googleapis.com">
<link rel="preconnect" href="//fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="//cdn.datatables.net">
<link rel="preconnect" href="//cdnjs.cloudflare.com">
<link rel="preconnect" href="//ajax.googleapis.com">
<link rel="preconnect" href="//staticxx.facebook.com">

<link rel="preload" href="{{mix('css/style.css')}}" as="style">
<link rel="preload" href="{{mix('js/manifest.js')}}" as="script">
<link rel="preload" href="{{mix('js/vendor.js')}}" as="script">
<link rel="preload" href="{{mix('js/main.js')}}" as="script">
<link rel="preload" href="{{mix('js/char.js')}}" as="script">
<link rel="preload" href="{{mix('js/custom.js')}}" as="script">

<link rel="stylesheet" href="{{mix('css/app.css')}}">
<link rel="stylesheet" href="{{mix('css/style.css')}}">
<link rel="stylesheet" href="{{mix('css/responsive.css')}}">

<link rel="stylesheet" href="{{mix('assets/bootstrap-icons/font/bootstrap-icons.min.css')}}">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="{{asset('js/jquery.min.js')}}" type="text/javascript" defer></script>