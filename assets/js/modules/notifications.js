/**
 * Module Notifications - Blazing Feedback
 * Affichage des notifications toast
 * @package Blazing_Feedback
 */
(function(window) {
    'use strict';

    const Notifications = {
        /**
         * Initialiser le module
         * @param {Object} widget
         */
        init: function(widget) {
            this.widget = widget;
        },

        /**
         * Afficher une notification
         * @param {string} message
         * @param {string} type - success, error, info, warning
         */
        show: function(message, type = 'info') {
            const notifications = this.widget.elements.notifications;
            if (!notifications) return;

            const notification = document.createElement('div');
            notification.className = `wpvfh-notification wpvfh-notification-${type}`;
            notification.textContent = message;

            notifications.appendChild(notification);

            requestAnimationFrame(() => {
                notification.classList.add('wpvfh-notification-show');
            });

            setTimeout(() => {
                notification.classList.remove('wpvfh-notification-show');
                setTimeout(() => notification.remove(), 300);
            }, 4000);
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.notifications = Notifications;

})(window);
