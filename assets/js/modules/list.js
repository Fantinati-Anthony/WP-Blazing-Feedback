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
                            <div class="wpvfh-pin-item wpvfh-metadata-item" draggable="true" data-feedback-id="${feedback.id}">
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
                        item.addEventListener('click', (e) => {
                            // Ne pas ouvrir les détails si on est en train de drag
                            if (item.classList.contains('dragging')) return;
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

                // Initialiser le drag and drop pour ce groupe
                this.initMetadataDragDrop(groupSlug);
            });
        },

        /**
         * Initialiser le drag and drop pour les sections de métadonnées
         * @param {string} groupSlug - Slug du groupe (statuses, types, priorities, tags)
         */
        initMetadataDragDrop: function(groupSlug) {
            const container = document.getElementById(`wpvfh-metadata-${groupSlug}`);
            if (!container) return;

            const lists = container.querySelectorAll('.wpvfh-metadata-list');
            const dropzones = container.querySelectorAll('.wpvfh-dropzone-metadata');
            const self = this;
            let draggedItem = null;
            let draggedFeedbackId = null;

            // Gestionnaires pour les items
            lists.forEach(list => {
                list.querySelectorAll('.wpvfh-pin-item').forEach(item => {
                    item.addEventListener('dragstart', (e) => {
                        draggedItem = item;
                        draggedFeedbackId = item.dataset.feedbackId;
                        item.classList.add('dragging');
                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('text/plain', draggedFeedbackId);
                    });

                    item.addEventListener('dragend', () => {
                        if (draggedItem) {
                            draggedItem.classList.remove('dragging');
                        }
                        draggedItem = null;
                        draggedFeedbackId = null;
                        dropzones.forEach(dz => dz.classList.remove('drag-over'));
                        lists.forEach(l => l.classList.remove('drag-over'));
                    });
                });

                // Drop sur les listes
                list.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    list.classList.add('drag-over');
                });

                list.addEventListener('dragleave', (e) => {
                    if (!list.contains(e.relatedTarget)) {
                        list.classList.remove('drag-over');
                    }
                });

                list.addEventListener('drop', (e) => {
                    e.preventDefault();
                    list.classList.remove('drag-over');

                    if (draggedItem && draggedFeedbackId) {
                        const section = list.closest('.wpvfh-metadata-section');
                        if (section) {
                            const newValue = section.dataset.value;
                            self.updateFeedbackMetadataValue(draggedFeedbackId, groupSlug, newValue);
                        }
                    }
                });
            });

            // Gestionnaires pour les dropzones sticky
            dropzones.forEach(dropzone => {
                dropzone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    dropzone.classList.add('drag-over');
                });

                dropzone.addEventListener('dragleave', () => {
                    dropzone.classList.remove('drag-over');
                });

                dropzone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    dropzone.classList.remove('drag-over');

                    if (draggedItem && draggedFeedbackId) {
                        const newValue = dropzone.dataset.value;
                        self.updateFeedbackMetadataValue(draggedFeedbackId, groupSlug, newValue);
                    }
                });
            });
        },

        /**
         * Mettre à jour la valeur de métadonnée d'un feedback
         * @param {string} feedbackId - ID du feedback
         * @param {string} groupSlug - Slug du groupe
         * @param {string} newValue - Nouvelle valeur
         */
        updateFeedbackMetadataValue: async function(feedbackId, groupSlug, newValue) {
            try {
                // Mapper le groupe au champ approprié
                const fieldMap = {
                    'statuses': 'status',
                    'types': 'feedback_type',
                    'priorities': 'priority',
                    'tags': 'tags'
                };
                const field = fieldMap[groupSlug] || groupSlug;

                // Mettre à jour localement
                const feedback = this.widget.state.currentFeedbacks.find(f => f.id == feedbackId);
                if (feedback) {
                    feedback[field] = newValue === 'none' ? '' : newValue;
                }

                // Re-rendre immédiatement
                this.renderMetadataLists();

                // Sauvegarder sur le serveur
                const data = {};
                data[field] = newValue === 'none' ? '' : newValue;

                // Utiliser l'endpoint approprié selon le type
                if (field === 'status') {
                    await this.widget.modules.api.request('PUT', `feedbacks/${feedbackId}/status`, { status: newValue === 'none' ? 'new' : newValue });
                } else if (field === 'priority') {
                    await this.widget.modules.api.request('PUT', `feedbacks/${feedbackId}`, { priority: newValue === 'none' ? '' : newValue });
                } else {
                    await this.widget.modules.api.request('PUT', `feedbacks/${feedbackId}`, data);
                }

                if (this.widget.modules.notifications) {
                    this.widget.modules.notifications.show('Métadonnée mise à jour', 'success');
                }

                // Mettre à jour le pin sur la page si c'est le statut
                if (field === 'status' && window.BlazingAnnotation) {
                    window.BlazingAnnotation.updatePin(feedbackId, { status: newValue });
                }

            } catch (error) {
                console.error('[Blazing Feedback] Erreur mise à jour métadonnée:', error);
                if (this.widget.modules.notifications) {
                    this.widget.modules.notifications.show('Erreur lors de la mise à jour', 'error');
                }
            }
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.list = List;

})(window);
