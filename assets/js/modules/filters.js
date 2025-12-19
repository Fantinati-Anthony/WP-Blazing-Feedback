/**
 * Filtrage par statut
 * 
 * Reference file for feedback-widget.js lines 2308-2400
 * See main file: assets/js/feedback-widget.js
 * 
 * Methods included:
 * - 
handleFilterClick * - getFilteredFeedbacks * - updateFilterCounts
 * 
 * @package Blazing_Feedback
 */

/* 
 * To view this section, read feedback-widget.js with:
 * offset=2308, limit=93
 */

        handleFilterClick: function(status) {
            this.state.currentFilter = status;

            // Mettre à jour les boutons
            if (this.elements.filterButtons) {
                this.elements.filterButtons.forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.status === status);
                });
            }

            // Filtrer et afficher la liste
            this.renderPinsList();
        },

        /**
         * Obtenir les feedbacks filtrés
         * @returns {Array} Feedbacks filtrés
         */
        getFilteredFeedbacks: function() {
            if (this.state.currentFilter === 'all') {
                return this.state.currentFeedbacks;
            }
            return this.state.currentFeedbacks.filter(f => f.status === this.state.currentFilter);
        },

        /**
         * Mettre à jour les compteurs des filtres
         */
        updateFilterCounts: function() {
            const feedbacks = this.state.currentFeedbacks || [];
            const statuses = window.wpvfhData?.statuses || [];

            // Mettre à jour le compteur "Tous"
            const allCount = document.querySelector('#wpvfh-filter-all-count span');
            if (allCount) allCount.textContent = feedbacks.length;

            // Mettre à jour les compteurs pour chaque statut dynamique
            statuses.forEach(status => {
                const count = feedbacks.filter(f => f.status === status.id).length;
                const countEl = document.querySelector(`#wpvfh-filter-${status.id}-count span`);
                if (countEl) countEl.textContent = count;
            });
        },

        /**
         * Mettre à jour les labels (Type, Priorité, Tags) dans la vue détails
         * @param {Object} feedback - Données du feedback
         */
        updateDetailLabels: function(feedback) {
            // Type label - utiliser les options dynamiques
            if (this.elements.detailTypeLabel) {
                const type = this.getTypeConfig(feedback.feedback_type);
                if (type) {
                    const iconEl = this.elements.detailTypeLabel.querySelector('.wpvfh-label-icon');
                    const textEl = this.elements.detailTypeLabel.querySelector('.wpvfh-label-text');
                    if (iconEl) iconEl.textContent = type.emoji;
                    if (textEl) textEl.textContent = type.label;
                    this.elements.detailTypeLabel.setAttribute('data-type', feedback.feedback_type);
                    this.elements.detailTypeLabel.style.cssText = `background-color: ${type.color}20 !important; color: ${type.color} !important; border-color: ${type.color}40 !important;`;
                    this.elements.detailTypeLabel.hidden = false;
                } else {
                    this.elements.detailTypeLabel.hidden = true;
                }
            }

            // Priority label - utiliser les options dynamiques
            if (this.elements.detailPriorityLabel) {
                const priority = this.getPriorityConfig(feedback.priority);
                if (priority && feedback.priority !== 'none') {
                    const iconEl = this.elements.detailPriorityLabel.querySelector('.wpvfh-label-icon');
                    const textEl = this.elements.detailPriorityLabel.querySelector('.wpvfh-label-text');
                    if (iconEl) iconEl.textContent = priority.emoji;
                    if (textEl) textEl.textContent = priority.label;
                    this.elements.detailPriorityLabel.setAttribute('data-priority', feedback.priority);
                    this.elements.detailPriorityLabel.style.cssText = `background-color: ${priority.color}20 !important; color: ${priority.color} !important; border-color: ${priority.color}40 !important;`;
                    this.elements.detailPriorityLabel.hidden = false;
                } else {
                    this.elements.detailPriorityLabel.hidden = true;
                }
            }

            // Rendre les tags dans le container
            this.renderDetailTags(feedback.tags);
        },

        /**
         * Rendre les tags dans le container de la vue détails
         * @param {string} tagsString - Tags séparés par des virgules
         */
        renderDetailTags: function(tagsString) {
            if (!this.elements.detailTagsContainer) return;

            // Supprimer les anciens badges (garder l'input)