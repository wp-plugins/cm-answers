(function ($) {

    var init_widget = function () {
        $('#cmob-widget-container select').selectric(
                {
                    maxHeight: 150,
                    onChange: function (element) {
                        var $this = $(this);
                        var selected = $this.find(':selected');
                        var url = selected.data('url');

                        if (typeof url !== 'undefined' && url.length)
                        {
                            window.open(url, '_blank');
                            return false;
                        }
                        else
                        {
                            $(element).change();
                        }
                    },
                    optionsItemBuilder: function (itemData, element, index)
                    {
                        var html;
                        var url = element.data('url');
                        html = (typeof url !== 'undefined' && url.length) ? '<a href="' + url + '" class="cmob-external-link">' + itemData.text + '</a>' : itemData.text;
                        return html;
                    }
                }
        );

        $('#cmob-search').fastLiveFilter('.selectricWrapper .selectricScroll ul', {
            timeout: 200
        });
    };

    $("select").change(function () {
        var id = $(this).children(":selected").attr("id");
        var idStr = id.toString();
        $('.cmob-widget-content ul').removeClass('show');
        $('.' + idStr).addClass('show');
    });

    var lib_url = cmob_data.js_path + 'jquery.selectric.js';
    $.getScript(lib_url, init_widget);

})(jQuery);