/**
 * Module Search - Blazing Feedback
 * Recherche de feedbacks
 * @package Blazing_Feedback
 */
(function(window) {
    'use strict';

    const Search = {
        init: function(widget) {
            this.widget = widget;
        },

        openSearchModal: function() {
            const panelBody = document.querySelector('.wpvfh-panel-body');
            const panelFooter = document.querySelector('.wpvfh-panel-footer');
            const panelTabs = document.querySelector('.wpvfh-tabs');
            
            if (panelBody) panelBody.style.display = 'none';
            if (panelFooter) panelFooter.style.display = 'none';
            if (panelTabs) panelTabs.style.display = 'none';

            if (this.widget.elements.searchModal && this.widget.elements.panel) {
                const panelHeader = this.widget.elements.panel.querySelector('.wpvfh-panel-header');
                if (panelHeader && this.widget.elements.searchModal.parentNode !== this.widget.elements.panel) {
                    panelHeader.after(this.widget.elements.searchModal);
                }
            }

            if (this.widget.elements.searchModal) {
                this.widget.elements.searchModal.hidden = false;
                this.widget.elements.searchModal.classList.add('active');
                this.widget.elements.searchModal.classList.add('wpvfh-search-inline');
            }

            if (this.widget.elements.panel && !this.widget.elements.panel.classList.contains('active')) {
                this.widget.modules.panel.openPanel();
            }
        },

        closeSearchModal: function() {
            if (this.widget.elements.searchModal) {
                this.widget.elements.searchModal.hidden = true;
                this.widget.elements.searchModal.classList.remove('active');
                this.widget.elements.searchModal.classList.remove('wpvfh-search-inline');
            }

            const panelBody = document.querySelector('.wpvfh-panel-body');
            const panelFooter = document.querySelector('.wpvfh-panel-footer');
            const panelTabs = document.querySelector('.wpvfh-tabs');
            
            if (panelBody) panelBody.style.display = '';
            if (panelFooter) panelFooter.style.display = '';
            if (panelTabs) panelTabs.style.display = '';
        },

        performSearch: async function() {
            const criteria = {
                id: this.widget.elements.searchId ? this.widget.elements.searchId.value.trim() : '',
                text: this.widget.elements.searchText ? this.widget.elements.searchText.value.trim() : '',
                status: this.widget.elements.searchStatus ? this.widget.elements.searchStatus.value : '',
                priority: this.widget.elements.searchPriority ? this.widget.elements.searchPriority.value : '',
            };

            let results = this.filterFeedbacksLocally(criteria);
            this.displaySearchResults(results);
        },

        filterFeedbacksLocally: function(criteria) {
            let results = [...(this.widget.state.currentFeedbacks || [])];

            if (criteria.id) {
                const searchId = parseInt(criteria.id, 10);
                results = results.filter(f => f.id === searchId);
            }

            if (criteria.text) {
                const searchText = criteria.text.toLowerCase();
                results = results.filter(f =>
                    (f.comment && f.comment.toLowerCase().includes(searchText))
                );
            }

            if (criteria.status) {
                results = results.filter(f => f.status === criteria.status);
            }

            if (criteria.priority) {
                results = results.filter(f => f.priority === criteria.priority);
            }

            return results;
        },

        /**
         * Réinitialiser le formulaire de recherche
         */
        resetSearch: function() {
            // Vider les champs
            if (this.widget.elements.searchId) {
                this.widget.elements.searchId.value = '';
            }
            if (this.widget.elements.searchText) {
                this.widget.elements.searchText.value = '';
            }
            if (this.widget.elements.searchStatus) {
                this.widget.elements.searchStatus.value = '';
            }
            if (this.widget.elements.searchPriority) {
                this.widget.elements.searchPriority.value = '';
            }
            if (this.widget.elements.searchAuthor) {
                this.widget.elements.searchAuthor.value = '';
            }
            if (this.widget.elements.searchDateFrom) {
                this.widget.elements.searchDateFrom.value = '';
            }
            if (this.widget.elements.searchDateTo) {
                this.widget.elements.searchDateTo.value = '';
            }

            // Masquer les résultats
            if (this.widget.elements.searchResults) {
                this.widget.elements.searchResults.hidden = true;
                this.widget.elements.searchResults.classList.remove('active');
            }
            if (this.widget.elements.searchResultsList) {
                this.widget.elements.searchResultsList.innerHTML = '';
            }
            if (this.widget.elements.searchCount) {
                this.widget.elements.searchCount.textContent = '';
            }
        },

        displaySearchResults: function(results) {
            if (!this.widget.elements.searchResults || !this.widget.elements.searchResultsList) return;

            this.widget.elements.searchResults.hidden = false;
            this.widget.elements.searchResults.classList.add('active');

            if (this.widget.elements.searchCount) {
                this.widget.elements.searchCount.textContent = `${results.length} résultat${results.length > 1 ? 's' : ''}`;
            }

            if (results.length === 0) {
                this.widget.elements.searchResultsList.innerHTML = '<div class="wpvfh-search-no-results">Aucun feedback trouvé</div>';
                return;
            }

            const labels = this.widget.modules.labels;
            const tools = this.widget.modules.tools;

            this.widget.elements.searchResultsList.innerHTML = results.map(feedback => {
                const statusColor = labels.getStatusColor(feedback.status);
                const text = feedback.comment || 'Sans contenu';
                const date = new Date(feedback.created_at || feedback.date).toLocaleDateString('fr-FR');

                return `
                    <div class="wpvfh-search-result-item" data-feedback-id="${feedback.id}">
                        <div class="wpvfh-search-result-header">
                            <span class="wpvfh-search-result-id">#${feedback.id}</span>
                            <span class="wpvfh-search-result-status" style="background: ${statusColor}"></span>
                        </div>
                        <div class="wpvfh-search-result-text">${tools.escapeHtml(text)}</div>
                        <div class="wpvfh-search-result-meta">
                            <span>${feedback.author_name || feedback.author?.name || 'Anonyme'}</span>
                            <span>${date}</span>
                        </div>
                    </div>
                `;
            }).join('');

            this.widget.elements.searchResultsList.querySelectorAll('.wpvfh-search-result-item').forEach(item => {
                item.addEventListener('click', () => {
                    const feedbackId = parseInt(item.dataset.feedbackId, 10);
                    const feedback = this.widget.state.currentFeedbacks.find(f => f.id === feedbackId);
                    if (feedback) {
                        this.closeSearchModal();
                        this.widget.modules.details.showFeedbackDetails(feedback);
                    }
                });
            });
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.search = Search;

})(window);
