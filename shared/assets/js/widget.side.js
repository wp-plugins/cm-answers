(function ($) {

    $('#cmob-widget-container .cmob-widget-nav li.link').click(function () {

        var link = $(this).find('a.title-link');

        if (link.length)
        {
            link[0].click();
            return;
        }
        $(this).addClass('active').siblings().removeClass('active');
        var id = $(this).attr('id');

        $('#cmob-widget-container .cmob-widget-content > ul').removeClass('show');
        $('#cmob-widget-container ul.' + id).addClass('show');
    });

    setTimeout(function () {

        $('#cmob-search').fastLiveFilter('#cmob-widget-nav', {
            timeout: 200
        });

    }, 100);

})(jQuery);