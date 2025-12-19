/**
 * Module Details - Blazing Feedback
 * Vue détails feedback
 * @package Blazing_Feedback
 */
(function(window) {
    'use strict';

    const Details = {
        init: function(widget) {
            this.widget = widget;
        },

        showFeedbackDetails: function(feedback) {
            this.widget.state.currentFeedbackId = feedback.id;

            const labels = this.widget.modules.labels;
            const tools = this.widget.modules.tools;
            const status = feedback.status || 'new';
            const statusLabel = labels.getStatusLabel(status);
            const statusEmoji = labels.getStatusEmoji(status);
            const statusColor = labels.getStatusColor(status);

            if (this.widget.elements.detailId) {
                this.widget.elements.detailId.textContent = `#${feedback.id}`;
            }

            if (this.widget.elements.detailStatus) {
                this.widget.elements.detailStatus.innerHTML = `
                    <span class="wpvfh-status-badge status-${status}" style="background-color: ${statusColor}20; color: ${statusColor}; border-color: ${statusColor}40;">
                        ${statusEmoji} ${statusLabel}
                    </span>
                `;
            }

            if (this.widget.elements.detailAuthor) {
                this.widget.elements.detailAuthor.innerHTML = `
                    <span>👤</span>
                    <span>${tools.escapeHtml(feedback.author?.name || 'Anonyme')}</span>
                `;
            }

            if (this.widget.elements.detailDate) {
                const date = feedback.date ? new Date(feedback.date).toLocaleString() : '';
                this.widget.elements.detailDate.innerHTML = `
                    <span>📅</span>
                    <span>${date}</span>
                `;
            }

            if (this.widget.elements.detailComment) {
                this.widget.elements.detailComment.textContent = feedback.comment || feedback.content || '';
            }

            this.updateDetailLabels(feedback);

            if (this.widget.elements.detailType) {
                this.widget.elements.detailType.value = feedback.feedback_type || '';
            }
            if (this.widget.elements.detailPrioritySelect) {
                this.widget.elements.detailPrioritySelect.value = feedback.priority || 'none';
            }

            if (this.widget.elements.detailScreenshot) {
                if (feedback.screenshot_url) {
                    const img = this.widget.elements.detailScreenshot.querySelector('img');
                    if (img) img.src = feedback.screenshot_url;
                    this.widget.elements.detailScreenshot.hidden = false;
                } else {
                    this.widget.elements.detailScreenshot.hidden = true;
                }
            }

            if (this.widget.elements.statusSelect) {
                this.widget.elements.statusSelect.value = status;
            }

            if (this.widget.elements.replyInput) {
                this.widget.elements.replyInput.value = '';
            }

            if (this.widget.modules.panel) {
                this.widget.modules.panel.openPanel('details');
            }

            const hasPosition = feedback.selector || feedback.position_x || feedback.position_y;
            if (hasPosition && window.BlazingAnnotation) {
                setTimeout(() => {
                    window.BlazingAnnotation.scrollToPinWithHighlight(feedback.id);
                }, 300);
            }
        },

        updateDetailLabels: function(feedback) {
            if (this.widget.modules.tags) {
                this.widget.modules.tags.renderDetailTags(feedback.tags);
            }
        },

        updateFeedbackStatus: async function(feedbackId, status) {
            try {
                await this.widget.modules.api.request('PUT', `feedbacks/${feedbackId}/status`, { status });

                if (window.BlazingAnnotation) {
                    window.BlazingAnnotation.updatePin(feedbackId, { status });
                }

                const labels = this.widget.modules.labels;
                if (this.widget.elements.detailStatus) {
                    const statusLabel = labels.getStatusLabel(status);
                    const statusEmoji = labels.getStatusEmoji(status);
                    const statusColor = labels.getStatusColor(status);
                    this.widget.elements.detailStatus.innerHTML = `
                        <span class="wpvfh-status-badge status-${status}" style="background-color: ${statusColor}20; color: ${statusColor}; border-color: ${statusColor}40;">
                            ${statusEmoji} ${statusLabel}
                        </span>
                    `;
                }

                this.widget.modules.notifications.show('Statut mis à jour', 'success');
            } catch (error) {
                console.error('[Blazing Feedback] Erreur mise à jour:', error);
                this.widget.modules.notifications.show('Erreur lors de la mise à jour', 'error');
            }
        },

        addReply: async function(feedbackId, content) {
            try {
                await this.widget.modules.api.request('POST', `feedbacks/${feedbackId}/replies`, { content });
                this.widget.modules.notifications.show('Réponse ajoutée', 'success');

                if (this.widget.elements.replyInput) {
                    this.widget.elements.replyInput.value = '';
                }

                const updatedFeedback = await this.widget.modules.api.request('GET', `feedbacks/${feedbackId}`);
                this.showFeedbackDetails(updatedFeedback);
            } catch (error) {
                console.error('[Blazing Feedback] Erreur ajout réponse:', error);
                this.widget.modules.notifications.show('Erreur lors de l\'ajout de la réponse', 'error');
            }
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.details = Details;

})(window);
