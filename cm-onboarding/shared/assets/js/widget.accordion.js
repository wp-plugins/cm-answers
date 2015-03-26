(function ($) {

        var init_widget = function () {
            $.fn.accordion.defaults.container = false;

            $("#cmob-widget-container .accordion").accordion({
                initShow: "#current"
            });
        };

        var lib_url = cmob_data.js_path + 'jquery.nestedAccordion.js';
        $.getScript(lib_url, init_widget);

})(jQuery);
