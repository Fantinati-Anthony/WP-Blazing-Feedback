/**
 * Module Filters - Blazing Feedback
 * Filtrage des feedbacks
 * @package Blazing_Feedback
 */
(function(window) {
    'use strict';

    const Filters = {
        init: function(widget) {
            this.widget = widget;
        },

        /**
         * Gérer le clic sur un filtre
         */
        handleFilterClick: function(status) {
            this.widget.state.currentFilter = status;

            if (this.widget.elements.filterButtons) {
                this.widget.elements.filterButtons.forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.status === status);
                });
            }

            if (this.widget.modules.list) {
                this.widget.modules.list.renderPinsList();
            }
        },

        /**
         * Obtenir les feedbacks filtrés
         */
        getFilteredFeedbacks: function() {
            const feedbacks = this.widget.state.currentFeedbacks || [];
            const filter = this.widget.state.currentFilter;

            if (filter === 'all') {
                return feedbacks;
            }
            return feedbacks.filter(f => f.status === filter);
        },

        /**
         * Mettre à jour les compteurs des filtres
         */
        updateFilterCounts: function() {
            const feedbacks = this.widget.state.currentFeedbacks || [];
            const statuses = window.wpvfhData?.statuses || [];

            const allCount = document.querySelector('#wpvfh-filter-all-count span');
            if (allCount) allCount.textContent = feedbacks.length;

            statuses.forEach(status => {
                const count = feedbacks.filter(f => f.status === status.id).length;
                const countEl = document.querySelector(`#wpvfh-filter-${status.id}-count span`);
                if (countEl) countEl.textContent = count;
            });
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.filters = Filters;

})(window);
