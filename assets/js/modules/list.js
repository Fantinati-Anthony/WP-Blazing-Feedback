/**
 * Rendu liste, drag-drop, scroll
 * 
 * Reference file for feedback-widget.js lines 1350-1630
 * See main file: assets/js/feedback-widget.js
 * 
 * Methods included:
 * - 
renderPinsList * - initDragAndDrop * - updateFeedbackOrder * - scrollToPin
 * 
 * @package Blazing_Feedback
 */

/* 
 * To view this section, read feedback-widget.js with:
 * offset=1350, limit=281
 */

            const feedbacks = this.getFilteredFeedbacks();

            // Mettre à jour le compteur
            if (this.elements.pinsCount) {
                this.elements.pinsCount.textContent = feedbacks.length > 0 ? `(${feedbacks.length})` : '';
            }

            // Afficher/masquer l'état vide
            if (this.elements.emptyState) {
                this.elements.emptyState.hidden = feedbacks.length > 0;
            }
            this.elements.pinsList.hidden = feedbacks.length === 0;

            if (feedbacks.length === 0) return;

            // Ajouter la classe sortable
            this.elements.pinsList.classList.add('sortable');

            // Générer le HTML des pins avec handle de drag et numéro
            const html = feedbacks.map((feedback, index) => {
                const status = feedback.status || 'new';
                const statusLabel = this.getStatusLabel(status);
                const statusColor = this.getStatusColor(status);
                const statusEmoji = this.getStatusEmoji(status);
                const date = feedback.date ? new Date(feedback.date).toLocaleDateString() : '';
                // Utiliser _displayOrder si disponible (cohérent avec les pins sur la page)
                const pinNumber = feedback._displayOrder || (index + 1);

                // Vérifier si l'utilisateur peut supprimer ce feedback
                const isCreator = feedback.author?.id === this.config.userId;
                const canDelete = isCreator || this.config.canManage;

                // Vérifier si un élément a été ciblé (position ou sélecteur)
                const hasPosition = feedback.selector || feedback.position_x || feedback.position_y;

                // Récupérer les labels de type et priorité
                const typeLabel = this.getTypeLabel(feedback.feedback_type);
                const typeEmoji = this.getTypeEmoji(feedback.feedback_type);
                const priorityLabel = this.getPriorityLabel(feedback.priority);
                const priorityEmoji = this.getPriorityEmoji(feedback.priority);
                const priorityColor = this.getPriorityColor(feedback.priority);
                const tags = feedback.tags ? feedback.tags.split(',').map(t => t.trim()).filter(t => t) : [];

                return `
                    <div class="wpvfh-pin-item" data-feedback-id="${feedback.id}" data-pin-number="${pinNumber}">
                        ${hasPosition ? `
                        <div class="wpvfh-pin-marker status-${status}" style="background-color: ${statusColor};">
                            ${pinNumber}
                        </div>
                        ` : ''}
                        <div class="wpvfh-pin-content">
                            <div class="wpvfh-pin-header">
                                <span class="wpvfh-pin-id">#${feedback.id}</span>
                            </div>
                            <p class="wpvfh-pin-text">${this.escapeHtml(feedback.comment || feedback.content || '')}</p>
                            <div class="wpvfh-pin-meta">
                                <span class="wpvfh-pin-status status-${status}" style="color: ${statusColor};">${statusEmoji} ${statusLabel}</span>
                                ${date ? `<span class="wpvfh-pin-date">${date}</span>` : ''}
                            </div>
                            <div class="wpvfh-pin-metadata">
                                ${feedback.feedback_type ? `<span class="wpvfh-pin-type">${typeEmoji} ${typeLabel}</span>` : ''}
                                ${feedback.priority && feedback.priority !== 'none' ? `<span class="wpvfh-pin-priority" style="color: ${priorityColor};">${priorityEmoji} ${priorityLabel}</span>` : ''}
                            </div>
                            ${tags.length > 0 ? `
                            <div class="wpvfh-pin-tags">
                                ${tags.map(tag => `<span class="wpvfh-pin-tag">🏷️ ${this.escapeHtml(tag)}</span>`).join('')}
                            </div>
                            ` : ''}
                            ${this.generateFeedbackLabelsHtml(feedback)}
                        </div>
                        <div class="wpvfh-pin-actions">
                            ${canDelete ? `
                            <button type="button" class="wpvfh-pin-action wpvfh-pin-delete" title="Supprimer" data-feedback-id="${feedback.id}">
                                🗑️
                            </button>
                            ` : ''}
                        </div>
                    </div>
                `;
            }).join('');

            this.elements.pinsList.innerHTML = html;

            // Ajouter les événements aux items
            this.elements.pinsList.querySelectorAll('.wpvfh-pin-item').forEach(item => {
                // Clic pour voir les détails du feedback
                item.addEventListener('click', (e) => {
                    // Ne pas réagir si on clique sur une action
                    if (e.target.closest('.wpvfh-pin-action')) {
                        return;
                    }
                    const feedbackId = parseInt(item.dataset.feedbackId, 10);

                    // Trouver le feedback dans la liste
                    const feedback = this.state.currentFeedbacks.find(f => f.id === feedbackId);
                    if (feedback) {
                        // Afficher les détails du feedback
                        this.showFeedbackDetails(feedback);
                    }
                });

                // Clic sur le bouton supprimer
                const deleteBtn = item.querySelector('.wpvfh-pin-delete');
                if (deleteBtn) {
                    deleteBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const feedbackId = parseInt(deleteBtn.dataset.feedbackId, 10);
                        this.showDeleteModalForFeedback(feedbackId);
                    });
                }
            });
        },

        /**
         * Initialiser le drag-and-drop pour la liste
         */
        initDragAndDrop: function() {
            const list = this.elements.pinsList;
            if (!list) return;

            let draggedItem = null;

            list.querySelectorAll('.wpvfh-pin-item').forEach(item => {
                // Début du drag
                item.addEventListener('dragstart', (e) => {
                    draggedItem = item;
                    item.classList.add('dragging');
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/plain', item.dataset.feedbackId);
                });

                // Fin du drag
                item.addEventListener('dragend', () => {
                    item.classList.remove('dragging');
                    list.querySelectorAll('.wpvfh-pin-item').forEach(i => {
                        i.classList.remove('drag-over');
                    });
                    draggedItem = null;
                });

                // Survol pendant le drag
                item.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';

                    if (item !== draggedItem) {
                        item.classList.add('drag-over');
                    }
                });

                // Sortie du survol
                item.addEventListener('dragleave', () => {
                    item.classList.remove('drag-over');
                });

                // Drop
                item.addEventListener('drop', (e) => {
                    e.preventDefault();
                    item.classList.remove('drag-over');

                    if (draggedItem && item !== draggedItem) {
                        // Réorganiser dans le DOM
                        const allItems = [...list.querySelectorAll('.wpvfh-pin-item')];
                        const fromIndex = allItems.indexOf(draggedItem);
                        const toIndex = allItems.indexOf(item);

                        if (fromIndex < toIndex) {
                            item.parentNode.insertBefore(draggedItem, item.nextSibling);
                        } else {
                            item.parentNode.insertBefore(draggedItem, item);
                        }

                        // Mettre à jour l'ordre dans currentFeedbacks
                        this.updateFeedbackOrder();
                    }
                });
            });
        },

        /**
         * Mettre à jour l'ordre des feedbacks après réorganisation
         */
        updateFeedbackOrder: function() {
            const list = this.elements.pinsList;
            if (!list) return;

            // Récupérer les IDs dans le nouvel ordre
            const orderedIds = [...list.querySelectorAll('.wpvfh-pin-item')]
                .map(item => parseInt(item.dataset.feedbackId, 10));

            // Réorganiser currentFeedbacks
            const newOrder = orderedIds.map(id =>
                this.state.currentFeedbacks.find(f => f.id === id)
            ).filter(Boolean);

            this.state.currentFeedbacks = newOrder;

            // Mettre à jour les numéros dans la liste
            list.querySelectorAll('.wpvfh-pin-item').forEach((item, index) => {
                const newNumber = index + 1;
                item.dataset.pinNumber = newNumber;
                const marker = item.querySelector('.wpvfh-pin-marker');
                if (marker) {
                    marker.textContent = newNumber;
                }
                // Animation flash
                item.classList.add('reordered');
                setTimeout(() => item.classList.remove('reordered'), 500);
            });

            // Renuméroter les pins sur la page
            if (window.BlazingAnnotation) {
                window.BlazingAnnotation.renumberPins(orderedIds);
            }

            console.log('[Blazing Feedback] Ordre mis à jour:', orderedIds);
        },

        /**
         * Scroller vers un pin sur la page
         * @param {number} feedbackId
         */
        scrollToPin: function(feedbackId) {
            if (window.BlazingAnnotation) {
                window.BlazingAnnotation.scrollToPin(feedbackId);
            }
        },

        /**
         * Échapper le HTML
         * @param {string} str
         * @returns {string}
         */
        escapeHtml: function(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        },

        /**
         * Gérer le placement d'un pin
         * @param {CustomEvent} event - Événement
         * @returns {void}
         */
        handlePinPlaced: function(event) {
            const position = event.detail;

            // Stocker la position
            this.state.pinPosition = position;

            // Mettre à jour les champs cachés
            if (this.elements.positionX) {
                this.elements.positionX.value = position.percentX;
            }
            if (this.elements.positionY) {
                this.elements.positionY.value = position.percentY;
            }

            // Afficher l'info du pin
            if (this.elements.pinInfo) {
                this.elements.pinInfo.hidden = false;
            }

            // Capturer le screenshot si activé
            if (this.elements.screenshotToggle && this.elements.screenshotToggle.checked) {
                this.captureScreenshot();
            }

            // Ouvrir le panel
            this.state.feedbackMode = 'create';
            this.openPanel();
        },

        /**
         * Gérer la sélection d'un pin existant
         * @param {CustomEvent} event - Événement
         * @returns {void}
         */
        handlePinSelected: function(event) {
            const { feedbackId, pinData } = event.detail;
