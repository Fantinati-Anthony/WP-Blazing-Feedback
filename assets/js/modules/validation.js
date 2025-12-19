/**
 * Module Validation - Blazing Feedback
 * Validation de page
 * @package Blazing_Feedback
 */
(function(window) {
    'use strict';

    const Validation = {
        init: function(widget) {
            this.widget = widget;
        },

        /**
         * Mettre à jour la section de validation
         */
        updateValidationSection: function() {
            const section = this.widget.elements.pageValidation;
            if (!section) return;

            const feedbacks = this.widget.state.currentFeedbacks || [];
            const totalCount = feedbacks.length;
            const resolvedCount = feedbacks.filter(f => f.status === 'resolved' || f.status === 'rejected').length;
            const allResolved = totalCount > 0 && resolvedCount === totalCount;

            section.hidden = totalCount === 0;
            if (totalCount === 0) return;

            const statusIcon = this.widget.elements.validationStatus?.querySelector('.wpvfh-validation-icon');
            const statusText = this.widget.elements.validationStatus?.querySelector('.wpvfh-validation-text');

            if (allResolved) {
                if (statusIcon) statusIcon.textContent = '✅';
                if (statusText) statusText.textContent = 'Tous les points ont été traités';
                section.classList.remove('pending');
                section.classList.add('ready');
            } else {
                if (statusIcon) statusIcon.textContent = '⏳';
                if (statusText) statusText.textContent = `${resolvedCount}/${totalCount} points traités`;
                section.classList.remove('ready');
                section.classList.add('pending');
            }

            if (this.widget.elements.validatePageBtn) {
                const canValidate = allResolved || this.widget.config.canManage;
                this.widget.elements.validatePageBtn.disabled = !canValidate;
            }

            if (this.widget.elements.validationHint) {
                if (!allResolved) {
                    this.widget.elements.validationHint.textContent = 'Tous les points doivent être résolus ou rejetés avant validation.';
                } else {
                    this.widget.elements.validationHint.textContent = '';
                }
            }
        },

        /**
         * Afficher le modal de validation
         */
        showValidateModal: function() {
            if (this.widget.elements.validateModal) {
                this.widget.elements.validateModal.hidden = false;
            }
        },

        /**
         * Confirmer la validation de la page
         */
        confirmValidatePage: async function() {
            try {
                const currentUrl = this.widget.config.currentUrl || window.location.href;
                await this.widget.modules.api.request('POST', 'pages/validate', { url: currentUrl });

                this.widget.modules.notifications.show('Page validée avec succès !', 'success');

                if (this.widget.elements.validateModal) {
                    this.widget.elements.validateModal.hidden = true;
                }

                this.widget.elements.pageValidation.classList.remove('ready', 'pending');
                this.widget.elements.pageValidation.classList.add('validated');

                const statusIcon = this.widget.elements.validationStatus?.querySelector('.wpvfh-validation-icon');
                const statusText = this.widget.elements.validationStatus?.querySelector('.wpvfh-validation-text');
                if (statusIcon) statusIcon.textContent = '🎉';
                if (statusText) statusText.textContent = 'Page validée';

                if (this.widget.elements.validatePageBtn) {
                    this.widget.elements.validatePageBtn.hidden = true;
                }
            } catch (error) {
                console.error('[Blazing Feedback] Erreur validation:', error);
                this.widget.modules.notifications.show('Erreur lors de la validation', 'error');
            }
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.validation = Validation;

})(window);
