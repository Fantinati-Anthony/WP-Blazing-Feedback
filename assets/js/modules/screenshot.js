/**
 * Module Screenshot - Blazing Feedback
 * Capture d'écran
 * @package Blazing_Feedback
 */
(function(window) {
    'use strict';

    const Screenshot = {
        init: function(widget) {
            this.widget = widget;
        },

        /**
         * Capturer le screenshot
         */
        captureScreenshot: async function() {
            if (!window.BlazingScreenshot || !window.BlazingScreenshot.isAvailable()) {
                console.warn('[Blazing Feedback] Screenshot non disponible');
                return;
            }

            try {
                this.widget.modules.notifications.show(this.widget.config.i18n?.loadingMessage || 'Capture en cours...', 'info');

                const dataUrl = await window.BlazingScreenshot.capture();
                const resizedDataUrl = await window.BlazingScreenshot.resize(dataUrl, 1200, 900);

                this.widget.state.screenshotData = resizedDataUrl;

                if (this.widget.elements.screenshotData) {
                    this.widget.elements.screenshotData.value = resizedDataUrl;
                }

                this.showScreenshotPreview(resizedDataUrl);
            } catch (error) {
                console.error('[Blazing Feedback] Erreur de capture:', error);
                this.widget.modules.notifications.show('Erreur lors de la capture', 'error');
            }
        },

        /**
         * Afficher l'aperçu du screenshot
         */
        showScreenshotPreview: function(dataUrl) {
            const preview = this.widget.elements.screenshotPreview;
            if (!preview) return;

            const img = preview.querySelector('img');
            if (img) img.src = dataUrl;

            preview.hidden = false;
        },

        /**
         * Effacer le screenshot
         */
        clearScreenshot: function() {
            this.widget.state.screenshotData = null;

            if (this.widget.elements.screenshotData) {
                this.widget.elements.screenshotData.value = '';
            }

            if (this.widget.elements.screenshotPreview) {
                this.widget.elements.screenshotPreview.hidden = true;
                const img = this.widget.elements.screenshotPreview.querySelector('img');
                if (img) img.src = '';
            }
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.screenshot = Screenshot;

})(window);
