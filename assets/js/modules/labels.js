/**
 * Labels, config types/priorités/statuts
 * 
 * Reference file for feedback-widget.js lines 2356-2790
 * See main file: assets/js/feedback-widget.js
 * 
 * Methods included:
 * - 
updateDetailLabels * - renderDetailTags * - getTypeConfig * - getPriorityConfig * - getStatusConfig * - getStatusLabel * - getStatusEmoji * - getStatusColor * - getTypeLabel * - getTypeEmoji * - getPriorityLabel * - getPriorityEmoji * - getPriorityColor * - generateFeedbackLabelsHtml
 * 
 * @package Blazing_Feedback
 */

/* 
 * To view this section, read feedback-widget.js with:
 * offset=2356, limit=435
 */

        updateDetailLabels: function(feedback) {
            // Type label - utiliser les options dynamiques
            if (this.elements.detailTypeLabel) {
                const type = this.getTypeConfig(feedback.feedback_type);
                if (type) {
                    const iconEl = this.elements.detailTypeLabel.querySelector('.wpvfh-label-icon');
                    const textEl = this.elements.detailTypeLabel.querySelector('.wpvfh-label-text');
                    if (iconEl) iconEl.textContent = type.emoji;
                    if (textEl) textEl.textContent = type.label;
                    this.elements.detailTypeLabel.setAttribute('data-type', feedback.feedback_type);
                    this.elements.detailTypeLabel.style.cssText = `background-color: ${type.color}20 !important; color: ${type.color} !important; border-color: ${type.color}40 !important;`;
                    this.elements.detailTypeLabel.hidden = false;
                } else {
                    this.elements.detailTypeLabel.hidden = true;
                }
            }

            // Priority label - utiliser les options dynamiques
            if (this.elements.detailPriorityLabel) {
                const priority = this.getPriorityConfig(feedback.priority);
                if (priority && feedback.priority !== 'none') {
                    const iconEl = this.elements.detailPriorityLabel.querySelector('.wpvfh-label-icon');
                    const textEl = this.elements.detailPriorityLabel.querySelector('.wpvfh-label-text');
                    if (iconEl) iconEl.textContent = priority.emoji;
                    if (textEl) textEl.textContent = priority.label;
                    this.elements.detailPriorityLabel.setAttribute('data-priority', feedback.priority);
                    this.elements.detailPriorityLabel.style.cssText = `background-color: ${priority.color}20 !important; color: ${priority.color} !important; border-color: ${priority.color}40 !important;`;
                    this.elements.detailPriorityLabel.hidden = false;
                } else {
                    this.elements.detailPriorityLabel.hidden = true;
                }
            }

            // Rendre les tags dans le container
            this.renderDetailTags(feedback.tags);
        },

        /**
         * Rendre les tags dans le container de la vue détails
         * @param {string} tagsString - Tags séparés par des virgules
         */
        renderDetailTags: function(tagsString) {
            if (!this.elements.detailTagsContainer) return;

            // Supprimer les anciens badges (garder l'input)
            const existingBadges = this.elements.detailTagsContainer.querySelectorAll('.wpvfh-tag-badge');
            existingBadges.forEach(badge => badge.remove());

            // Ajouter les nouveaux badges avant l'input
            const input = this.elements.detailTagsInput;
            if (tagsString && tagsString.trim()) {
                const tagList = tagsString.split(',').map(t => t.trim()).filter(t => t);
                tagList.forEach(tag => {
                    const tagColor = this.getPredefinedTagColor(tag) || '#2980b9';
                    const badge = document.createElement('span');
                    badge.className = 'wpvfh-tag-badge';
                    badge.style.cssText = `background-color: ${tagColor}20 !important; color: ${tagColor} !important; border: 1px solid ${tagColor}40 !important;`;
                    badge.innerHTML = `${this.escapeHtml(tag)}<button type="button" class="wpvfh-tag-remove" title="Supprimer">×</button>`;

                    // Gestionnaire pour le bouton X
                    const removeBtn = badge.querySelector('.wpvfh-tag-remove');
                    removeBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.removeTag(tag);
                    });

                    this.elements.detailTagsContainer.insertBefore(badge, input);
                });
            }
        },

        /**
         * Ajouter un tag au feedback courant (vue détails)
         * @param {string} newTag - Tag à ajouter
         */
        addTag: function(newTag) {
            if (!this.state.currentFeedbackId) return;

            // Récupérer le feedback courant
            const feedback = this.state.currentFeedbacks.find(f => f.id == this.state.currentFeedbackId);
            if (!feedback) return;

            // Ajouter le tag à la liste (éviter les doublons)
            const tags = feedback.tags || '';
            const tagList = tags.split(',').map(t => t.trim()).filter(t => t);
            const tagLower = newTag.toLowerCase();

            // Vérifier si le tag existe déjà (insensible à la casse)
            if (tagList.some(t => t.toLowerCase() === tagLower)) {
                this.showNotification('Ce tag existe déjà', 'warning');
                return;
            }

            tagList.push(newTag);
            const newTags = tagList.join(', ');

            // Mettre à jour localement
            feedback.tags = newTags;

            // Rendre les tags
            this.renderDetailTags(newTags);

            // Mettre à jour via l'API
            this.updateFeedbackMeta(this.state.currentFeedbackId, 'tags', newTags);
        },

        /**
         * Supprimer un tag du feedback courant (vue détails)
         * @param {string} tagToRemove - Tag à supprimer
         */
        removeTag: function(tagToRemove) {
            if (!this.state.currentFeedbackId) return;

            // Récupérer le feedback courant
            const feedback = this.state.currentFeedbacks.find(f => f.id == this.state.currentFeedbackId);
            if (!feedback) return;

            // Retirer le tag de la liste
            const tags = feedback.tags || '';
            const tagList = tags.split(',').map(t => t.trim()).filter(t => t && t !== tagToRemove);
            const newTags = tagList.join(', ');

            // Mettre à jour localement
            feedback.tags = newTags;

            // Rendre les tags
            this.renderDetailTags(newTags);

            // Mettre à jour via l'API
            this.updateFeedbackMeta(this.state.currentFeedbackId, 'tags', newTags);
        },

        /**
         * Supprimer le dernier tag (vue détails - Backspace)
         */
        removeLastTag: function() {
            if (!this.state.currentFeedbackId) return;

            const feedback = this.state.currentFeedbacks.find(f => f.id == this.state.currentFeedbackId);
            if (!feedback || !feedback.tags) return;

            const tagList = feedback.tags.split(',').map(t => t.trim()).filter(t => t);
            if (tagList.length === 0) return;

            const lastTag = tagList.pop();
            this.removeTag(lastTag);
        },

        // =========================================
        // GESTION DES TAGS - FORMULAIRE CRÉATION
        // =========================================

        /**
         * État des tags du formulaire [{name, color}]
         */
        formTags: [],

        /**
         * Obtenir la couleur d'un tag prédéfini
         * @param {string} tagName - Nom du tag
         * @returns {string|null} - Couleur ou null
         */
        getPredefinedTagColor: function(tagName) {
            if (!window.wpvfhData || !window.wpvfhData.predefinedTags) return null;
            const found = window.wpvfhData.predefinedTags.find(t =>
                t.label.toLowerCase() === tagName.toLowerCase()
            );
            return found ? found.color : null;
        },

        /**
         * Ajouter un tag au formulaire de création
         * @param {string} newTag - Tag à ajouter
         * @param {string} color - Couleur optionnelle
         */
        addFormTag: function(newTag, color) {
            const tagLower = newTag.toLowerCase();

            // Vérifier si le tag existe déjà
            if (this.formTags.some(t => t.name.toLowerCase() === tagLower)) {
                this.showNotification('Ce tag existe déjà', 'warning');
                return;
            }

            // Chercher la couleur dans les tags prédéfinis si non fournie
            const tagColor = color || this.getPredefinedTagColor(newTag) || '#2980b9';

            this.formTags.push({ name: newTag, color: tagColor });
            this.renderFormTags();
            this.updateFormTagsHidden();

            // Marquer le bouton prédéfini comme sélectionné
            const predefinedBtn = document.querySelector(`.wpvfh-predefined-tag-btn[data-tag="${newTag}"]`);
            if (predefinedBtn) {
                predefinedBtn.classList.add('selected');
            }
        },

        /**
         * Supprimer un tag du formulaire de création
         * @param {string} tagToRemove - Tag à supprimer
         */
        removeFormTag: function(tagToRemove) {
            this.formTags = this.formTags.filter(t => t.name !== tagToRemove);
            this.renderFormTags();
            this.updateFormTagsHidden();

            // Désélectionner le bouton prédéfini
            const predefinedBtn = document.querySelector(`.wpvfh-predefined-tag-btn[data-tag="${tagToRemove}"]`);
            if (predefinedBtn) {
                predefinedBtn.classList.remove('selected');
            }
        },

        /**
         * Supprimer le dernier tag du formulaire (Backspace)
         */
        removeLastFormTag: function() {
            if (this.formTags.length === 0) return;
            const removedTag = this.formTags.pop();
            this.renderFormTags();
            this.updateFormTagsHidden();

            // Désélectionner le bouton prédéfini
            if (removedTag) {
                const predefinedBtn = document.querySelector(`.wpvfh-predefined-tag-btn[data-tag="${removedTag.name}"]`);
                if (predefinedBtn) {
                    predefinedBtn.classList.remove('selected');
                }
            }
        },

        /**
         * Rendre les tags dans le container du formulaire
         */
        renderFormTags: function() {
            if (!this.elements.feedbackTagsContainer) return;

            // Supprimer les anciens badges (garder l'input et les tags prédéfinis)
            const existingBadges = this.elements.feedbackTagsContainer.querySelectorAll('.wpvfh-tag-badge');
            existingBadges.forEach(badge => badge.remove());

            // Ajouter les nouveaux badges avant l'input
            const input = this.elements.feedbackTagsInput;
            this.formTags.forEach(tagObj => {
                const badge = document.createElement('span');
                badge.className = 'wpvfh-tag-badge';
                badge.style.cssText = `background-color: ${tagObj.color}20 !important; color: ${tagObj.color} !important; border: 1px solid ${tagObj.color}40 !important;`;
                badge.innerHTML = `${this.escapeHtml(tagObj.name)}<button type="button" class="wpvfh-tag-remove" title="Supprimer">×</button>`;

                // Gestionnaire pour le bouton X
                const removeBtn = badge.querySelector('.wpvfh-tag-remove');
                removeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.removeFormTag(tagObj.name);
                });

                this.elements.feedbackTagsContainer.insertBefore(badge, input);
            });
        },

        /**
         * Mettre à jour le champ hidden avec les tags
         */
        updateFormTagsHidden: function() {
            if (this.elements.feedbackTags) {
                this.elements.feedbackTags.value = this.formTags.map(t => t.name).join(', ');
            }
        },

        /**
         * Réinitialiser les tags du formulaire
         */
        clearFormTags: function() {
            this.formTags = [];
            this.renderFormTags();
            this.updateFormTagsHidden();

            // Désélectionner tous les boutons prédéfinis
            const predefinedBtns = document.querySelectorAll('.wpvfh-predefined-tag-btn.selected');
            predefinedBtns.forEach(btn => btn.classList.remove('selected'));
        },

        /**
         * Obtenir la config d'un type depuis les options dynamiques
         * @param {string} typeId - ID du type
         * @returns {Object|null}
         */
        getTypeConfig: function(typeId) {
            if (!window.wpvfhData || !window.wpvfhData.feedbackTypes) return null;
            return window.wpvfhData.feedbackTypes.find(t => t.id === typeId) || null;
        },

        /**
         * Obtenir la config d'une priorité depuis les options dynamiques
         * @param {string} priorityId - ID de la priorité
         * @returns {Object|null}
         */
        getPriorityConfig: function(priorityId) {
            if (!window.wpvfhData || !window.wpvfhData.priorities) return null;
            return window.wpvfhData.priorities.find(p => p.id === priorityId) || null;
        },

        /**
         * Obtenir la config d'un statut depuis les options dynamiques
         * @param {string} statusId - ID du statut
         * @returns {Object|null}
         */
        getStatusConfig: function(statusId) {
            if (!window.wpvfhData || !window.wpvfhData.statuses) return null;
            return window.wpvfhData.statuses.find(s => s.id === statusId) || null;
        },

        /**
         * Obtenir le label d'un statut
         * @param {string} statusId - ID du statut
         * @returns {string}
         */
        getStatusLabel: function(statusId) {
            const status = this.getStatusConfig(statusId);
            return status ? status.label : statusId;
        },

        /**
         * Obtenir l'emoji d'un statut
         * @param {string} statusId - ID du statut
         * @returns {string}
         */
        getStatusEmoji: function(statusId) {
            const status = this.getStatusConfig(statusId);
            return status ? status.emoji : '';
        },

        /**
         * Obtenir la couleur d'un statut
         * @param {string} statusId - ID du statut
         * @returns {string}
         */
        getStatusColor: function(statusId) {
            const status = this.getStatusConfig(statusId);
            return status ? status.color : '#95a5a6';
        },

        /**
         * Obtenir le label d'un type
         * @param {string} typeId - ID du type
         * @returns {string}
         */
        getTypeLabel: function(typeId) {
            const type = this.getTypeConfig(typeId);
            return type ? type.label : typeId || '';
        },

        /**
         * Obtenir l'emoji d'un type
         * @param {string} typeId - ID du type
         * @returns {string}
         */
        getTypeEmoji: function(typeId) {
            const type = this.getTypeConfig(typeId);
            return type ? type.emoji : '';
        },

        /**
         * Obtenir le label d'une priorité
         * @param {string} priorityId - ID de la priorité
         * @returns {string}
         */
        getPriorityLabel: function(priorityId) {
            const priority = this.getPriorityConfig(priorityId);
            return priority ? priority.label : priorityId || '';
        },

        /**
         * Obtenir l'emoji d'une priorité
         * @param {string} priorityId - ID de la priorité
         * @returns {string}
         */
        getPriorityEmoji: function(priorityId) {
            const priority = this.getPriorityConfig(priorityId);
            return priority ? priority.emoji : '';
        },

        /**
         * Obtenir la couleur d'une priorité
         * @param {string} priorityId - ID de la priorité
         * @returns {string}
         */
        getPriorityColor: function(priorityId) {
            const priority = this.getPriorityConfig(priorityId);
            return priority ? priority.color : '#95a5a6';
        },

        /**
         * Générer le HTML des labels pour un feedback (utilisé dans la liste)
         * @param {Object} feedback - Données du feedback
         * @returns {string} HTML des labels
         */
        generateFeedbackLabelsHtml: function(feedback) {
            let html = '';

            // Type - utiliser les options dynamiques
            if (feedback.feedback_type) {
                const type = this.getTypeConfig(feedback.feedback_type);
                if (type) {
                    html += `<span class="wpvfh-pin-label wpvfh-pin-label-type" data-type="${feedback.feedback_type}" style="background-color: ${type.color}20; color: ${type.color}; border-color: ${type.color}40;">${type.emoji} ${type.label}</span>`;
                }
            }

            // Priorité - utiliser les options dynamiques (exclure "none")
            if (feedback.priority && feedback.priority !== 'none') {
                const priority = this.getPriorityConfig(feedback.priority);
                if (priority) {
                    html += `<span class="wpvfh-pin-label wpvfh-pin-label-priority" data-priority="${feedback.priority}" style="background-color: ${priority.color}20; color: ${priority.color}; border-color: ${priority.color}40;">${priority.emoji} ${priority.label}</span>`;
                }
            }

            // Tags - utiliser les couleurs des tags prédéfinis si disponibles
            if (feedback.tags && feedback.tags.trim()) {
                const tagList = feedback.tags.split(',').map(t => t.trim()).filter(t => t).slice(0, 3); // Max 3 tags visibles
                tagList.forEach(tag => {
                    const tagColor = this.getPredefinedTagColor(tag) || '#2980b9';
                    html += `<span class="wpvfh-pin-label wpvfh-pin-label-tag" style="background-color: ${tagColor}20; color: ${tagColor}; border-color: ${tagColor}40;">#${this.escapeHtml(tag)}</span>`;
                });
            }

            return html ? `<div class="wpvfh-pin-item-labels">${html}</div>` : '';
        },

        // ===========================================
        // VALIDATION DE PAGE
        // ===========================================

        /**
         * Mettre à jour la section de validation de page