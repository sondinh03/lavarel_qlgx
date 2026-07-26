const mix = require('laravel-mix');
require('laravel-mix-tailwind');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
	.extract(['vue', 'axios'])
    .postCss('resources/css/app.css', 'public/css', [
        //
    ])
    .tailwind('./tailwind.config.js');
	
mix.sass("resources/css/style.scss", "public/css")
mix.sass("resources/css/responsive.scss", "public/css")
mix.sass("resources/css/backpack-admin.scss", "public/css")
mix.js("resources/js/jquery.min.js", "public/js")
mix.js("resources/js/main.js", "public/js")
mix.js("resources/js/char.js", "public/js")
mix.js('resources/js/custom.js', 'public/js')
// Chỉ copy icon font (~6 file). Copy cả package sẽ ghi đè >2000 file SVG mỗi lần
// build, gây EBUSY trên Windows và không file nào trong số đó được dùng.
mix.copyDirectory("node_modules/bootstrap-icons/font", "public/assets/bootstrap-icons/font")
mix.copy("node_modules/apexcharts/dist/apexcharts.min.js", "public/js/apexcharts.min.js")
mix.copy("node_modules/apexcharts/dist/apexcharts.css", "public/css/apexcharts.css")

mix.webpackConfig({
    stats: {
        children: true
    }
})

mix.options({
    // Don't perform any css url rewriting by default
    processCssUrls: false,
    // postCss: [
    //     tailwindcss('./tailwind.config.js'),
    // ],
})

mix.extract(['@fancyapps/ui', 'dayjs', 'notiflix', 'choices.js', 'validate.js', 'alpinejs', 'autonumeric'])
mix.version();