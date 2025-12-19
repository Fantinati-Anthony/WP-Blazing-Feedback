/**
 * Mentions utilisateurs
 * 
 * Reference file for feedback-widget.js lines 3550-3800
 * See main file: assets/js/feedback-widget.js
 * 
 * Methods included:
 * - 
handleMentionInput * - showMentionDropdown * - hideMentionDropdown * - selectMention * - searchUsers
 * 
 * @package Blazing_Feedback
 */

/* 
 * To view this section, read feedback-widget.js with:
 * offset=3550, limit=251
 */

                });

                zone.addEventListener('dragleave', () => {
                    zone.classList.remove('drag-over');
                });

                zone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    zone.classList.remove('drag-over');
                    const priority = zone.dataset.priority;
                    if (draggedFeedbackId && priority) {
                        this.updateFeedbackPriority(draggedFeedbackId, priority);
                    }
                });
            });

            // Gestionnaires pour les listes (réordonner dans la même priorité)
            Object.entries(lists).forEach(([priority, list]) => {
                if (!list) return;

                list.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    list.classList.add('drag-over');

                    // Trouver la position d'insertion
                    const afterElement = this.getDragAfterElement(list, e.clientY);
                    if (draggedItem) {
                        if (afterElement) {
                            list.insertBefore(draggedItem, afterElement);
                        } else {
                            list.appendChild(draggedItem);
                        }
                    }
                });

                list.addEventListener('dragleave', (e) => {
                    if (!list.contains(e.relatedTarget)) {
                        list.classList.remove('drag-over');
                    }
                });

                list.addEventListener('drop', (e) => {
                    e.preventDefault();
                    list.classList.remove('drag-over');
                    if (draggedFeedbackId) {
                        this.updateFeedbackPriority(draggedFeedbackId, priority);
                    }
                });
            });
        },

        /**
         * Trouver l'élément après lequel insérer
         */
        getDragAfterElement: function(container, y) {
            const draggableElements = [...container.querySelectorAll('.wpvfh-pin-item:not(.dragging)')];

            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        },

        /**
         * Mettre à jour la priorité d'un feedback
         */
        updateFeedbackPriority: async function(feedbackId, priority) {
            try {
                // Mettre à jour localement d'abord pour un feedback immédiat
                const feedback = this.state.currentFeedbacks.find(f => f.id == feedbackId);
                if (feedback) {
                    feedback.priority = priority;
                }

                // Re-rendre immédiatement les listes
                this.renderPriorityLists();

                // Puis sauvegarder sur le serveur
                await this.apiRequest('POST', `feedbacks/${feedbackId}/priority`, {
                    priority: priority
                });

                this.showNotification('Priorité mise à jour', 'success');
            } catch (error) {
                console.error('[Blazing Feedback] Erreur mise à jour priorité:', error);
                this.showNotification('Erreur lors de la mise à jour', 'error');
                // Re-charger les feedbacks en cas d'erreur pour revenir à l'état serveur
                this.loadExistingFeedbacks();
            }
        },

        /**
         * Mettre à jour un champ meta d'un feedback (type, tags)
         * @param {number} feedbackId - ID du feedback
         * @param {string} field - Nom du champ (feedback_type, tags)
         * @param {string} value - Nouvelle valeur
         */
        updateFeedbackMeta: async function(feedbackId, field, value) {
            try {
                // Mettre à jour localement d'abord
                const feedback = this.state.currentFeedbacks.find(f => f.id == feedbackId);
                if (feedback) {
                    feedback[field] = value;
                    // Mettre à jour les labels dans la vue détails
                    this.updateDetailLabels(feedback);
                }

                // Sauvegarder sur le serveur
                const data = {};
                data[field] = value;
                await this.apiRequest('POST', `feedbacks/${feedbackId}`, data);

                // Re-rendre la liste
                this.renderPinsList();

                this.showNotification('Modification enregistrée', 'success');
            } catch (error) {
                console.error('[Blazing Feedback] Erreur mise à jour meta:', error);
                this.showNotification('Erreur lors de la mise à jour', 'error');
                this.loadExistingFeedbacks();
            }
        },

        /**
         * Sauvegarder l'ordre dans une liste de priorité (local uniquement)
         */
        savePriorityOrder: function(priority, list) {
            // L'ordre est géré localement pour le réarrangement visuel
            // La priorité est sauvegardée via updateFeedbackPriority
        },

        // ===========================================
        // MÉTADATAS TAB METHODS
        // ===========================================

        /**
         * Changer de sous-onglet métadatas
         */
        switchMetadataSubtab: function(subtabName) {
            // Mettre à jour les boutons de sous-onglet
            if (this.elements.metadataSubtabs) {
                this.elements.metadataSubtabs.forEach(subtab => {
                    subtab.classList.toggle('active', subtab.dataset.subtab === subtabName);
                });
            }

            // Mettre à jour les contenus
            if (this.elements.metadataSubtabContents) {
                this.elements.metadataSubtabContents.forEach(content => {
                    content.classList.toggle('active', content.dataset.group === subtabName);
                });
            }

            // Re-rendre les listes pour ce groupe
            this.renderMetadataListsForGroup(subtabName);
        },

        /**
         * Rendre les listes de métadatas pour tous les groupes actifs
         */
        renderMetadataLists: function() {
            // Trouver le sous-onglet actif
            const activeSubtab = document.querySelector('.wpvfh-subtab.active');
            if (activeSubtab) {
                this.renderMetadataListsForGroup(activeSubtab.dataset.subtab);
            }
        },

        /**
         * Vérifier si un statut est considéré comme traité
         */
        isStatusTreated: function(statusSlug) {
            // Chercher dans les metadataGroups
            const statusGroup = this.config.metadataGroups?.statuses;
            if (!statusGroup || !statusGroup.items) return false;

            const status = statusGroup.items.find(s => s.id === statusSlug);
            return status && status.is_treated;
        },

        /**
         * Rendre les listes de métadatas pour un groupe spécifique
         */
        renderMetadataListsForGroup: function(groupSlug) {
            const container = document.getElementById(`wpvfh-metadata-${groupSlug}`);
            if (!container) return;

            const sections = container.querySelectorAll('.wpvfh-metadata-section');
            const feedbacks = this.state.currentFeedbacks || [];

            // Déterminer le champ à utiliser selon le groupe
            const fieldMap = {
                'statuses': 'status',
                'types': 'feedback_type',
                'priorities': 'priority',
                'tags': 'tags'
            };
            const field = fieldMap[groupSlug] || groupSlug;

            // Vider toutes les listes
            sections.forEach(section => {
                const list = section.querySelector('.wpvfh-metadata-list');
                if (list) list.innerHTML = '';
            });

            // Filtrer les feedbacks traités (statuts avec is_treated = true)
            const visibleFeedbacks = feedbacks.filter(feedback => {
                const status = feedback.status || 'new';
                return !this.isStatusTreated(status);
            });

            // Grouper les feedbacks par valeur de métadata
            visibleFeedbacks.forEach(feedback => {
                let value = feedback[field];

                // Gestion spéciale pour les tags (multiple)
                if (groupSlug === 'tags') {
                    // Les tags sont souvent une chaîne séparée par virgules ou un tableau
                    const tags = Array.isArray(value) ? value : (value ? String(value).split(',').map(t => t.trim()) : []);
                    if (tags.length === 0) {
                        this.addFeedbackToMetadataList(groupSlug, 'none', feedback);
                    } else {
                        tags.forEach(tag => {
                            this.addFeedbackToMetadataList(groupSlug, tag, feedback);
                        });
                    }
                } else {
                    // Pour les autres groupes, une seule valeur
                    if (!value || value === '' || value === 'none') {
                        this.addFeedbackToMetadataList(groupSlug, 'none', feedback);
                    } else {
                        this.addFeedbackToMetadataList(groupSlug, value, feedback);
                    }
                }
            });

            // Masquer les sections vides
            sections.forEach(section => {
                const list = section.querySelector('.wpvfh-metadata-list');
                const count = list ? list.children.length : 0;

                if (count === 0) {
                    section.classList.add('wpvfh-section-empty');
                    section.style.display = 'none';