
(function ($) {

    $(document).ready(function ($) {

        var data = {
            'action': 'cm_onboarding_widget',
            'post_id': cmob_data.post_id
        };

        if(typeof cmob_data.help_id !== 'undefined')
        {
            data['help_id'] = cmob_data.help_id;
        }

        var body = $('body');

        var init_widget = function () {

            $('#cmob-search').fastLiveFilter('#cmob-widget-content', {
                timeout: 200,
                'nothing_found': cmob_data.nothing_found
            });

        };

        $.ajax({
            url: cmob_data.ajaxurl,
            data: data,
            method: 'post'
        }).done(function (response) {
            var lib_url, stylesheet, widget_container;
            widget_container = $('<div id="cmob-widget-container-wrapper" class="' + cmob_data.side + ' ' + response.type + '"></div>').appendTo(body);

            if (!response || typeof response === 'undefined' || typeof response.body === 'undefined')
            {
                return;
            }

            widget_container.append(response.body);

            $('#cmob-widget-container-wrapper .cmob-btn-open').click(function () {
                $('#cmob-widget-container').toggleClass('show');
                $('#cmob-widget-container-wrapper .cmob-btn-clse').toggleClass('show');
                return false;
            });

            $('#cmob-widget-container-wrapper .cmob-btn-close').click(function () {
                $('#cmob-widget-container').toggleClass('show');
                $('#cmob-widget-container-wrapper .cmob-btn-open').toggleClass('show');
            });

            lib_url = cmob_data.js_path + 'widget.' + response.type + '.js';
            $.getScript(lib_url);

            lib_url = cmob_data.js_path + 'jquery.search.js';
            $.getScript(lib_url, init_widget);

            stylesheet = document.createElement('link');
            stylesheet.href = cmob_data.css_path + 'base.css';
            stylesheet.rel = 'stylesheet';
            stylesheet.type = 'text/css';
            document.getElementsByTagName('head')[0].appendChild(stylesheet);

            stylesheet = document.createElement('link');
            stylesheet.href = cmob_data.css_path + 'widget.' + response.type + '/' + response.theme + '.css';
            stylesheet.rel = 'stylesheet';
            stylesheet.type = 'text/css';
            document.getElementsByTagName('head')[0].appendChild(stylesheet);
        }).fail( function (response){
            console.log(response);
        });

    });

})(jQuery);