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
        },

        /**
         * Rendre les feedbacks dans les sections de métadonnées
         */
        renderMetadataLists: function() {
            const feedbacks = this.widget.state.currentFeedbacks || [];
            const labels = this.widget.modules.labels;
            const tools = this.widget.modules.tools;

            // Configuration des groupes de métadonnées
            const metadataGroups = {
                statuses: { field: 'status', defaultValue: 'new' },
                types: { field: 'feedback_type', defaultValue: '' },
                priorities: { field: 'priority', defaultValue: 'none' },
                tags: { field: 'tags', isMultiple: true }
            };

            // Pour chaque groupe de métadonnées
            Object.keys(metadataGroups).forEach(groupSlug => {
                const config = metadataGroups[groupSlug];
                const sections = document.querySelectorAll(`.wpvfh-metadata-section[data-group="${groupSlug}"]`);

                // Vider toutes les listes du groupe
                sections.forEach(section => {
                    const list = section.querySelector('.wpvfh-metadata-list');
                    if (list) list.innerHTML = '';
                });

                // Grouper les feedbacks par valeur de métadonnée
                const groupedFeedbacks = {};

                feedbacks.forEach(feedback => {
                    if (config.isMultiple) {
                        // Pour les tags, un feedback peut avoir plusieurs valeurs
                        const tagsStr = feedback[config.field] || '';
                        const tags = tagsStr.split(',').map(t => t.trim()).filter(t => t);

                        if (tags.length === 0) {
                            // Sans tags -> section "none"
                            if (!groupedFeedbacks['none']) groupedFeedbacks['none'] = [];
                            groupedFeedbacks['none'].push(feedback);
                        } else {
                            tags.forEach(tag => {
                                if (!groupedFeedbacks[tag]) groupedFeedbacks[tag] = [];
                                groupedFeedbacks[tag].push(feedback);
                            });
                        }
                    } else {
                        // Pour les autres métadonnées (status, type, priority)
                        let value = feedback[config.field];

                        // Gérer les valeurs vides
                        if (!value || value === '' || value === 'none') {
                            value = 'none';
                        }

                        if (!groupedFeedbacks[value]) groupedFeedbacks[value] = [];
                        groupedFeedbacks[value].push(feedback);
                    }
                });

                // Remplir chaque section avec ses feedbacks
                Object.keys(groupedFeedbacks).forEach(value => {
                    const listEl = document.getElementById(`wpvfh-metadata-${groupSlug}-${value}-list`);
                    if (!listEl) return;

                    const html = groupedFeedbacks[value].map(feedback => {
                        const status = feedback.status || 'new';
                        const statusColor = labels.getStatusColor(status);
                        const statusEmoji = labels.getStatusEmoji(status);
                        const statusLabel = labels.getStatusLabel(status);

                        return `
                            <div class="wpvfh-pin-item wpvfh-metadata-item" data-feedback-id="${feedback.id}">
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

                    listEl.innerHTML = html;

                    // Ajouter les événements de clic
                    listEl.querySelectorAll('.wpvfh-pin-item').forEach(item => {
                        item.addEventListener('click', () => {
                            const feedbackId = parseInt(item.dataset.feedbackId, 10);
                            const feedback = this.widget.state.currentFeedbacks.find(f => f.id === feedbackId);
                            if (feedback && this.widget.modules.details) {
                                this.widget.modules.details.showFeedbackDetails(feedback);
                            }
                        });
                    });
                });

                // Mettre à jour les compteurs dans les titres de section
                sections.forEach(section => {
                    const value = section.dataset.value;
                    const count = groupedFeedbacks[value]?.length || 0;
                    const title = section.querySelector('.wpvfh-metadata-title');
                    if (title) {
                        // Ajouter ou mettre à jour le compteur
                        let countSpan = title.querySelector('.wpvfh-metadata-count');
                        if (!countSpan) {
                            countSpan = document.createElement('span');
                            countSpan.className = 'wpvfh-metadata-count';
                            title.appendChild(countSpan);
                        }
                        countSpan.textContent = count > 0 ? ` (${count})` : '';
                    }
                });
            });
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.list = List;

})(window);
