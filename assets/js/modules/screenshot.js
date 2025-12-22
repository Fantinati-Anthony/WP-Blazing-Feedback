/**
 * Module Screenshot - Blazing Feedback
 * Capture d'écran (viewport et pleine page)
 * @package Blazing_Feedback
 */
(function(window) {
    'use strict';

    const Screenshot = {
        init: function(widget) {
            this.widget = widget;
            this.isFullPageCapture = false;
            this.setupFullPageProgressListener();
        },

        /**
         * Écouter les événements de progression de capture pleine page
         */
        setupFullPageProgressListener: function() {
            document.addEventListener('blazing-feedback:fullpage-capture-progress', (e) => {
                const { current, total, percent } = e.detail;
                this.widget.modules.notifications.show(
                    `Capture pleine page: ${current}/${total} (${percent}%)`,
                    'info'
                );
            });
        },

        /**
         * Capturer le screenshot (viewport uniquement)
         */
        captureScreenshot: async function() {
            if (!window.BlazingScreenshot || !window.BlazingScreenshot.isAvailable()) {
                console.warn('[Blazing Feedback] Screenshot non disponible');
                return;
            }

            try {
                this.widget.modules.notifications.show(this.widget.config.i18n?.loadingMessage || 'Capture en cours...', 'info');

                const dataUrl = await window.BlazingScreenshot.capture();

                // Use screen resolution for better quality
                const maxWidth = Math.min(window.screen.width * (window.devicePixelRatio || 1), 2560);
                const maxHeight = Math.min(window.screen.height * (window.devicePixelRatio || 1), 1600);
                const resizedDataUrl = await window.BlazingScreenshot.resize(dataUrl, maxWidth, maxHeight);

                // Ouvrir l'éditeur d'annotation
                if (window.BlazingScreenshotEditor) {
                    window.BlazingScreenshotEditor.open(resizedDataUrl, (editedDataUrl) => {
                        this.applyScreenshot(editedDataUrl);
                    });
                } else {
                    // Fallback si l'éditeur n'est pas disponible
                    this.applyScreenshot(resizedDataUrl);
                }
            } catch (error) {
                console.error('[Blazing Feedback] Erreur de capture:', error);
                this.widget.modules.notifications.show('Erreur lors de la capture', 'error');
            }
        },

        /**
         * Capturer la page complète (avec défilement)
         * @param {Object} options - Options de capture
         */
        captureFullPage: async function(options = {}) {
            if (!window.BlazingScreenshot || !window.BlazingScreenshot.isAvailable()) {
                console.warn('[Blazing Feedback] Screenshot non disponible');
                return;
            }

            if (!window.BlazingScreenshot.captureFullPage) {
                console.warn('[Blazing Feedback] Capture pleine page non disponible');
                this.widget.modules.notifications.show('Capture pleine page non disponible', 'error');
                return;
            }

            try {
                this.isFullPageCapture = true;
                this.widget.modules.notifications.show('Capture pleine page en cours...', 'info');

                // Fermer temporairement le panneau pour éviter qu'il soit visible
                const panel = this.widget.elements.panel;
                const wasPanelOpen = panel && !panel.classList.contains('wpvfh-panel-hidden');
                if (wasPanelOpen) {
                    panel.classList.add('wpvfh-panel-hidden');
                }

                const dataUrl = await window.BlazingScreenshot.captureFullPage({
                    scrollDelay: options.scrollDelay || 400,
                    overlap: options.overlap || 100,
                    maxHeight: options.maxHeight || 50000,
                    quality: options.quality || 0.9
                });

                // Réouvrir le panneau
                if (wasPanelOpen) {
                    panel.classList.remove('wpvfh-panel-hidden');
                }

                // Redimensionner si l'image est trop grande (max 4000px de large)
                const maxWidth = 4000;
                const resizedDataUrl = await window.BlazingScreenshot.resize(dataUrl, maxWidth, 50000);

                // Ouvrir l'éditeur d'annotation
                if (window.BlazingScreenshotEditor) {
                    window.BlazingScreenshotEditor.open(resizedDataUrl, (editedDataUrl) => {
                        this.applyScreenshot(editedDataUrl);
                    });
                } else {
                    this.applyScreenshot(resizedDataUrl);
                }

                this.widget.modules.notifications.show('Capture pleine page terminée', 'success');

            } catch (error) {
                console.error('[Blazing Feedback] Erreur capture pleine page:', error);
                this.widget.modules.notifications.show('Erreur lors de la capture pleine page', 'error');
            } finally {
                this.isFullPageCapture = false;
            }
        },

        /**
         * Appliquer le screenshot (après édition ou directement)
         */
        applyScreenshot: function(dataUrl) {
            this.widget.state.screenshotData = dataUrl;

            if (this.widget.elements.screenshotData) {
                this.widget.elements.screenshotData.value = dataUrl;
            }

            this.showScreenshotPreview(dataUrl);
            this.widget.modules.notifications.show('Capture enregistrée', 'success');
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
