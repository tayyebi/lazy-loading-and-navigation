<?php
/**
 * Plugin Name: Lazy Loading and Navigation
 * Plugin URI: https://github.com/tayyebi/ll-navigation
 * Description: Loads page content via AJAX with a loading indicator.
 * Text Domain: ll-navigation
 * Domain Path: /languages
 * Version: 1.0.0
 * Author: MohammadReza Tayyebi
 * Author URI: https://gordarg.com
 * License: GPL2
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Enqueue our inline AJAX page loader script.
 */
function apl_enqueue_scripts() {
    // Ensure jQuery is loaded.
    wp_enqueue_script( 'jquery' );

    // Register an empty script handle to attach our inline code.
    wp_register_script( 'll-navigation', false, array( 'jquery' ), '1.0.0', true );
    wp_enqueue_script( 'll-navigation' );

    // Localize the script to pass dynamic strings.
    wp_localize_script( 'll-navigation', 'strings', array(
        'loading' => __( 'Loading', 'll-navigation' )
    ) );

    // Inline JavaScript code.
    $inline_script = `EOT
(function($) {
    var AjaxPageLoader = {
        // Initialization function: dispatches an initial event and appends a loading indicator.
        init: function() {
            // Dispatch a custom event indicating the initial content is loaded.
            document.dispatchEvent(new CustomEvent('contentLoaded'));
            // Append the loading indicator if it doesn't already exist.
            if (!\$('#loading').length) {
                \$('body').append(
                    '<div id="loading" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background-color:#fff; padding:10px; border:1px solid #ccc; z-index:1000;">' +
                    strings.loading + ' ...' +
                    '</div>'
                );
            }
        },
        // Shows the loading indicator.
        showLoading: function() {
            \$('#loading').show();
        },
        // Hides the loading indicator.
        hideLoading: function() {
            \$('#loading').hide();
        },
        // Load page content via AJAX.
        loadPage: function(urlPath) {
            this.showLoading();
            \$.ajax({
                url: urlPath,
                dataType: 'html',
                success: function(response) {
                    // Parse the returned HTML.
                    var parsedHTML = \$($.parseHTML(response, null, true));
                    
                    // Attempt to extract the <title> tag content.
                    var newTitle = parsedHTML.filter('title').text() || parsedHTML.find('title').text();

                    // Extract content contained within an element with id "content".
                    var newContent = parsedHTML.find('#content').html();
                    if (typeof newContent === 'undefined') {
                        // Fallback: if #content isn't found, use the raw response.
                        newContent = response;
                    }
                    
                    // Update the document title.
                    if (newTitle) {
                        document.title = newTitle;
                    }
                    
                    // Replace the old content with new content.
                    \$('#content').html(newContent);
                    
                    // Hide the loading indicator.
                    AjaxPageLoader.hideLoading();
                    
                    // Update the browser history so the user can navigate back.
                    window.history.pushState({
                        html: newContent,
                        pageTitle: newTitle || document.title
                    }, "", urlPath);
                    
                    // Dispatch a custom event to signal the content has loaded.
                    document.dispatchEvent(new CustomEvent('contentLoaded'));
                },
                error: function() {
                    AjaxPageLoader.hideLoading();
                }
            });
        }
    };

    // Expose the AjaxPageLoader object to the global scope.
    window.AjaxPageLoader = AjaxPageLoader;

    // Initialize when the DOM is ready.
    \$(document).ready(function() {
        AjaxPageLoader.init();
    });

    // Handle browser navigation (back/forward buttons).
    window.addEventListener("popstate", function(e) {
        if (e.state) {
            \$('#content').html(e.state.html);
            document.title = e.state.pageTitle || document.title;
            document.dispatchEvent(new CustomEvent('contentLoaded'));
        }
    });
})(jQuery);
`;

    // Append the inline script to our registered handle.
    wp_add_inline_script( 'll-navigation', $inline_script );
}
add_action( 'wp_enqueue_scripts', 'apl_enqueue_scripts' );
    