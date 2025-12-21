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

        /**
         * Formater une date en format relatif
         */
        formatRelativeDate: function(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000);

            if (diff < 60) return 'à l\'instant';
            if (diff < 3600) return Math.floor(diff / 60) + 'min';
            if (diff < 86400) return Math.floor(diff / 3600) + 'h';
            if (diff < 604800) return Math.floor(diff / 86400) + 'j';
            return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });
        },

        /**
         * Obtenir les initiales d'un nom
         */
        getInitials: function(name) {
            if (!name) return '?';
            const parts = name.trim().split(/\s+/);
            if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
            return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
        },

        /**
         * Générer le HTML d'une carte feedback compacte
         */
        generatePinItemHtml: function(feedback, isDraggable = false) {
            const labels = this.widget.modules.labels;
            const tools = this.widget.modules.tools;

            // Données de base
            const status = feedback.status || 'new';
            const statusLabel = labels.getStatusLabel(status);
            const statusColor = labels.getStatusColor(status);
            const statusEmoji = labels.getStatusEmoji(status);

            const priority = feedback.priority || 'none';
            const priorityLabel = labels.getPriorityLabel(priority);
            const priorityColor = labels.getPriorityColor(priority);
            const priorityEmoji = labels.getPriorityEmoji(priority);

            const type = feedback.feedback_type || '';
            const typeLabel = labels.getTypeLabel(type);
            const typeEmoji = labels.getTypeEmoji(type);
            const typeConfig = labels.getTypeConfig(type);
            const typeColor = typeConfig?.color || '#6c757d';

            // Auteur
            const authorName = feedback.author?.name || 'Anonyme';
            const initials = this.getInitials(authorName);

            // Date relative
            const relativeDate = this.formatRelativeDate(feedback.date);

            // Tags (max 3)
            const tagsStr = feedback.tags || '';
            const tagList = tagsStr.split(',').map(t => t.trim()).filter(t => t).slice(0, 3);
            const tagsModule = this.widget.modules.tags;

            // Indicateurs
            const hasScreenshot = feedback.screenshot_id > 0;
            const repliesCount = feedback.replies?.length || 0;

            // Génération HTML
            const draggableAttr = isDraggable ? 'draggable="true"' : '';
            const metadataClass = isDraggable ? ' wpvfh-metadata-item' : '';

            let tagsHtml = '';
            if (tagList.length > 0) {
                tagsHtml = tagList.map(tag => {
                    const tagColor = tagsModule?.getPredefinedTagColor?.(tag) || '#2980b9';
                    return `<span class="wpvfh-card-tag" style="--tag-color: ${tagColor};">#${tools.escapeHtml(tag)}</span>`;
                }).join('');
            }

            return `
                <div class="wpvfh-pin-item wpvfh-pin-compact${metadataClass}" ${draggableAttr} data-feedback-id="${feedback.id}">
                    <div class="wpvfh-card-left">
                        <div class="wpvfh-card-header">
                            <span class="wpvfh-card-id">#${feedback.id}</span>
                            <span class="wpvfh-card-badge" style="--badge-color: ${statusColor};">${statusEmoji} ${statusLabel}</span>
                            ${priority && priority !== 'none' ? `<span class="wpvfh-card-badge" style="--badge-color: ${priorityColor};">${priorityEmoji} ${priorityLabel}</span>` : ''}
                            ${type ? `<span class="wpvfh-card-badge" style="--badge-color: ${typeColor};">${typeEmoji} ${typeLabel}</span>` : ''}
                        </div>
                        <p class="wpvfh-card-comment">${tools.escapeHtml(feedback.comment || '')}</p>
                        <div class="wpvfh-card-footer">
                            <div class="wpvfh-card-tags">${tagsHtml}</div>
                            <div class="wpvfh-card-meta">
                                <span class="wpvfh-card-author" title="${tools.escapeHtml(authorName)}">${initials}</span>
                                <span class="wpvfh-card-date">${relativeDate}</span>
                            </div>
                        </div>
                    </div>
                    <div class="wpvfh-card-right">
                        ${hasScreenshot ? '<span class="wpvfh-card-indicator" title="Screenshot">📷</span>' : ''}
                        ${repliesCount > 0 ? `<span class="wpvfh-card-indicator" title="${repliesCount} réponse(s)">💬${repliesCount}</span>` : ''}
                        <button type="button" class="wpvfh-card-edit" title="Modifier">✏️</button>
                    </div>
                </div>
            `;
        },

        renderPinsList: function() {
            const pinsList = this.widget.elements.pinsList;
            if (!pinsList) return;

            const feedbacks = this.widget.modules.filters.getFilteredFeedbacks();

            if (this.widget.elements.pinsCount) {
                this.widget.elements.pinsCount.textContent = feedbacks.length > 0 ? `(${feedbacks.length})` : '';
            }

            if (this.widget.elements.emptyState) {
                this.widget.elements.emptyState.hidden = feedbacks.length > 0;
            }
            pinsList.hidden = feedbacks.length === 0;

            if (feedbacks.length === 0) return;

            const html = feedbacks.map(feedback => this.generatePinItemHtml(feedback, false)).join('');
            pinsList.innerHTML = html;

            // Event listeners
            pinsList.querySelectorAll('.wpvfh-pin-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    // Si clic sur le bouton edit, ouvrir les détails
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

                    const html = groupedFeedbacks[value].map(feedback => this.generatePinItemHtml(feedback, true)).join('');
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

                // Vérifier si on doit masquer les sections vides
                const groupSettings = window.wpvfhData?.metadataGroups?.[groupSlug]?.settings;
                const hideEmptySections = groupSettings?.hide_empty_sections || false;

                // Mettre à jour les compteurs dans les titres de section et gérer la visibilité
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

                    // Afficher ou masquer la section selon le réglage
                    if (hideEmptySections && count === 0) {
                        section.style.display = 'none';
                    } else {
                        section.style.display = '';
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

                // Déterminer la valeur à envoyer
                let apiValue = newValue;
                if (newValue === 'none') {
                    if (field === 'status') {
                        apiValue = 'new'; // Le statut "none" n'existe pas, utiliser "new"
                    } else if (field === 'priority') {
                        apiValue = 'none'; // La priorité accepte "none"
                    } else {
                        apiValue = ''; // Pour type et tags, utiliser chaîne vide
                    }
                }

                // Mettre à jour localement
                const feedback = this.widget.state.currentFeedbacks.find(f => f.id == feedbackId);
                if (feedback) {
                    if (field === 'status') {
                        feedback[field] = apiValue;
                    } else if (field === 'priority') {
                        feedback[field] = apiValue;
                    } else {
                        feedback[field] = newValue === 'none' ? '' : newValue;
                    }
                }

                // Re-rendre immédiatement
                this.renderMetadataLists();

                // Utiliser l'endpoint approprié selon le type
                if (field === 'status') {
                    await this.widget.modules.api.request('PUT', `feedbacks/${feedbackId}/status`, { status: apiValue });
                } else if (field === 'priority') {
                    await this.widget.modules.api.request('PUT', `feedbacks/${feedbackId}/priority`, { priority: apiValue });
                } else {
                    // Pour type et tags, utiliser l'endpoint général
                    const data = {};
                    data[field] = newValue === 'none' ? '' : newValue;
                    await this.widget.modules.api.request('PUT', `feedbacks/${feedbackId}`, data);
                }

                if (this.widget.modules.notifications) {
                    this.widget.modules.notifications.show('Métadonnée mise à jour', 'success');
                }

                // Mettre à jour le pin sur la page si c'est le statut
                if (field === 'status' && window.BlazingAnnotation) {
                    window.BlazingAnnotation.updatePin(feedbackId, { status: apiValue });
                }

            } catch (error) {
                console.error('[Blazing Feedback] Erreur mise à jour métadonnée:', error);
                if (this.widget.modules.notifications) {
                    this.widget.modules.notifications.show('Erreur lors de la mise à jour', 'error');
                }
                // Recharger les feedbacks pour revenir à l'état serveur
                if (this.widget.modules.api && this.widget.modules.api.loadExistingFeedbacks) {
                    await this.widget.modules.api.loadExistingFeedbacks();
                    this.renderMetadataLists();
                }
            }
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.list = List;

})(window);
