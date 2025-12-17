/**
 * Blazing Feedback - Widget Principal
 *
 * Contrôleur principal qui orchestre les modules
 * Screenshot et Annotation
 *
 * @package Blazing_Feedback
 * @since 1.0.0
 */

(function(window, document) {
    'use strict';

    /**
     * Widget Blazing Feedback
     * @namespace
     */
    const BlazingFeedback = {

        /**
         * Configuration depuis WordPress
         * @type {Object}
         */
        config: window.wpvfhData || {},

        /**
         * État du widget
         * @type {Object}
         */
        state: {
            isOpen: false,
            isSubmitting: false,
            feedbackMode: 'view',      // 'view' | 'create' | 'annotate'
            currentFeedbacks: [],
            screenshotData: null,
            pinPosition: null,
            currentFeedbackId: null,   // ID du feedback en cours de visualisation
        },

        /**
         * Éléments DOM
         * @type {Object}
         */
        elements: {},

        /**
         * Initialiser le widget
         * @returns {void}
         */
        init: function() {
            // Vérifier les permissions
            if (!this.config.canCreate && !this.config.canModerate) {
                console.log('[Blazing Feedback] Utilisateur sans permissions');
                return;
            }

            this.cacheElements();
            this.bindEvents();
            this.loadExistingFeedbacks();

            console.log('[Blazing Feedback] Widget initialisé');
        },

        /**
         * Mettre en cache les éléments DOM
         * @returns {void}
         */
        cacheElements: function() {
            this.elements = {
                container: document.getElementById('wpvfh-container'),
                toggleBtn: document.getElementById('wpvfh-toggle-btn'),
                panel: document.getElementById('wpvfh-panel'),
                sidebarOverlay: document.getElementById('wpvfh-sidebar-overlay'),
                form: document.getElementById('wpvfh-form'),
                commentField: document.getElementById('wpvfh-comment'),
                screenshotPreview: document.getElementById('wpvfh-screenshot-preview'),
                screenshotData: document.getElementById('wpvfh-screenshot-data'),
                positionX: document.getElementById('wpvfh-position-x'),
                positionY: document.getElementById('wpvfh-position-y'),
                pinInfo: document.querySelector('.wpvfh-pin-info'),
                closeBtn: document.querySelector('.wpvfh-close-btn'),
                cancelBtn: document.querySelector('.wpvfh-cancel-btn'),
                submitBtn: document.querySelector('.wpvfh-submit-btn'),
                notifications: document.getElementById('wpvfh-notifications'),
                overlay: document.getElementById('wpvfh-annotation-overlay'),
                // Onglets et liste
                tabs: document.querySelectorAll('.wpvfh-tab'),
                tabNew: document.getElementById('wpvfh-tab-new'),
                tabList: document.getElementById('wpvfh-tab-list'),
                tabDetails: document.getElementById('wpvfh-tab-details'),
                tabDetailsBtn: document.getElementById('wpvfh-tab-details-btn'),
                pinsList: document.getElementById('wpvfh-pins-list'),
                pinsCount: document.getElementById('wpvfh-pins-count'),
                emptyState: document.getElementById('wpvfh-empty-state'),
                addFeedbackBtn: document.querySelector('.wpvfh-add-feedback-btn'),
                // Éléments détails
                backToListBtn: document.getElementById('wpvfh-back-to-list'),
                detailId: document.getElementById('wpvfh-detail-id'),
                detailStatus: document.getElementById('wpvfh-detail-status'),
                detailAuthor: document.getElementById('wpvfh-detail-author'),
                detailDate: document.getElementById('wpvfh-detail-date'),
                detailComment: document.getElementById('wpvfh-detail-comment'),
                detailScreenshot: document.getElementById('wpvfh-detail-screenshot'),
                detailReplies: document.getElementById('wpvfh-detail-replies'),
                repliesList: document.getElementById('wpvfh-replies-list'),
                detailActions: document.getElementById('wpvfh-detail-actions'),
                statusSelect: document.getElementById('wpvfh-status-select'),
                replyInput: document.getElementById('wpvfh-reply-input'),
                sendReplyBtn: document.getElementById('wpvfh-send-reply'),
                // Éléments média
                mediaToolbar: document.querySelector('.wpvfh-media-toolbar'),
                toolButtons: document.querySelectorAll('.wpvfh-tool-btn'),
                voiceSection: document.getElementById('wpvfh-voice-section'),
                voiceRecordBtn: document.getElementById('wpvfh-voice-record'),
                voicePreview: document.getElementById('wpvfh-voice-preview'),
                transcriptPreview: document.getElementById('wpvfh-transcript-preview'),
                videoSection: document.getElementById('wpvfh-video-section'),
                videoRecordBtn: document.getElementById('wpvfh-video-record'),
                videoPreview: document.getElementById('wpvfh-video-preview'),
                audioData: document.getElementById('wpvfh-audio-data'),
                videoData: document.getElementById('wpvfh-video-data'),
                transcriptField: document.getElementById('wpvfh-transcript'),
            };
        },

        /**
         * Attacher les gestionnaires d'événements
         * @returns {void}
         */
        bindEvents: function() {
            // Bouton toggle
            if (this.elements.toggleBtn) {
                this.elements.toggleBtn.addEventListener('click', this.handleToggle.bind(this));
            }

            // Boutons du panel
            if (this.elements.closeBtn) {
                this.elements.closeBtn.addEventListener('click', this.closePanel.bind(this));
            }
            if (this.elements.cancelBtn) {
                this.elements.cancelBtn.addEventListener('click', this.handleCancel.bind(this));
            }

            // Soumission du formulaire
            if (this.elements.form) {
                this.elements.form.addEventListener('submit', this.handleSubmit.bind(this));
            }

            // Boutons de la barre d'outils média
            if (this.elements.toolButtons) {
                this.elements.toolButtons.forEach(btn => {
                    btn.addEventListener('click', (e) => this.handleToolClick(e, btn.dataset.tool));
                });
            }

            // Bouton enregistrement vocal
            if (this.elements.voiceRecordBtn) {
                this.elements.voiceRecordBtn.addEventListener('click', this.handleVoiceRecord.bind(this));
            }

            // Bouton enregistrement vidéo
            if (this.elements.videoRecordBtn) {
                this.elements.videoRecordBtn.addEventListener('click', this.handleVideoRecord.bind(this));
            }

            // Boutons supprimer média
            document.querySelectorAll('.wpvfh-remove-media').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const parent = e.target.closest('.wpvfh-screenshot-preview, .wpvfh-audio-preview, .wpvfh-video-preview');
                    if (parent) {
                        if (parent.classList.contains('wpvfh-screenshot-preview')) this.clearScreenshot();
                        if (parent.classList.contains('wpvfh-audio-preview')) this.clearVoiceRecording();
                        if (parent.classList.contains('wpvfh-video-preview')) this.clearVideoRecording();
                    }
                });
            });

            // Événements des modules
            document.addEventListener('blazing-feedback:pin-placed', this.handlePinPlaced.bind(this));
            document.addEventListener('blazing-feedback:pin-selected', this.handlePinSelected.bind(this));
            document.addEventListener('blazing-feedback:capture-success', this.handleCaptureSuccess.bind(this));
            document.addEventListener('blazing-feedback:capture-error', this.handleCaptureError.bind(this));

            // Événements enregistrement vocal
            document.addEventListener('blazing-feedback:voice-recording-complete', this.handleVoiceComplete.bind(this));
            document.addEventListener('blazing-feedback:voice-transcription-update', this.handleTranscriptionUpdate.bind(this));

            // Événements enregistrement vidéo
            document.addEventListener('blazing-feedback:screen-recording-complete', this.handleVideoComplete.bind(this));

            // Échap pour fermer
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.state.isOpen) {
                    this.closePanel();
                }
            });

            // Annuler dans l'overlay
            const hintClose = document.querySelector('.wpvfh-hint-close');
            if (hintClose) {
                hintClose.addEventListener('click', () => {
                    this.emitEvent('stop-annotation');
                    this.state.feedbackMode = 'view';
                });
            }

            // Sidebar overlay (fermer au clic)
            if (this.elements.sidebarOverlay) {
                this.elements.sidebarOverlay.addEventListener('click', this.closePanel.bind(this));
            }

            // Onglets
            if (this.elements.tabs) {
                this.elements.tabs.forEach(tab => {
                    tab.addEventListener('click', () => this.switchTab(tab.dataset.tab));
                });
            }

            // Bouton "Ajouter un feedback" dans l'onglet liste
            if (this.elements.addFeedbackBtn) {
                this.elements.addFeedbackBtn.addEventListener('click', () => {
                    this.switchTab('new');
                    this.startFeedbackMode();
                });
            }

            // Bouton retour dans les détails
            if (this.elements.backToListBtn) {
                this.elements.backToListBtn.addEventListener('click', () => {
                    this.switchTab('list');
                });
            }

            // Changement de statut
            if (this.elements.statusSelect) {
                this.elements.statusSelect.addEventListener('change', (e) => {
                    if (this.state.currentFeedbackId) {
                        this.updateFeedbackStatus(this.state.currentFeedbackId, e.target.value);
                    }
                });
            }

            // Envoi de réponse
            if (this.elements.sendReplyBtn) {
                this.elements.sendReplyBtn.addEventListener('click', () => {
                    if (this.state.currentFeedbackId && this.elements.replyInput) {
                        const content = this.elements.replyInput.value.trim();
                        if (content) {
                            this.addReply(this.state.currentFeedbackId, content);
                        }
                    }
                });
            }
        },

        /**
         * Gérer le clic sur un outil média
         * @param {Event} event - Événement
         * @param {string} tool - Nom de l'outil
         */
        handleToolClick: function(event, tool) {
            event.preventDefault();

            // Désactiver tous les boutons
            this.elements.toolButtons.forEach(btn => btn.classList.remove('active'));

            // Masquer toutes les sections
            if (this.elements.voiceSection) this.elements.voiceSection.hidden = true;
            if (this.elements.videoSection) this.elements.videoSection.hidden = true;

            switch (tool) {
                case 'screenshot':
                    event.target.closest('.wpvfh-tool-btn').classList.add('active');
                    this.captureScreenshot();
                    break;
                case 'voice':
                    event.target.closest('.wpvfh-tool-btn').classList.add('active');
                    if (this.elements.voiceSection) this.elements.voiceSection.hidden = false;
                    break;
                case 'video':
                    event.target.closest('.wpvfh-tool-btn').classList.add('active');
                    if (this.elements.videoSection) this.elements.videoSection.hidden = false;
                    break;
            }
        },

        /**
         * Gérer l'enregistrement vocal
         */
        handleVoiceRecord: async function() {
            if (!window.BlazingVoiceRecorder) {
                this.showNotification('Enregistrement vocal non disponible', 'error');
                return;
            }

            const recorder = window.BlazingVoiceRecorder;

            if (recorder.state.isRecording) {
                recorder.stop();
                this.elements.voiceRecordBtn.classList.remove('recording');
                this.elements.voiceRecordBtn.querySelector('.wpvfh-record-text').textContent = 'Enregistrer';
                if (this.voiceTimer) clearInterval(this.voiceTimer);
            } else {
                const started = await recorder.start();
                if (started) {
                    this.elements.voiceRecordBtn.classList.add('recording');
                    this.elements.voiceRecordBtn.querySelector('.wpvfh-record-text').textContent = 'Arrêter';
                    this.startVoiceTimer();
                } else {
                    this.showNotification('Impossible d\'accéder au microphone', 'error');
                }
            }
        },

        /**
         * Démarrer le timer d'enregistrement vocal
         */
        startVoiceTimer: function() {
            const timeDisplay = this.elements.voiceSection?.querySelector('.wpvfh-recorder-time');
            if (!timeDisplay) return;

            this.voiceTimer = setInterval(() => {
                if (window.BlazingVoiceRecorder) {
                    const duration = window.BlazingVoiceRecorder.getCurrentDuration();
                    timeDisplay.textContent = window.BlazingVoiceRecorder.formatDuration(duration);
                }
            }, 100);
        },

        /**
         * Gérer la fin de l'enregistrement vocal
         * @param {CustomEvent} event
         */
        handleVoiceComplete: async function(event) {
            const { audioUrl, transcript } = event.detail;

            if (this.elements.voicePreview) {
                const audio = this.elements.voicePreview.querySelector('audio');
                if (audio) audio.src = audioUrl;
                this.elements.voicePreview.hidden = false;
            }

            // Stocker les données audio en base64
            if (window.BlazingVoiceRecorder && this.elements.audioData) {
                const base64 = await window.BlazingVoiceRecorder.getAudioBase64();
                this.elements.audioData.value = base64 || '';
            }

            // Afficher la transcription
            if (transcript && this.elements.transcriptPreview) {
                const textEl = this.elements.transcriptPreview.querySelector('.wpvfh-transcript-text');
                if (textEl) textEl.textContent = transcript;
                this.elements.transcriptPreview.hidden = false;
                if (this.elements.transcriptField) {
                    this.elements.transcriptField.value = transcript;
                }
            }
        },

        /**
         * Gérer la mise à jour de la transcription
         * @param {CustomEvent} event
         */
        handleTranscriptionUpdate: function(event) {
            const { final, interim } = event.detail;
            if (this.elements.transcriptPreview) {
                const textEl = this.elements.transcriptPreview.querySelector('.wpvfh-transcript-text');
                if (textEl) {
                    textEl.textContent = final + (interim ? ' ' + interim : '');
                }
                this.elements.transcriptPreview.hidden = false;
            }
        },

        /**
         * Effacer l'enregistrement vocal
         */
        clearVoiceRecording: function() {
            if (window.BlazingVoiceRecorder) {
                window.BlazingVoiceRecorder.clear();
            }
            if (this.elements.voicePreview) {
                this.elements.voicePreview.hidden = true;
                const audio = this.elements.voicePreview.querySelector('audio');
                if (audio) audio.src = '';
            }
            if (this.elements.transcriptPreview) {
                this.elements.transcriptPreview.hidden = true;
            }
            if (this.elements.audioData) this.elements.audioData.value = '';
            if (this.elements.transcriptField) this.elements.transcriptField.value = '';
        },

        /**
         * Gérer l'enregistrement vidéo
         */
        handleVideoRecord: async function() {
            if (!window.BlazingScreenRecorder) {
                this.showNotification('Enregistrement d\'écran non disponible', 'error');
                return;
            }

            const recorder = window.BlazingScreenRecorder;

            if (recorder.state.isRecording) {
                recorder.stop();
                this.elements.videoRecordBtn.classList.remove('recording');
                this.elements.videoRecordBtn.querySelector('.wpvfh-record-text').textContent = 'Enregistrer l\'écran';
                if (this.videoTimer) clearInterval(this.videoTimer);
            } else {
                const started = await recorder.start({ includeMicrophone: true });
                if (started) {
                    this.elements.videoRecordBtn.classList.add('recording');
                    this.elements.videoRecordBtn.querySelector('.wpvfh-record-text').textContent = 'Arrêter';
                    this.startVideoTimer();
                } else {
                    this.showNotification('Impossible d\'accéder à l\'écran', 'error');
                }
            }
        },

        /**
         * Démarrer le timer d'enregistrement vidéo
         */
        startVideoTimer: function() {
            const timeDisplay = this.elements.videoSection?.querySelector('.wpvfh-recorder-time');
            if (!timeDisplay) return;

            this.videoTimer = setInterval(() => {
                if (window.BlazingScreenRecorder) {
                    const duration = window.BlazingScreenRecorder.getCurrentDuration();
                    timeDisplay.textContent = window.BlazingScreenRecorder.formatDuration(duration);
                }
            }, 100);
        },

        /**
         * Gérer la fin de l'enregistrement vidéo
         * @param {CustomEvent} event
         */
        handleVideoComplete: async function(event) {
            const { videoUrl } = event.detail;

            if (this.elements.videoPreview) {
                const video = this.elements.videoPreview.querySelector('video');
                if (video) video.src = videoUrl;
                this.elements.videoPreview.hidden = false;
            }

            // Note: Les vidéos sont trop volumineuses pour base64
            // On stocke l'URL blob pour l'upload séparé
            this.state.videoBlob = event.detail.videoBlob;
        },

        /**
         * Effacer l'enregistrement vidéo
         */
        clearVideoRecording: function() {
            if (window.BlazingScreenRecorder) {
                window.BlazingScreenRecorder.clear();
            }
            if (this.elements.videoPreview) {
                this.elements.videoPreview.hidden = true;
                const video = this.elements.videoPreview.querySelector('video');
                if (video) video.src = '';
            }
            this.state.videoBlob = null;
        },

        /**
         * Gérer le toggle du widget
         * @param {Event} event - Événement de clic
         * @returns {void}
         */
        handleToggle: function(event) {
            event.preventDefault();

            if (this.state.isOpen) {
                this.closePanel();
            } else {
                this.startFeedbackMode();
            }
        },

        /**
         * Démarrer le mode feedback
         * @returns {void}
         */
        startFeedbackMode: function() {
            this.state.feedbackMode = 'annotate';

            // Activer le mode annotation
            this.emitEvent('start-annotation');

            // Notification
            this.showNotification(this.config.i18n?.clickToPin || 'Cliquez pour placer un marqueur', 'info');
        },

        /**
         * Ouvrir le panel de feedback (sidebar)
         * @param {string} tab - Onglet à afficher ('new' ou 'list')
         * @returns {void}
         */
        openPanel: function(tab = 'new') {
            console.log('[Blazing Feedback] Ouverture du panel...');

            this.state.isOpen = true;

            if (this.elements.panel) {
                // Retirer l'attribut hidden
                this.elements.panel.removeAttribute('hidden');
                this.elements.panel.hidden = false;
                this.elements.panel.setAttribute('aria-hidden', 'false');

                // Forcer un reflow avant d'ajouter la classe d'animation
                void this.elements.panel.offsetHeight;

                // Ajouter la classe pour l'animation d'ouverture
                this.elements.panel.classList.add('wpvfh-panel-open');

                console.log('[Blazing Feedback] Panel classes:', this.elements.panel.className);
            }

            // Afficher l'overlay
            if (this.elements.sidebarOverlay) {
                this.elements.sidebarOverlay.classList.add('wpvfh-overlay-visible');
            }

            if (this.elements.toggleBtn) {
                this.elements.toggleBtn.setAttribute('aria-expanded', 'true');
            }

            // S'assurer de basculer sur le bon onglet
            this.switchTab(tab);

            // Focus sur le champ de commentaire si onglet nouveau
            if (tab === 'new' && this.elements.commentField) {
                setTimeout(() => this.elements.commentField.focus(), 350);
            }

            console.log('[Blazing Feedback] Panel ouvert, onglet:', tab);

            this.emitEvent('panel-opened');
        },

        /**
         * Fermer le panel de feedback (sidebar)
         * @returns {void}
         */
        closePanel: function() {
            this.state.isOpen = false;
            this.state.feedbackMode = 'view';

            if (this.elements.panel) {
                this.elements.panel.classList.remove('wpvfh-panel-open');
                // Masquer après l'animation
                setTimeout(() => {
                    if (!this.state.isOpen) {
                        this.elements.panel.hidden = true;
                        this.elements.panel.setAttribute('hidden', '');
                        this.elements.panel.setAttribute('aria-hidden', 'true');
                        this.elements.panel.style.pointerEvents = '';
                    }
                }, 300);
            }

            // Masquer l'overlay
            if (this.elements.sidebarOverlay) {
                this.elements.sidebarOverlay.classList.remove('wpvfh-overlay-visible');
            }

            if (this.elements.toggleBtn) {
                this.elements.toggleBtn.setAttribute('aria-expanded', 'false');
            }

            // Nettoyer le formulaire
            this.resetForm();

            console.log('[Blazing Feedback] Panel fermé');

            this.emitEvent('panel-closed');
        },

        /**
         * Changer d'onglet
         * @param {string} tabName - Nom de l'onglet ('new', 'list' ou 'details')
         */
        switchTab: function(tabName) {
            // Mettre à jour les boutons d'onglet
            if (this.elements.tabs && this.elements.tabs.length > 0) {
                this.elements.tabs.forEach(tab => {
                    // Masquer/Afficher le bouton détails selon l'onglet
                    if (tab.dataset.tab === 'details') {
                        tab.hidden = (tabName !== 'details');
                    }
                    tab.classList.toggle('active', tab.dataset.tab === tabName);
                });
            }

            // Afficher/masquer les contenus
            if (this.elements.tabNew) {
                this.elements.tabNew.classList.toggle('active', tabName === 'new');
            }
            if (this.elements.tabList) {
                this.elements.tabList.classList.toggle('active', tabName === 'list');
            }
            if (this.elements.tabDetails) {
                this.elements.tabDetails.classList.toggle('active', tabName === 'details');
            }

            // Si on va sur la liste, charger les feedbacks
            if (tabName === 'list') {
                this.renderPinsList();
                // Réinitialiser le feedback courant
                this.state.currentFeedbackId = null;
            }
        },

        /**
         * Afficher la liste des pins dans la sidebar
         */
        renderPinsList: function() {
            if (!this.elements.pinsList) return;

            const feedbacks = this.state.currentFeedbacks || [];

            // Mettre à jour le compteur
            if (this.elements.pinsCount) {
                this.elements.pinsCount.textContent = feedbacks.length > 0 ? `(${feedbacks.length})` : '';
            }

            // Afficher/masquer l'état vide
            if (this.elements.emptyState) {
                this.elements.emptyState.hidden = feedbacks.length > 0;
            }
            this.elements.pinsList.hidden = feedbacks.length === 0;

            if (feedbacks.length === 0) return;

            // Générer le HTML des pins
            const html = feedbacks.map((feedback, index) => {
                const statusLabels = {
                    new: this.config.i18n?.statusNew || 'Nouveau',
                    in_progress: this.config.i18n?.statusInProgress || 'En cours',
                    resolved: this.config.i18n?.statusResolved || 'Résolu',
                    rejected: this.config.i18n?.statusRejected || 'Rejeté',
                };

                const statusIcons = {
                    new: '!',
                    in_progress: '⏳',
                    resolved: '✓',
                    rejected: '✗',
                };

                const status = feedback.status || 'new';
                const date = feedback.date ? new Date(feedback.date).toLocaleDateString() : '';

                return `
                    <div class="wpvfh-pin-item" data-feedback-id="${feedback.id}">
                        <div class="wpvfh-pin-marker status-${status}">
                            ${statusIcons[status] || (index + 1)}
                        </div>
                        <div class="wpvfh-pin-content">
                            <p class="wpvfh-pin-text">${this.escapeHtml(feedback.comment || feedback.content || '')}</p>
                            <div class="wpvfh-pin-meta">
                                <span class="wpvfh-pin-status status-${status}">${statusLabels[status]}</span>
                                ${date ? `<span class="wpvfh-pin-date">${date}</span>` : ''}
                            </div>
                        </div>
                        <div class="wpvfh-pin-actions">
                            <button type="button" class="wpvfh-pin-action wpvfh-pin-goto" title="Aller au pin">
                                📍
                            </button>
                        </div>
                    </div>
                `;
            }).join('');

            this.elements.pinsList.innerHTML = html;

            // Ajouter les événements aux items
            this.elements.pinsList.querySelectorAll('.wpvfh-pin-item').forEach(item => {
                item.addEventListener('click', () => {
                    const feedbackId = parseInt(item.dataset.feedbackId, 10);
                    this.scrollToPin(feedbackId);
                });
            });
        },

        /**
         * Scroller vers un pin sur la page
         * @param {number} feedbackId
         */
        scrollToPin: function(feedbackId) {
            if (window.BlazingAnnotation) {
                window.BlazingAnnotation.scrollToPin(feedbackId);
            }
        },

        /**
         * Échapper le HTML
         * @param {string} str
         * @returns {string}
         */
        escapeHtml: function(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        },

        /**
         * Gérer le placement d'un pin
         * @param {CustomEvent} event - Événement
         * @returns {void}
         */
        handlePinPlaced: function(event) {
            const position = event.detail;

            // Stocker la position
            this.state.pinPosition = position;

            // Mettre à jour les champs cachés
            if (this.elements.positionX) {
                this.elements.positionX.value = position.percentX;
            }
            if (this.elements.positionY) {
                this.elements.positionY.value = position.percentY;
            }

            // Afficher l'info du pin
            if (this.elements.pinInfo) {
                this.elements.pinInfo.hidden = false;
            }

            // Capturer le screenshot si activé
            if (this.elements.screenshotToggle && this.elements.screenshotToggle.checked) {
                this.captureScreenshot();
            }

            // Ouvrir le panel
            this.state.feedbackMode = 'create';
            this.openPanel();
        },

        /**
         * Gérer la sélection d'un pin existant
         * @param {CustomEvent} event - Événement
         * @returns {void}
         */
        handlePinSelected: function(event) {
            const { feedbackId, pinData } = event.detail;

            if (!pinData) return;

            // Afficher les détails du feedback
            this.showFeedbackDetails(pinData);
        },

        /**
         * Capturer le screenshot
         * @returns {void}
         */
        captureScreenshot: async function() {
            if (!window.BlazingScreenshot || !window.BlazingScreenshot.isAvailable()) {
                console.warn('[Blazing Feedback] Screenshot non disponible');
                return;
            }

            try {
                this.showNotification(this.config.i18n?.loadingMessage || 'Capture en cours...', 'info');

                const dataUrl = await window.BlazingScreenshot.capture();

                // Redimensionner si nécessaire
                const resizedDataUrl = await window.BlazingScreenshot.resize(dataUrl, 1200, 900);

                this.state.screenshotData = resizedDataUrl;

                // Mettre à jour le champ caché
                if (this.elements.screenshotData) {
                    this.elements.screenshotData.value = resizedDataUrl;
                }

                // Afficher l'aperçu
                this.showScreenshotPreview(resizedDataUrl);

            } catch (error) {
                console.error('[Blazing Feedback] Erreur de capture:', error);
                this.showNotification('Erreur lors de la capture', 'error');
            }
        },

        /**
         * Afficher l'aperçu du screenshot
         * @param {string} dataUrl - Image en base64
         * @returns {void}
         */
        showScreenshotPreview: function(dataUrl) {
            if (!this.elements.screenshotPreview) return;

            const img = this.elements.screenshotPreview.querySelector('img');
            if (img) {
                img.src = dataUrl;
            }

            this.elements.screenshotPreview.hidden = false;
        },

        /**
         * Effacer le screenshot
         * @returns {void}
         */
        clearScreenshot: function() {
            this.state.screenshotData = null;

            if (this.elements.screenshotData) {
                this.elements.screenshotData.value = '';
            }

            if (this.elements.screenshotPreview) {
                this.elements.screenshotPreview.hidden = true;
                const img = this.elements.screenshotPreview.querySelector('img');
                if (img) {
                    img.src = '';
                }
            }
        },

        /**
         * Gérer le succès de capture
         * @param {CustomEvent} event - Événement
         * @returns {void}
         */
        handleCaptureSuccess: function(event) {
            // Capture gérée dans captureScreenshot
        },

        /**
         * Gérer l'erreur de capture
         * @param {CustomEvent} event - Événement
         * @returns {void}
         */
        handleCaptureError: function(event) {
            console.warn('[Blazing Feedback] Erreur de capture:', event.detail.error);
        },

        /**
         * Gérer l'annulation
         * @returns {void}
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
                // Collecter les métadonnées
                const metadata = window.BlazingScreenshot ? window.BlazingScreenshot.getMetadata() : {};

                // Préparer les données
                const feedbackData = {
                    comment: comment,
                    url: this.config.currentUrl || window.location.href,
                    position_x: this.state.pinPosition?.position_x || this.elements.positionX?.value || null,
                    position_y: this.state.pinPosition?.position_y || this.elements.positionY?.value || null,
                    screenshot_data: this.state.screenshotData || null,
                    screen_width: metadata.screenWidth,
                    screen_height: metadata.screenHeight,
                    viewport_width: metadata.viewportWidth,
                    viewport_height: metadata.viewportHeight,
                    browser: metadata.browser,
                    os: metadata.os,
                    device: metadata.device,
                    user_agent: metadata.userAgent,
                    // DOM Anchoring data
                    selector: this.state.pinPosition?.selector || null,
                    element_offset_x: this.state.pinPosition?.element_offset_x || null,
                    element_offset_y: this.state.pinPosition?.element_offset_y || null,
                    scroll_x: this.state.pinPosition?.scrollX || metadata.scrollX,
                    scroll_y: this.state.pinPosition?.scrollY || metadata.scrollY,
                };

                // Envoyer à l'API
                const response = await this.apiRequest('POST', 'feedbacks', feedbackData);

                if (response.id) {
                    // Succès
                    this.showNotification(this.config.i18n?.successMessage || 'Feedback envoyé avec succès !', 'success');

                    // Supprimer le pin temporaire et créer le permanent
                    if (window.BlazingAnnotation) {
                        window.BlazingAnnotation.removeTemporaryPin();
                        window.BlazingAnnotation.createPin(response);
                    }

                    // Fermer le panel
                    this.closePanel();

                    // Ajouter à la liste locale
                    this.state.currentFeedbacks.push(response);

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
                const url = encodeURIComponent(this.config.currentUrl || window.location.href);
                const response = await this.apiRequest('GET', `feedbacks/by-url?url=${url}`);

                if (Array.isArray(response)) {
                    this.state.currentFeedbacks = response;

                    // Afficher les pins
                    this.emitEvent('load-pins', { pins: response });
                }

            } catch (error) {
                console.warn('[Blazing Feedback] Impossible de charger les feedbacks:', error);
            }
        },

        /**
         * Afficher les détails d'un feedback dans la sidebar
         * @param {Object} feedback - Données du feedback
         * @returns {void}
         */
        showFeedbackDetails: function(feedback) {
            // Stocker l'ID du feedback courant
            this.state.currentFeedbackId = feedback.id;

            // Labels des statuts
            const statusLabels = {
                new: this.config.i18n?.statusNew || 'Nouveau',
                in_progress: this.config.i18n?.statusInProgress || 'En cours',
                resolved: this.config.i18n?.statusResolved || 'Résolu',
                rejected: this.config.i18n?.statusRejected || 'Rejeté',
            };

            const statusIcons = {
                new: '🆕',
                in_progress: '⏳',
                resolved: '✅',
                rejected: '❌',
            };

            const status = feedback.status || 'new';

            // Remplir les éléments
            if (this.elements.detailId) {
                this.elements.detailId.textContent = `#${feedback.id}`;
            }

            if (this.elements.detailStatus) {
                this.elements.detailStatus.innerHTML = `
                    <span class="wpvfh-status-badge status-${status}">
                        ${statusIcons[status] || ''} ${statusLabels[status] || status}
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

            // Ouvrir le panel et basculer sur l'onglet détails
            this.openPanel('details');
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
                    const statusLabels = {
                        new: this.config.i18n?.statusNew || 'Nouveau',
                        in_progress: this.config.i18n?.statusInProgress || 'En cours',
                        resolved: this.config.i18n?.statusResolved || 'Résolu',
                        rejected: this.config.i18n?.statusRejected || 'Rejeté',
                    };
                    const statusIcons = {
                        new: '🆕',
                        in_progress: '⏳',
                        resolved: '✅',
                        rejected: '❌',
                    };
                    this.elements.detailStatus.innerHTML = `
                        <span class="wpvfh-status-badge status-${status}">
                            ${statusIcons[status] || ''} ${statusLabels[status] || status}
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
         */
        emitEvent: function(name, detail = {}) {
            const event = new CustomEvent('blazing-feedback:' + name, {
                bubbles: true,
                detail: detail,
            });
            document.dispatchEvent(event);
        },
    };

    // Exposer le widget globalement
    window.BlazingFeedback = BlazingFeedback;

    // Initialiser au chargement du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => BlazingFeedback.init());
    } else {
        BlazingFeedback.init();
    }

})(window, document);
