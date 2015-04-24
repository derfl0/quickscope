$(document).ready(function () {
    $('a[href*="seminar_main.php"]').mouseenter(function (e) {
        var position = $(this).position();
        var width = $(this).width();
        var href = $(this).attr('href');
        var begin = href.indexOf("auswahl=") + 8;
        var id = href.substr(begin, 32);
        var quickscope = $('div.quickscope[data-quickscope="'+id+'"]');

        if (quickscope.length <= 0) {
            var scope = $('<div>').addClass('quickscope').attr('data-quickscope', id);
            $.ajax({
                url: STUDIP.URLHelper.getURL('plugins.php/QuickscopePlugin/show/course/'+id),
                method: "POST",
                dataType: 'html'
            }).done(function (data) {
                $('div.quickscope[data-quickscope="'+id+'"]').replaceWith(data);
                quickscope = $('div.quickscope[data-quickscope="'+id+'"]');
                quickscope.css('left', position.left + (width / 2) - (quickscope.width() / 2));
                quickscope.css('top', position.top - quickscope.outerHeight(true) - 30);
            });
            $('body').prepend(scope);
        }
        quickscope.fadeIn(300);
        quickscope.css('left', position.left + (width / 2) - (quickscope.width() / 2));
        quickscope.css('top', position.top - quickscope.outerHeight(true) - 30);
    });

    $('a[href*="seminar_main.php"]').mouseleave(function (e) {
        $('div.quickscope').fadeOut(300);
    });
});