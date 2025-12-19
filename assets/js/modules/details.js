/**
 * Module Details - Blazing Feedback
 * Vue détails feedback
 * @package Blazing_Feedback
 */
(function(window) {
    'use strict';

    const Details = {
        /**
         * État du module
         */
        state: {
            repositioningFeedbackId: null,
            currentFeedback: null,
            isEditMode: false,
        },

        init: function(widget) {
            this.widget = widget;
            this.bindRepositionEvents();
            this.bindEditEvents();
        },

        /**
         * Attacher les événements d'édition
         */
        bindEditEvents: function() {
            const editBtn = document.getElementById('wpvfh-edit-feedback-btn');
            if (editBtn) {
                editBtn.addEventListener('click', () => {
                    this.startEditMode();
                });
            }
        },

        /**
         * Attacher les événements de ciblage/repositionnement
         */
        bindRepositionEvents: function() {
            // Bouton ajouter un ciblage
            const addTargetBtn = document.getElementById('wpvfh-add-target-btn');
            if (addTargetBtn) {
                addTargetBtn.addEventListener('click', () => {
                    this.startTargeting(this.widget.state.currentFeedbackId, false);
                });
            }

            // Bouton repositionner
            const repositionBtn = document.getElementById('wpvfh-reposition-feedback-btn');
            if (repositionBtn) {
                repositionBtn.addEventListener('click', () => {
                    this.startTargeting(this.widget.state.currentFeedbackId, true);
                });
            }

            // Écouter l'événement de placement du pin (mode ciblage/repositionnement)
            document.addEventListener('blazing-feedback:pin-placed', (e) => {
                if (this.state.repositioningFeedbackId) {
                    this.handleNewPosition(e.detail);
                }
            });

            // Écouter l'annulation du mode annotation
            document.addEventListener('blazing-feedback:annotation-deactivated', () => {
                if (this.state.repositioningFeedbackId && !window.BlazingAnnotation.getPosition()) {
                    // Annulé sans nouvelle position
                    this.state.repositioningFeedbackId = null;
                    if (this.widget.modules.notifications) {
                        this.widget.modules.notifications.show('Ciblage annulé', 'info');
                    }
                }
            });
        },

        showFeedbackDetails: function(feedback) {
            this.widget.state.currentFeedbackId = feedback.id;
            this.state.currentFeedback = feedback;
            this.state.isEditMode = false;

            const labels = this.widget.modules.labels;
            const tools = this.widget.modules.tools;
            const status = feedback.status || 'new';
            const statusLabel = labels.getStatusLabel(status);
            const statusEmoji = labels.getStatusEmoji(status);
            const statusColor = labels.getStatusColor(status);

            if (this.widget.elements.detailId) {
                this.widget.elements.detailId.textContent = `#${feedback.id}`;
            }

            if (this.widget.elements.detailStatus) {
                this.widget.elements.detailStatus.innerHTML = `
                    <span class="wpvfh-status-badge status-${status}" style="background-color: ${statusColor}20; color: ${statusColor}; border-color: ${statusColor}40;">
                        ${statusEmoji} ${statusLabel}
                    </span>
                `;
            }

            if (this.widget.elements.detailAuthor) {
                this.widget.elements.detailAuthor.innerHTML = `
                    <span>👤</span>
                    <span>${tools.escapeHtml(feedback.author?.name || 'Anonyme')}</span>
                `;
            }

            if (this.widget.elements.detailDate) {
                const date = feedback.date ? new Date(feedback.date).toLocaleString() : '';
                this.widget.elements.detailDate.innerHTML = `
                    <span>📅</span>
                    <span>${date}</span>
                `;
            }

            if (this.widget.elements.detailComment) {
                this.widget.elements.detailComment.textContent = feedback.comment || feedback.content || '';
            }

            this.updateDetailLabels(feedback);

            if (this.widget.elements.detailType) {
                this.widget.elements.detailType.value = feedback.feedback_type || '';
            }
            if (this.widget.elements.detailPrioritySelect) {
                this.widget.elements.detailPrioritySelect.value = feedback.priority || 'none';
            }

            if (this.widget.elements.detailScreenshot) {
                if (feedback.screenshot_url) {
                    const img = this.widget.elements.detailScreenshot.querySelector('img');
                    if (img) img.src = feedback.screenshot_url;
                    this.widget.elements.detailScreenshot.hidden = false;
                } else {
                    this.widget.elements.detailScreenshot.hidden = true;
                }
            }

            if (this.widget.elements.statusSelect) {
                this.widget.elements.statusSelect.value = status;
            }

            if (this.widget.elements.replyInput) {
                this.widget.elements.replyInput.value = '';
            }

            // Afficher les informations de ciblage/position
            this.displayPinInfo(feedback);

            if (this.widget.modules.panel) {
                this.widget.modules.panel.openPanel('details');
            }

            const hasPosition = feedback.selector || feedback.position_x || feedback.position_y;
            if (hasPosition && window.BlazingAnnotation) {
                setTimeout(() => {
                    window.BlazingAnnotation.scrollToPinWithHighlight(feedback.id);
                }, 300);
            }

            // Afficher le bon bouton selon la présence d'une position
            const addTargetBtn = document.getElementById('wpvfh-add-target-btn');
            const repositionBtn = document.getElementById('wpvfh-reposition-feedback-btn');
            if (addTargetBtn) {
                addTargetBtn.hidden = hasPosition;
            }
            if (repositionBtn) {
                repositionBtn.hidden = !hasPosition;
            }
        },

        /**
         * Afficher les informations du pin (ciblage/position)
         */
        displayPinInfo: function(feedback) {
            const pinInfoEl = this.widget.elements.detailPinInfo;
            if (!pinInfoEl) return;

            const hasPosition = feedback.selector || feedback.position_x || feedback.position_y;

            if (!hasPosition) {
                pinInfoEl.hidden = true;
                return;
            }

            pinInfoEl.hidden = false;

            // Sélecteur
            if (this.widget.elements.pinSelectorValue) {
                if (feedback.selector) {
                    this.widget.elements.pinSelectorValue.textContent = this.formatSelector(feedback.selector);
                    this.widget.elements.pinSelectorValue.parentElement.hidden = false;
                } else {
                    this.widget.elements.pinSelectorValue.parentElement.hidden = true;
                }
            }

            // Position
            if (this.widget.elements.pinPositionValue) {
                const x = feedback.position_x || feedback.element_offset_x || 0;
                const y = feedback.position_y || feedback.element_offset_y || 0;
                this.widget.elements.pinPositionValue.textContent = `X: ${Math.round(x)}px, Y: ${Math.round(y)}px`;
            }

            // Page
            if (this.widget.elements.pinPageValue) {
                const pageUrl = feedback.page_url || feedback.url || window.location.href;
                this.widget.elements.pinPageValue.textContent = this.shortenUrl(pageUrl);
                this.widget.elements.pinPageValue.title = pageUrl;
            }
        },

        /**
         * Formater le sélecteur pour l'affichage
         */
        formatSelector: function(selector) {
            if (!selector) return '';
            // Tronquer si trop long
            if (selector.length > 50) {
                return selector.substring(0, 47) + '...';
            }
            return selector;
        },

        /**
         * Raccourcir une URL pour l'affichage
         */
        shortenUrl: function(url) {
            if (!url) return '';
            try {
                const urlObj = new URL(url);
                let path = urlObj.pathname;
                if (path.length > 40) {
                    path = '...' + path.substring(path.length - 37);
                }
                return path || '/';
            } catch {
                return url.length > 40 ? url.substring(0, 37) + '...' : url;
            }
        },

        /**
         * Démarrer le mode édition
         */
        startEditMode: function() {
            if (!this.state.currentFeedback) {
                console.warn('[Blazing Feedback] Pas de feedback à éditer');
                return;
            }

            this.state.isEditMode = true;
            const feedback = this.state.currentFeedback;

            // Passer le feedback au formulaire pour édition
            if (this.widget.modules.form) {
                this.widget.modules.form.loadFeedbackForEdit(feedback);
            }

            // Ouvrir l'onglet "new" en mode édition
            if (this.widget.modules.panel) {
                this.widget.modules.panel.openPanel('new');
            }

            if (this.widget.modules.notifications) {
                this.widget.modules.notifications.show('Mode édition activé', 'info');
            }
        },

        /**
         * Démarrer le mode ciblage/repositionnement
         * @param {number} feedbackId - ID du feedback à cibler
         * @param {boolean} isReposition - true si repositionnement, false si nouveau ciblage
         */
        startTargeting: function(feedbackId, isReposition) {
            if (!feedbackId) {
                console.warn('[Blazing Feedback] Pas de feedback à cibler');
                return;
            }

            this.state.repositioningFeedbackId = feedbackId;

            // Fermer le panel pour permettre de cliquer sur la page
            if (this.widget.modules.panel) {
                this.widget.modules.panel.closePanel();
            }

            // Activer le mode annotation
            if (window.BlazingAnnotation) {
                window.BlazingAnnotation.activate({
                    reposition: isReposition,
                    feedbackId: feedbackId,
                });
            }

            const action = isReposition ? 'repositionnement' : 'ciblage';
            console.log('[Blazing Feedback] Mode ' + action + ' activé pour feedback #' + feedbackId);
        },

        /**
         * Gérer la nouvelle position après repositionnement
         * @param {Object} positionData - Données de la nouvelle position
         */
        handleNewPosition: async function(positionData) {
            const feedbackId = this.state.repositioningFeedbackId;
            this.state.repositioningFeedbackId = null;

            if (!feedbackId || !positionData) {
                console.warn('[Blazing Feedback] Données de repositionnement invalides');
                return;
            }

            try {
                // Préparer les données pour l'API
                const updateData = {
                    position_x: positionData.position_x,
                    position_y: positionData.position_y,
                    selector: positionData.selector,
                    element_offset_x: positionData.element_offset_x,
                    element_offset_y: positionData.element_offset_y,
                    scroll_x: positionData.scrollX,
                    scroll_y: positionData.scrollY,
                };

                // Mettre à jour via l'API
                await this.widget.modules.api.request('PUT', `feedbacks/${feedbackId}`, updateData);

                // Supprimer le pin temporaire
                if (window.BlazingAnnotation) {
                    window.BlazingAnnotation.removeTemporaryPin();
                }

                // Recharger les feedbacks pour mettre à jour les pins
                if (this.widget.modules.list && typeof this.widget.modules.list.loadFeedbacks === 'function') {
                    await this.widget.modules.list.loadFeedbacks();
                }

                // Rouvrir le panel sur les détails
                if (this.widget.modules.panel) {
                    this.widget.modules.panel.openPanel('details');
                }

                if (this.widget.modules.notifications) {
                    this.widget.modules.notifications.show('Ciblage enregistré', 'success');
                }

                console.log('[Blazing Feedback] Feedback #' + feedbackId + ' ciblé avec succès');

            } catch (error) {
                console.error('[Blazing Feedback] Erreur lors du ciblage:', error);
                if (this.widget.modules.notifications) {
                    this.widget.modules.notifications.show('Erreur lors du ciblage', 'error');
                }
            }
        },

        updateDetailLabels: function(feedback) {
            if (this.widget.modules.tags) {
                this.widget.modules.tags.renderDetailTags(feedback.tags);
            }
        },

        updateFeedbackStatus: async function(feedbackId, status) {
            try {
                await this.widget.modules.api.request('PUT', `feedbacks/${feedbackId}/status`, { status });

                if (window.BlazingAnnotation) {
                    window.BlazingAnnotation.updatePin(feedbackId, { status });
                }

                const labels = this.widget.modules.labels;
                if (this.widget.elements.detailStatus) {
                    const statusLabel = labels.getStatusLabel(status);
                    const statusEmoji = labels.getStatusEmoji(status);
                    const statusColor = labels.getStatusColor(status);
                    this.widget.elements.detailStatus.innerHTML = `
                        <span class="wpvfh-status-badge status-${status}" style="background-color: ${statusColor}20; color: ${statusColor}; border-color: ${statusColor}40;">
                            ${statusEmoji} ${statusLabel}
                        </span>
                    `;
                }

                this.widget.modules.notifications.show('Statut mis à jour', 'success');
            } catch (error) {
                console.error('[Blazing Feedback] Erreur mise à jour:', error);
                this.widget.modules.notifications.show('Erreur lors de la mise à jour', 'error');
            }
        },

        addReply: async function(feedbackId, content) {
            try {
                await this.widget.modules.api.request('POST', `feedbacks/${feedbackId}/replies`, { content });
                this.widget.modules.notifications.show('Réponse ajoutée', 'success');

                if (this.widget.elements.replyInput) {
                    this.widget.elements.replyInput.value = '';
                }

                const updatedFeedback = await this.widget.modules.api.request('GET', `feedbacks/${feedbackId}`);
                this.showFeedbackDetails(updatedFeedback);
            } catch (error) {
                console.error('[Blazing Feedback] Erreur ajout réponse:', error);
                this.widget.modules.notifications.show('Erreur lors de l\'ajout de la réponse', 'error');
            }
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.details = Details;

})(window);
