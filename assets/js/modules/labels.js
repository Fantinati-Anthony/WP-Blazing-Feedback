/**
 * Module Labels - Blazing Feedback
 * Gestion des labels (type, statut, priorité)
 * @package Blazing_Feedback
 */
(function(window) {
    'use strict';

    const Labels = {
        /**
         * Initialiser le module
         * @param {Object} widget
         */
        init: function(widget) {
            this.widget = widget;
        },

        /**
         * Obtenir la config d'un type
         * @param {string} typeId
         * @returns {Object|null}
         */
        getTypeConfig: function(typeId) {
            const data = window.wpvfhData;
            if (!data || !data.feedbackTypes) return null;
            return data.feedbackTypes.find(t => t.id === typeId) || null;
        },

        /**
         * Obtenir la config d'une priorité
         * @param {string} priorityId
         * @returns {Object|null}
         */
        getPriorityConfig: function(priorityId) {
            const data = window.wpvfhData;
            if (!data || !data.priorities) return null;
            return data.priorities.find(p => p.id === priorityId) || null;
        },

        /**
         * Obtenir la config d'un statut
         * @param {string} statusId
         * @returns {Object|null}
         */
        getStatusConfig: function(statusId) {
            const data = window.wpvfhData;
            if (!data || !data.statuses) return null;
            return data.statuses.find(s => s.id === statusId) || null;
        },

        /**
         * Obtenir le label d'un statut
         * @param {string} statusId
         * @returns {string}
         */
        getStatusLabel: function(statusId) {
            const status = this.getStatusConfig(statusId);
            return status ? status.label : statusId;
        },

        /**
         * Obtenir l'emoji d'un statut
         * @param {string} statusId
         * @returns {string}
         */
        getStatusEmoji: function(statusId) {
            const status = this.getStatusConfig(statusId);
            return status ? status.emoji : '';
        },

        /**
         * Obtenir la couleur d'un statut
         * @param {string} statusId
         * @returns {string}
         */
        getStatusColor: function(statusId) {
            const status = this.getStatusConfig(statusId);
            return status ? status.color : '#95a5a6';
        },

        /**
         * Obtenir le label d'un type
         * @param {string} typeId
         * @returns {string}
         */
        getTypeLabel: function(typeId) {
            const type = this.getTypeConfig(typeId);
            return type ? type.label : typeId || '';
        },

        /**
         * Obtenir l'emoji d'un type
         * @param {string} typeId
         * @returns {string}
         */
        getTypeEmoji: function(typeId) {
            const type = this.getTypeConfig(typeId);
            return type ? type.emoji : '';
        },

        /**
         * Obtenir le label d'une priorité
         * @param {string} priorityId
         * @returns {string}
         */
        getPriorityLabel: function(priorityId) {
            const priority = this.getPriorityConfig(priorityId);
            return priority ? priority.label : priorityId || '';
        },

        /**
         * Obtenir l'emoji d'une priorité
         * @param {string} priorityId
         * @returns {string}
         */
        getPriorityEmoji: function(priorityId) {
            const priority = this.getPriorityConfig(priorityId);
            return priority ? priority.emoji : '';
        },

        /**
         * Obtenir la couleur d'une priorité
         * @param {string} priorityId
         * @returns {string}
         */
        getPriorityColor: function(priorityId) {
            const priority = this.getPriorityConfig(priorityId);
            return priority ? priority.color : '#95a5a6';
        },

        /**
         * Générer le HTML des labels pour un feedback
         * @param {Object} feedback
         * @returns {string}
         */
        generateFeedbackLabelsHtml: function(feedback) {
            let html = '';
            const tools = this.widget.modules.tools;

            if (feedback.feedback_type) {
                const type = this.getTypeConfig(feedback.feedback_type);
                if (type) {
                    html += `<span class="wpvfh-pin-label wpvfh-pin-label-type" data-type="${feedback.feedback_type}" style="background-color: ${type.color}20; color: ${type.color}; border-color: ${type.color}40;">${type.emoji} ${type.label}</span>`;
                }
            }

            if (feedback.priority && feedback.priority !== 'none') {
                const priority = this.getPriorityConfig(feedback.priority);
                if (priority) {
                    html += `<span class="wpvfh-pin-label wpvfh-pin-label-priority" data-priority="${feedback.priority}" style="background-color: ${priority.color}20; color: ${priority.color}; border-color: ${priority.color}40;">${priority.emoji} ${priority.label}</span>`;
                }
            }

            if (feedback.tags && feedback.tags.trim()) {
                const tagList = feedback.tags.split(',').map(t => t.trim()).filter(t => t).slice(0, 3);
                const tagsModule = this.widget.modules.tags;
                tagList.forEach(tag => {
                    const tagColor = tagsModule.getPredefinedTagColor(tag) || '#2980b9';
                    html += `<span class="wpvfh-pin-label wpvfh-pin-label-tag" style="background-color: ${tagColor}20; color: ${tagColor}; border-color: ${tagColor}40;">#${tools.escapeHtml(tag)}</span>`;
                });
            }

            return html ? `<div class="wpvfh-pin-item-labels">${html}</div>` : '';
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.labels = Labels;

})(window);
