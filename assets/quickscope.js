STUDIP.quickscope = {
    hooks: [
        {type: 'user', searchstring: 'dispatch.php/profile', idregex: /username=(.[^\&]*)/i},
        {type: 'course', searchstring: 'seminar_main.php', idregex: /auswahl=(.[^\&]*)/i},
        {type: 'course', searchstring: 'details.php', idregex: /sem_id=(.[^\&]*)/i},
        {type: 'course', searchstring: 'dispatch.php/course/details', idregex: /sem_id=(.[^\&]*)/i},
        {type: 'course', searchstring: 'dispatch.php/course/overview', idregex: /cid=(.[^\&]*)/i}
    ],
    blacklist: ['ul#tabs'],
    init: function () {
        $.each(STUDIP.quickscope.hooks, function (id, hook) {
            STUDIP.quickscope.register(hook.type, hook.searchstring, hook.idregex);
        });
    },
    register: function (type, searchstring, idregex) {
        $('body').on('mouseenter', 'a[href*="' + searchstring + '"]', function (e) {
            var self = $(this);
            var position = $(this).position();
            var width = $(this).width();
            var href = $(this).attr('href');
            var idmatch = href.match(idregex);
            var blacklist;

            // Apply blacklist
            $.each(STUDIP.quickscope.blacklist, function (index, value) {
                if (self.parents(value).length > 0) {
                    blacklist = true;
                }
            });

            if (blacklist) {
                return false;
            }

            if (idmatch !== null && idmatch.length >= 1) {
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

                            if (data === null) {
                                return;
                            }

                            // Build quickscope
                            var quickscope = $('div.quickscope[data-quickscope="' + id + '"]');

                            if (data.avatar) {
                                quickscope.append($('<img>', {
                                    class: 'quickscope_avatar',
                                    src: data.avatar
                                }));
                            }

                            text = $('<div>', {class: 'quickscope_text'});
                            $('div.quickscope[data-quickscope="' + id + '"]').append(text);

                            if (data.header) {
                                text.append($('<h3>', {text: data.header}));
                            }

                            // Add texts
                            if (data.text) {
                                $.each(data.text, function (id, entry) {
                                    if ($.trim(entry) !== '') {
                                        text.append($('<p>', {html: entry}));
                                    }
                                });
                            }

                            // Add important texts
                            if (data.important) {
                                $.each(data.important, function (id, entry) {
                                    if ($.trim(entry) !== '') {
                                        text.append($('<p>', {class: 'important', text: entry}));
                                    }
                                });
                            }

                            // Add errors
                            if (data.error) {
                                $.each(data.error, function (id, entry) {
                                    if ($.trim(entry) !== '') {
                                        text.append($('<p>', {class: 'problem', text: entry}));
                                    }
                                });
                            }

                            // Make it appear
                            quickscope = $('div.quickscope[data-quickscope="' + id + '"]');
                            quickscope.css('left', e.pageX - (quickscope.width() / 2));
                            quickscope.css('top', e.pageY - quickscope.outerHeight(true) - 30);
                            quickscope.fadeIn(300);
                        });
                    }, 300);
                } else {
                    quickscope.css('left', e.pageX - (quickscope.width() / 2));
                    quickscope.css('top', e.pageY - quickscope.outerHeight(true) - 30);
                    quickscope.fadeIn(300);
                }
            }
        });

        // Mouseleave event
        $('body').on('mouseleave', 'a[href*="' + searchstring + '"]', function (e) {
            if (typeof timeout !== 'undefined') {
                clearTimeout(timeout);
            }
            $('div.quickscope').fadeOut(300);
        });
    }
};
$(document).ready(function () {
    STUDIP.quickscope.init();
});
