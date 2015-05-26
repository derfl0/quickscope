STUDIP.quickscope = {
    hooks: [
        {type: 'user', searchstring: 'dispatch.php/profile', idregex: /username=(.[^\&]*)/i},
        {type: 'course', searchstring: 'seminar_main.php', idregex: /auswahl=(.[^\&]*)/i},
        {type: 'course', searchstring: 'details.php', idregex: /sem_id=(.[^\&]*)/i},
        {type: 'course', searchstring: 'dispatch.php/course/details', idregex: /sem_id=(.[^\&]*)/i}
    ],
    init: function () {
        $.each(STUDIP.quickscope.hooks, function (id, hook) {
            STUDIP.quickscope.register(hook.type, hook.searchstring, hook.idregex);
        });
    },
    register: function (type, searchstring, idregex) {
        $('a[href*="' + searchstring + '"]').mouseenter(function (e) {
            var self = $(this);
            var position = $(this).position();
            var width = $(this).width();
            var href = $(this).attr('href');
            var idmatch = href.match(idregex);

            if (idmatch.length >= 1) {
                var id = href.match(idregex)[1];
                var quickscope = $('div.quickscope[data-quickscope="' + id + '"]');
                if (quickscope.length <= 0) {
                    timeout = setTimeout(function () {
                        var scope = $('<div>').addClass('quickscope').attr('data-quickscope', id);
                        $('body').prepend(scope);
                        $.ajax({
                            url: STUDIP.URLHelper.getURL('plugins.php/QuickscopePlugin/show/' + type + '/' + id),
                            method: "POST",
                            dataType: 'json'
                        }).done(function (data) {

                            // Build quickscope
                            $('div.quickscope[data-quickscope="' + id + '"]').append($('<img>', {class: 'quickscope_avatar', src: data.avatar}));
                            var text = $('<div>', {class: 'quickscope_text'});
                            $('div.quickscope[data-quickscope="' + id + '"]').append(text);
                            text.append($('<h3>', {html: data.header}));

                            $.each(data.text, function (id, entry) {
                                if ($.trim(entry) !== '') {
                                    text.append($('<p>', {html: entry}));
                                }
                            });

                            // Build menu
                            if ($('menu#menu-' + id).length <= 0) {
                                var contextmenu = $('<menu>', {id: 'menu-' + id, type: "context", class: "quickscope-contextmenu"});
                                $('body').append(contextmenu);

                                $.each(data.action, function (id, menu) {
                                    var appendix = "";
                                    if (menu.type) {
                                        var appendix = ", \""+menu.type+"\"";
                                    }

                                    var menuitem = $("<menuitem>", {label: menu.label,
                                        icon: menu.icon,
                                        onClick: "return STUDIP.quickscope.contextmenu(\""+menu.url+"\""+appendix+")"
                                    });
                                    contextmenu.append(menuitem);

                                });
                            }

                            // Check if attr was already appended
                            var attr = self.attr('contextmenu');
                            if (typeof attr === typeof undefined || attr === false) {
                                self.attr('contextmenu', 'menu-' + id);
                            }

                            // Make it appear
                            quickscope = $('div.quickscope[data-quickscope="' + id + '"]');
                            quickscope.css('left', position.left + (width / 2) - (quickscope.width() / 2));
                            quickscope.css('top', position.top - quickscope.outerHeight(true) - 30);
                        });
                    }, 300);
                }
                quickscope.css('left', position.left + (width / 2) - (quickscope.width() / 2));
                quickscope.css('top', position.top - quickscope.outerHeight(true) - 30);
                quickscope.fadeIn(300);
            }
        });

        // Mouseleave event
        $('a[href*="' + searchstring + '"]').mouseleave(function (e) {
            clearTimeout(timeout);
            $('div.quickscope').fadeOut(300);
        });
    },
    contextmenu: function (url, type) {
        switch (type) {
            case 'dialog':
                STUDIP.Dialog.fromURL(url);
                break;
            default:
                $.ajax({
                    url: STUDIP.URLHelper.getURL(url)
                });
        }
        return false;
    }
};
$(document).ready(function () {
    STUDIP.quickscope.init();
});