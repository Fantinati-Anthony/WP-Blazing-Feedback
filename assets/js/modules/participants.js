/**
 * Gestion des participants
 * 
 * Reference file for feedback-widget.js lines 3800-4000
 * See main file: assets/js/feedback-widget.js
 * 
 * Methods included:
 * - 
loadParticipants * - renderParticipants * - inviteParticipant
 * 
 * @package Blazing_Feedback
 */

/* 
 * To view this section, read feedback-widget.js with:
 * offset=3800, limit=201
 */

                    section.style.display = 'none';
                } else {
                    section.classList.remove('wpvfh-section-empty');
                    section.style.display = '';
                }
            });

            // Initialiser le drag-drop
            this.initMetadataDragDrop(groupSlug);
        },

        /**
         * Ajouter un feedback à une liste de métadatas
         */
        addFeedbackToMetadataList: function(groupSlug, value, feedback) {
            const listId = `wpvfh-metadata-${groupSlug}-${value}-list`;
            const list = document.getElementById(listId);
            if (!list) return;

            const item = this.createMetadataItem(feedback);
            list.appendChild(item);
        },

        /**
         * Créer un élément de feedback pour la liste de métadatas
         */
        createMetadataItem: function(feedback) {
            const item = document.createElement('div');
            item.className = 'wpvfh-pin-item';
            item.draggable = true;
            item.dataset.feedbackId = feedback.id;

            const status = feedback.status || 'new';
            const statusLabel = BlazingFeedback.getStatusLabel(status);
            const statusColor = BlazingFeedback.getStatusColor(status);

            item.innerHTML = `
                <div class="wpvfh-pin-header">
                    <span class="wpvfh-pin-id">#${feedback.id}</span>
                    <span class="wpvfh-pin-status" style="background: ${statusColor}15; color: ${statusColor};">${statusLabel}</span>
                </div>
                <div class="wpvfh-pin-content">
                    <p class="wpvfh-pin-comment">${this.truncateText(feedback.comment || '', 80)}</p>
                </div>
            `;

            // Clic pour voir les détails
            item.addEventListener('click', (e) => {
                if (e.target.closest('.wpvfh-drag-handle')) return;
                this.showFeedbackDetails(feedback);
            });

            return item;
        },

        /**
         * Initialiser le drag-drop pour les métadatas
         */
        initMetadataDragDrop: function(groupSlug) {
            const container = document.getElementById(`wpvfh-metadata-${groupSlug}`);
            if (!container) return;

            const lists = container.querySelectorAll('.wpvfh-metadata-list');
            const dropzones = container.querySelectorAll('.wpvfh-dropzone-metadata');
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

                list.addEventListener('dragleave', () => {
                    list.classList.remove('drag-over');
                });

                list.addEventListener('drop', (e) => {
                    e.preventDefault();
                    list.classList.remove('drag-over');

                    if (draggedItem && draggedFeedbackId) {
                        const section = list.closest('.wpvfh-metadata-section');
                        if (section) {
                            const newValue = section.dataset.value;
                            this.updateFeedbackMetadataValue(draggedFeedbackId, groupSlug, newValue);
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
                        this.updateFeedbackMetadataValue(draggedFeedbackId, groupSlug, newValue);
                    }
                });
            });
        },

        /**
         * Mettre à jour la valeur de métadata d'un feedback
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
                const feedback = this.state.currentFeedbacks.find(f => f.id == feedbackId);
                if (feedback) {
                    feedback[field] = newValue === 'none' ? '' : newValue;
                }

                // Re-rendre immédiatement
                this.renderMetadataListsForGroup(groupSlug);

                // Sauvegarder sur le serveur
                const data = {};
                data[field] = newValue === 'none' ? '' : newValue;

                // Utiliser l'endpoint approprié selon le type
                if (field === 'status') {
                    await this.apiRequest('POST', `feedbacks/${feedbackId}/status`, { status: newValue === 'none' ? 'new' : newValue });
                } else if (field === 'priority') {
                    await this.apiRequest('POST', `feedbacks/${feedbackId}/priority`, { priority: newValue === 'none' ? '' : newValue });
                } else {
                    await this.apiRequest('POST', `feedbacks/${feedbackId}`, data);
                }

                this.showNotification('Métadata mise à jour', 'success');
            } catch (error) {
                console.error('[Blazing Feedback] Erreur mise à jour métadata:', error);
                this.showNotification('Erreur lors de la mise à jour', 'error');
                this.loadExistingFeedbacks();
            }
        },

        /**
         * Ouvrir la recherche (remplace le panel-body)
         */
        openSearchModal: function() {
            // Masquer le panel-body, le footer et les tabs
            const panelBody = document.querySelector('.wpvfh-panel-body');
            const panelFooter = document.querySelector('.wpvfh-panel-footer');
            const panelTabs = document.querySelector('.wpvfh-tabs');
            if (panelBody) {
                panelBody.style.display = 'none';
            }
            if (panelFooter) {
                panelFooter.style.display = 'none';
            }
            if (panelTabs) {
                panelTabs.style.display = 'none';
            }

            // Déplacer le modal de recherche dans le panel (après le header)