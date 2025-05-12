(function($) {
    var lazyloadnav_fadeDuration = lazyloadnav_settings.fade_duration || 300;
    var lazyloadnav_containerSelector = lazyloadnav_settings.container || '#content';
    var lazyloadnav_debugMode = lazyloadnav_settings.debug_mode || false;

    var lazyloadnav_AjaxPageLoader = {
        init: function() {
            if (lazyloadnav_debugMode) {
                console.log("Lazy Loading and Navigation: Debug mode enabled");
                console.log("Settings:", lazyloadnav_settings);
            }
            document.dispatchEvent(new CustomEvent('contentLoaded'));
            if (!$('#loading').length) {
                $('body').append(
                    '<div id="loading" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background-color:#fff; padding:10px; border:1px solid #ccc; z-index:1000;">' +
                    lazyloadnav_strings.loading + ' ...' +
                    '</div>'
                );
            }
        },
        showLoading: function() {
            if (lazyloadnav_debugMode) {
                console.log("Showing loading indicator");
            }
            $('#loading').show();
        },
        hideLoading: function() {
            if (lazyloadnav_debugMode) {
                console.log("Hiding loading indicator");
            }
            $('#loading').hide();
        },
        loadPage: function(urlPath) {
            if (lazyloadnav_debugMode) {
                console.log("Loading page:", urlPath);
            }
            this.showLoading();
            $.ajax({
                url: urlPath,
                dataType: 'html',
                success: function(response) {
                    if (lazyloadnav_debugMode) {
                        console.log("AJAX response received:", response);
                    }
                    var parsedHTML = $($.parseHTML(response, null, true));
                    var newTitle = parsedHTML.filter('title').text() || parsedHTML.find('title').text();
                    var newContent = parsedHTML.find(lazyloadnav_containerSelector).html();
                    if (newTitle) {
                        document.title = newTitle;
                    }
                    if (newContent) {
                        $(lazyloadnav_containerSelector).fadeOut(lazyloadnav_fadeDuration, function() {
                            $(this).html(newContent).fadeIn(lazyloadnav_fadeDuration);
                        });
                    }
                    lazyloadnav_AjaxPageLoader.hideLoading();
                    window.history.pushState({ html: newContent, pageTitle: newTitle }, "", urlPath);
                    document.dispatchEvent(new CustomEvent('contentLoaded'));
                },
                error: function() {
                    console.error("AJAX request failed for URL: " + urlPath);
                    lazyloadnav_AjaxPageLoader.hideLoading();
                }
            });
        }
    };

    $(document).ready(function() {
        lazyloadnav_AjaxPageLoader.init();
    });

    $(document).on('click', 'a', function(e) {
        var link = $(this);
        var href = link.attr('href');
        if (!href || href.indexOf('#') === 0 || link.attr('target') === '_blank' || href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) {
            return;
        }
        if (href.indexOf('http') === 0 && href.indexOf(window.location.origin) === -1) {
            return;
        }
        e.preventDefault();
        lazyloadnav_AjaxPageLoader.loadPage(href);
    });

    window.addEventListener("popstate", function(e) {
        if (e.state) {
            if (lazyloadnav_debugMode) {
                console.log("Popstate event triggered:", e.state);
            }
            $(lazyloadnav_containerSelector).fadeOut(lazyloadnav_fadeDuration, function() {
                $(this).html(e.state.html).fadeIn(lazyloadnav_fadeDuration);
            });
            document.title = e.state.pageTitle || document.title;
            document.dispatchEvent(new CustomEvent('contentLoaded'));
        }
    });
})(jQuery);
