/**
 * Gestion formulaire, soumission, reset
 * 
 * Reference file for feedback-widget.js lines 1729-2010
 * See main file: assets/js/feedback-widget.js
 * 
 * Methods included:
 * - 
handleCancel * - handleSubmitFeedback * - resetForm * - setSubmitState
 * 
 * @package Blazing_Feedback
 */

/* 
 * To view this section, read feedback-widget.js with:
 * offset=1729, limit=282
 */

        handleCancel: function() {
            // Supprimer le pin temporaire
            if (window.BlazingAnnotation) {
                window.BlazingAnnotation.removeTemporaryPin();
            }

            this.closePanel();
        },

        /**
         * Gérer la soumission du formulaire
         * @param {Event} event - Événement de soumission
         * @returns {void}
         */
        handleSubmit: async function(event) {
            event.preventDefault();

            if (this.state.isSubmitting) return;

            // Validation
            const comment = this.elements.commentField?.value?.trim();
            if (!comment) {
                this.showNotification(this.config.i18n?.errorMessage || 'Veuillez entrer un commentaire', 'error');
                this.elements.commentField?.focus();
                return;
            }

            this.state.isSubmitting = true;
            this.setSubmitState(true);

            try {
                // Collecter les métadonnées système complètes
                const metadata = window.BlazingScreenshot ? window.BlazingScreenshot.getMetadata() : {};

                // Capture d'écran automatique si pas déjà fournie
                let screenshotData = this.state.screenshotData || null;
                if (!screenshotData && window.BlazingScreenshot && window.BlazingScreenshot.isAvailable()) {
                    try {
                        console.log('[Blazing Feedback] Capture d\'écran automatique...');
                        screenshotData = await window.BlazingScreenshot.capture();
                        console.log('[Blazing Feedback] Screenshot automatique capturé');
                    } catch (screenshotError) {
                        console.warn('[Blazing Feedback] Erreur capture auto:', screenshotError);
                        // Continuer sans screenshot
                    }
                }

                // Préparer les données avec toutes les infos système
                const feedbackData = {
                    comment: comment,
                    url: this.config.currentUrl || window.location.href,
                    position_x: this.state.pinPosition?.position_x || this.elements.positionX?.value || null,
                    position_y: this.state.pinPosition?.position_y || this.elements.positionY?.value || null,
                    screenshot_data: screenshotData,
                    // Dimensions écran
                    screen_width: metadata.screenWidth,
                    screen_height: metadata.screenHeight,
                    viewport_width: metadata.viewportWidth,
                    viewport_height: metadata.viewportHeight,
                    device_pixel_ratio: metadata.devicePixelRatio,
                    color_depth: metadata.colorDepth,
                    orientation: metadata.orientation,
                    // Navigateur & OS
                    browser: metadata.browser,
                    browser_version: metadata.browserVersion,
                    os: metadata.os,
                    os_version: metadata.osVersion,
                    device: metadata.device,
                    platform: metadata.platform,
                    user_agent: metadata.userAgent,
                    // Langue & locale
                    language: metadata.language,
                    languages: metadata.languages,
                    timezone: metadata.timezone,
                    timezone_offset: metadata.timezoneOffset,
                    local_time: metadata.localTime,
                    // Capacités
                    cookies_enabled: metadata.cookiesEnabled,
                    online: metadata.onLine,
                    touch_support: metadata.touchSupport ? JSON.stringify(metadata.touchSupport) : null,
                    max_touch_points: metadata.maxTouchPoints,
                    // Hardware (si disponible)
                    device_memory: metadata.deviceMemory,
                    hardware_concurrency: metadata.hardwareConcurrency,
                    // Connexion (si disponible)
                    connection_type: metadata.connectionType ? JSON.stringify(metadata.connectionType) : null,
                    // DOM Anchoring data
                    selector: this.state.pinPosition?.selector || null,
                    element_offset_x: this.state.pinPosition?.element_offset_x || null,
                    element_offset_y: this.state.pinPosition?.element_offset_y || null,
                    scroll_x: this.state.pinPosition?.scrollX || metadata.scrollX,
                    scroll_y: this.state.pinPosition?.scrollY || metadata.scrollY,
                    // Référent
                    referrer: metadata.referrer,
                    // Type, Priorité, Tags
                    feedback_type: this.elements.feedbackType?.value || '',
                    priority: this.elements.feedbackPriority?.value || 'none',
                    tags: this.elements.feedbackTags?.value || '',
                };

                console.log('[Blazing Feedback] Envoi du feedback:', feedbackData);

                // Envoyer à l'API
                const response = await this.apiRequest('POST', 'feedbacks', feedbackData);
                console.log('[Blazing Feedback] Réponse création:', response);

                if (response.id) {
                    // Succès
                    this.showNotification(this.config.i18n?.successMessage || 'Feedback envoyé avec succès !', 'success');

                    // Supprimer le pin temporaire et créer le permanent
                    if (window.BlazingAnnotation) {
                        window.BlazingAnnotation.removeTemporaryPin();
                        window.BlazingAnnotation.createPin(response);
                    }

                    // Ajouter à la liste locale
                    this.state.currentFeedbacks.push(response);

                    // Action post-feedback selon la configuration
                    const postAction = this.config.postFeedbackAction || 'close';
                    if (postAction === 'list') {
                        // Ouvrir la liste des feedbacks
                        this.resetForm();
                        this.switchTab('list');
                    } else {
                        // Fermer le panel (comportement par défaut)
                        this.closePanel();
                    }

                    // Mettre à jour les compteurs
                    const count = this.state.currentFeedbacks.length;
                    if (this.elements.pinsCount) {
                        this.elements.pinsCount.textContent = count > 0 ? count : '';
                    }
                    if (this.elements.feedbackCount) {
                        this.elements.feedbackCount.textContent = count;
                        this.elements.feedbackCount.hidden = count === 0;
                    }

                    // Émettre l'événement
                    this.emitEvent('feedback-created', response);
                }

            } catch (error) {
                console.error('[Blazing Feedback] Erreur de soumission:', error);
                this.showNotification(error.message || this.config.i18n?.errorMessage || 'Erreur lors de l\'envoi', 'error');
            } finally {
                this.state.isSubmitting = false;
                this.setSubmitState(false);
            }
        },

        /**
         * Réinitialiser le formulaire
         * @returns {void}
         */
        resetForm: function() {
            if (this.elements.form) {
                this.elements.form.reset();
            }

            this.state.pinPosition = null;
            this.clearScreenshot();

            if (this.elements.pinInfo) {
                this.elements.pinInfo.hidden = true;
            }

            if (this.elements.positionX) {
                this.elements.positionX.value = '';
            }
            if (this.elements.positionY) {
                this.elements.positionY.value = '';
            }

            // Supprimer le pin temporaire
            if (window.BlazingAnnotation) {
                window.BlazingAnnotation.removeTemporaryPin();
            }

            // Effacer la sélection d'élément
            this.emitEvent('clear-selection');

            // Réinitialiser l'affichage de la section ciblage
            if (this.elements.selectElementBtn) {
                this.elements.selectElementBtn.hidden = false;
            }
            if (this.elements.selectedElement) {
                this.elements.selectedElement.hidden = true;
            }

            // Réinitialiser les champs Type, Priorité, Tags
            if (this.elements.feedbackType) {
                this.elements.feedbackType.value = '';
            }
            if (this.elements.feedbackPriority) {
                this.elements.feedbackPriority.value = 'none';
            }
            // Réinitialiser les tags visuels
            this.clearFormTags();
            if (this.elements.feedbackTagsInput) {
                this.elements.feedbackTagsInput.value = '';
            }
        },

        /**
         * Définir l'état du bouton de soumission
         * @param {boolean} isLoading - En cours de chargement
         * @returns {void}
         */
        setSubmitState: function(isLoading) {
            if (!this.elements.submitBtn) return;

            this.elements.submitBtn.disabled = isLoading;

            if (isLoading) {
                this.elements.submitBtn.innerHTML = '<span class="wpvfh-spinner"></span> ' + (this.config.i18n?.loadingMessage || 'Envoi...');
            } else {
                this.elements.submitBtn.innerHTML = '<span class="wpvfh-btn-emoji">📤</span> ' + (this.config.i18n?.submitButton || 'Envoyer');
            }
        },

        /**
         * Charger les feedbacks existants pour cette page
         * @returns {void}
         */
        loadExistingFeedbacks: async function() {
            try {
                const currentUrl = this.config.currentUrl || window.location.href;
                console.log('[Blazing Feedback] Chargement des feedbacks pour URL:', currentUrl);

                const url = encodeURIComponent(currentUrl);
                const response = await this.apiRequest('GET', `feedbacks/by-url?url=${url}`);

                console.log('[Blazing Feedback] Réponse API:', response);

                if (Array.isArray(response)) {
                    // Calculer _displayOrder pour les feedbacks avec position
                    let pinIndex = 1;
                    response.forEach(feedback => {
                        const hasPosition = feedback.selector || feedback.position_x || feedback.position_y;
                        if (hasPosition) {
                            feedback._displayOrder = pinIndex++;
                        }
                    });

                    this.state.currentFeedbacks = response;
                    console.log('[Blazing Feedback] Feedbacks chargés:', response.length);

                    // Afficher les pins
                    if (response.length > 0) {
                        this.emitEvent('load-pins', { pins: response });
                    }

                    // Mettre à jour le compteur dans l'onglet Liste
                    if (this.elements.pinsCount) {
                        this.elements.pinsCount.textContent = response.length > 0 ? response.length : '';
                    }

                    // Mettre à jour le badge compteur sur le bouton principal
                    if (this.elements.feedbackCount) {
                        if (response.length > 0) {
                            this.elements.feedbackCount.textContent = response.length;
                            this.elements.feedbackCount.hidden = false;
                        } else {
                            this.elements.feedbackCount.hidden = true;
                        }
                    }

                    // Masquer l'onglet Priorité si aucun feedback
                    this.updatePriorityTabVisibility(response.length);
                }

            } catch (error) {
                console.error('[Blazing Feedback] Erreur chargement feedbacks:', error);
            }
        },

        /**
         * Afficher les détails d'un feedback dans la sidebar
         * @param {Object} feedback - Données du feedback