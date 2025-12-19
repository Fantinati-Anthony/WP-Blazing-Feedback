/**
 * Appels API, chargement feedbacks
 * 
 * Reference file for feedback-widget.js lines 3350-3550
 * See main file: assets/js/feedback-widget.js
 * 
 * Methods included:
 * - 
loadExistingFeedbacks * - handleFeedbacksLoaded
 * 
 * @package Blazing_Feedback
 */

/* 
 * To view this section, read feedback-widget.js with:
 * offset=3350, limit=201
 */

        },

        /**
         * Charger la liste des utilisateurs pour les mentions
         */
        loadMentionUsers: async function() {
            try {
                const response = await this.apiRequest('GET', 'users');
                this.state.mentionUsers = Array.isArray(response) ? response : [];
            } catch (error) {
                console.error('[Blazing Feedback] Erreur chargement utilisateurs:', error);
                this.state.mentionUsers = [];
            }
        },

        // ===========================================
        // PRIORITÉ
        // ===========================================

        /**
         * Mettre à jour la visibilité de l'onglet Priorité
         */
        updatePriorityTabVisibility: function(feedbackCount) {
            const priorityTabBtn = document.querySelector('.wpvfh-tab[data-tab="priority"]');
            if (priorityTabBtn) {
                priorityTabBtn.style.display = feedbackCount > 0 ? '' : 'none';
            }
        },

        /**
         * Rendre les listes de priorité
         */
        renderPriorityLists: function() {
            const lists = this.elements.priorityLists;
            if (!lists || !lists.none) return;

            // Vider les listes
            Object.values(lists).forEach(list => {
                if (list) list.innerHTML = '';
            });

            // Grouper les feedbacks par priorité (ordre: none, high, medium, low)
            const feedbacksByPriority = {
                none: [],
                high: [],
                medium: [],
                low: []
            };

            this.state.currentFeedbacks.forEach(feedback => {
                const priority = feedback.priority || 'none';
                if (feedbacksByPriority[priority]) {
                    feedbacksByPriority[priority].push(feedback);
                } else {
                    feedbacksByPriority.none.push(feedback);
                }
            });

            // Trier par ordre dans chaque groupe
            Object.keys(feedbacksByPriority).forEach(priority => {
                feedbacksByPriority[priority].sort((a, b) => (a.priority_order || 0) - (b.priority_order || 0));
            });

            // Rendre les feedbacks dans chaque liste et gérer la visibilité des sections
            Object.keys(feedbacksByPriority).forEach(priority => {
                const list = lists[priority];
                if (!list) return;

                const section = list.closest('.wpvfh-priority-section');
                const allFeedbacks = feedbacksByPriority[priority];

                // Séparer les feedbacks actifs et archivés
                const activeFeedbacks = allFeedbacks.filter(f => !['resolved', 'rejected'].includes(f.status));
                const archivedFeedbacks = allFeedbacks.filter(f => ['resolved', 'rejected'].includes(f.status));

                const hasFeedbacks = allFeedbacks.length > 0;

                // Masquer la section si vide
                if (section) {
                    section.style.display = hasFeedbacks ? '' : 'none';
                }

                // Ajouter les feedbacks actifs
                activeFeedbacks.forEach(feedback => {
                    const item = this.createPriorityItem(feedback);
                    list.appendChild(item);
                });

                // Ajouter la zone dépliable pour les feedbacks archivés
                if (archivedFeedbacks.length > 0) {
                    const collapsible = document.createElement('div');
                    collapsible.className = 'wpvfh-priority-archived';

                    const toggle = document.createElement('button');
                    toggle.type = 'button';
                    toggle.className = 'wpvfh-archived-toggle';
                    toggle.innerHTML = `<span class="wpvfh-archived-icon">▶</span> Résolu/Rejeté (${archivedFeedbacks.length})`;

                    const content = document.createElement('div');
                    content.className = 'wpvfh-archived-content';
                    content.style.display = 'none';

                    archivedFeedbacks.forEach(feedback => {
                        const item = this.createPriorityItem(feedback);
                        content.appendChild(item);
                    });

                    toggle.addEventListener('click', () => {
                        const isExpanded = content.style.display !== 'none';
                        content.style.display = isExpanded ? 'none' : 'block';
                        toggle.querySelector('.wpvfh-archived-icon').textContent = isExpanded ? '▶' : '▼';
                    });

                    collapsible.appendChild(toggle);
                    collapsible.appendChild(content);
                    list.appendChild(collapsible);
                }
            });

            // Initialiser le drag-drop
            this.initPriorityDragDrop();
        },

        /**
         * Créer un élément de priorité
         */
        createPriorityItem: function(feedback) {
            const item = document.createElement('div');
            item.className = 'wpvfh-pin-item';
            item.draggable = true;
            item.dataset.feedbackId = feedback.id;

            const status = feedback.status || 'new';
            const statusClass = `status-${status}`;
            const statusLabel = BlazingFeedback.getStatusLabel(status);
            const statusColor = BlazingFeedback.getStatusColor(status);

            item.innerHTML = `
                <span class="wpvfh-pin-marker ${statusClass}" style="background-color: ${statusColor};"></span>
                <div class="wpvfh-pin-content">
                    <div class="wpvfh-pin-header">
                        <span class="wpvfh-pin-id">#${feedback.id}</span>
                    </div>
                    <div class="wpvfh-pin-text">${this.escapeHtml(feedback.content || feedback.comment || 'Sans commentaire')}</div>
                    <div class="wpvfh-pin-meta">
                        <span class="wpvfh-pin-status ${statusClass}" style="color: ${statusColor};">${statusLabel}</span>
                    </div>
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
         * Initialiser le drag-drop pour la priorité
         */
        initPriorityDragDrop: function() {
            const lists = this.elements.priorityLists;
            const dropzones = this.elements.priorityDropzones;
            let draggedItem = null;
            let draggedFeedbackId = null;

            // Gestionnaires pour les items
            Object.values(lists).forEach(list => {
                if (!list) return;

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
                        // Retirer les classes drag-over
                        document.querySelectorAll('.drag-over').forEach(el => el.classList.remove('drag-over'));
                    });
                });
            });

            // Gestionnaires pour les zones de dépôt (sticky)
            dropzones.forEach(zone => {
                zone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    e.dataTransfer.dropEffect = 'move';
                    zone.classList.add('drag-over');
                });