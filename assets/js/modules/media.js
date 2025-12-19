/**
 * Module Media - Blazing Feedback
 * Enregistrement audio/vidéo
 * @package Blazing_Feedback
 */
(function(window) {
    'use strict';

    const Media = {
        voiceTimer: null,
        videoTimer: null,

        init: function(widget) {
            this.widget = widget;
        },

        /**
         * Gérer l'enregistrement vocal
         */
        handleVoiceRecord: async function() {
            if (!window.BlazingVoiceRecorder) {
                this.widget.modules.notifications.show('Enregistrement vocal non disponible', 'error');
                return;
            }

            const recorder = window.BlazingVoiceRecorder;
            const btn = this.widget.elements.voiceRecordBtn;

            if (recorder.state.isRecording) {
                recorder.stop();
                btn.classList.remove('recording');
                btn.querySelector('.wpvfh-record-text').textContent = 'Enregistrer';
                if (this.voiceTimer) clearInterval(this.voiceTimer);
            } else {
                const started = await recorder.start();
                if (started) {
                    btn.classList.add('recording');
                    btn.querySelector('.wpvfh-record-text').textContent = 'Arrêter';
                    this.startVoiceTimer();
                } else {
                    this.widget.modules.notifications.show('Impossible d\'accéder au microphone', 'error');
                }
            }
        },

        /**
         * Démarrer le timer vocal
         */
        startVoiceTimer: function() {
            const timeDisplay = this.widget.elements.voiceSection?.querySelector('.wpvfh-recorder-time');
            if (!timeDisplay) return;

            this.voiceTimer = setInterval(() => {
                if (window.BlazingVoiceRecorder) {
                    const duration = window.BlazingVoiceRecorder.getCurrentDuration();
                    timeDisplay.textContent = window.BlazingVoiceRecorder.formatDuration(duration);
                }
            }, 100);
        },

        /**
         * Gérer l'enregistrement vidéo
         */
        handleVideoRecord: async function() {
            if (!window.BlazingScreenRecorder) {
                this.widget.modules.notifications.show('Enregistrement d\'écran non disponible', 'error');
                return;
            }

            const recorder = window.BlazingScreenRecorder;
            const btn = this.widget.elements.videoRecordBtn;

            if (recorder.state.isRecording) {
                recorder.stop();
                btn.classList.remove('recording');
                btn.querySelector('.wpvfh-record-text').textContent = 'Enregistrer l\'écran';
                if (this.videoTimer) clearInterval(this.videoTimer);
            } else {
                const started = await recorder.start({ includeMicrophone: true });
                if (started) {
                    btn.classList.add('recording');
                    btn.querySelector('.wpvfh-record-text').textContent = 'Arrêter';
                    this.startVideoTimer();
                } else {
                    this.widget.modules.notifications.show('Impossible d\'accéder à l\'écran', 'error');
                }
            }
        },

        /**
         * Démarrer le timer vidéo
         */
        startVideoTimer: function() {
            const timeDisplay = this.widget.elements.videoSection?.querySelector('.wpvfh-recorder-time');
            if (!timeDisplay) return;

            this.videoTimer = setInterval(() => {
                if (window.BlazingScreenRecorder) {
                    const duration = window.BlazingScreenRecorder.getCurrentDuration();
                    timeDisplay.textContent = window.BlazingScreenRecorder.formatDuration(duration);
                }
            }, 100);
        },

        /**
         * Effacer l'enregistrement vocal
         */
        clearVoiceRecording: function() {
            if (window.BlazingVoiceRecorder) {
                window.BlazingVoiceRecorder.clear();
            }
            if (this.widget.elements.voicePreview) {
                this.widget.elements.voicePreview.hidden = true;
                const audio = this.widget.elements.voicePreview.querySelector('audio');
                if (audio) audio.src = '';
            }
            if (this.widget.elements.transcriptPreview) {
                this.widget.elements.transcriptPreview.hidden = true;
            }
            if (this.widget.elements.audioData) this.widget.elements.audioData.value = '';
            if (this.widget.elements.transcriptField) this.widget.elements.transcriptField.value = '';
        },

        /**
         * Effacer l'enregistrement vidéo
         */
        clearVideoRecording: function() {
            if (window.BlazingScreenRecorder) {
                window.BlazingScreenRecorder.clear();
            }
            if (this.widget.elements.videoPreview) {
                this.widget.elements.videoPreview.hidden = true;
                const video = this.widget.elements.videoPreview.querySelector('video');
                if (video) video.src = '';
            }
            this.widget.state.videoBlob = null;
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.media = Media;

})(window);
