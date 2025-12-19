/**
 * Module Form - Blazing Feedback
 * Gestion du formulaire de feedback
 * @package Blazing_Feedback
 */
(function(window) {
    'use strict';

    const Form = {
        init: function(widget) {
            this.widget = widget;
        },

        /**
         * Gérer la soumission du formulaire
         */
        handleSubmit: async function(event) {
            event.preventDefault();

            if (this.widget.state.isSubmitting) return;

            const comment = this.widget.elements.commentField?.value?.trim();
            if (!comment) {
                this.widget.modules.notifications.show(this.widget.config.i18n?.errorMessage || 'Veuillez entrer un commentaire', 'error');
                this.widget.elements.commentField?.focus();
                return;
            }

            this.widget.state.isSubmitting = true;
            this.setSubmitState(true);

            try {
                const metadata = window.BlazingScreenshot ? window.BlazingScreenshot.getMetadata() : {};

                // Capture automatique si pas déjà fournie
                let screenshotData = this.widget.state.screenshotData || null;
                if (!screenshotData && window.BlazingScreenshot && window.BlazingScreenshot.isAvailable()) {
                    try {
                        screenshotData = await window.BlazingScreenshot.capture();
                    } catch (screenshotError) {
                        console.warn('[Blazing Feedback] Erreur capture auto:', screenshotError);
                    }
                }

                const feedbackData = {
                    comment: comment,
                    url: this.widget.config.currentUrl || window.location.href,
                    position_x: this.widget.state.pinPosition?.position_x || this.widget.elements.positionX?.value || null,
                    position_y: this.widget.state.pinPosition?.position_y || this.widget.elements.positionY?.value || null,
                    screenshot_data: screenshotData,
                    screen_width: metadata.screenWidth,
                    screen_height: metadata.screenHeight,
                    viewport_width: metadata.viewportWidth,
                    viewport_height: metadata.viewportHeight,
                    device_pixel_ratio: metadata.devicePixelRatio,
                    color_depth: metadata.colorDepth,
                    orientation: metadata.orientation,
                    browser: metadata.browser,
                    browser_version: metadata.browserVersion,
                    os: metadata.os,
                    os_version: metadata.osVersion,
                    device: metadata.device,
                    platform: metadata.platform,
                    user_agent: metadata.userAgent,
                    language: metadata.language,
                    languages: metadata.languages,
                    timezone: metadata.timezone,
                    timezone_offset: metadata.timezoneOffset,
                    local_time: metadata.localTime,
                    cookies_enabled: metadata.cookiesEnabled,
                    online: metadata.onLine,
                    touch_support: metadata.touchSupport ? JSON.stringify(metadata.touchSupport) : null,
                    max_touch_points: metadata.maxTouchPoints,
                    device_memory: metadata.deviceMemory,
                    hardware_concurrency: metadata.hardwareConcurrency,
                    connection_type: metadata.connectionType ? JSON.stringify(metadata.connectionType) : null,
                    selector: this.widget.state.pinPosition?.selector || null,
                    element_offset_x: this.widget.state.pinPosition?.element_offset_x || null,
                    element_offset_y: this.widget.state.pinPosition?.element_offset_y || null,
                    scroll_x: this.widget.state.pinPosition?.scrollX || metadata.scrollX,
                    scroll_y: this.widget.state.pinPosition?.scrollY || metadata.scrollY,
                    referrer: metadata.referrer,
                    feedback_type: this.widget.elements.feedbackType?.value || '',
                    priority: this.widget.elements.feedbackPriority?.value || 'none',
                    tags: this.widget.elements.feedbackTags?.value || '',
                };

                const response = await this.widget.modules.api.request('POST', 'feedbacks', feedbackData);

                if (response.id) {
                    this.widget.modules.notifications.show(this.widget.config.i18n?.successMessage || 'Feedback envoyé avec succès !', 'success');

                    if (window.BlazingAnnotation) {
                        window.BlazingAnnotation.removeTemporaryPin();
                        window.BlazingAnnotation.createPin(response);
                    }

                    this.widget.state.currentFeedbacks.push(response);

                    const postAction = this.widget.config.postFeedbackAction || 'close';
                    if (postAction === 'list') {
                        this.resetForm();
                        this.widget.modules.panel.switchTab('list');
                    } else {
                        this.widget.modules.panel.closePanel();
                    }

                    this.widget.modules.api.updateFeedbackCounts(this.widget.state.currentFeedbacks.length);
                    this.widget.modules.tools.emitEvent('feedback-created', response);
                }
            } catch (error) {
                console.error('[Blazing Feedback] Erreur de soumission:', error);
                this.widget.modules.notifications.show(error.message || this.widget.config.i18n?.errorMessage || 'Erreur lors de l\'envoi', 'error');
            } finally {
                this.widget.state.isSubmitting = false;
                this.setSubmitState(false);
            }
        },

        /**
         * Réinitialiser le formulaire
         */
        resetForm: function() {
            if (this.widget.elements.form) {
                this.widget.elements.form.reset();
            }

            this.widget.state.pinPosition = null;
            this.widget.modules.screenshot.clearScreenshot();

            if (this.widget.elements.pinInfo) {
                this.widget.elements.pinInfo.hidden = true;
            }

            if (this.widget.elements.positionX) {
                this.widget.elements.positionX.value = '';
            }
            if (this.widget.elements.positionY) {
                this.widget.elements.positionY.value = '';
            }

            if (window.BlazingAnnotation) {
                window.BlazingAnnotation.removeTemporaryPin();
            }

            this.widget.modules.tools.emitEvent('clear-selection');

            if (this.widget.elements.selectElementBtn) {
                this.widget.elements.selectElementBtn.hidden = false;
            }
            if (this.widget.elements.selectedElement) {
                this.widget.elements.selectedElement.hidden = true;
            }

            if (this.widget.elements.feedbackType) {
                this.widget.elements.feedbackType.value = '';
            }
            if (this.widget.elements.feedbackPriority) {
                this.widget.elements.feedbackPriority.value = 'none';
            }

            this.widget.modules.tags.clearFormTags();
            if (this.widget.elements.feedbackTagsInput) {
                this.widget.elements.feedbackTagsInput.value = '';
            }
        },

        /**
         * Définir l'état du bouton de soumission
         */
        setSubmitState: function(isLoading) {
            const btn = this.widget.elements.submitBtn;
            if (!btn) return;

            btn.disabled = isLoading;

            if (isLoading) {
                btn.innerHTML = '<span class="wpvfh-spinner"></span> ' + (this.widget.config.i18n?.loadingMessage || 'Envoi...');
            } else {
                btn.innerHTML = '<span class="wpvfh-btn-emoji">📤</span> ' + (this.widget.config.i18n?.submitButton || 'Envoyer');
            }
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.form = Form;

})(window);
