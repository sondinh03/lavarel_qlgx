import 'bootstrap/dist/js/bootstrap.min.js';

jQuery(document).ready(function($) {
    $(function() {
        $(".vertical-menu .sub-menu").before('<i class="bi bi-chevron-down switch"></i>');
        $(".vertical-menu li i.switch").on('click', function() {
            var $submenu = $(this).next(".vertical-menu .sub-menu");
            $submenu.slideToggle(300);
            $submenu.parent().toggleClass("openmenu");
            $(this).toggleClass("rotated")
        });
        $("button.burger-menu").on('click', function() {
            $(".canvas-menu").toggleClass("open");
            $(".main-overlay").toggleClass("active");
            $('body').addClass("overflow-hidden");
        });
        $(".canvas-menu .btn-close, .main-overlay").on('click', function() {
            $(".canvas-menu").removeClass("open");
            $(".main-overlay").removeClass("active");
            $("#site-header-cart").removeClass("focus");
            $('body').removeClass("overflow-hidden");
        });
    });
});

jQuery(document).ready(function($) {
	//  Scroll back to top
    var progressPath = document.querySelector('.progress-wrap path');
    var pathLength = progressPath.getTotalLength();
    progressPath.style.transition = progressPath.style.WebkitTransition = 'none';
    progressPath.style.strokeDasharray = pathLength + ' ' + pathLength;
    progressPath.style.strokeDashoffset = pathLength;
    progressPath.getBoundingClientRect();
    progressPath.style.transition = progressPath.style.WebkitTransition = 'stroke-dashoffset 10ms linear';
    var updateProgress = function () {
        var scroll = $(window).scrollTop();
        var height = $(document).height() - $(window).height();
        var progress = pathLength - (scroll * pathLength / height);
        progressPath.style.strokeDashoffset = progress;
    }
    updateProgress();
    $(window).scroll(updateProgress);
    var offset = 150;
    var duration = 550;
    jQuery(window).on('scroll', function () {
        if (jQuery(this).scrollTop() > offset) {
            jQuery('.progress-wrap').addClass('active-progress');
        } else {
            jQuery('.progress-wrap').removeClass('active-progress');
        }
    });
    jQuery('.progress-wrap').on('click', function (event) {
        event.preventDefault();
        jQuery('html, body').animate({
            scrollTop: 0
        }, duration);
        return false;
    });
	
	$('.navbar .dropdown').hover(function () {
        $(this).find('.dropdown-menu').first().stop(true, true).slideDown(150);
    }, function () {
        $(this).find('.dropdown-menu').first().stop(true, true).slideUp(105)
    });
	
	$(function () {
	  	$('.time_start').datepicker();
	 	$('.time_stop').datepicker();
	});
	
	$.fn.datepicker.dates['en'] = {
	    days: ["Chủ nhật", "Thứ 2", "Thứ 3", "Thứ 4", "Thứ 5", "Thứ 6", "Thứ 7"],
	    daysShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
	    daysMin: ["CN", "T2", "T3", "T4", "T5", "T6", "T7"],
	    months: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
	    monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
	    today: "Today",
	    clear: "Clear",
	    format: "dd/mm/yyyy",
	    titleFormat: "MM yyyy",
	    weekStart: 0
	};
	

	// ______________Full screen
	$("#fullscreen-button").on("click", function toggleFullScreen() {
		if ((document.fullScreenElement !== undefined && document.fullScreenElement === null) || (document.msFullscreenElement !== undefined && document.msFullscreenElement === null) || (document.mozFullScreen !== undefined && !document.mozFullScreen) || (document.webkitIsFullScreen !== undefined && !document.webkitIsFullScreen)) {
	        if (document.documentElement.requestFullScreen) {
				document.documentElement.requestFullScreen();
	        } else if (document.documentElement.mozRequestFullScreen) {
	          document.documentElement.mozRequestFullScreen();
	        } else if (document.documentElement.webkitRequestFullScreen) {
	          document.documentElement.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
	        } else if (document.documentElement.msRequestFullscreen) {
	          document.documentElement.msRequestFullscreen();
	        }
		} else {
			if (document.cancelFullScreen) {
			  document.cancelFullScreen();
			} else if (document.mozCancelFullScreen) {
			  document.mozCancelFullScreen();
			} else if (document.webkitCancelFullScreen) {
			  document.webkitCancelFullScreen();
			} else if (document.msExitFullscreen) {
			  document.msExitFullscreen();
			}
		}
	});
});

import {Fancybox} from "@fancyapps/ui";
import Choices from "choices.js";
import validate from "validate.js";
import Alpine from 'alpinejs'
//import Autonumeric from 'autonumeric'


window.Fancybox = Fancybox
window.Choices = Choices
window.validate = validate
//window.Autonumeric = Autonumeric

window.loadCss = (cssURL) => {
    const link = document.createElement('link');
    link.id = hashCode(cssURL)
    link.rel = 'stylesheet';
    link.href = cssURL;
    if (!document.getElementById(link.id)) document.head.appendChild(link);
}

window.loadJs = (scriptURL) => {
    const script = document.createElement('script');
    script.id = hashCode(scriptURL)
    script.src = scriptURL;
    if (!document.getElementById(script.id)) document.body.appendChild(script);
}

window.hashCode = (str) => {
    let hash = '0';
    if (typeof str !== 'string' && str.length === 0) return hash;
    for (let i = 0; i < str.length; i++) {
        const char = str.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash; // Convert to 32bit integer
    }
    return hash;
}


window.Alpine = Alpine
Alpine.start()