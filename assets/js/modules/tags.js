/**
 * Gestion des tags (ajout, suppression)
 * 
 * Reference file for feedback-widget.js lines 2397-2640
 * See main file: assets/js/feedback-widget.js
 * 
 * Methods included:
 * - 
addTag * - removeTag * - removeLastTag * - getPredefinedTagColor * - addFormTag * - removeFormTag * - removeLastFormTag * - renderFormTags * - updateFormTagsHidden * - clearFormTags
 * 
 * @package Blazing_Feedback
 */

/* 
 * To view this section, read feedback-widget.js with:
 * offset=2397, limit=244
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