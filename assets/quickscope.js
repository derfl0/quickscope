$(document).ready(function () {
    $('a[href*="seminar_main.php"]').mouseenter(function (e) {
        var position = $(this).position();
        var width = $(this).width();
        var href = $(this).attr('href');
        var begin = href.indexOf("auswahl=") + 8;
        var id = href.substr(begin, 32);
        var quickscope = $('div.quickscope[data-quickscope="' + id + '"]');

        if (quickscope.length <= 0) {
            var scope = $('<div>').addClass('quickscope').attr('data-quickscope', id);
            $('body').prepend(scope);
            $.ajax({
                url: STUDIP.URLHelper.getURL('plugins.php/QuickscopePlugin/show/course/' + id),
                method: "POST",
                dataType: 'html'
            }).done(function (data) {
                $('div.quickscope[data-quickscope="' + id + '"]').html(data);
                quickscope = $('div.quickscope[data-quickscope="' + id + '"]');
                quickscope.css('left', position.left + (width / 2) - (quickscope.width() / 2));
                quickscope.css('top', position.top - quickscope.outerHeight(true) - 30);
            });
        }
        quickscope.css('left', position.left + (width / 2) - (quickscope.width() / 2));
        quickscope.css('top', position.top - quickscope.outerHeight(true) - 30);
        quickscope.fadeIn(300);
    });

    $('a[href*="seminar_main.php"]').mouseleave(function (e) {
        $('div.quickscope').fadeOut(300);
        $('div.quickscope.not-loaded').hide();
    });
});