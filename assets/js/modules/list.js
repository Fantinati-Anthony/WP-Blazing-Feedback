/**
 * Module List - Blazing Feedback
 * Affichage liste feedbacks
 * @package Blazing_Feedback
 */
(function(window) {
    'use strict';

    const List = {
        init: function(widget) {
            this.widget = widget;
        },

        renderPinsList: function() {
            const pinsList = this.widget.elements.pinsList;
            if (!pinsList) return;

            const feedbacks = this.widget.modules.filters.getFilteredFeedbacks();
            const labels = this.widget.modules.labels;
            const tools = this.widget.modules.tools;

            if (this.widget.elements.pinsCount) {
                this.widget.elements.pinsCount.textContent = feedbacks.length > 0 ? `(${feedbacks.length})` : '';
            }

            if (this.widget.elements.emptyState) {
                this.widget.elements.emptyState.hidden = feedbacks.length > 0;
            }
            pinsList.hidden = feedbacks.length === 0;

            if (feedbacks.length === 0) return;

            const html = feedbacks.map((feedback, index) => {
                const status = feedback.status || 'new';
                const statusLabel = labels.getStatusLabel(status);
                const statusColor = labels.getStatusColor(status);
                const statusEmoji = labels.getStatusEmoji(status);
                const date = feedback.date ? new Date(feedback.date).toLocaleDateString() : '';
                const pinNumber = feedback._displayOrder || (index + 1);

                return `
                    <div class="wpvfh-pin-item" data-feedback-id="${feedback.id}">
                        <div class="wpvfh-pin-content">
                            <div class="wpvfh-pin-header">
                                <span class="wpvfh-pin-id">#${feedback.id}</span>
                            </div>
                            <p class="wpvfh-pin-text">${tools.escapeHtml(feedback.comment || '')}</p>
                            <div class="wpvfh-pin-meta">
                                <span class="wpvfh-pin-status" style="color: ${statusColor};">${statusEmoji} ${statusLabel}</span>
                            </div>
                            ${labels.generateFeedbackLabelsHtml(feedback)}
                        </div>
                    </div>
                `;
            }).join('');

            pinsList.innerHTML = html;

            pinsList.querySelectorAll('.wpvfh-pin-item').forEach(item => {
                item.addEventListener('click', () => {
                    const feedbackId = parseInt(item.dataset.feedbackId, 10);
                    const feedback = this.widget.state.currentFeedbacks.find(f => f.id === feedbackId);
                    if (feedback && this.widget.modules.details) {
                        this.widget.modules.details.showFeedbackDetails(feedback);
                    }
                });
            });
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.list = List;

})(window);
