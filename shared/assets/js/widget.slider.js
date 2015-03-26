(function ($) {

    var slider;

    var init_widget = function () {

        var of_label = cmob_data.of_label || ' of ';

        slider = $('#cmob-widget-container .bxslider').bxSlider({
            slideSelector: 'li:not(.cmob-hidden)',
//                adaptiveHeight: true,
            pager: false,
            onSlideBefore: function () {
                $('.count').text((slider.getCurrentSlide() + 1) + of_label + slider.getSlideCount());
            }
        });

        $('#cmob-search').on('change', function () {
            slider.reloadSlider();
            $('.count').text((slider.getCurrentSlide() + 1) + of_label + slider.getSlideCount());
        });

        var stylesheet = document.createElement('link');
        stylesheet.href = cmob_data.css_path + 'widget.slider/jquery.bxslider.css';
        stylesheet.rel = 'stylesheet';
        stylesheet.type = 'text/css';
        document.getElementsByTagName('head')[0].appendChild(stylesheet);
    };

    var lib_url = cmob_data.js_path + 'jquery.bxslider.min.js';
    $.getScript(lib_url, init_widget);

})(jQuery);