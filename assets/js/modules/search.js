/**
 * Recherche, modal recherche
 * 
 * Reference file for feedback-widget.js lines 3050-3350
 * See main file: assets/js/feedback-widget.js
 * 
 * Methods included:
 * - 
openSearchModal * - closeSearchModal * - performSearch * - renderSearchResults * - goToFeedback
 * 
 * @package Blazing_Feedback
 */

/* 
 * To view this section, read feedback-widget.js with:
 * offset=3050, limit=301
 */

            try {
                const urlObj = new URL(url);
                let path = urlObj.pathname;

                // Retirer les slashes de début/fin
                path = path.replace(/^\/|\/$/g, '');

                if (!path || path === '') return 'Accueil';

                // Prendre le dernier segment et nettoyer
                const segments = path.split('/');
                let title = segments[segments.length - 1];

                // Retirer les extensions
                title = title.replace(/\.(html?|php|aspx?)$/i, '');

                // Remplacer les tirets/underscores par des espaces
                title = title.replace(/[-_]/g, ' ');

                // Capitaliser la première lettre
                return title.charAt(0).toUpperCase() + title.slice(1);
            } catch (e) {
                return url;
            }
        },

        /**
         * Raccourcir une URL
         * @param {string} url - URL complète
         * @returns {string} URL raccourcie
         */
        shortenUrl: function(url) {
            try {
                const urlObj = new URL(url);
                return urlObj.pathname || '/';
            } catch (e) {
                return url;
            }
        },

        // ===========================================
        // PIÈCES JOINTES
        // ===========================================

        /**
         * Gérer la sélection de fichiers
         * @param {FileList} files - Fichiers sélectionnés
         */
        handleAttachmentSelect: function(files) {
            const maxFiles = 5;
            const maxSize = 10 * 1024 * 1024; // 10 Mo

            for (const file of files) {
                // Vérifier le nombre maximum
                if (this.state.attachments.length >= maxFiles) {
                    this.showNotification(`Maximum ${maxFiles} fichiers autorisés`, 'warning');
                    break;
                }

                // Vérifier la taille
                if (file.size > maxSize) {
                    this.showNotification(`"${file.name}" dépasse la limite de 10 Mo`, 'warning');
                    continue;
                }

                // Ajouter à la liste
                this.state.attachments.push(file);
            }

            // Mettre à jour l'aperçu
            this.renderAttachmentsPreview();

            // Réinitialiser l'input
            if (this.elements.attachmentsInput) {
                this.elements.attachmentsInput.value = '';
            }
        },

        /**
         * Afficher l'aperçu des pièces jointes
         */
        renderAttachmentsPreview: function() {
            if (!this.elements.attachmentsPreview) return;

            if (this.state.attachments.length === 0) {
                this.elements.attachmentsPreview.innerHTML = '';
                return;
            }

            const html = this.state.attachments.map((file, index) => {
                const icon = this.getFileIcon(file.type);
                const size = this.formatFileSize(file.size);

                return `
                    <div class="wpvfh-attachment-preview-item" data-index="${index}">
                        <span class="wpvfh-file-icon">${icon}</span>
                        <span class="wpvfh-file-name">${this.escapeHtml(file.name)}</span>
                        <span class="wpvfh-file-size">${size}</span>
                        <button type="button" class="wpvfh-remove-attachment" data-index="${index}">&times;</button>
                    </div>
                `;
            }).join('');

            this.elements.attachmentsPreview.innerHTML = html;

            // Ajouter les événements de suppression
            this.elements.attachmentsPreview.querySelectorAll('.wpvfh-remove-attachment').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const index = parseInt(btn.dataset.index, 10);
                    this.state.attachments.splice(index, 1);
                    this.renderAttachmentsPreview();
                });
            });
        },

        /**
         * Obtenir l'icône d'un fichier selon son type
         * @param {string} mimeType - Type MIME
         * @returns {string} Emoji
         */
        getFileIcon: function(mimeType) {
            if (mimeType.startsWith('image/')) return '🖼️';
            if (mimeType === 'application/pdf') return '📕';
            if (mimeType.includes('word') || mimeType.includes('document')) return '📝';
            if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return '📊';
            return '📎';
        },

        /**
         * Formater la taille d'un fichier
         * @param {number} bytes - Taille en bytes
         * @returns {string} Taille formatée
         */
        formatFileSize: function(bytes) {
            if (bytes < 1024) return bytes + ' o';
            if (bytes < 1024 * 1024) return Math.round(bytes / 1024) + ' Ko';
            return (bytes / (1024 * 1024)).toFixed(1) + ' Mo';
        },

        // ===========================================
        // MENTIONS @
        // ===========================================

        /**
         * Gérer l'input pour les mentions
         * @param {Event} e - Événement input
         */
        handleMentionInput: function(e) {
            const textarea = e.target;
            const text = textarea.value;
            const cursorPos = textarea.selectionStart;

            // Trouver si on est en train d'écrire une mention
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
         * Gérer les touches pour les mentions
         * @param {KeyboardEvent} e - Événement keydown
         */
        handleMentionKeydown: function(e) {
            if (!this.elements.mentionDropdown || this.elements.mentionDropdown.hidden) {
                return;
            }

            const items = this.elements.mentionList?.querySelectorAll('.wpvfh-mention-item');
            const activeItem = this.elements.mentionList?.querySelector('.wpvfh-mention-item.active');
            let activeIndex = -1;

            if (items) {
                items.forEach((item, i) => {
                    if (item === activeItem) activeIndex = i;
                });
            }

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    if (items && items.length > 0) {
                        const nextIndex = (activeIndex + 1) % items.length;
                        items.forEach((item, i) => item.classList.toggle('active', i === nextIndex));
                    }
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    if (items && items.length > 0) {
                        const prevIndex = activeIndex <= 0 ? items.length - 1 : activeIndex - 1;
                        items.forEach((item, i) => item.classList.toggle('active', i === prevIndex));
                    }
                    break;
                case 'Enter':
                case 'Tab':
                    if (activeItem) {
                        e.preventDefault();
                        this.insertMention(activeItem.dataset.username);
                    }
                    break;
                case 'Escape':
                    this.hideMentionDropdown();
                    break;
            }
        },

        /**
         * Afficher le dropdown des mentions
         * @param {string} searchTerm - Terme de recherche
         * @param {HTMLElement} textarea - Textarea source
         */
        showMentionDropdown: async function(searchTerm, textarea) {
            // Charger les utilisateurs si pas encore fait
            if (this.state.mentionUsers.length === 0) {
                await this.loadMentionUsers();
            }

            // Filtrer les utilisateurs
            const filtered = this.state.mentionUsers.filter(user =>
                user.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                user.username.toLowerCase().includes(searchTerm.toLowerCase())
            ).slice(0, 6);

            if (filtered.length === 0) {
                this.hideMentionDropdown();
                return;
            }

            // Générer le HTML
            const html = filtered.map((user, i) => `
                <div class="wpvfh-mention-item ${i === 0 ? 'active' : ''}" data-username="${this.escapeHtml(user.username)}">
                    <div class="wpvfh-mention-avatar">${user.name.charAt(0).toUpperCase()}</div>
                    <div class="wpvfh-mention-info">
                        <div class="wpvfh-mention-name">${this.escapeHtml(user.name)}</div>
                        <div class="wpvfh-mention-email">@${this.escapeHtml(user.username)}</div>
                    </div>
                </div>
            `).join('');

            if (this.elements.mentionList) {
                this.elements.mentionList.innerHTML = html;
            }

            // Positionner le dropdown
            if (this.elements.mentionDropdown) {
                const rect = textarea.getBoundingClientRect();
                this.elements.mentionDropdown.style.top = (rect.bottom + window.scrollY) + 'px';
                this.elements.mentionDropdown.style.left = rect.left + 'px';
                this.elements.mentionDropdown.hidden = false;
            }

            // Ajouter les événements de clic
            this.elements.mentionList?.querySelectorAll('.wpvfh-mention-item').forEach(item => {
                item.addEventListener('click', () => {
                    this.insertMention(item.dataset.username);
                });
            });
        },

        /**
         * Masquer le dropdown des mentions
         */
        hideMentionDropdown: function() {
            if (this.elements.mentionDropdown) {
                this.elements.mentionDropdown.hidden = true;
            }
        },

        /**
         * Insérer une mention dans le textarea
         * @param {string} username - Nom d'utilisateur
         */
        insertMention: function(username) {
            const textarea = this.elements.commentField;
            if (!textarea) return;

            const text = textarea.value;
            const cursorPos = textarea.selectionStart;

            // Trouver le début de la mention
            const textBeforeCursor = text.substring(0, cursorPos);
            const mentionStart = textBeforeCursor.lastIndexOf('@');

            if (mentionStart >= 0) {
                // Remplacer @xxx par @username
                const newText = text.substring(0, mentionStart) + '@' + username + ' ' + text.substring(cursorPos);
                textarea.value = newText;

                // Positionner le curseur après la mention
                const newCursorPos = mentionStart + username.length + 2;
                textarea.setSelectionRange(newCursorPos, newCursorPos);
                textarea.focus();
            }

            this.hideMentionDropdown();
        },