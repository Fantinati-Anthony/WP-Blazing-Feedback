/**
 * Module Mentions - Blazing Feedback
 * Mentions @utilisateur
 * @package Blazing_Feedback
 */
(function(window) {
    'use strict';

    const Mentions = {
        init: function(widget) {
            this.widget = widget;
        },

        /**
         * Gérer l'input pour les mentions
         */
        handleMentionInput: function(e) {
            const textarea = e.target;
            const text = textarea.value;
            const cursorPos = textarea.selectionStart;

            const textBeforeCursor = text.substring(0, cursorPos);
            const mentionMatch = textBeforeCursor.match(/@(\w*)$/);

            if (mentionMatch) {
                const searchTerm = mentionMatch[1];
                this.showMentionDropdown(searchTerm, textarea);
            } else {
                this.hideMentionDropdown();
            }
        },

        /**
         * Afficher le dropdown des mentions
         */
        showMentionDropdown: async function(searchTerm, textarea) {
            if (this.widget.state.mentionUsers.length === 0) {
                await this.loadMentionUsers();
            }

            const filtered = this.widget.state.mentionUsers.filter(user =>
                user.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                user.username.toLowerCase().includes(searchTerm.toLowerCase())
            ).slice(0, 6);

            if (filtered.length === 0) {
                this.hideMentionDropdown();
                return;
            }

            const tools = this.widget.modules.tools;
            const html = filtered.map((user, i) => `
                <div class="wpvfh-mention-item ${i === 0 ? 'active' : ''}" data-username="${tools.escapeHtml(user.username)}">
                    <div class="wpvfh-mention-avatar">${user.name.charAt(0).toUpperCase()}</div>
                    <div class="wpvfh-mention-info">
                        <div class="wpvfh-mention-name">${tools.escapeHtml(user.name)}</div>
                        <div class="wpvfh-mention-email">@${tools.escapeHtml(user.username)}</div>
                    </div>
                </div>
            `).join('');

            if (this.widget.elements.mentionList) {
                this.widget.elements.mentionList.innerHTML = html;
            }

            if (this.widget.elements.mentionDropdown) {
                const rect = textarea.getBoundingClientRect();
                this.widget.elements.mentionDropdown.style.top = (rect.bottom + window.scrollY) + 'px';
                this.widget.elements.mentionDropdown.style.left = rect.left + 'px';
                this.widget.elements.mentionDropdown.hidden = false;
            }

            this.widget.elements.mentionList?.querySelectorAll('.wpvfh-mention-item').forEach(item => {
                item.addEventListener('click', () => {
                    this.insertMention(item.dataset.username);
                });
            });
        },

        /**
         * Masquer le dropdown des mentions
         */
        hideMentionDropdown: function() {
            if (this.widget.elements.mentionDropdown) {
                this.widget.elements.mentionDropdown.hidden = true;
            }
        },

        /**
         * Insérer une mention dans le textarea
         */
        insertMention: function(username) {
            const textarea = this.widget.elements.commentField;
            if (!textarea) return;

            const text = textarea.value;
            const cursorPos = textarea.selectionStart;
            const textBeforeCursor = text.substring(0, cursorPos);
            const mentionStart = textBeforeCursor.lastIndexOf('@');

            if (mentionStart >= 0) {
                const newText = text.substring(0, mentionStart) + '@' + username + ' ' + text.substring(cursorPos);
                textarea.value = newText;

                const newCursorPos = mentionStart + username.length + 2;
                textarea.setSelectionRange(newCursorPos, newCursorPos);
                textarea.focus();
            }

            this.hideMentionDropdown();
        },

        /**
         * Charger la liste des utilisateurs
         */
        loadMentionUsers: async function() {
            try {
                const response = await this.widget.modules.api.request('GET', 'users');
                this.widget.state.mentionUsers = Array.isArray(response) ? response : [];
            } catch (error) {
                console.error('[Blazing Feedback] Erreur chargement utilisateurs:', error);
                this.widget.state.mentionUsers = [];
            }
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.mentions = Mentions;

})(window);
