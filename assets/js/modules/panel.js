/**
 * Module Panel - Blazing Feedback
 * Gestion du panneau latéral
 * @package Blazing_Feedback
 */
(function(window, document) {
    'use strict';

    const Panel = {
        init: function(widget) {
            this.widget = widget;
        },

        openPanel: function(tab = 'new') {
            this.widget.state.isOpen = true;

            document.body.classList.add('wpvfh-panel-active');

            const panelPosition = this.widget.elements.container?.dataset?.panelPosition || 'right';
            if (panelPosition === 'left') {
                document.body.classList.add('wpvfh-panel-left');
            }

            const panel = this.widget.elements.panel;
            if (panel) {
                panel.removeAttribute('hidden');
                panel.hidden = false;
                panel.setAttribute('aria-hidden', 'false');

                void panel.offsetHeight;

                panel.classList.add('wpvfh-panel-open');
            }

            if (this.widget.elements.sidebarOverlay) {
                this.widget.elements.sidebarOverlay.classList.add('wpvfh-overlay-visible');
            }

            if (this.widget.elements.toggleBtn) {
                this.widget.elements.toggleBtn.setAttribute('aria-expanded', 'true');
            }

            this.switchTab(tab);

            if (tab === 'new' && this.widget.elements.commentField) {
                setTimeout(() => this.widget.elements.commentField.focus(), 350);
            }

            setTimeout(() => {
                if (window.BlazingAnnotation && window.BlazingAnnotation.repositionAllPins) {
                    window.BlazingAnnotation.repositionAllPins();
                }
            }, 350);

            this.widget.modules.tools.emitEvent('panel-opened');
        },

        closePanel: function() {
            this.widget.state.isOpen = false;
            this.widget.state.feedbackMode = 'view';

            document.body.classList.remove('wpvfh-panel-active');
            document.body.classList.remove('wpvfh-panel-left');

            const panel = this.widget.elements.panel;
            if (panel) {
                panel.classList.remove('wpvfh-panel-open');
                setTimeout(() => {
                    if (!this.widget.state.isOpen) {
                        panel.hidden = true;
                        panel.setAttribute('hidden', '');
                        panel.setAttribute('aria-hidden', 'true');
                    }
                }, 300);
            }

            if (this.widget.elements.sidebarOverlay) {
                this.widget.elements.sidebarOverlay.classList.remove('wpvfh-overlay-visible');
            }

            if (this.widget.elements.toggleBtn) {
                this.widget.elements.toggleBtn.setAttribute('aria-expanded', 'false');
            }

            if (!this.widget.state.isSelectingElement && this.widget.modules.form) {
                this.widget.modules.form.resetForm();
            }

            setTimeout(() => {
                if (window.BlazingAnnotation && window.BlazingAnnotation.repositionAllPins) {
                    window.BlazingAnnotation.repositionAllPins();
                }
            }, 350);

            this.widget.modules.tools.emitEvent('panel-closed');
        },

        switchTab: function(tabName) {
            if (this.widget.elements.tabs && this.widget.elements.tabs.length > 0) {
                this.widget.elements.tabs.forEach(tab => {
                    if (tab.dataset.tab === 'new') {
                        tab.hidden = (tabName !== 'new');
                    }
                    if (tab.dataset.tab === 'details') {
                        const showDetailsTab = (tabName === 'details' && this.widget.state.currentFeedbackId);
                        tab.hidden = !showDetailsTab;
                    }
                    tab.classList.toggle('active', tab.dataset.tab === tabName);
                });
            }

            if (this.widget.elements.tabNew) {
                this.widget.elements.tabNew.classList.toggle('active', tabName === 'new');
            }
            if (this.widget.elements.tabList) {
                this.widget.elements.tabList.classList.toggle('active', tabName === 'list');
            }
            if (this.widget.elements.tabDetails) {
                this.widget.elements.tabDetails.classList.toggle('active', tabName === 'details');
            }
            if (this.widget.elements.tabPages) {
                this.widget.elements.tabPages.classList.toggle('active', tabName === 'pages');
            }
            if (this.widget.elements.tabPriority) {
                this.widget.elements.tabPriority.classList.toggle('active', tabName === 'priority');
            }
            if (this.widget.elements.tabMetadata) {
                this.widget.elements.tabMetadata.classList.toggle('active', tabName === 'metadata');
            }

            if (tabName === 'list' && this.widget.modules.list) {
                this.widget.modules.list.renderPinsList();
                if (this.widget.modules.filters) {
                    this.widget.modules.filters.updateFilterCounts();
                }
                if (this.widget.modules.validation) {
                    this.widget.modules.validation.updateValidationSection();
                }
                this.widget.state.currentFeedbackId = null;
            }

            if (tabName === 'pages' && this.widget.modules.pages) {
                this.widget.modules.pages.loadAllPages();
            }
        },

        /**
         * Changer de sous-onglet métadatas
         * @param {string} subtabName - Nom du sous-onglet
         */
        switchMetadataSubtab: function(subtabName) {
            const el = this.widget.elements;

            // Mettre à jour les boutons de sous-onglet
            if (el.metadataSubtabs) {
                el.metadataSubtabs.forEach(subtab => {
                    subtab.classList.toggle('active', subtab.dataset.subtab === subtabName);
                });
            }

            // Mettre à jour les contenus
            if (el.metadataSubtabContents) {
                el.metadataSubtabContents.forEach(content => {
                    content.classList.toggle('active', content.dataset.group === subtabName);
                });
            }
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.panel = Panel;

})(window, document);
