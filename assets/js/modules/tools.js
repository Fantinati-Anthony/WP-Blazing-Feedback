/**
 * Gestion des outils (click, voice, video)
 * 
 * Reference file for feedback-widget.js lines 700-940
 * See main file: assets/js/feedback-widget.js
 * 
 * Methods included:
 * - 
handleToolClick * - startVoiceTimer * - handleTranscriptionUpdate * - clearVoiceRecording * - startVideoTimer * - clearVideoRecording
 * 
 * @package Blazing_Feedback
 */

/* 
 * To view this section, read feedback-widget.js with:
 * offset=700, limit=241
 */

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
         * Gérer le toggle du widget (bouton principal "Feedback")
         * Ouvre le panel avec la liste des feedbacks
         * @param {Event} event - Événement de clic
         * @returns {void}
         */
        handleToggle: function(event) {
            event.preventDefault();

            if (this.state.isOpen) {
                this.closePanel();
            } else {
                // Ouvrir le panel avec l'onglet liste
                this.openPanel('list');
            }
        },

        /**
         * Gérer le clic sur le bouton "+" (ajouter)
         * Ouvre le panel avec le formulaire nouveau feedback
         * @param {Event} event
         * @returns {void}
         */
        handleAddClick: function(event) {
            event.preventDefault();

            // Ouvrir le panel avec l'onglet nouveau
            this.openPanel('new');

            // Focus sur le champ commentaire
            if (this.elements.commentField) {
                setTimeout(() => this.elements.commentField.focus(), 350);
            }
        },

        /**
         * Gérer le clic sur le bouton de visibilité
         * Affiche/masque les pins
         * @param {Event} event
         * @returns {void}