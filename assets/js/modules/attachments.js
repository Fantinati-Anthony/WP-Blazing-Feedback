/**
 * Module Attachments - Blazing Feedback
 * Gestion des pièces jointes
 * @package Blazing_Feedback
 */
(function(window) {
    'use strict';

    const Attachments = {
        init: function(widget) {
            this.widget = widget;
        },

        /**
         * Gérer la sélection de fichiers
         */
        handleAttachmentSelect: function(files) {
            const maxFiles = 5;
            const maxSize = 10 * 1024 * 1024; // 10 Mo

            for (const file of files) {
                if (this.widget.state.attachments.length >= maxFiles) {
                    this.widget.modules.notifications.show(`Maximum ${maxFiles} fichiers autorisés`, 'warning');
                    break;
                }

                if (file.size > maxSize) {
                    this.widget.modules.notifications.show(`"${file.name}" dépasse la limite de 10 Mo`, 'warning');
                    continue;
                }

                this.widget.state.attachments.push(file);
            }

            this.renderAttachmentsPreview();

            if (this.widget.elements.attachmentsInput) {
                this.widget.elements.attachmentsInput.value = '';
            }
        },

        /**
         * Afficher l'aperçu des pièces jointes
         */
        renderAttachmentsPreview: function() {
            const preview = this.widget.elements.attachmentsPreview;
            if (!preview) return;

            if (this.widget.state.attachments.length === 0) {
                preview.innerHTML = '';
                return;
            }

            const tools = this.widget.modules.tools;
            const html = this.widget.state.attachments.map((file, index) => {
                const icon = tools.getFileIcon(file.type);
                const size = tools.formatFileSize(file.size);

                return `
                    <div class="wpvfh-attachment-preview-item" data-index="${index}">
                        <span class="wpvfh-file-icon">${icon}</span>
                        <span class="wpvfh-file-name">${tools.escapeHtml(file.name)}</span>
                        <span class="wpvfh-file-size">${size}</span>
                        <button type="button" class="wpvfh-remove-attachment" data-index="${index}">&times;</button>
                    </div>
                `;
            }).join('');

            preview.innerHTML = html;

            preview.querySelectorAll('.wpvfh-remove-attachment').forEach(btn => {
                btn.addEventListener('click', () => {
                    const index = parseInt(btn.dataset.index, 10);
                    this.widget.state.attachments.splice(index, 1);
                    this.renderAttachmentsPreview();
                });
            });
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.attachments = Attachments;

})(window);
