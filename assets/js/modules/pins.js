/**
 * Gestion des pins (placement, sélection)
 * 
 * Reference file for feedback-widget.js lines 1594-1690
 * See main file: assets/js/feedback-widget.js
 * 
 * Methods included:
 * - 
handlePinPlaced * - handlePinSelected
 * 
 * @package Blazing_Feedback
 */

/* 
 * To view this section, read feedback-widget.js with:
 * offset=1594, limit=97
 */

        handlePinPlaced: function(event) {
            const position = event.detail;

            // Stocker la position
            this.state.pinPosition = position;

            // Mettre à jour les champs cachés
            if (this.elements.positionX) {
                this.elements.positionX.value = position.percentX;
            }
            if (this.elements.positionY) {
                this.elements.positionY.value = position.percentY;
            }

            // Afficher l'info du pin
            if (this.elements.pinInfo) {
                this.elements.pinInfo.hidden = false;
            }

            // Capturer le screenshot si activé
            if (this.elements.screenshotToggle && this.elements.screenshotToggle.checked) {
                this.captureScreenshot();
            }

            // Ouvrir le panel
            this.state.feedbackMode = 'create';
            this.openPanel();
        },

        /**
         * Gérer la sélection d'un pin existant
         * @param {CustomEvent} event - Événement
         * @returns {void}
         */
        handlePinSelected: function(event) {
            const { feedbackId, pinData } = event.detail;

            if (!pinData) return;

            // Afficher les détails du feedback
            this.showFeedbackDetails(pinData);
        },

        /**
         * Capturer le screenshot
         * @returns {void}
         */
        captureScreenshot: async function() {
            if (!window.BlazingScreenshot || !window.BlazingScreenshot.isAvailable()) {
                console.warn('[Blazing Feedback] Screenshot non disponible');
                return;
            }

            try {
                this.showNotification(this.config.i18n?.loadingMessage || 'Capture en cours...', 'info');

                const dataUrl = await window.BlazingScreenshot.capture();

                // Redimensionner si nécessaire
                const resizedDataUrl = await window.BlazingScreenshot.resize(dataUrl, 1200, 900);

                this.state.screenshotData = resizedDataUrl;

                // Mettre à jour le champ caché
                if (this.elements.screenshotData) {
                    this.elements.screenshotData.value = resizedDataUrl;
                }

                // Afficher l'aperçu
                this.showScreenshotPreview(resizedDataUrl);

            } catch (error) {
                console.error('[Blazing Feedback] Erreur de capture:', error);
                this.showNotification('Erreur lors de la capture', 'error');
            }
        },

        /**
         * Afficher l'aperçu du screenshot
         * @param {string} dataUrl - Image en base64
         * @returns {void}
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