/**
 * Pièces jointes
 * 
 * Reference file for feedback-widget.js lines 4000-4268
 * See main file: assets/js/feedback-widget.js
 * 
 * Methods included:
 * - 
handleFileSelect * - renderAttachments * - removeAttachment
 * 
 * @package Blazing_Feedback
 */

/* 
 * To view this section, read feedback-widget.js with:
 * offset=4000, limit=269
 */

            // Déplacer le modal de recherche dans le panel (après le header)
            if (this.elements.searchModal && this.elements.panel) {
                const panelHeader = this.elements.panel.querySelector('.wpvfh-panel-header');
                if (panelHeader && this.elements.searchModal.parentNode !== this.elements.panel) {
                    panelHeader.after(this.elements.searchModal);
                }
            }

            // Afficher le contenu de recherche dans le panel
            if (this.elements.searchModal) {
                this.elements.searchModal.hidden = false;
                this.elements.searchModal.classList.add('active');
                this.elements.searchModal.classList.add('wpvfh-search-inline');
            }

            // Ouvrir le panel s'il n'est pas ouvert
            if (this.elements.panel && !this.elements.panel.classList.contains('active')) {
                this.openPanel();
            }
        },

        /**
         * Fermer la recherche (restaure le panel-body)
         */
        closeSearchModal: function() {
            // Masquer le contenu de recherche
            if (this.elements.searchModal) {
                this.elements.searchModal.hidden = true;
                this.elements.searchModal.classList.remove('active');
                this.elements.searchModal.classList.remove('wpvfh-search-inline');
            }

            // Restaurer le panel-body, le footer et les tabs
            const panelBody = document.querySelector('.wpvfh-panel-body');
            const panelFooter = document.querySelector('.wpvfh-panel-footer');
            const panelTabs = document.querySelector('.wpvfh-tabs');
            if (panelBody) {
                panelBody.style.display = '';
            }
            if (panelFooter) {
                panelFooter.style.display = '';
            }
            if (panelTabs) {
                panelTabs.style.display = '';
            }
        },

        /**
         * Réinitialiser la recherche
         */
        resetSearch: function() {
            if (this.elements.searchId) this.elements.searchId.value = '';
            if (this.elements.searchText) this.elements.searchText.value = '';
            if (this.elements.searchStatus) this.elements.searchStatus.value = '';
            if (this.elements.searchPriority) this.elements.searchPriority.value = '';
            if (this.elements.searchAuthor) this.elements.searchAuthor.value = '';
            if (this.elements.searchDateFrom) this.elements.searchDateFrom.value = '';
            if (this.elements.searchDateTo) this.elements.searchDateTo.value = '';

            if (this.elements.searchResults) {
                this.elements.searchResults.hidden = true;
                this.elements.searchResults.classList.remove('active');
            }
            if (this.elements.searchResultsList) {
                this.elements.searchResultsList.innerHTML = '';
            }
        },

        /**
         * Effectuer la recherche
         */
        performSearch: async function() {
            const criteria = {
                id: this.elements.searchId ? this.elements.searchId.value.trim() : '',
                text: this.elements.searchText ? this.elements.searchText.value.trim() : '',
                status: this.elements.searchStatus ? this.elements.searchStatus.value : '',
                priority: this.elements.searchPriority ? this.elements.searchPriority.value : '',
                author: this.elements.searchAuthor ? this.elements.searchAuthor.value.trim() : '',
                dateFrom: this.elements.searchDateFrom ? this.elements.searchDateFrom.value : '',
                dateTo: this.elements.searchDateTo ? this.elements.searchDateTo.value : '',
            };

            // Filtrer tous les feedbacks (localement)
            let results = [];

            // Recherche sur toutes les pages
            try {
                const response = await this.apiRequest('GET', 'feedback/search', criteria);
                results = response.feedbacks || [];
            } catch (error) {
                // Si l'API search n'existe pas, filtrer localement
                results = this.filterFeedbacksLocally(criteria);
            }

            this.displaySearchResults(results);
        },

        /**
         * Filtrer les feedbacks localement
         */
        filterFeedbacksLocally: function(criteria) {
            let results = [...(this.state.currentFeedbacks || [])];

            // Filtrer par ID
            if (criteria.id) {
                const searchId = parseInt(criteria.id, 10);
                results = results.filter(f => f.id === searchId);
            }

            // Filtrer par texte
            if (criteria.text) {
                const searchText = criteria.text.toLowerCase();
                results = results.filter(f =>
                    (f.comment && f.comment.toLowerCase().includes(searchText)) ||
                    (f.transcript && f.transcript.toLowerCase().includes(searchText))
                );
            }

            // Filtrer par statut
            if (criteria.status) {
                results = results.filter(f => f.status === criteria.status);
            }

            // Filtrer par priorité
            if (criteria.priority) {
                results = results.filter(f => f.priority === criteria.priority);
            }

            // Filtrer par auteur
            if (criteria.author) {
                const searchAuthor = criteria.author.toLowerCase();
                results = results.filter(f =>
                    f.author_name && f.author_name.toLowerCase().includes(searchAuthor)
                );
            }

            // Filtrer par date
            if (criteria.dateFrom) {
                const fromDate = new Date(criteria.dateFrom);
                results = results.filter(f => new Date(f.created_at) >= fromDate);
            }
            if (criteria.dateTo) {
                const toDate = new Date(criteria.dateTo);
                toDate.setHours(23, 59, 59, 999);
                results = results.filter(f => new Date(f.created_at) <= toDate);
            }

            return results;
        },

        /**
         * Afficher les résultats de recherche
         */
        displaySearchResults: function(results) {
            if (!this.elements.searchResults || !this.elements.searchResultsList) return;

            // Afficher la section résultats
            this.elements.searchResults.hidden = false;
            this.elements.searchResults.classList.add('active');

            if (this.elements.searchCount) {
                this.elements.searchCount.textContent = `${results.length} résultat${results.length > 1 ? 's' : ''}`;
            }

            if (results.length === 0) {
                this.elements.searchResultsList.innerHTML = `
                    <div class="wpvfh-search-no-results">
                        Aucun feedback trouvé
                    </div>
                `;
                return;
            }

            this.elements.searchResultsList.innerHTML = results.map(feedback => {
                const statusColor = BlazingFeedback.getStatusColor(feedback.status);
                const text = feedback.comment || feedback.transcript || 'Sans contenu';
                const date = new Date(feedback.created_at).toLocaleDateString('fr-FR');
                const pageUrl = feedback.page_url || '';
                const pageTitle = feedback.page_title || pageUrl;

                return `
                    <div class="wpvfh-search-result-item"
                         data-feedback-id="${feedback.id}"
                         data-page-url="${this.escapeHtml(pageUrl)}">
                        <div class="wpvfh-search-result-header">
                            <span class="wpvfh-search-result-id">#${feedback.id}</span>
                            <span class="wpvfh-search-result-status" style="background: ${statusColor}"></span>
                        </div>
                        <div class="wpvfh-search-result-text">${this.escapeHtml(text)}</div>
                        <div class="wpvfh-search-result-meta">
                            <span>${feedback.author_name || 'Anonyme'}</span>
                            <span>${date}</span>
                            ${pageTitle ? `<span title="${this.escapeHtml(pageUrl)}">${this.escapeHtml(pageTitle.substring(0, 30))}${pageTitle.length > 30 ? '...' : ''}</span>` : ''}
                        </div>
                    </div>
                `;
            }).join('');

            // Ajouter les événements de clic
            this.elements.searchResultsList.querySelectorAll('.wpvfh-search-result-item').forEach(item => {
                item.addEventListener('click', () => {
                    const feedbackId = parseInt(item.dataset.feedbackId, 10);
                    const pageUrl = item.dataset.pageUrl;
                    this.goToFeedback(feedbackId, pageUrl);
                });
            });
        },

        /**
         * Naviguer vers un feedback (changer de page si nécessaire)
         */
        goToFeedback: function(feedbackId, pageUrl) {
            // Fermer la modal de recherche
            this.closeSearchModal();

            // Vérifier si on est sur la bonne page
            const currentUrl = window.location.href.split('?')[0].split('#')[0];
            const targetUrl = pageUrl ? pageUrl.split('?')[0].split('#')[0] : '';

            if (targetUrl && currentUrl !== targetUrl) {
                // Naviguer vers la page avec le feedback ID en paramètre
                const separator = pageUrl.includes('?') ? '&' : '?';
                window.location.href = pageUrl + separator + 'wpvfh_open=' + feedbackId;
            } else {
                // On est sur la bonne page, trouver le feedback et ouvrir les détails
                const feedback = this.state.currentFeedbacks.find(f => f.id === feedbackId);
                if (feedback) {
                    // showFeedbackDetails appelle déjà openPanel('details')
                    this.showFeedbackDetails(feedback);
                }
            }
        },

        /**
         * Vérifier si un feedback doit être ouvert au chargement
         */
        checkOpenFeedbackParam: function() {
            const urlParams = new URLSearchParams(window.location.search);
            const feedbackId = urlParams.get('wpvfh_open');

            if (feedbackId) {
                // Attendre que les feedbacks soient chargés
                setTimeout(() => {
                    const id = parseInt(feedbackId, 10);
                    const feedback = this.state.currentFeedbacks.find(f => f.id === id);
                    if (feedback) {
                        // showFeedbackDetails appelle déjà openPanel('details')
                        this.showFeedbackDetails(feedback);
                    }

                    // Nettoyer l'URL
                    const cleanUrl = window.location.href.replace(/[?&]wpvfh_open=\d+/, '');
                    window.history.replaceState({}, '', cleanUrl);
                }, 500);
            }
        },
    };

    // Exposer le widget globalement
    window.BlazingFeedback = BlazingFeedback;

    // Initialiser au chargement du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => BlazingFeedback.init());
    } else {
        BlazingFeedback.init();
    }

})(window, document);