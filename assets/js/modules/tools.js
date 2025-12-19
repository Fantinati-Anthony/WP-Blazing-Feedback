/**
 * Module Tools - Blazing Feedback
 * Utilitaires DOM et helpers
 * @package Blazing_Feedback
 */
(function(window) {
    'use strict';

    const Tools = {
        /**
         * Initialiser le module
         * @param {Object} widget - Instance BlazingFeedback
         */
        init: function(widget) {
            this.widget = widget;
        },

        /**
         * Échapper le HTML
         * @param {string} str
         * @returns {string}
         */
        escapeHtml: function(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        },

        /**
         * Tronquer un texte
         * @param {string} text
         * @param {number} maxLength
         * @returns {string}
         */
        truncateText: function(text, maxLength = 100) {
            if (!text || text.length <= maxLength) return text;
            return text.substring(0, maxLength - 3) + '...';
        },

        /**
         * Formater la taille d'un fichier
         * @param {number} bytes
         * @returns {string}
         */
        formatFileSize: function(bytes) {
            if (bytes < 1024) return bytes + ' o';
            if (bytes < 1024 * 1024) return Math.round(bytes / 1024) + ' Ko';
            return (bytes / (1024 * 1024)).toFixed(1) + ' Mo';
        },

        /**
         * Obtenir l'icône d'un fichier selon son type
         * @param {string} mimeType
         * @returns {string}
         */
        getFileIcon: function(mimeType) {
            if (!mimeType) return '📎';
            if (mimeType.startsWith('image/')) return '🖼️';
            if (mimeType === 'application/pdf') return '📕';
            if (mimeType.includes('word') || mimeType.includes('document')) return '📝';
            if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return '📊';
            return '📎';
        },

        /**
         * Extraire un titre de l'URL
         * @param {string} url
         * @returns {string}
         */
        extractPageTitle: function(url) {
            try {
                const urlObj = new URL(url);
                let path = urlObj.pathname;
                path = path.replace(/^\/|\/$/g, '');
                if (!path || path === '') return 'Accueil';
                const segments = path.split('/');
                let title = segments[segments.length - 1];
                title = title.replace(/\.(html?|php|aspx?)$/i, '');
                title = title.replace(/[-_]/g, ' ');
                return title.charAt(0).toUpperCase() + title.slice(1);
            } catch (e) {
                return url;
            }
        },

        /**
         * Raccourcir une URL
         * @param {string} url
         * @returns {string}
         */
        shortenUrl: function(url) {
            try {
                const urlObj = new URL(url);
                return urlObj.pathname || '/';
            } catch (e) {
                return url;
            }
        },

        /**
         * Émettre un événement personnalisé
         * @param {string} name
         * @param {Object} detail
         */
        emitEvent: function(name, detail = {}) {
            const event = new CustomEvent('blazing-feedback:' + name, {
                bubbles: true,
                detail: detail,
            });
            document.dispatchEvent(event);
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.tools = Tools;

})(window);
