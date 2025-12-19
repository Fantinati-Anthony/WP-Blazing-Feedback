/**
 * Affichage détails feedback, mise à jour
 * 
 * Reference file for feedback-widget.js lines 2013-2290
 * See main file: assets/js/feedback-widget.js
 * 
 * Methods included:
 * - 
showFeedbackDetails * - handleDetailChange * - handleDeleteFeedback
 * 
 * @package Blazing_Feedback
 */

/* 
 * To view this section, read feedback-widget.js with:
 * offset=2013, limit=278
 */

        showFeedbackDetails: function(feedback) {
            // Stocker l'ID du feedback courant
            this.state.currentFeedbackId = feedback.id;

            const status = feedback.status || 'new';
            const statusLabel = this.getStatusLabel(status);
            const statusEmoji = this.getStatusEmoji(status);
            const statusColor = this.getStatusColor(status);

            // Remplir les éléments
            if (this.elements.detailId) {
                this.elements.detailId.textContent = `#${feedback.id}`;
            }

            if (this.elements.detailStatus) {
                this.elements.detailStatus.innerHTML = `
                    <span class="wpvfh-status-badge status-${status}" style="background-color: ${statusColor}20; color: ${statusColor}; border-color: ${statusColor}40;">
                        ${statusEmoji} ${statusLabel}
                    </span>
                `;
            }

            if (this.elements.detailAuthor) {
                this.elements.detailAuthor.innerHTML = `
                    <span>👤</span>
                    <span>${this.escapeHtml(feedback.author?.name || 'Anonyme')}</span>
                `;
            }

            if (this.elements.detailDate) {
                const date = feedback.date ? new Date(feedback.date).toLocaleString() : '';
                this.elements.detailDate.innerHTML = `
                    <span>📅</span>
                    <span>${date}</span>
                `;
            }

            if (this.elements.detailComment) {
                this.elements.detailComment.textContent = feedback.comment || feedback.content || '';
            }

            // Type, Priorité, Tags - Labels et dropdowns
            this.updateDetailLabels(feedback);

            // Mettre à jour les valeurs des dropdowns
            if (this.elements.detailType) {
                this.elements.detailType.value = feedback.feedback_type || '';
            }
            if (this.elements.detailPrioritySelect) {
                this.elements.detailPrioritySelect.value = feedback.priority || 'none';
            }
            // Vider le champ d'ajout de tags (les tags existants sont affichés comme badges)
            if (this.elements.detailTagsInput) {
                this.elements.detailTagsInput.value = '';
            }

            // Screenshot
            if (this.elements.detailScreenshot) {
                if (feedback.screenshot_url) {
                    const img = this.elements.detailScreenshot.querySelector('img');
                    if (img) img.src = feedback.screenshot_url;
                    this.elements.detailScreenshot.hidden = false;
                } else {
                    this.elements.detailScreenshot.hidden = true;
                }
            }

            // Réponses
            if (this.elements.detailReplies && this.elements.repliesList) {
                if (feedback.replies && feedback.replies.length > 0) {
                    this.elements.repliesList.innerHTML = feedback.replies.map(reply => `
                        <div class="wpvfh-reply-item">
                            <div class="wpvfh-reply-meta">
                                ${this.escapeHtml(reply.author?.name || 'Anonyme')} -
                                ${new Date(reply.date).toLocaleString()}
                            </div>
                            <div class="wpvfh-reply-content">${this.escapeHtml(reply.content)}</div>
                        </div>
                    `).join('');
                    this.elements.detailReplies.hidden = false;
                } else {
                    this.elements.detailReplies.hidden = true;
                }
            }

            // Actions modérateur
            if (this.elements.detailActions) {
                this.elements.detailActions.hidden = !this.config.canModerate;
            }

            // Sélecteur de statut
            if (this.elements.statusSelect) {
                this.elements.statusSelect.value = status;
            }

            // Vider le champ de réponse
            if (this.elements.replyInput) {
                this.elements.replyInput.value = '';
            }

            // Section suppression - visible pour le créateur ou un admin
            if (this.elements.deleteSection) {
                const isCreator = feedback.author?.id === this.config.userId;
                const canDelete = isCreator || this.config.canManage;
                this.elements.deleteSection.hidden = !canDelete;
            }

            // Ouvrir le panel et basculer sur l'onglet détails
            this.openPanel('details');

            // Scroller vers le pin et le mettre en évidence avec un highlight jaune
            const hasPosition = feedback.selector || feedback.position_x || feedback.position_y;
            if (hasPosition && window.BlazingAnnotation) {
                setTimeout(() => {
                    window.BlazingAnnotation.scrollToPinWithHighlight(feedback.id);
                }, 300);
            }
        },

        /**
         * Mettre à jour le statut d'un feedback
         * @param {number} feedbackId - ID du feedback
         * @param {string} status - Nouveau statut
         * @returns {void}
         */
        updateFeedbackStatus: async function(feedbackId, status) {
            try {
                await this.apiRequest('PUT', `feedbacks/${feedbackId}/status`, { status });

                // Mettre à jour le pin
                if (window.BlazingAnnotation) {
                    window.BlazingAnnotation.updatePin(feedbackId, { status });
                }

                // Mettre à jour l'affichage du statut dans la sidebar
                if (this.elements.detailStatus) {
                    const statusLabel = this.getStatusLabel(status);
                    const statusEmoji = this.getStatusEmoji(status);
                    const statusColor = this.getStatusColor(status);
                    this.elements.detailStatus.innerHTML = `
                        <span class="wpvfh-status-badge status-${status}" style="background-color: ${statusColor}20; color: ${statusColor}; border-color: ${statusColor}40;">
                            ${statusEmoji} ${statusLabel}
                        </span>
                    `;
                }

                this.showNotification('Statut mis à jour', 'success');

            } catch (error) {
                console.error('[Blazing Feedback] Erreur de mise à jour:', error);
                this.showNotification('Erreur lors de la mise à jour', 'error');
            }
        },

        /**
         * Ajouter une réponse à un feedback
         * @param {number} feedbackId - ID du feedback
         * @param {string} content - Contenu de la réponse
         * @returns {void}
         */
        addReply: async function(feedbackId, content) {
            try {
                await this.apiRequest('POST', `feedbacks/${feedbackId}/replies`, { content });
                this.showNotification('Réponse ajoutée', 'success');

                // Vider le champ de réponse
                if (this.elements.replyInput) {
                    this.elements.replyInput.value = '';
                }

                // Recharger les détails du feedback
                const updatedFeedback = await this.apiRequest('GET', `feedbacks/${feedbackId}`);
                this.showFeedbackDetails(updatedFeedback);

            } catch (error) {
                console.error('[Blazing Feedback] Erreur d\'ajout de réponse:', error);
                this.showNotification('Erreur lors de l\'ajout de la réponse', 'error');
            }
        },

        /**
         * Effectuer une requête à l'API REST
         * @param {string} method - Méthode HTTP
         * @param {string} endpoint - Endpoint
         * @param {Object} data - Données (optionnel)
         * @returns {Promise<Object>} Réponse
         */
        apiRequest: async function(method, endpoint, data = null) {
            const url = this.config.restUrl + endpoint;

            console.log('[Blazing Feedback] API Request:', method, url);

            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': this.config.restNonce,
                },
                credentials: 'same-origin',
            };

            if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
                options.body = JSON.stringify(data);
                console.log('[Blazing Feedback] Request data:', data);
            }

            try {
                const response = await fetch(url, options);

                console.log('[Blazing Feedback] Response status:', response.status);

                // Lire le texte de la réponse d'abord
                const responseText = await response.text();

                // Vérifier si c'est du JSON valide
                let responseData;
                try {
                    responseData = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('[Blazing Feedback] Réponse non-JSON:', responseText.substring(0, 500));
                    throw new Error('La réponse du serveur n\'est pas valide. Vérifiez que les permaliens WordPress sont activés.');
                }

                if (!response.ok) {
                    throw new Error(responseData.message || `Erreur HTTP ${response.status}`);
                }

                return responseData;

            } catch (error) {
                console.error('[Blazing Feedback] Erreur API:', error);
                throw error;
            }
        },

        /**
         * Afficher une notification
         * @param {string} message - Message
         * @param {string} type - Type (success, error, info, warning)
         * @returns {void}
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