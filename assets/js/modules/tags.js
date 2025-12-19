/**
 * Module Tags - Blazing Feedback
 * Gestion des tags
 * @package Blazing_Feedback
 */
(function(window) {
    'use strict';

    const Tags = {
        formTags: [],

        init: function(widget) {
            this.widget = widget;
            this.formTags = [];
        },

        /**
         * Obtenir la couleur d'un tag prédéfini
         */
        getPredefinedTagColor: function(tagName) {
            const data = window.wpvfhData;
            if (!data || !data.predefinedTags) return null;
            const found = data.predefinedTags.find(t =>
                t.label.toLowerCase() === tagName.toLowerCase()
            );
            return found ? found.color : null;
        },

        /**
         * Ajouter un tag au formulaire
         */
        addFormTag: function(newTag, color) {
            const tagLower = newTag.toLowerCase();
            if (this.formTags.some(t => t.name.toLowerCase() === tagLower)) {
                this.widget.modules.notifications.show('Ce tag existe déjà', 'warning');
                return;
            }

            const tagColor = color || this.getPredefinedTagColor(newTag) || '#2980b9';
            this.formTags.push({ name: newTag, color: tagColor });
            this.renderFormTags();
            this.updateFormTagsHidden();
        },

        /**
         * Supprimer un tag du formulaire
         */
        removeFormTag: function(tagToRemove) {
            this.formTags = this.formTags.filter(t => t.name !== tagToRemove);
            this.renderFormTags();
            this.updateFormTagsHidden();
        },

        /**
         * Rendre les tags du formulaire
         */
        renderFormTags: function() {
            const container = this.widget.elements.feedbackTagsContainer;
            if (!container) return;

            const existingBadges = container.querySelectorAll('.wpvfh-tag-badge');
            existingBadges.forEach(badge => badge.remove());

            const input = this.widget.elements.feedbackTagsInput;
            const tools = this.widget.modules.tools;

            this.formTags.forEach(tagObj => {
                const badge = document.createElement('span');
                badge.className = 'wpvfh-tag-badge';
                badge.style.cssText = `background-color: ${tagObj.color}20 !important; color: ${tagObj.color} !important; border: 1px solid ${tagObj.color}40 !important;`;
                badge.innerHTML = `${tools.escapeHtml(tagObj.name)}<button type="button" class="wpvfh-tag-remove" title="Supprimer">×</button>`;

                const removeBtn = badge.querySelector('.wpvfh-tag-remove');
                removeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.removeFormTag(tagObj.name);
                });

                container.insertBefore(badge, input);
            });
        },

        /**
         * Mettre à jour le champ hidden
         */
        updateFormTagsHidden: function() {
            const field = this.widget.elements.feedbackTags;
            if (field) {
                field.value = this.formTags.map(t => t.name).join(', ');
            }
        },

        /**
         * Réinitialiser les tags du formulaire
         */
        clearFormTags: function() {
            this.formTags = [];
            this.renderFormTags();
            this.updateFormTagsHidden();
        },

        /**
         * Rendre les tags dans la vue détails
         */
        renderDetailTags: function(tagsString) {
            const container = this.widget.elements.detailTagsContainer;
            if (!container) return;

            const existingBadges = container.querySelectorAll('.wpvfh-tag-badge');
            existingBadges.forEach(badge => badge.remove());

            const input = this.widget.elements.detailTagsInput;
            const tools = this.widget.modules.tools;

            if (tagsString && tagsString.trim()) {
                const tagList = tagsString.split(',').map(t => t.trim()).filter(t => t);
                tagList.forEach(tag => {
                    const tagColor = this.getPredefinedTagColor(tag) || '#2980b9';
                    const badge = document.createElement('span');
                    badge.className = 'wpvfh-tag-badge';
                    badge.style.cssText = `background-color: ${tagColor}20 !important; color: ${tagColor} !important; border: 1px solid ${tagColor}40 !important;`;
                    badge.innerHTML = `${tools.escapeHtml(tag)}<button type="button" class="wpvfh-tag-remove" title="Supprimer">×</button>`;

                    const removeBtn = badge.querySelector('.wpvfh-tag-remove');
                    removeBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.removeDetailTag(tag);
                    });

                    container.insertBefore(badge, input);
                });
            }
        },

        /**
         * Ajouter un tag au feedback courant (vue détails)
         */
        addDetailTag: async function(newTag) {
            const feedbackId = this.widget.state.currentFeedbackId;
            if (!feedbackId) return;

            const feedback = this.widget.state.currentFeedbacks.find(f => f.id == feedbackId);
            if (!feedback) return;

            const tags = feedback.tags || '';
            const tagList = tags.split(',').map(t => t.trim()).filter(t => t);
            const tagLower = newTag.toLowerCase();

            if (tagList.some(t => t.toLowerCase() === tagLower)) {
                this.widget.modules.notifications.show('Ce tag existe déjà', 'warning');
                return;
            }

            tagList.push(newTag);
            const newTags = tagList.join(', ');
            feedback.tags = newTags;

            this.renderDetailTags(newTags);
            await this.widget.modules.api.request('POST', `feedbacks/${feedbackId}`, { tags: newTags });
        },

        /**
         * Supprimer un tag du feedback courant (vue détails)
         */
        removeDetailTag: async function(tagToRemove) {
            const feedbackId = this.widget.state.currentFeedbackId;
            if (!feedbackId) return;

            const feedback = this.widget.state.currentFeedbacks.find(f => f.id == feedbackId);
            if (!feedback) return;

            const tags = feedback.tags || '';
            const tagList = tags.split(',').map(t => t.trim()).filter(t => t && t !== tagToRemove);
            const newTags = tagList.join(', ');
            feedback.tags = newTags;

            this.renderDetailTags(newTags);
            await this.widget.modules.api.request('POST', `feedbacks/${feedbackId}`, { tags: newTags });
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.tags = Tags;

})(window);
