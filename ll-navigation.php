<?php
/**
 * Plugin Name: Lazy Loading and Navigation
 * Plugin URI: https://github.com/tayyebi/lazy-loading-and-navigation
 * Description: Loads page content via AJAX with a loading indicator.
 * Text Domain: lazy-loading-and-navigation
 * Version: 1.0.0
 * Author: MohammadReza Tayyebi
 * Author URI: https://gordarg.com
 * License: GPL2
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Define a debug constant to control verbose logging.
if ( ! defined( 'LL_NAVIGATION_DEBUG' ) ) {
    define( 'LL_NAVIGATION_DEBUG', true );
}

/**
 * Enqueue jQuery (if not already enqueued).
 */
function apl_enqueue_scripts() {
    wp_enqueue_script( 'jquery' );
}
add_action( 'wp_enqueue_scripts', 'apl_enqueue_scripts' );

/**
 * Output the inline AJAX page loader script using inline HTML.
 */
function apl_print_inline_script() {
    ?>
    <!-- Begin Lazy Loading and Navigation Inline Script -->
    <script type="text/javascript">
        // Get the debug flag from PHP.
        var debugEnabled = <?php echo ( defined( 'LL_NAVIGATION_DEBUG' ) && LL_NAVIGATION_DEBUG ) ? 'true' : 'false'; ?>;
        
        // Debug helper functions.
        function debugLog(message) {
            if (!debugEnabled) return;
            console.log(message);
        }
        function debugWarn(message) {
            if (!debugEnabled) return;
            console.warn(message);
        }
        function debugError(message) {
            if (!debugEnabled) return;
            console.error(message);
        }
        
        debugLog("Lazy Loading and Navigation: Debug logging enabled");
        
        // Localized strings.
        var strings = {
            "loading": "<?php echo esc_js( __( 'Loading', 'lazy-loading-and-navigation' ) ); ?>"
        };

        // Auto-detect the content container from common selectors.
        function getContentSelector() {
            var candidates = ['#content', 'main', '.site-main', '.entry-content'];
            for (var i = 0; i < candidates.length; i++) {
                if (jQuery(candidates[i]).length) {
                    debugLog("Detected content container: " + candidates[i]);
                    return candidates[i];
                }
            }
            debugWarn("No content container found. AJAX loading may not work as expected.");
            return null;
        }
        var contentSelector = getContentSelector();

        (function($) {
            var fadeDuration = 300; // Adjust fade duration (in milliseconds) as needed.
            
            var AjaxPageLoader = {
                // Initialization: dispatch event and append loading indicator.
                init: function() {
                    debugLog("AjaxPageLoader.init(): Initializing AJAX Page Loader");
                    document.dispatchEvent(new CustomEvent('contentLoaded'));
                    if (!$('#loading').length) {
                        $('body').append(
                            '<div id="loading" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background-color:#fff; padding:10px; border:1px solid #ccc; z-index:1000;">' +
                            strings.loading + ' ...' +
                            '</div>'
                        );
                        debugLog("AjaxPageLoader.init(): Loading indicator appended to the body.");
                    }
                },
                // Show the loading indicator.
                showLoading: function() {
                    debugLog("AjaxPageLoader.showLoading(): Showing loading indicator.");
                    $('#loading').show();
                },
                // Hide the loading indicator.
                hideLoading: function() {
                    debugLog("AjaxPageLoader.hideLoading(): Hiding loading indicator.");
                    $('#loading').hide();
                },
                // Load page content via AJAX.
                loadPage: function(urlPath) {
                    debugLog("AjaxPageLoader.loadPage(): Called with URL: " + urlPath);
                    this.showLoading();
                    $.ajax({
                        url: urlPath,
                        dataType: 'html',
                        success: function(response) {
                            debugLog("AjaxPageLoader.loadPage(): AJAX request succeeded for URL: " + urlPath);
                            
                            var parsedHTML = $($.parseHTML(response, null, true));
                            var newTitle = parsedHTML.filter('title').text() || parsedHTML.find('title').text();
                            debugLog("Extracted title: " + newTitle);
                            
                            var newContent = parsedHTML.find(contentSelector).html();
                            if (typeof newContent === 'undefined' || newContent === null) {
                                debugWarn("No content found using selector: " + contentSelector + ". Falling back to the full response.");
                                newContent = response;
                            }
                            if (newTitle) {
                                document.title = newTitle;
                                debugLog("Updated document title to: " + newTitle);
                            }
                            
                            if (contentSelector) {
                                $(contentSelector).fadeOut(fadeDuration, function(){
                                    $(this).html(newContent).fadeIn(fadeDuration, function(){
                                        debugLog("Updated content in container (" + contentSelector + ") with fade effect.");
                                    });
                                });
                            }
                            
                            AjaxPageLoader.hideLoading();
                            window.history.pushState({
                                html: newContent,
                                pageTitle: newTitle || document.title
                            }, "", urlPath);
                            debugLog("Pushed new state into history for URL: " + urlPath);
                            document.dispatchEvent(new CustomEvent('contentLoaded'));
                        },
                        error: function() {
                            debugError("AJAX request failed for URL: " + urlPath);
                            AjaxPageLoader.hideLoading();
                        }
                    });
                }
            };

            // Expose AjaxPageLoader globally.
            window.AjaxPageLoader = AjaxPageLoader;

            // Initialize when document is ready.
            $(document).ready(function() {
                AjaxPageLoader.init();
            });

            // Intercept internal link clicks for smooth AJAX navigation.
            $(document).on('click', 'a', function(e) {
                var link = $(this);
                var href = link.attr('href');
                if (!href) {
                    return;
                }
                // Allow anchors, external, mailto/tel links, or links opening in a new tab.
                if (
                    href.indexOf('#') === 0 ||
                    link.attr('target') === '_blank' ||
                    href.indexOf('mailto:') === 0 ||
                    href.indexOf('tel:') === 0
                ) {
                    return;
                }
                // If href is an absolute URL but on a different domain, don't intercept.
                if (href.indexOf('http') === 0 && href.indexOf(window.location.origin) === -1) {
                    return;
                }
                e.preventDefault();
                debugLog("Intercepted click on link: " + href);
                AjaxPageLoader.loadPage(href);
            });

            // Handle browser back/forward navigation.
            window.addEventListener("popstate", function(e) {
                if (e.state) {
                    debugLog("Detected popstate event.");
                    if (contentSelector) {
                        $(contentSelector).fadeOut(fadeDuration, function(){
                            $(this).html(e.state.html).fadeIn(fadeDuration, function(){
                                debugLog("Updated content from popstate in container (" + contentSelector + ") with fade effect.");
                            });
                        });
                    }
                    document.title = e.state.pageTitle || document.title;
                    document.dispatchEvent(new CustomEvent('contentLoaded'));
                }
            });
        })(jQuery);
    </script>
    <!-- End Lazy Loading and Navigation Inline Script -->
    <?php
}
add_action( 'wp_footer', 'apl_print_inline_script' );
