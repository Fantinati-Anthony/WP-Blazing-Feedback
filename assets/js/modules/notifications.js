/**
 * Notifications, helpers
 * 
 * Reference file for feedback-widget.js lines 2254-2310
 * See main file: assets/js/feedback-widget.js
 * 
 * Methods included:
 * - 
showNotification * - escapeHtml * - emitEvent
 * 
 * @package Blazing_Feedback
 */

/* 
 * To view this section, read feedback-widget.js with:
 * offset=2254, limit=57
 */

        showNotification: function(message, type = 'info') {
            if (!this.elements.notifications) return;

            const notification = document.createElement('div');
            notification.className = `wpvfh-notification wpvfh-notification-${type}`;
            notification.textContent = message;

            this.elements.notifications.appendChild(notification);

            // Animation d'entrée
            requestAnimationFrame(() => {
                notification.classList.add('wpvfh-notification-show');
            });

            // Supprimer après 4 secondes
            setTimeout(() => {
                notification.classList.remove('wpvfh-notification-show');
                setTimeout(() => notification.remove(), 300);
            }, 4000);
        },

        /**
         * Échapper le HTML
         * @param {string} text - Texte à échapper
         * @returns {string} Texte échappé
         */
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        /**
         * Émettre un événement personnalisé
         * @param {string} name - Nom de l'événement
         * @param {Object} detail - Détails
         * @returns {void}
         */
        emitEvent: function(name, detail = {}) {
            const event = new CustomEvent('blazing-feedback:' + name, {
                bubbles: true,
                detail: detail,
            });
            document.dispatchEvent(event);
        },

        // ===========================================
        // FILTRES
        // ===========================================

        /**
         * Gérer le clic sur un filtre
         * @param {string} status - Statut à filtrer
         */
        handleFilterClick: function(status) {
            this.state.currentFilter = status;
