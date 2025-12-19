/**
 * Module Events - Blazing Feedback
 * Gestion de tous les événements
 * @package Blazing_Feedback
 */
(function(window, document) {
    'use strict';

    const Events = {
        init: function(widget) {
            this.widget = widget;
        },

        bindEvents: function() {
            const w = this.widget;
            const el = w.elements;

            // Bouton toggle principal
            if (el.toggleBtn) {
                el.toggleBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (w.state.isOpen) {
                        w.modules.panel.closePanel();
                    } else {
                        w.modules.panel.openPanel('list');
                    }
                });
            }

            // Bouton ajouter
            if (el.addBtn) {
                el.addBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    w.modules.panel.openPanel('new');
                    if (el.commentField) {
                        setTimeout(() => el.commentField.focus(), 350);
                    }
                });
            }

            // Bouton visibilité
            if (el.visibilityBtn) {
                el.visibilityBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const btn = el.visibilityBtn;
                    const isVisible = btn.dataset.visible === 'true';
                    const newVisible = !isVisible;
                    btn.dataset.visible = newVisible.toString();

                    const iconVisible = btn.querySelector('.wpvfh-icon-visible');
                    const iconHidden = btn.querySelector('.wpvfh-icon-hidden');
                    if (iconVisible) iconVisible.hidden = !newVisible;
                    if (iconHidden) iconHidden.hidden = newVisible;

                    w.modules.tools.emitEvent('toggle-pins', { visible: newVisible });
                    w.modules.notifications.show(newVisible ? 'Points affichés' : 'Points masqués', 'info');
                });
            }

            // Boutons du panel
            if (el.closeBtn) {
                el.closeBtn.addEventListener('click', () => w.modules.panel.closePanel());
            }
            if (el.cancelBtn) {
                el.cancelBtn.addEventListener('click', () => {
                    if (window.BlazingAnnotation) {
                        window.BlazingAnnotation.removeTemporaryPin();
                    }
                    w.modules.panel.closePanel();
                });
            }

            // Soumission du formulaire
            if (el.form) {
                el.form.addEventListener('submit', (e) => w.modules.form.handleSubmit(e));
            }

            // Outils média
            if (el.toolButtons) {
                el.toolButtons.forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        el.toolButtons.forEach(b => b.classList.remove('active'));

                        if (el.voiceSection) el.voiceSection.hidden = true;
                        if (el.videoSection) el.videoSection.hidden = true;

                        const tool = btn.dataset.tool;
                        if (tool === 'screenshot') {
                            btn.classList.add('active');
                            w.modules.screenshot.captureScreenshot();
                        } else if (tool === 'voice') {
                            btn.classList.add('active');
                            if (el.voiceSection) el.voiceSection.hidden = false;
                        } else if (tool === 'video') {
                            btn.classList.add('active');
                            if (el.videoSection) el.videoSection.hidden = false;
                        }
                    });
                });
            }

            // Enregistrement vocal
            if (el.voiceRecordBtn) {
                el.voiceRecordBtn.addEventListener('click', () => w.modules.media.handleVoiceRecord());
            }

            // Enregistrement vidéo
            if (el.videoRecordBtn) {
                el.videoRecordBtn.addEventListener('click', () => w.modules.media.handleVideoRecord());
            }

            // Boutons supprimer média
            document.querySelectorAll('.wpvfh-remove-media').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const parent = e.target.closest('.wpvfh-screenshot-preview, .wpvfh-audio-preview, .wpvfh-video-preview');
                    if (parent) {
                        if (parent.classList.contains('wpvfh-screenshot-preview')) w.modules.screenshot.clearScreenshot();
                        if (parent.classList.contains('wpvfh-audio-preview')) w.modules.media.clearVoiceRecording();
                        if (parent.classList.contains('wpvfh-video-preview')) w.modules.media.clearVideoRecording();
                    }
                });
            });

            // Événements custom
            document.addEventListener('blazing-feedback:pin-selected', (e) => {
                const { feedbackId, pinData } = e.detail;
                if (pinData && w.modules.details) {
                    w.modules.details.showFeedbackDetails(pinData);
                }
            });

            document.addEventListener('blazing-feedback:voice-recording-complete', async (e) => {
                const { audioUrl, transcript } = e.detail;
                if (el.voicePreview) {
                    const audio = el.voicePreview.querySelector('audio');
                    if (audio) audio.src = audioUrl;
                    el.voicePreview.hidden = false;
                }
                if (window.BlazingVoiceRecorder && el.audioData) {
                    const base64 = await window.BlazingVoiceRecorder.getAudioBase64();
                    el.audioData.value = base64 || '';
                }
                if (transcript && el.transcriptPreview) {
                    const textEl = el.transcriptPreview.querySelector('.wpvfh-transcript-text');
                    if (textEl) textEl.textContent = transcript;
                    el.transcriptPreview.hidden = false;
                    if (el.transcriptField) el.transcriptField.value = transcript;
                }
            });

            document.addEventListener('blazing-feedback:screen-recording-complete', (e) => {
                const { videoUrl } = e.detail;
                if (el.videoPreview) {
                    const video = el.videoPreview.querySelector('video');
                    if (video) video.src = videoUrl;
                    el.videoPreview.hidden = false;
                }
                w.state.videoBlob = e.detail.videoBlob;
            });

            // Échap pour fermer
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && w.state.isOpen) {
                    w.modules.panel.closePanel();
                }
            });

            // Overlay sidebar
            if (el.sidebarOverlay) {
                el.sidebarOverlay.addEventListener('click', () => w.modules.panel.closePanel());
            }

            // Onglets
            if (el.tabs) {
                el.tabs.forEach(tab => {
                    tab.addEventListener('click', () => w.modules.panel.switchTab(tab.dataset.tab));
                });
            }

            // Bouton "Ajouter un feedback"
            if (el.addFeedbackBtn) {
                el.addFeedbackBtn.addEventListener('click', () => {
                    w.modules.panel.switchTab('new');
                    if (el.commentField) {
                        setTimeout(() => el.commentField.focus(), 350);
                    }
                });
            }

            // Bouton retour dans les détails
            if (el.backToListBtn) {
                el.backToListBtn.addEventListener('click', () => w.modules.panel.switchTab('list'));
            }

            // Changement de statut
            if (el.statusSelect) {
                el.statusSelect.addEventListener('change', (e) => {
                    if (w.state.currentFeedbackId && w.modules.details) {
                        w.modules.details.updateFeedbackStatus(w.state.currentFeedbackId, e.target.value);
                    }
                });
            }

            // Envoi de réponse
            if (el.sendReplyBtn) {
                el.sendReplyBtn.addEventListener('click', () => {
                    if (w.state.currentFeedbackId && el.replyInput && w.modules.details) {
                        const content = el.replyInput.value.trim();
                        if (content) {
                            w.modules.details.addReply(w.state.currentFeedbackId, content);
                        }
                    }
                });
            }

            // Filtres
            if (el.filterButtons) {
                el.filterButtons.forEach(btn => {
                    btn.addEventListener('click', () => w.modules.filters.handleFilterClick(btn.dataset.status));
                });
            }

            // Bouton supprimer feedback
            if (el.deleteFeedbackBtn) {
                el.deleteFeedbackBtn.addEventListener('click', () => {
                    if (w.state.currentFeedbackId && w.modules.list) {
                        w.modules.list.showDeleteModal(w.state.currentFeedbackId);
                    }
                });
            }

            // Modal suppression
            if (el.cancelDeleteBtn) {
                el.cancelDeleteBtn.addEventListener('click', () => w.modules.list.hideDeleteModal());
            }
            if (el.confirmDeleteBtn) {
                el.confirmDeleteBtn.addEventListener('click', () => w.modules.list.confirmDeleteFeedback());
            }

            // Fermer modal en cliquant sur l'overlay
            document.querySelectorAll('.wpvfh-modal-overlay').forEach(overlay => {
                overlay.addEventListener('click', () => {
                    if (el.confirmModal) el.confirmModal.hidden = true;
                    if (el.validateModal) el.validateModal.hidden = true;
                });
            });

            // Bouton valider la page
            if (el.validatePageBtn) {
                el.validatePageBtn.addEventListener('click', () => w.modules.validation.showValidateModal());
            }

            // Modal validation
            if (el.cancelValidateBtn) {
                el.cancelValidateBtn.addEventListener('click', () => {
                    if (el.validateModal) el.validateModal.hidden = true;
                });
            }
            if (el.confirmValidateBtn) {
                el.confirmValidateBtn.addEventListener('click', () => w.modules.validation.confirmValidatePage());
            }

            // Pièces jointes
            if (el.addAttachmentBtn && el.attachmentsInput) {
                el.addAttachmentBtn.addEventListener('click', () => el.attachmentsInput.click());
                el.attachmentsInput.addEventListener('change', (e) => {
                    if (w.modules.attachments) {
                        w.modules.attachments.handleAttachmentSelect(e.target.files);
                    }
                });
            }

            // Mentions
            if (el.commentField) {
                el.commentField.addEventListener('input', (e) => {
                    if (w.modules.mentions) {
                        w.modules.mentions.handleMentionInput(e);
                    }
                });
            }

            // Recherche
            if (el.searchBtn) {
                el.searchBtn.addEventListener('click', () => w.modules.search.openSearchModal());
            }
            if (el.searchClose) {
                el.searchClose.addEventListener('click', () => w.modules.search.closeSearchModal());
            }
            if (el.searchModal) {
                el.searchModal.addEventListener('click', (e) => {
                    if (e.target === el.searchModal) {
                        w.modules.search.closeSearchModal();
                    }
                });
            }
            if (el.searchForm) {
                el.searchForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                    w.modules.search.performSearch();
                });
            }

            // Tags formulaire
            if (el.feedbackTagsInput) {
                el.feedbackTagsInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ',') {
                        e.preventDefault();
                        const newTag = e.target.value.replace(/,/g, '').trim();
                        if (newTag && w.modules.tags) {
                            w.modules.tags.addFormTag(newTag);
                            e.target.value = '';
                        }
                    }
                });
            }

            // Tags détails
            if (el.detailTagsInput) {
                el.detailTagsInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ',') {
                        e.preventDefault();
                        const newTag = e.target.value.replace(/,/g, '').trim();
                        if (newTag && w.state.currentFeedbackId && w.modules.tags) {
                            w.modules.tags.addDetailTag(newTag);
                            e.target.value = '';
                        }
                    }
                });
            }

            // =======================
            // CIBLAGE D'ÉLÉMENTS
            // =======================

            // Bouton cibler un élément
            if (el.selectElementBtn) {
                el.selectElementBtn.addEventListener('click', () => {
                    if (window.BlazingAnnotation) {
                        w.modules.panel.closePanel();
                        window.BlazingAnnotation.startInspector();
                    }
                });
            }

            // Bouton effacer la sélection
            if (el.clearSelectionBtn) {
                el.clearSelectionBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    if (window.BlazingAnnotation) {
                        window.BlazingAnnotation.clearSelection();
                    }
                    w.state.pinPosition = null;

                    if (el.selectElementBtn) {
                        el.selectElementBtn.hidden = false;
                    }
                    if (el.selectedElement) {
                        el.selectedElement.hidden = true;
                    }
                });
            }

            // Événement: élément sélectionné
            document.addEventListener('blazing-feedback:element-selected', (e) => {
                const { element, data } = e.detail;
                if (!element || !data) return;

                // Stocker la position
                w.state.pinPosition = data;

                // Mettre à jour l'UI
                if (el.selectElementBtn) {
                    el.selectElementBtn.hidden = true;
                }

                if (el.selectedElement) {
                    el.selectedElement.hidden = false;
                    const label = el.selectedElement.querySelector('.wpvfh-selected-element-label');
                    if (label) {
                        const tagName = element.tagName.toLowerCase();
                        const id = element.id ? `#${element.id}` : '';
                        const classes = element.className && typeof element.className === 'string'
                            ? '.' + element.className.split(/\s+/).filter(c => c && !c.startsWith('wpvfh-')).slice(0, 2).join('.')
                            : '';
                        label.textContent = tagName + id + classes;
                    }
                }

                // Rouvrir le panel sur l'onglet nouveau
                if (!w.state.isOpen) {
                    w.modules.panel.openPanel('new');
                } else {
                    w.modules.panel.switchTab('new');
                }

                if (el.commentField) {
                    setTimeout(() => el.commentField.focus(), 350);
                }

                w.modules.notifications.show('Élément ciblé', 'success');
            });

            // Événement: sélection effacée
            document.addEventListener('blazing-feedback:selection-cleared', () => {
                w.state.pinPosition = null;

                if (el.selectElementBtn) {
                    el.selectElementBtn.hidden = false;
                }
                if (el.selectedElement) {
                    el.selectedElement.hidden = true;
                }
            });

            // Événement: inspecteur arrêté (sans sélection)
            document.addEventListener('blazing-feedback:inspector-stopped', () => {
                // Rouvrir le panel si fermé sans sélection
                if (!w.state.isOpen && !window.BlazingAnnotation?.hasSelection()) {
                    w.modules.panel.openPanel('new');
                }
            });

            // =======================
            // SOUS-ONGLETS METADATA
            // =======================

            if (el.metadataSubtabs) {
                el.metadataSubtabs.forEach(subtab => {
                    subtab.addEventListener('click', () => {
                        if (w.modules.panel) {
                            w.modules.panel.switchMetadataSubtab(subtab.dataset.subtab);
                        }
                    });
                });
            }
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.events = Events;

})(window, document);
