/**
 * Admin JavaScript for Content Scheduler Pro
 */

(function ($) {
    'use strict';

    /**
     * Handle copying of shortcodes.
     */
    function initShortcodeCopy() {
        $('.csp-copy-shortcode').on('click', function () {
            const shortcode = $(this).data('clipboard-text');
            const tempInput = $('<input>');
            
            $('body').append(tempInput);
            tempInput.val(shortcode).select();
            document.execCommand('copy');
            tempInput.remove();
            
            // Change button text temporarily
            const $button = $(this);
            const originalText = $button.text();
            
            $button.text('Copied!');
            setTimeout(function () {
                $button.text(originalText);
            }, 2000);
        });
    }

    /**
     * Initialize date validation.
     */
    function initDateValidation() {
        const $startDate = $('#csp_start_date');
        const $startTime = $('#csp_start_time');
        const $endDate = $('#csp_end_date');
        const $endTime = $('#csp_end_time');

        function validateDates() {
            const startDateTime = new Date($startDate.val() + 'T' + $startTime.val());
            const endDateTime = new Date($endDate.val() + 'T' + $endTime.val());
            
            if (startDateTime >= endDateTime) {
                alert('End date and time must be after start date and time.');
                
                // Set end date to one day after start date
                const newEndDate = new Date(startDateTime);
                newEndDate.setDate(newEndDate.getDate() + 1);
                
                // Format date for the input (YYYY-MM-DD)
                const year = newEndDate.getFullYear();
                const month = String(newEndDate.getMonth() + 1).padStart(2, '0');
                const day = String(newEndDate.getDate()).padStart(2, '0');
                
                $endDate.val(`${year}-${month}-${day}`);
            }
        }

        $startDate.on('change', validateDates);
        $startTime.on('change', validateDates);
        $endDate.on('change', validateDates);
        $endTime.on('change', validateDates);
    }

    /**
     * Initialize content preview functionality.
     */
    function initContentPreview() {
        // Create modal HTML if it doesn't exist
        if ($('.csp-preview-modal').length === 0) {
            $('body').append(`
                <div class="csp-preview-modal">
                    <div class="csp-preview-content">
                        <span class="csp-preview-close">&times;</span>
                        <div class="csp-preview-body"></div>
                    </div>
                </div>
            `);
        }

        // Handle preview link clicks
        $(document).on('click', '.csp-preview-link', function (e) {
            e.preventDefault();
            
            const postId = $(this).data('id');
            const $modal = $('.csp-preview-modal');
            const $modalBody = $('.csp-preview-body');
            
            $modalBody.html('<p>Loading preview...</p>');
            $modal.show();
            
            // Ajax call to get content
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'csp_preview_content',
                    post_id: postId,
                    nonce: cspAdminData.nonce
                },
                success: function (response) {
                    if (response.success) {
                        $modalBody.html(response.data.content);
                    } else {
                        $modalBody.html('<p>Error loading preview. Please try again.</p>');
                    }
                },
                error: function () {
                    $modalBody.html('<p>Error loading preview. Please try again.</p>');
                }
            });
        });

        // Close modal when clicking X or outside the modal
        $(document).on('click', '.csp-preview-close, .csp-preview-modal', function (e) {
            if (e.target === this) {
                $('.csp-preview-modal').hide();
            }
        });

        // Prevent closing when clicking inside the modal content
        $(document).on('click', '.csp-preview-content', function (e) {
            e.stopPropagation();
        });
    }

    /**
     * Initialize everything when the DOM is ready.
     */
    $(document).ready(function () {
        initShortcodeCopy();
        initDateValidation();
        initContentPreview();
    });

})(jQuery);