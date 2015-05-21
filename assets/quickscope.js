$(document).ready(function () {
    $('a[href*="seminar_main.php"], a[href*="dispatch.php/course/details"]').mouseenter(function (e) {
        var position = $(this).position();
        var width = $(this).width();
        var href = $(this).attr('href');
        if (href.contains('seminar_main.php')) {
            var begin = href.indexOf("auswahl=") + 8;
        } else {
            var begin = href.indexOf("sem_id=") + 7;
        }
        var id = href.substr(begin, 32);
        var quickscope = $('div.quickscope[data-quickscope="' + id + '"]');

        // Check if context menu exists
        if ($('menu#menu-' + id).length <= 0) {
            var icon = STUDIP.ASSETS_URL+"/images/icons/16/blue/door-enter.png";
            var menu = $('<menu>', {id: 'menu-' + id, type: "context", class: "quickscope-contextmenu"});
            menu.append($("<menuitem>", {label: "In Veranstaltung eintragen", icon: icon, onClick: ""}));
            $(this).append(menu);
            $(this).attr('contextmenu', 'menu-' + id);
        }

        if (quickscope.length <= 0) {
            timeout = setTimeout(function () {
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
            }, 300);
        }
        quickscope.css('left', position.left + (width / 2) - (quickscope.width() / 2));
        quickscope.css('top', position.top - quickscope.outerHeight(true) - 30);
        quickscope.fadeIn(300);
    });

    $('a[href*="seminar_main.php"], a[href*="dispatch.php/course/details"]').mouseleave(function (e) {
        clearTimeout(timeout);
        $('div.quickscope').fadeOut(300);
    });
});