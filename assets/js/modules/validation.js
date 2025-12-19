/**
 * Validation de page, modales
 * 
 * Reference file for feedback-widget.js lines 2792-3050
 * See main file: assets/js/feedback-widget.js
 * 
 * Methods included:
 * - 
updateValidationSection * - showValidateModal * - handleValidatePage * - handleRejectPage * - loadPageStatus * - updateValidationUI
 * 
 * @package Blazing_Feedback
 */

/* 
 * To view this section, read feedback-widget.js with:
 * offset=2792, limit=259
 */

        updateValidationSection: function() {
            if (!this.elements.pageValidation) return;

            const feedbacks = this.state.currentFeedbacks || [];

            // Vérifier si tous les feedbacks sont traités
            const totalCount = feedbacks.length;
            const resolvedCount = feedbacks.filter(f => f.status === 'resolved' || f.status === 'rejected').length;
            const allResolved = totalCount > 0 && resolvedCount === totalCount;

            // Afficher la section seulement s'il y a des feedbacks
            this.elements.pageValidation.hidden = totalCount === 0;

            if (totalCount === 0) return;

            // Mettre à jour l'icône et le texte selon l'état
            const statusIcon = this.elements.validationStatus?.querySelector('.wpvfh-validation-icon');
            const statusText = this.elements.validationStatus?.querySelector('.wpvfh-validation-text');

            if (allResolved) {
                if (statusIcon) statusIcon.textContent = '✅';
                if (statusText) statusText.textContent = 'Tous les points ont été traités';
                this.elements.pageValidation.classList.remove('pending');
                this.elements.pageValidation.classList.add('ready');
            } else {
                if (statusIcon) statusIcon.textContent = '⏳';
                if (statusText) statusText.textContent = `${resolvedCount}/${totalCount} points traités`;
                this.elements.pageValidation.classList.remove('ready');
                this.elements.pageValidation.classList.add('pending');
            }

            // Activer/désactiver le bouton (admin peut toujours valider)
            if (this.elements.validatePageBtn) {
                const canValidate = allResolved || this.config.canManage;
                this.elements.validatePageBtn.disabled = !canValidate;
            }

            // Mettre à jour l'indice
            if (this.elements.validationHint) {
                if (!allResolved) {
                    this.elements.validationHint.textContent = 'Tous les points doivent être résolus ou rejetés avant validation.';
                } else {
                    this.elements.validationHint.textContent = '';
                }
            }
        },

        /**
         * Afficher le modal de validation de page
         */
        showValidateModal: function() {
            if (this.elements.validateModal) {
                this.elements.validateModal.hidden = false;
            }
        },

        /**
         * Confirmer la validation de la page
         */
        confirmValidatePage: async function() {
            try {
                const currentUrl = this.config.currentUrl || window.location.href;
                await this.apiRequest('POST', 'pages/validate', { url: currentUrl });

                this.showNotification('Page validée avec succès !', 'success');

                // Fermer le modal
                if (this.elements.validateModal) {
                    this.elements.validateModal.hidden = true;
                }

                // Mettre à jour l'affichage
                this.elements.pageValidation.classList.remove('ready', 'pending');
                this.elements.pageValidation.classList.add('validated');

                const statusIcon = this.elements.validationStatus?.querySelector('.wpvfh-validation-icon');
                const statusText = this.elements.validationStatus?.querySelector('.wpvfh-validation-text');
                if (statusIcon) statusIcon.textContent = '🎉';
                if (statusText) statusText.textContent = 'Page validée';

                if (this.elements.validatePageBtn) {
                    this.elements.validatePageBtn.hidden = true;
                }

            } catch (error) {
                console.error('[Blazing Feedback] Erreur validation:', error);
                this.showNotification('Erreur lors de la validation', 'error');
            }
        },

        // ===========================================
        // SUPPRESSION
        // ===========================================

        /**
         * Afficher le modal de suppression
         */
        showDeleteModal: function() {
            if (!this.state.currentFeedbackId) return;

            this.state.feedbackToDelete = this.state.currentFeedbackId;
            if (this.elements.confirmModal) {
                this.elements.confirmModal.hidden = false;
            }
        },

        /**
         * Afficher le modal de suppression pour un feedback spécifique
         * @param {number} feedbackId - ID du feedback
         */
        showDeleteModalForFeedback: function(feedbackId) {
            if (!feedbackId) return;

            this.state.feedbackToDelete = feedbackId;
            if (this.elements.confirmModal) {
                this.elements.confirmModal.hidden = false;
            }
        },

        /**
         * Masquer le modal de suppression
         */
        hideDeleteModal: function() {
            this.state.feedbackToDelete = null;
            if (this.elements.confirmModal) {
                this.elements.confirmModal.hidden = true;
            }
        },

        /**
         * Confirmer la suppression du feedback
         */
        confirmDeleteFeedback: async function() {
            const feedbackId = this.state.feedbackToDelete;
            if (!feedbackId) return;

            try {
                await this.apiRequest('DELETE', `feedbacks/${feedbackId}`);

                this.showNotification('Feedback supprimé', 'success');

                // Supprimer de la liste locale
                this.state.currentFeedbacks = this.state.currentFeedbacks.filter(f => f.id !== feedbackId);

                // Supprimer le pin sur la page
                if (window.BlazingAnnotation) {
                    window.BlazingAnnotation.removePin(feedbackId);
                }

                // Fermer le modal
                this.hideDeleteModal();

                // Retourner à la liste
                this.switchTab('list');

                // Mettre à jour les compteurs
                this.updateFilterCounts();
                if (this.elements.pinsCount) {
                    this.elements.pinsCount.textContent = this.state.currentFeedbacks.length > 0
                        ? this.state.currentFeedbacks.length : '';
                }
                if (this.elements.feedbackCount) {
                    const count = this.state.currentFeedbacks.length;
                    this.elements.feedbackCount.textContent = count;
                    this.elements.feedbackCount.hidden = count === 0;
                }

            } catch (error) {
                console.error('[Blazing Feedback] Erreur suppression:', error);
                this.showNotification('Erreur lors de la suppression', 'error');
            }
        },

        // ===========================================
        // PAGES
        // ===========================================

        /**
         * Charger la liste de toutes les pages avec feedbacks
         */
        loadAllPages: async function() {
            if (this.elements.pagesLoading) this.elements.pagesLoading.hidden = false;
            if (this.elements.pagesList) this.elements.pagesList.hidden = true;
            if (this.elements.pagesEmpty) this.elements.pagesEmpty.hidden = true;

            try {
                const response = await this.apiRequest('GET', 'pages');
                this.state.allPages = Array.isArray(response) ? response : [];

                this.renderPagesList();

            } catch (error) {
                console.error('[Blazing Feedback] Erreur chargement pages:', error);
                this.showNotification('Erreur lors du chargement des pages', 'error');
            } finally {
                if (this.elements.pagesLoading) this.elements.pagesLoading.hidden = true;
            }
        },

        /**
         * Afficher la liste des pages
         */
        renderPagesList: function() {
            if (!this.elements.pagesList) return;

            const pages = this.state.allPages || [];
            const currentUrl = this.config.currentUrl || window.location.href;

            // Afficher/masquer l'état vide
            if (this.elements.pagesEmpty) {
                this.elements.pagesEmpty.hidden = pages.length > 0;
            }
            this.elements.pagesList.hidden = pages.length === 0;

            if (pages.length === 0) return;

            // Générer le HTML
            const html = pages.map(page => {
                const isCurrent = page.url === currentUrl;
                const title = page.title || this.extractPageTitle(page.url);
                const shortUrl = this.shortenUrl(page.url);

                return `
                    <div class="wpvfh-page-item ${isCurrent ? 'current' : ''}" data-url="${this.escapeHtml(page.url)}">
                        <span class="wpvfh-page-icon">${page.validated ? '✅' : '📄'}</span>
                        <div class="wpvfh-page-info">
                            <div class="wpvfh-page-title">${this.escapeHtml(title)}</div>
                            <div class="wpvfh-page-url">${this.escapeHtml(shortUrl)}</div>
                        </div>
                        <span class="wpvfh-page-badge ${page.validated ? 'validated' : 'has-feedbacks'}">
                            ${page.validated ? '✓' : page.count || 0}
                        </span>
                    </div>
                `;
            }).join('');

            this.elements.pagesList.innerHTML = html;

            // Ajouter les événements
            this.elements.pagesList.querySelectorAll('.wpvfh-page-item').forEach(item => {
                item.addEventListener('click', () => {
                    const url = item.dataset.url;
                    if (url && url !== currentUrl) {
                        window.location.href = url;
                    } else {
                        // Page courante, aller à la liste
                        this.switchTab('list');
                    }
                });
            });
        },

        /**
         * Extraire un titre de l'URL
         * @param {string} url - URL
         * @returns {string} Titre
         */
        extractPageTitle: function(url) {
            try {