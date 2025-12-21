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
            // Construire la liste des statuts "traités" à partir de la config
            this.treatedStatuses = this.getTreatedStatuses();
        },

        /**
         * Récupérer les statuts considérés comme traités depuis la config
         */
        getTreatedStatuses: function() {
            const treatedList = [];
            const config = this.widget.config;

            // Chercher dans metadataGroups.statuses.items
            if (config.metadataGroups && config.metadataGroups.statuses && config.metadataGroups.statuses.items) {
                config.metadataGroups.statuses.items.forEach(status => {
                    if (status.is_treated) {
                        treatedList.push(status.id);
                    }
                });
            }

            // Fallback: si aucun statut n'est marqué comme traité, utiliser les valeurs par défaut
            if (treatedList.length === 0) {
                return ['resolved', 'rejected'];
            }

            return treatedList;
        },

        /**
         * Mettre à jour la section de validation
         */
        updateValidationSection: function() {
            // Utiliser validationStatus (wpvfh-validation-status) au lieu de pageValidation
            const section = this.widget.elements.validationStatus;

            const feedbacks = this.widget.state.currentFeedbacks || [];
            const totalCount = feedbacks.length;

            // Utiliser la liste des statuts traités depuis la config
            const resolvedCount = feedbacks.filter(f => this.treatedStatuses.includes(f.status)).length;
            const allResolved = totalCount > 0 && resolvedCount === totalCount;

            // Update progress bar
            const progressFill = document.getElementById('wpvfh-progress-fill');
            const progressText = document.getElementById('wpvfh-progress-text');

            if (progressFill) {
                const percentage = totalCount > 0 ? (resolvedCount / totalCount) * 100 : 0;
                progressFill.style.width = percentage + '%';
            }

            if (progressText) {
                progressText.textContent = `${resolvedCount}/${totalCount} traité`;
            }

            if (section) {
                if (allResolved) {
                    section.classList.remove('pending');
                    section.classList.add('ready');
                } else {
                    section.classList.remove('ready');
                    section.classList.add('pending');
                }
            }

            if (this.widget.elements.validatePageBtn) {
                const canValidate = allResolved || this.widget.config.canManage;
                this.widget.elements.validatePageBtn.disabled = !canValidate;
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

                const section = this.widget.elements.validationStatus;
                if (section) {
                    section.classList.remove('ready', 'pending');
                    section.classList.add('validated');
                }

                // Update progress bar to show complete
                const progressFill = document.getElementById('wpvfh-progress-fill');
                const progressText = document.getElementById('wpvfh-progress-text');
                if (progressFill) progressFill.style.width = '100%';
                if (progressText) progressText.textContent = '✅ Page validée';

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
