/**
 * Module Participants - Blazing Feedback
 * Gestion des participants
 * @package Blazing_Feedback
 */
(function(window) {
    'use strict';

    const Participants = {
        init: function(widget) {
            this.widget = widget;
        },

        // Module pour extension future
        loadParticipants: function(feedbackId) {
            console.log('[Participants] loadParticipants not yet implemented', feedbackId);
        },

        inviteParticipant: function(feedbackId, userEmail) {
            console.log('[Participants] inviteParticipant not yet implemented', feedbackId, userEmail);
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.participants = Participants;

})(window);
