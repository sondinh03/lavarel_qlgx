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
mix.copyDirectory("node_modules/bootstrap-icons", "public/assets/bootstrap-icons")	
mix.copyDirectory("node_modules/apexcharts/dist/apexcharts.min.js", "public/js/apexcharts.min.js")
mix.copyDirectory("node_modules/apexcharts/dist/apexcharts.css", "public/css/apexcharts.css")

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