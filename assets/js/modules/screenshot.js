/**
 * Capture d'écran
 * 
 * Reference file for feedback-widget.js lines 1676-1730
 * See main file: assets/js/feedback-widget.js
 * 
 * Methods included:
 * - 
showScreenshotPreview * - clearScreenshot * - handleCaptureSuccess * - handleCaptureError
 * 
 * @package Blazing_Feedback
 */

/* 
 * To view this section, read feedback-widget.js with:
 * offset=1676, limit=55
 */

        showScreenshotPreview: function(dataUrl) {
            if (!this.elements.screenshotPreview) return;

            const img = this.elements.screenshotPreview.querySelector('img');
            if (img) {
                img.src = dataUrl;
            }

            this.elements.screenshotPreview.hidden = false;
        },

        /**
         * Effacer le screenshot
         * @returns {void}
         */
        clearScreenshot: function() {
            this.state.screenshotData = null;

            if (this.elements.screenshotData) {
                this.elements.screenshotData.value = '';
            }

            if (this.elements.screenshotPreview) {
                this.elements.screenshotPreview.hidden = true;
                const img = this.elements.screenshotPreview.querySelector('img');
                if (img) {
                    img.src = '';
                }
            }
        },

        /**
         * Gérer le succès de capture
         * @param {CustomEvent} event - Événement
         * @returns {void}
         */
        handleCaptureSuccess: function(event) {
            // Capture gérée dans captureScreenshot
        },

        /**
         * Gérer l'erreur de capture
         * @param {CustomEvent} event - Événement
         * @returns {void}
         */
        handleCaptureError: function(event) {
            console.warn('[Blazing Feedback] Erreur de capture:', event.detail.error);
        },

        /**
         * Gérer l'annulation
         * @returns {void}
         */
        handleCancel: function() {
            // Supprimer le pin temporaire