/**
 * Wpp Table — JavaScript for Enhanced UX
 *
 * Enables AJAX-powered search and pagination without full page reloads.
 * Automatically detects and enhances Wpp_List_Table instances.
 *
 * Features:
 * - Smooth AJAX loading
 * - URL history management
 * - Loading state
 * - Mobile-friendly
 *
 * @version 2.2.0
 * @author Your Name
 * @license GPL-2.0-or-later
 */

(function ($) {
    'use strict';

    /**
     * Initialize Wpp Table AJAX functionality
     */
    function initWppTable() {
        const config = typeof wppTableConfig !== 'undefined' ? wppTableConfig : null;

        if (!config || !config.containerId) {
            return;
        }

        const $container = $('#' + config.containerId);

        if ($container.length === 0) {
            return;
        }

        // Handle pagination and search submission
        $container.on('click', '.wpp-pagination a, .wpp-table-search-form button[type="submit"]', function (e) {
            e.preventDefault();

            const $form = $(this).closest('form');
            const url = $(this).is('a') ? $(this).attr('href') : $form.attr('action');
            const method = $form.length ? 'GET' : 'GET';

            // Show loading state
            $container.addClass('loading');

            // Perform AJAX request
            $.ajax({
                url: url,
                type: method,
                dataType: 'html',
                success: function (data) {
                    try {
                        // Extract updated container from response
                        const $newContent = $(data).find('#' + config.containerId);

                        if ($newContent.length > 0) {
                            $container.replaceWith($newContent);
                        } else {
                            // Fallback: reload page
                            window.location.href = url;
                        }

                        // Update browser history
                        if (window.history.pushState) {
                            window.history.pushState({ wppTable: true }, '', url);
                        }
                    } catch (err) {
                        console.error('Wpp Table: Failed to parse AJAX response', err);
                        window.location.href = url;
                    }
                },
                error: function () {
                    alert('Failed to load data. Please try again.');
                    $container.removeClass('loading');
                }
            });
        });

        // Handle browser back/forward buttons
        $(window).on('popstate', function (e) {
            if (e.originalEvent.state && e.originalEvent.state.wppTable) {
                location.reload();
            }
        });
    }

    // Initialize on DOM ready
    $(document).ready(function () {
        initWppTable();
    });

    // Reinitialize after dynamic content update (if needed)
    $(document).on('wpp_table_updated', function () {
        initWppTable();
    });

})(jQuery);