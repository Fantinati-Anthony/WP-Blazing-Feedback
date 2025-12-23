/**
 * Blazing Minds - Foundations Admin JavaScript
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 */

(function($) {
	'use strict';

	// Configuration globale
	const bzmiFoundations = window.bzmiFoundationsData || {};

	/**
	 * Main Foundations Admin Object
	 */
	const BZMIFoundations = {
		// Configuration
		config: {
			apiBase: (bzmiFoundations.apiBase || '/wp-json/blazing-minds/v1/foundations').replace(/\/$/, ''),
			nonce: bzmiFoundations.nonce || '',
			adminUrl: bzmiFoundations.adminUrl || '/wp-admin/',
			foundationId: null,
			currentTab: 'identity'
		},

		// DOM Elements cache
		elements: {
			$wrap: null,
			$modal: null,
			$aiSidebar: null,
			$aiResults: null
		},

		/**
		 * Initialize
		 */
		init: function() {
			this.cacheElements();
			this.bindEvents();
			this.initCurrentPage();
		},

		/**
		 * Cache DOM elements
		 */
		cacheElements: function() {
			this.elements.$wrap = $('.bzmi-foundations-wrap');
			this.elements.$modal = $('#bzmi-modal');
			this.elements.$aiSidebar = $('#bzmi-ai-sidebar');
			this.elements.$aiResults = $('#bzmi-ai-results');

			// Get foundation ID from content
			const $content = $('.bzmi-foundation-content');
			if ($content.length) {
				this.config.foundationId = $content.data('foundation-id');
				this.config.currentTab = $content.data('tab') || 'identity';
			}
		},

		/**
		 * Bind all events
		 */
		bindEvents: function() {
			// Modal events
			this.elements.$modal.on('click', '.bzmi-modal__backdrop, .bzmi-modal__close, .bzmi-modal__cancel', this.closeModal.bind(this));
			this.elements.$modal.on('click', '.bzmi-modal__save', this.handleModalSave.bind(this));

			// AI Sidebar events
			this.elements.$aiSidebar.on('click', '.bzmi-ai-sidebar__close, .bzmi-ai-sidebar__dismiss', this.closeAiSidebar.bind(this));
			this.elements.$aiSidebar.on('click', '.bzmi-ai-sidebar__apply', this.handleAiApply.bind(this));

			// Identity tab events
			this.elements.$wrap.on('click', '.bzmi-identity-section__header', this.toggleIdentitySection.bind(this));
			this.elements.$wrap.on('click', '.bzmi-btn-edit-section', this.handleEditSection.bind(this));
			this.elements.$wrap.on('click', '.bzmi-btn-ai-section', this.handleAiSection.bind(this));
			this.elements.$wrap.on('click', '.bzmi-btn-validate-section', this.handleValidateSection.bind(this));

			// Personas events
			this.elements.$wrap.on('click', '.bzmi-btn-add-persona, .bzmi-persona-card--add', this.handleAddPersona.bind(this));
			this.elements.$wrap.on('click', '.bzmi-btn-edit-persona', this.handleEditPersona.bind(this));
			this.elements.$wrap.on('click', '.bzmi-btn-delete-persona', this.handleDeletePersona.bind(this));

			// Offers events
			this.elements.$wrap.on('click', '.bzmi-btn-add-offer, .bzmi-offer-card--add', this.handleAddOffer.bind(this));
			this.elements.$wrap.on('click', '.bzmi-btn-edit-offer', this.handleEditOffer.bind(this));
			this.elements.$wrap.on('click', '.bzmi-btn-delete-offer', this.handleDeleteOffer.bind(this));

			// Competitors events
			this.elements.$wrap.on('click', '.bzmi-btn-add-competitor', this.handleAddCompetitor.bind(this));
			this.elements.$wrap.on('click', '.bzmi-btn-edit-competitor', this.handleEditCompetitor.bind(this));
			this.elements.$wrap.on('click', '.bzmi-btn-delete-competitor', this.handleDeleteCompetitor.bind(this));

			// Journeys events
			this.elements.$wrap.on('click', '.bzmi-btn-add-journey', this.handleAddJourney.bind(this));
			this.elements.$wrap.on('click', '.bzmi-btn-edit-journey', this.handleEditJourney.bind(this));
			this.elements.$wrap.on('click', '.bzmi-btn-delete-journey', this.handleDeleteJourney.bind(this));
			this.elements.$wrap.on('click', '.bzmi-template-list button', this.handleJourneyTemplate.bind(this));
			this.elements.$wrap.on('click', '.bzmi-btn-template-toggle', this.toggleTemplateDropdown.bind(this));

			// Channels events
			this.elements.$wrap.on('click', '.bzmi-btn-add-channel', this.handleAddChannel.bind(this));
			this.elements.$wrap.on('click', '.bzmi-btn-edit-channel', this.handleEditChannel.bind(this));
			this.elements.$wrap.on('click', '.bzmi-btn-delete-channel', this.handleDeleteChannel.bind(this));

			// Execution events
			this.elements.$wrap.on('click', '.bzmi-btn-edit-execution', this.handleEditExecution.bind(this));
			this.elements.$wrap.on('click', '.bzmi-btn-validate-execution', this.handleValidateExecution.bind(this));
			this.elements.$wrap.on('click', '.bzmi-btn-ai-execution', this.handleAiExecution.bind(this));

			// AI Tab events
			this.elements.$wrap.on('click', '.bzmi-btn-ai-audit', this.handleAiAudit.bind(this));
			this.elements.$wrap.on('click', '.bzmi-btn-ai-enrich', this.handleAiEnrich.bind(this));
			this.elements.$wrap.on('click', '.bzmi-btn-view-log', this.handleViewLog.bind(this));
			this.elements.$wrap.on('click', '.bzmi-btn-apply-log', this.handleApplyLog.bind(this));
			this.elements.$wrap.on('click', '.bzmi-btn-close-results', this.closeAiResults.bind(this));

			// Global events
			this.elements.$wrap.on('click', '.bzmi-btn-ai', this.handleGlobalAi.bind(this));
			this.elements.$wrap.on('click', '.bzmi-btn-save-all', this.handleSaveAll.bind(this));

			// New foundation page
			this.elements.$wrap.on('click', '.bzmi-client-option', this.handleClientSelect.bind(this));
			this.elements.$wrap.on('click', '.bzmi-mode-option', this.handleModeSelect.bind(this));
			this.elements.$wrap.on('click', '.bzmi-btn-create-foundation', this.handleCreateFoundation.bind(this));
		},

		/**
		 * Initialize current page
		 */
		initCurrentPage: function() {
			// Escape key to close modals
			$(document).on('keydown', (e) => {
				if (e.key === 'Escape') {
					this.closeModal();
					this.closeAiSidebar();
				}
			});
		},

		// =====================================================================
		// MODAL MANAGEMENT
		// =====================================================================

		/**
		 * Open modal with content
		 */
		openModal: function(title, content, saveCallback) {
			this.elements.$modal.find('.bzmi-modal__title').text(title);
			this.elements.$modal.find('.bzmi-modal__body').html(content);
			this.elements.$modal.data('saveCallback', saveCallback);
			this.elements.$modal.show();

			// Initialize color pickers
			setTimeout(() => {
				this.elements.$modal.find('.bzmi-color-picker').each(function() {
					if ($.fn.wpColorPicker) {
						$(this).wpColorPicker({
							change: function(event, ui) {
								$(this).val(ui.color.toString());
							}
						});
					}
				});

				// Initialize media selectors
				this.initMediaSelectors();

				// Load attachment images
				this.loadAttachmentImages();

				// Focus first input
				this.elements.$modal.find('input, textarea, select').first().focus();
			}, 100);
		},

		/**
		 * Initialize media selectors in modal
		 */
		initMediaSelectors: function() {
			const self = this;

			this.elements.$modal.find('.bzmi-select-media').off('click').on('click', function(e) {
				e.preventDefault();
				const $field = $(this).closest('.bzmi-media-field');
				const $input = $field.find('input[type="hidden"]');
				const $preview = $field.find('.bzmi-media-preview');
				const $removeBtn = $field.find('.bzmi-remove-media');

				const frame = wp.media({
					title: bzmiFoundations.strings?.select_image || 'Sélectionner une image',
					button: { text: bzmiFoundations.strings?.use_image || 'Utiliser cette image' },
					multiple: false
				});

				frame.on('select', function() {
					const attachment = frame.state().get('selection').first().toJSON();
					$input.val(attachment.id);
					$preview.html('<img src="' + (attachment.sizes?.thumbnail?.url || attachment.url) + '">');
					$removeBtn.show();
				});

				frame.open();
			});

			this.elements.$modal.find('.bzmi-remove-media').off('click').on('click', function(e) {
				e.preventDefault();
				const $field = $(this).closest('.bzmi-media-field');
				$field.find('input[type="hidden"]').val('');
				$field.find('.bzmi-media-preview').empty();
				$(this).hide();
			});
		},

		/**
		 * Load attachment images for previews
		 */
		loadAttachmentImages: function() {
			this.elements.$modal.find('.bzmi-media-preview img[data-attachment-id]').each(function() {
				const $img = $(this);
				const attachmentId = $img.data('attachment-id');
				if (attachmentId && wp.media?.attachment) {
					const attachment = wp.media.attachment(attachmentId);
					attachment.fetch().then(function() {
						const url = attachment.get('sizes')?.thumbnail?.url || attachment.get('url');
						if (url) {
							$img.attr('src', url);
						}
					});
				}
			});
		},

		/**
		 * Close modal
		 */
		closeModal: function() {
			this.elements.$modal.hide();
			this.elements.$modal.find('.bzmi-modal__body').empty();
			this.elements.$modal.removeData('saveCallback');
		},

		/**
		 * Handle modal save
		 */
		handleModalSave: function() {
			const callback = this.elements.$modal.data('saveCallback');
			if (typeof callback === 'function') {
				callback();
			}
		},

		// =====================================================================
		// AI SIDEBAR MANAGEMENT
		// =====================================================================

		/**
		 * Open AI sidebar
		 */
		openAiSidebar: function(content, applyCallback) {
			this.elements.$aiSidebar.find('.bzmi-ai-sidebar__content').html(content);
			this.elements.$aiSidebar.data('applyCallback', applyCallback);
			this.elements.$aiSidebar.show();
		},

		/**
		 * Close AI sidebar
		 */
		closeAiSidebar: function() {
			this.elements.$aiSidebar.hide();
			this.elements.$aiSidebar.find('.bzmi-ai-sidebar__content').empty();
			this.elements.$aiSidebar.removeData('applyCallback');
		},

		/**
		 * Handle AI apply
		 */
		handleAiApply: function() {
			const callback = this.elements.$aiSidebar.data('applyCallback');
			if (typeof callback === 'function') {
				callback();
			}
		},

		/**
		 * Show AI results
		 */
		showAiResults: function(content) {
			this.elements.$aiResults.find('.bzmi-ai-results-panel__content').html(content);
			this.elements.$aiResults.show();
		},

		/**
		 * Close AI results
		 */
		closeAiResults: function() {
			this.elements.$aiResults.hide();
		},

		// =====================================================================
		// IDENTITY TAB
		// =====================================================================

		/**
		 * Toggle identity section
		 */
		toggleIdentitySection: function(e) {
			if ($(e.target).closest('button').length) return;

			const $section = $(e.currentTarget).closest('.bzmi-identity-section');
			$section.toggleClass('bzmi-identity-section--expanded');
		},

		/**
		 * Handle edit section
		 */
		handleEditSection: function(e) {
			e.stopPropagation();
			const $btn = $(e.currentTarget);
			const section = $btn.data('section');

			this.loadSectionForm(section);
		},

		/**
		 * Load section form
		 */
		loadSectionForm: function(section) {
			const sectionLabels = {
				brand_dna: 'ADN de marque',
				vision: 'Vision',
				tone_voice: 'Ton & Voix',
				visuals: 'Identit\u00e9 visuelle',
				colors: 'Couleurs',
				typography: 'Typographies'
			};

			this.showLoading();

			this.apiRequest('GET', `/identity/${section}`)
				.then(data => {
					const formHtml = this.buildSectionForm(section, data);
					this.openModal(
						sectionLabels[section] || section,
						formHtml,
						() => this.saveSectionForm(section)
					);
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		/**
		 * Build section form HTML
		 */
		buildSectionForm: function(section, data) {
			const content = data.content || {};
			let html = '<form class="bzmi-section-form" data-section="' + section + '">';

			switch (section) {
				case 'brand_dna':
					html += this.buildTextField('mission', 'Mission', content.mission, 'textarea');
					html += this.buildTextField('values', 'Valeurs (une par ligne)', (content.values || []).join('\n'), 'textarea');
					html += this.buildTextField('promise', 'Promesse de marque', content.promise, 'textarea');
					html += this.buildTextField('positioning', 'Positionnement', content.positioning, 'textarea');
					break;

				case 'vision':
					html += this.buildTextField('short_term', 'Vision court terme (1 an)', content.short_term, 'textarea');
					html += this.buildTextField('mid_term', 'Vision moyen terme (3 ans)', content.mid_term, 'textarea');
					html += this.buildTextField('long_term', 'Vision long terme (5+ ans)', content.long_term, 'textarea');
					html += this.buildTextField('success_indicators', 'Indicateurs de succ\u00e8s', content.success_indicators, 'textarea');
					break;

				case 'tone_voice':
					html += this.buildTextField('personality', 'Personnalit\u00e9', content.personality, 'textarea');
					html += this.buildTextField('tone_adjectives', 'Adjectifs de ton (un par ligne)', (content.tone_adjectives || []).join('\n'), 'textarea');
					html += this.buildTextField('vocabulary_do', '\u00c0 utiliser', content.vocabulary_do, 'textarea');
					html += this.buildTextField('vocabulary_dont', '\u00c0 \u00e9viter', content.vocabulary_dont, 'textarea');
					html += this.buildTextField('example_phrases', 'Phrases exemples (une par ligne)', (content.example_phrases || []).join('\n'), 'textarea');
					break;

				case 'visuals':
					html += '<div class="bzmi-form-group">';
					html += '<label>Logo principal</label>';
					html += '<div class="bzmi-media-field" data-field="logo_primary_id">';
					html += '<input type="hidden" name="logo_primary_id" value="' + (content.logo_primary_id || '') + '">';
					html += '<div class="bzmi-media-preview">' + (content.logo_primary_id ? '<img src="" data-attachment-id="' + content.logo_primary_id + '">' : '') + '</div>';
					html += '<button type="button" class="button bzmi-select-media">' + (bzmiFoundations.strings?.select_image || 'Sélectionner') + '</button>';
					html += '<button type="button" class="button bzmi-remove-media" style="' + (content.logo_primary_id ? '' : 'display:none') + '">Supprimer</button>';
					html += '</div></div>';
					html += '<div class="bzmi-form-group">';
					html += '<label>Logo secondaire</label>';
					html += '<div class="bzmi-media-field" data-field="logo_secondary_id">';
					html += '<input type="hidden" name="logo_secondary_id" value="' + (content.logo_secondary_id || '') + '">';
					html += '<div class="bzmi-media-preview">' + (content.logo_secondary_id ? '<img src="" data-attachment-id="' + content.logo_secondary_id + '">' : '') + '</div>';
					html += '<button type="button" class="button bzmi-select-media">' + (bzmiFoundations.strings?.select_image || 'Sélectionner') + '</button>';
					html += '<button type="button" class="button bzmi-remove-media" style="' + (content.logo_secondary_id ? '' : 'display:none') + '">Supprimer</button>';
					html += '</div></div>';
					html += '<div class="bzmi-form-group">';
					html += '<label>Icône/Favicon</label>';
					html += '<div class="bzmi-media-field" data-field="logo_icon_id">';
					html += '<input type="hidden" name="logo_icon_id" value="' + (content.logo_icon_id || '') + '">';
					html += '<div class="bzmi-media-preview">' + (content.logo_icon_id ? '<img src="" data-attachment-id="' + content.logo_icon_id + '">' : '') + '</div>';
					html += '<button type="button" class="button bzmi-select-media">' + (bzmiFoundations.strings?.select_image || 'Sélectionner') + '</button>';
					html += '<button type="button" class="button bzmi-remove-media" style="' + (content.logo_icon_id ? '' : 'display:none') + '">Supprimer</button>';
					html += '</div></div>';
					html += this.buildTextField('logo_guidelines', 'Guidelines d\'utilisation du logo', content.logo_guidelines, 'textarea');
					html += this.buildTextField('imagery_style', 'Style d\'imagerie (photos, illustrations)', content.imagery_style, 'textarea');
					html += this.buildTextField('iconography_style', 'Style d\'iconographie', content.iconography_style, 'textarea');
					break;

				case 'colors':
					html += '<div class="bzmi-colors-section">';
					html += '<h4>Couleurs principales</h4>';
					html += '<div class="bzmi-color-row">';
					html += this.buildColorField('primary_color', 'Couleur primaire', content.primary_color);
					html += this.buildColorField('secondary_color', 'Couleur secondaire', content.secondary_color);
					html += this.buildColorField('accent_color', 'Couleur d\'accent', content.accent_color);
					html += '</div>';
					html += '<div class="bzmi-color-row">';
					html += this.buildColorField('background_color', 'Fond', content.background_color);
					html += this.buildColorField('text_color', 'Texte', content.text_color);
					html += '</div>';
					html += '<h4>Palette compl\u00e8te</h4>';
					html += '<p class="description">Format: nom|#hexadecimal|usage (une couleur par ligne)</p>';
					html += this.buildTextField('palette_raw', 'Palette', this.formatColors(content.palette || []), 'textarea');
					html += this.buildTextField('usage_guidelines', 'Guidelines d\'utilisation des couleurs', content.usage_guidelines, 'textarea');
					html += '</div>';
					break;

				case 'typography':
					html += this.buildTextField('heading_font', 'Police des titres', content.heading_font);
					html += this.buildTextField('body_font', 'Police du corps de texte', content.body_font);
					html += this.buildTextField('accent_font', 'Police d\'accent (optionnel)', content.accent_font);
					html += '<div class="bzmi-form-group">';
					html += '<label>Tailles de police</label>';
					html += '<p class="description">Format: nom|taille (ex: h1|48px)</p>';
					const fontSizesText = (content.font_sizes || []).map(s => `${s.name}|${s.size}`).join('\\n');
					html += '<textarea name="font_sizes" rows="4">' + fontSizesText + '</textarea>';
					html += '</div>';
					html += this.buildTextField('usage_guidelines', 'Guidelines typographiques', content.usage_guidelines, 'textarea');
					break;
			}

			html += '</form>';
			return html;
		},

		/**
		 * Format colors for textarea
		 */
		formatColors: function(colors) {
			return colors.map(c => `${c.name}|${c.hex}|${c.usage || ''}`).join('\n');
		},

		/**
		 * Parse colors from textarea
		 */
		parseColors: function(text) {
			return text.split('\n')
				.filter(line => line.trim())
				.map(line => {
					const parts = line.split('|');
					return {
						name: parts[0] || '',
						hex: parts[1] || '',
						usage: parts[2] || ''
					};
				});
		},

		/**
		 * Build text field HTML
		 */
		buildTextField: function(name, label, value, type = 'text') {
			let html = '<div class="bzmi-form-group">';
			html += '<label for="' + name + '">' + label + '</label>';

			if (type === 'textarea') {
				html += '<textarea id="' + name + '" name="' + name + '" rows="4">' + (value || '') + '</textarea>';
			} else {
				html += '<input type="' + type + '" id="' + name + '" name="' + name + '" value="' + (value || '') + '">';
			}

			html += '</div>';
			return html;
		},

		/**
		 * Build color field HTML
		 */
		buildColorField: function(name, label, value) {
			let html = '<div class="bzmi-form-group bzmi-form-group--color">';
			html += '<label for="' + name + '">' + label + '</label>';
			html += '<div class="bzmi-color-input-wrap">';
			html += '<input type="text" id="' + name + '" name="' + name + '" value="' + (value || '') + '" class="bzmi-color-picker" data-default-color="' + (value || '#ffffff') + '">';
			html += '</div>';
			html += '</div>';
			return html;
		},

		/**
		 * Save section form
		 */
		saveSectionForm: function(section) {
			const $form = this.elements.$modal.find('.bzmi-section-form');
			const formData = this.serializeForm($form);

			// Transform data based on section
			const content = this.transformSectionData(section, formData);

			this.showLoading();

			this.apiRequest('PUT', `/identity/${section}`, { content })
				.then(() => {
					this.closeModal();
					this.showNotice('Section mise \u00e0 jour avec succ\u00e8s', 'success');
					this.refreshPage();
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		/**
		 * Transform section data for API
		 */
		transformSectionData: function(section, formData) {
			const content = {};

			switch (section) {
				case 'brand_dna':
					content.mission = formData.mission;
					content.values = formData.values ? formData.values.split('\n').filter(v => v.trim()) : [];
					content.promise = formData.promise;
					content.positioning = formData.positioning;
					break;

				case 'vision':
					content.short_term = formData.short_term;
					content.mid_term = formData.mid_term;
					content.long_term = formData.long_term;
					content.success_indicators = formData.success_indicators;
					break;

				case 'tone_voice':
					content.personality = formData.personality;
					content.tone_adjectives = formData.tone_adjectives ? formData.tone_adjectives.split('\n').filter(v => v.trim()) : [];
					content.vocabulary_do = formData.vocabulary_do;
					content.vocabulary_dont = formData.vocabulary_dont;
					content.example_phrases = formData.example_phrases ? formData.example_phrases.split('\n').filter(v => v.trim()) : [];
					break;

				case 'visuals':
					content.logo_primary_id = parseInt(formData.logo_primary_id) || 0;
					content.logo_secondary_id = parseInt(formData.logo_secondary_id) || 0;
					content.logo_icon_id = parseInt(formData.logo_icon_id) || 0;
					content.logo_guidelines = formData.logo_guidelines;
					content.imagery_style = formData.imagery_style;
					content.iconography_style = formData.iconography_style;
					break;

				case 'colors':
					content.primary_color = formData.primary_color;
					content.secondary_color = formData.secondary_color;
					content.accent_color = formData.accent_color;
					content.background_color = formData.background_color;
					content.text_color = formData.text_color;
					content.palette = this.parseColors(formData.palette_raw || '');
					content.usage_guidelines = formData.usage_guidelines;
					break;

				case 'typography':
					content.heading_font = formData.heading_font;
					content.body_font = formData.body_font;
					content.accent_font = formData.accent_font;
					content.font_sizes = formData.font_sizes ? formData.font_sizes.split('\n')
						.filter(v => v.trim())
						.map(line => {
							const parts = line.split('|');
							return { name: parts[0].trim(), size: parts[1] ? parts[1].trim() : '' };
						}) : [];
					content.usage_guidelines = formData.usage_guidelines;
					break;

				default:
					Object.assign(content, formData);
			}

			return content;
		},

		/**
		 * Handle AI section
		 */
		handleAiSection: function(e) {
			e.stopPropagation();
			const $btn = $(e.currentTarget);
			const section = $btn.data('section');

			this.enrichSection('identity', section);
		},

		/**
		 * Handle validate section
		 */
		handleValidateSection: function(e) {
			e.stopPropagation();
			const $btn = $(e.currentTarget);
			const section = $btn.data('section');

			this.showLoading();

			this.apiRequest('POST', `/identity/${section}/validate`)
				.then(() => {
					this.showNotice('Section valid\u00e9e', 'success');
					this.refreshPage();
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		// =====================================================================
		// PERSONAS
		// =====================================================================

		/**
		 * Handle add persona
		 */
		handleAddPersona: function() {
			const formHtml = this.buildPersonaForm();
			this.openModal('Nouveau persona', formHtml, () => this.savePersona());
		},

		/**
		 * Handle edit persona
		 */
		handleEditPersona: function(e) {
			const personaId = $(e.currentTarget).data('persona-id');

			this.showLoading();

			this.apiRequest('GET', `/personas/${personaId}`)
				.then(persona => {
					const formHtml = this.buildPersonaForm(persona);
					this.openModal('Modifier le persona', formHtml, () => this.savePersona(personaId));
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		/**
		 * Build persona form
		 */
		buildPersonaForm: function(persona = {}) {
			let html = '<form class="bzmi-persona-form">';

			html += '<div class="bzmi-form-row">';
			html += this.buildTextField('name', 'Nom', persona.name);
			html += this.buildTextField('segment', 'Segment', persona.segment);
			html += '</div>';

			html += '<div class="bzmi-form-row">';
			html += this.buildTextField('age_range', 'Tranche d\'\u00e2ge', persona.age_range);
			html += this.buildTextField('location', 'Localisation', persona.location);
			html += '</div>';

			html += this.buildTextField('bio', 'Biographie', persona.bio, 'textarea');
			html += this.buildTextField('goals', 'Objectifs (un par ligne)', (persona.goals || []).join('\n'), 'textarea');
			html += this.buildTextField('pain_points', 'Points de douleur (un par ligne)', (persona.pain_points || []).join('\n'), 'textarea');
			html += this.buildTextField('behaviors', 'Comportements (un par ligne)', (persona.behaviors || []).join('\n'), 'textarea');
			html += this.buildTextField('channels', 'Canaux pr\u00e9f\u00e9r\u00e9s (un par ligne)', (persona.preferred_channels || []).join('\n'), 'textarea');

			html += '</form>';
			return html;
		},

		/**
		 * Save persona
		 */
		savePersona: function(personaId = null) {
			const $form = this.elements.$modal.find('.bzmi-persona-form');
			const formData = this.serializeForm($form);

			const data = {
				name: formData.name,
				segment: formData.segment,
				age_range: formData.age_range,
				location: formData.location,
				bio: formData.bio,
				goals: formData.goals ? formData.goals.split('\n').filter(v => v.trim()) : [],
				pain_points: formData.pain_points ? formData.pain_points.split('\n').filter(v => v.trim()) : [],
				behaviors: formData.behaviors ? formData.behaviors.split('\n').filter(v => v.trim()) : [],
				preferred_channels: formData.channels ? formData.channels.split('\n').filter(v => v.trim()) : []
			};

			this.showLoading();

			const method = personaId ? 'PUT' : 'POST';
			const endpoint = personaId ? `/personas/${personaId}` : '/personas';

			this.apiRequest(method, endpoint, data)
				.then(() => {
					this.closeModal();
					this.showNotice('Persona ' + (personaId ? 'mis \u00e0 jour' : 'cr\u00e9\u00e9'), 'success');
					this.refreshPage();
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		/**
		 * Handle delete persona
		 */
		handleDeletePersona: function(e) {
			const personaId = $(e.currentTarget).data('persona-id');

			if (!confirm('Supprimer ce persona ?')) return;

			this.showLoading();

			this.apiRequest('DELETE', `/personas/${personaId}`)
				.then(() => {
					this.showNotice('Persona supprim\u00e9', 'success');
					this.refreshPage();
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		// =====================================================================
		// OFFERS
		// =====================================================================

		/**
		 * Handle add offer
		 */
		handleAddOffer: function() {
			const formHtml = this.buildOfferForm();
			this.openModal('Nouvelle offre', formHtml, () => this.saveOffer());
		},

		/**
		 * Handle edit offer
		 */
		handleEditOffer: function(e) {
			const offerId = $(e.currentTarget).data('offer-id');

			this.showLoading();

			this.apiRequest('GET', `/offers/${offerId}`)
				.then(offer => {
					const formHtml = this.buildOfferForm(offer);
					this.openModal('Modifier l\'offre', formHtml, () => this.saveOffer(offerId));
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		/**
		 * Build offer form
		 */
		buildOfferForm: function(offer = {}) {
			let html = '<form class="bzmi-offer-form">';

			html += this.buildTextField('name', 'Nom', offer.name);

			html += '<div class="bzmi-form-group">';
			html += '<label for="type">Type</label>';
			html += '<select id="type" name="type">';
			['product', 'service', 'subscription', 'bundle'].forEach(type => {
				const selected = offer.type === type ? ' selected' : '';
				html += `<option value="${type}"${selected}>${type}</option>`;
			});
			html += '</select>';
			html += '</div>';

			html += this.buildTextField('description', 'Description', offer.description, 'textarea');

			html += '<div class="bzmi-form-row">';
			html += this.buildTextField('price', 'Prix', offer.price, 'number');
			html += '<div class="bzmi-form-group">';
			html += '<label for="pricing_model">Mod\u00e8le de prix</label>';
			html += '<select id="pricing_model" name="pricing_model">';
			['one_time', 'subscription', 'usage', 'freemium', 'custom'].forEach(model => {
				const selected = offer.pricing_model === model ? ' selected' : '';
				html += `<option value="${model}"${selected}>${model}</option>`;
			});
			html += '</select>';
			html += '</div>';
			html += '</div>';

			html += this.buildTextField('value_proposition', 'Proposition de valeur', offer.value_proposition, 'textarea');
			html += this.buildTextField('features', 'Fonctionnalit\u00e9s (une par ligne)', (offer.features || []).join('\n'), 'textarea');
			html += this.buildTextField('differentiators', 'Diff\u00e9renciateurs (un par ligne)', (offer.differentiators || []).join('\n'), 'textarea');

			html += '</form>';
			return html;
		},

		/**
		 * Save offer
		 */
		saveOffer: function(offerId = null) {
			const $form = this.elements.$modal.find('.bzmi-offer-form');
			const formData = this.serializeForm($form);

			const data = {
				name: formData.name,
				type: formData.type,
				description: formData.description,
				price: parseFloat(formData.price) || 0,
				pricing_model: formData.pricing_model,
				value_proposition: formData.value_proposition,
				features: formData.features ? formData.features.split('\n').filter(v => v.trim()) : [],
				differentiators: formData.differentiators ? formData.differentiators.split('\n').filter(v => v.trim()) : []
			};

			this.showLoading();

			const method = offerId ? 'PUT' : 'POST';
			const endpoint = offerId ? `/offers/${offerId}` : '/offers';

			this.apiRequest(method, endpoint, data)
				.then(() => {
					this.closeModal();
					this.showNotice('Offre ' + (offerId ? 'mise \u00e0 jour' : 'cr\u00e9\u00e9e'), 'success');
					this.refreshPage();
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		/**
		 * Handle delete offer
		 */
		handleDeleteOffer: function(e) {
			const offerId = $(e.currentTarget).data('offer-id');

			if (!confirm('Supprimer cette offre ?')) return;

			this.showLoading();

			this.apiRequest('DELETE', `/offers/${offerId}`)
				.then(() => {
					this.showNotice('Offre supprim\u00e9e', 'success');
					this.refreshPage();
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		// =====================================================================
		// COMPETITORS
		// =====================================================================

		/**
		 * Handle add competitor
		 */
		handleAddCompetitor: function() {
			const formHtml = this.buildCompetitorForm();
			this.openModal('Nouveau concurrent', formHtml, () => this.saveCompetitor());
		},

		/**
		 * Handle edit competitor
		 */
		handleEditCompetitor: function(e) {
			const competitorId = $(e.currentTarget).data('competitor-id');

			this.showLoading();

			this.apiRequest('GET', `/competitors/${competitorId}`)
				.then(competitor => {
					const formHtml = this.buildCompetitorForm(competitor);
					this.openModal('Modifier le concurrent', formHtml, () => this.saveCompetitor(competitorId));
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		/**
		 * Build competitor form
		 */
		buildCompetitorForm: function(competitor = {}) {
			let html = '<form class="bzmi-competitor-form">';

			html += this.buildTextField('name', 'Nom', competitor.name);
			html += this.buildTextField('website', 'Site web', competitor.website, 'url');
			html += this.buildTextField('description', 'Description', competitor.description, 'textarea');

			html += '<div class="bzmi-form-row">';
			html += '<div class="bzmi-form-group">';
			html += '<label for="threat_level">Niveau de menace (1-10)</label>';
			html += '<input type="number" id="threat_level" name="threat_level" min="1" max="10" value="' + (competitor.threat_level || 5) + '">';
			html += '</div>';
			html += '<div class="bzmi-form-group">';
			html += '<label for="market_share">Part de march\u00e9 (%)</label>';
			html += '<input type="number" id="market_share" name="market_share" min="0" max="100" value="' + (competitor.market_share || 0) + '">';
			html += '</div>';
			html += '</div>';

			html += this.buildTextField('strengths', 'Forces (une par ligne)', (competitor.strengths || []).join('\n'), 'textarea');
			html += this.buildTextField('weaknesses', 'Faiblesses (une par ligne)', (competitor.weaknesses || []).join('\n'), 'textarea');
			html += this.buildTextField('positioning', 'Positionnement', competitor.positioning, 'textarea');

			html += '</form>';
			return html;
		},

		/**
		 * Save competitor
		 */
		saveCompetitor: function(competitorId = null) {
			const $form = this.elements.$modal.find('.bzmi-competitor-form');
			const formData = this.serializeForm($form);

			const data = {
				name: formData.name,
				website: formData.website,
				description: formData.description,
				threat_level: parseInt(formData.threat_level) || 5,
				market_share: parseFloat(formData.market_share) || 0,
				strengths: formData.strengths ? formData.strengths.split('\n').filter(v => v.trim()) : [],
				weaknesses: formData.weaknesses ? formData.weaknesses.split('\n').filter(v => v.trim()) : [],
				positioning: formData.positioning
			};

			this.showLoading();

			const method = competitorId ? 'PUT' : 'POST';
			const endpoint = competitorId ? `/competitors/${competitorId}` : '/competitors';

			this.apiRequest(method, endpoint, data)
				.then(() => {
					this.closeModal();
					this.showNotice('Concurrent ' + (competitorId ? 'mis \u00e0 jour' : 'cr\u00e9\u00e9'), 'success');
					this.refreshPage();
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		/**
		 * Handle delete competitor
		 */
		handleDeleteCompetitor: function(e) {
			const competitorId = $(e.currentTarget).data('competitor-id');

			if (!confirm('Supprimer ce concurrent ?')) return;

			this.showLoading();

			this.apiRequest('DELETE', `/competitors/${competitorId}`)
				.then(() => {
					this.showNotice('Concurrent supprim\u00e9', 'success');
					this.refreshPage();
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		// =====================================================================
		// JOURNEYS
		// =====================================================================

		/**
		 * Handle add journey
		 */
		handleAddJourney: function() {
			const formHtml = this.buildJourneyForm();
			this.openModal('Nouveau parcours', formHtml, () => this.saveJourney());
		},

		/**
		 * Handle edit journey
		 */
		handleEditJourney: function(e) {
			const journeyId = $(e.currentTarget).data('journey-id');

			this.showLoading();

			this.apiRequest('GET', `/journeys/${journeyId}`)
				.then(journey => {
					const formHtml = this.buildJourneyForm(journey);
					this.openModal('Modifier le parcours', formHtml, () => this.saveJourney(journeyId));
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		/**
		 * Build journey form
		 */
		buildJourneyForm: function(journey = {}) {
			let html = '<form class="bzmi-journey-form">';

			html += this.buildTextField('name', 'Nom du parcours', journey.name);
			html += this.buildTextField('objective', 'Objectif', journey.objective, 'textarea');

			// Stages
			html += '<div class="bzmi-form-group">';
			html += '<label>\u00c9tapes (format: id|nom - une par ligne)</label>';
			const stagesText = (journey.stages || []).map(s => `${s.id}|${s.name}`).join('\n');
			html += '<textarea name="stages" rows="5">' + stagesText + '</textarea>';
			html += '<p class="bzmi-form-help">Exemple: awareness|D\u00e9couverte</p>';
			html += '</div>';

			html += '</form>';
			return html;
		},

		/**
		 * Save journey
		 */
		saveJourney: function(journeyId = null) {
			const $form = this.elements.$modal.find('.bzmi-journey-form');
			const formData = this.serializeForm($form);

			const stages = formData.stages ? formData.stages.split('\n')
				.filter(v => v.trim())
				.map(line => {
					const parts = line.split('|');
					return { id: parts[0].trim(), name: parts[1] ? parts[1].trim() : parts[0].trim() };
				}) : [];

			const data = {
				name: formData.name,
				objective: formData.objective,
				stages: stages
			};

			this.showLoading();

			const method = journeyId ? 'PUT' : 'POST';
			const endpoint = journeyId ? `/journeys/${journeyId}` : '/journeys';

			this.apiRequest(method, endpoint, data)
				.then(() => {
					this.closeModal();
					this.showNotice('Parcours ' + (journeyId ? 'mis \u00e0 jour' : 'cr\u00e9\u00e9'), 'success');
					this.refreshPage();
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		/**
		 * Handle delete journey
		 */
		handleDeleteJourney: function(e) {
			const journeyId = $(e.currentTarget).data('journey-id');

			if (!confirm('Supprimer ce parcours ?')) return;

			this.showLoading();

			this.apiRequest('DELETE', `/journeys/${journeyId}`)
				.then(() => {
					this.showNotice('Parcours supprim\u00e9', 'success');
					this.refreshPage();
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		/**
		 * Toggle template dropdown
		 */
		toggleTemplateDropdown: function(e) {
			$(e.currentTarget).siblings('.bzmi-template-list').toggle();
		},

		/**
		 * Handle journey template
		 */
		handleJourneyTemplate: function(e) {
			const template = $(e.currentTarget).data('template');

			this.showLoading();

			this.apiRequest('POST', '/journeys/template', { template })
				.then(() => {
					this.showNotice('Template appliqu\u00e9', 'success');
					this.refreshPage();
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		// =====================================================================
		// CHANNELS
		// =====================================================================

		/**
		 * Handle add channel
		 */
		handleAddChannel: function() {
			const formHtml = this.buildChannelForm();
			this.openModal('Nouveau canal', formHtml, () => this.saveChannel());
		},

		/**
		 * Handle edit channel
		 */
		handleEditChannel: function(e) {
			const channelId = $(e.currentTarget).data('channel-id');

			this.showLoading();

			this.apiRequest('GET', `/channels/${channelId}`)
				.then(channel => {
					const formHtml = this.buildChannelForm(channel);
					this.openModal('Modifier le canal', formHtml, () => this.saveChannel(channelId));
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		/**
		 * Build channel form
		 */
		buildChannelForm: function(channel = {}) {
			let html = '<form class="bzmi-channel-form">';

			html += this.buildTextField('name', 'Nom', channel.name);

			html += '<div class="bzmi-form-group">';
			html += '<label for="type">Type</label>';
			html += '<select id="type" name="type">';
			['website', 'social', 'email', 'advertising', 'offline', 'partner'].forEach(type => {
				const selected = channel.type === type ? ' selected' : '';
				html += `<option value="${type}"${selected}>${type}</option>`;
			});
			html += '</select>';
			html += '</div>';

			html += this.buildTextField('platform', 'Plateforme', channel.platform);
			html += this.buildTextField('url', 'URL', channel.url, 'url');
			html += this.buildTextField('objective', 'Objectif', channel.objective, 'textarea');
			html += this.buildTextField('key_messages', 'Messages cl\u00e9s (un par ligne)', (channel.key_messages || []).join('\n'), 'textarea');
			html += this.buildTextField('cta_primary', 'CTA principal', channel.cta_primary);
			html += this.buildTextField('kpis', 'KPIs (un par ligne)', (channel.kpis || []).join('\n'), 'textarea');

			html += '</form>';
			return html;
		},

		/**
		 * Save channel
		 */
		saveChannel: function(channelId = null) {
			const $form = this.elements.$modal.find('.bzmi-channel-form');
			const formData = this.serializeForm($form);

			const data = {
				name: formData.name,
				type: formData.type,
				platform: formData.platform,
				url: formData.url,
				objective: formData.objective,
				key_messages: formData.key_messages ? formData.key_messages.split('\n').filter(v => v.trim()) : [],
				cta_primary: formData.cta_primary,
				kpis: formData.kpis ? formData.kpis.split('\n').filter(v => v.trim()) : []
			};

			this.showLoading();

			const method = channelId ? 'PUT' : 'POST';
			const endpoint = channelId ? `/channels/${channelId}` : '/channels';

			this.apiRequest(method, endpoint, data)
				.then(() => {
					this.closeModal();
					this.showNotice('Canal ' + (channelId ? 'mis \u00e0 jour' : 'cr\u00e9\u00e9'), 'success');
					this.refreshPage();
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		/**
		 * Handle delete channel
		 */
		handleDeleteChannel: function(e) {
			const channelId = $(e.currentTarget).data('channel-id');

			if (!confirm('Supprimer ce canal ?')) return;

			this.showLoading();

			this.apiRequest('DELETE', `/channels/${channelId}`)
				.then(() => {
					this.showNotice('Canal supprim\u00e9', 'success');
					this.refreshPage();
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		// =====================================================================
		// EXECUTION
		// =====================================================================

		/**
		 * Handle edit execution
		 */
		handleEditExecution: function(e) {
			const section = $(e.currentTarget).data('section');
			this.loadExecutionForm(section);
		},

		/**
		 * Load execution form
		 */
		loadExecutionForm: function(section) {
			const sectionLabels = {
				scope: 'P\u00e9rim\u00e8tre',
				deliverables: 'Livrables',
				planning: 'Planning',
				budget: 'Budget',
				constraints: 'Contraintes',
				legal: 'L\u00e9gal & RGPD'
			};

			this.showLoading();

			this.apiRequest('GET', `/execution/${section}`)
				.then(data => {
					const formHtml = this.buildExecutionForm(section, data);
					this.openModal(
						sectionLabels[section] || section,
						formHtml,
						() => this.saveExecutionForm(section)
					);
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		/**
		 * Build execution form
		 */
		buildExecutionForm: function(section, data) {
			const content = data.content || {};
			let html = '<form class="bzmi-execution-form" data-section="' + section + '">';

			switch (section) {
				case 'scope':
					html += this.buildTextField('description', 'Description du p\u00e9rim\u00e8tre', content.description, 'textarea');
					html += this.buildTextField('inclusions', 'Inclusions (une par ligne)', (content.inclusions || []).join('\n'), 'textarea');
					html += this.buildTextField('exclusions', 'Exclusions (une par ligne)', (content.exclusions || []).join('\n'), 'textarea');
					break;

				case 'deliverables':
					html += '<div class="bzmi-form-group">';
					html += '<label>Livrables (format: nom|description - un par ligne)</label>';
					const deliverablesText = (content.items || []).map(d => `${d.name}|${d.description || ''}`).join('\n');
					html += '<textarea name="items" rows="6">' + deliverablesText + '</textarea>';
					html += '</div>';
					break;

				case 'planning':
					html += '<div class="bzmi-form-row">';
					html += this.buildTextField('start_date', 'Date de d\u00e9but', content.start_date, 'date');
					html += this.buildTextField('end_date', 'Date de fin', content.end_date, 'date');
					html += '</div>';
					html += '<div class="bzmi-form-group">';
					html += '<label>Jalons (format: date|nom - un par ligne)</label>';
					const milestonesText = (content.milestones || []).map(m => `${m.date}|${m.name}`).join('\n');
					html += '<textarea name="milestones" rows="5">' + milestonesText + '</textarea>';
					html += '</div>';
					break;

				case 'budget':
					html += '<div class="bzmi-form-row">';
					html += this.buildTextField('total', 'Budget total', content.total, 'number');
					html += '<div class="bzmi-form-group">';
					html += '<label for="currency">Devise</label>';
					html += '<select id="currency" name="currency">';
					['EUR', 'USD', 'GBP', 'CHF'].forEach(curr => {
						const selected = content.currency === curr ? ' selected' : '';
						html += `<option value="${curr}"${selected}>${curr}</option>`;
					});
					html += '</select>';
					html += '</div>';
					html += '</div>';
					html += '<div class="bzmi-form-group">';
					html += '<label>Postes budg\u00e9taires (format: poste|montant - un par ligne)</label>';
					const breakdownText = (content.breakdown || []).map(b => `${b.name}|${b.amount}`).join('\n');
					html += '<textarea name="breakdown" rows="5">' + breakdownText + '</textarea>';
					html += '</div>';
					break;

				case 'constraints':
					html += this.buildTextField('technical', 'Contraintes techniques (une par ligne)', (content.technical || []).join('\n'), 'textarea');
					html += this.buildTextField('organizational', 'Contraintes organisationnelles (une par ligne)', (content.organizational || []).join('\n'), 'textarea');
					html += this.buildTextField('time', 'Contraintes de temps (une par ligne)', (content.time || []).join('\n'), 'textarea');
					html += this.buildTextField('resource', 'Contraintes de ressources (une par ligne)', (content.resource || []).join('\n'), 'textarea');
					break;

				case 'legal':
					html += '<div class="bzmi-form-group">';
					html += '<label><input type="checkbox" name="gdpr_compliance" value="1"' + (content.gdpr_compliance ? ' checked' : '') + '> Conformit\u00e9 RGPD</label>';
					html += '</div>';
					html += this.buildTextField('data_handling', 'Gestion des donn\u00e9es', content.data_handling, 'textarea');
					html += '<div class="bzmi-form-group">';
					html += '<label>Outils tiers (format: nom|usage - un par ligne)</label>';
					const toolsText = (content.third_party_tools || []).map(t => `${t.name}|${t.usage || ''}`).join('\n');
					html += '<textarea name="third_party_tools" rows="5">' + toolsText + '</textarea>';
					html += '</div>';
					break;
			}

			html += '</form>';
			return html;
		},

		/**
		 * Save execution form
		 */
		saveExecutionForm: function(section) {
			const $form = this.elements.$modal.find('.bzmi-execution-form');
			const formData = this.serializeForm($form);

			const content = this.transformExecutionData(section, formData);

			this.showLoading();

			this.apiRequest('PUT', `/execution/${section}`, { content })
				.then(() => {
					this.closeModal();
					this.showNotice('Section mise \u00e0 jour', 'success');
					this.refreshPage();
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		/**
		 * Transform execution data
		 */
		transformExecutionData: function(section, formData) {
			const content = {};

			switch (section) {
				case 'scope':
					content.description = formData.description;
					content.inclusions = formData.inclusions ? formData.inclusions.split('\n').filter(v => v.trim()) : [];
					content.exclusions = formData.exclusions ? formData.exclusions.split('\n').filter(v => v.trim()) : [];
					break;

				case 'deliverables':
					content.items = formData.items ? formData.items.split('\n')
						.filter(v => v.trim())
						.map(line => {
							const parts = line.split('|');
							return { name: parts[0].trim(), description: parts[1] ? parts[1].trim() : '' };
						}) : [];
					break;

				case 'planning':
					content.start_date = formData.start_date;
					content.end_date = formData.end_date;
					content.milestones = formData.milestones ? formData.milestones.split('\n')
						.filter(v => v.trim())
						.map(line => {
							const parts = line.split('|');
							return { date: parts[0].trim(), name: parts[1] ? parts[1].trim() : '' };
						}) : [];
					break;

				case 'budget':
					content.total = parseFloat(formData.total) || 0;
					content.currency = formData.currency;
					content.breakdown = formData.breakdown ? formData.breakdown.split('\n')
						.filter(v => v.trim())
						.map(line => {
							const parts = line.split('|');
							return { name: parts[0].trim(), amount: parseFloat(parts[1]) || 0 };
						}) : [];
					break;

				case 'constraints':
					content.technical = formData.technical ? formData.technical.split('\n').filter(v => v.trim()) : [];
					content.organizational = formData.organizational ? formData.organizational.split('\n').filter(v => v.trim()) : [];
					content.time = formData.time ? formData.time.split('\n').filter(v => v.trim()) : [];
					content.resource = formData.resource ? formData.resource.split('\n').filter(v => v.trim()) : [];
					break;

				case 'legal':
					content.gdpr_compliance = !!formData.gdpr_compliance;
					content.data_handling = formData.data_handling;
					content.third_party_tools = formData.third_party_tools ? formData.third_party_tools.split('\n')
						.filter(v => v.trim())
						.map(line => {
							const parts = line.split('|');
							return { name: parts[0].trim(), usage: parts[1] ? parts[1].trim() : '' };
						}) : [];
					break;
			}

			return content;
		},

		/**
		 * Handle validate execution
		 */
		handleValidateExecution: function(e) {
			const section = $(e.currentTarget).data('section');

			this.showLoading();

			this.apiRequest('POST', `/execution/${section}/validate`)
				.then(() => {
					this.showNotice('Section valid\u00e9e', 'success');
					this.refreshPage();
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		/**
		 * Handle AI execution
		 */
		handleAiExecution: function(e) {
			const target = $(e.currentTarget).data('target');
			this.enrichSection('execution', target);
		},

		// =====================================================================
		// AI TAB
		// =====================================================================

		/**
		 * Handle AI audit
		 */
		handleAiAudit: function() {
			this.showLoading();

			this.apiRequest('POST', '/ai/audit')
				.then(result => {
					this.showAiResults(this.formatAuditResults(result));
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		/**
		 * Format audit results
		 */
		formatAuditResults: function(result) {
			let html = '<div class="bzmi-audit-results">';

			if (result.score) {
				html += '<div class="bzmi-audit-score">';
				html += '<span class="bzmi-audit-score__value">' + result.score + '</span>';
				html += '<span class="bzmi-audit-score__label">Score global</span>';
				html += '</div>';
			}

			if (result.summary) {
				html += '<div class="bzmi-audit-summary">';
				html += '<h4>R\u00e9sum\u00e9</h4>';
				html += '<p>' + result.summary + '</p>';
				html += '</div>';
			}

			if (result.recommendations && result.recommendations.length) {
				html += '<div class="bzmi-audit-recommendations">';
				html += '<h4>Recommandations</h4>';
				html += '<ul>';
				result.recommendations.forEach(rec => {
					html += '<li>' + rec + '</li>';
				});
				html += '</ul>';
				html += '</div>';
			}

			html += '</div>';
			return html;
		},

		/**
		 * Handle AI enrich
		 */
		handleAiEnrich: function(e) {
			const $btn = $(e.currentTarget);
			const socle = $btn.data('socle');
			const target = $btn.data('target');

			this.enrichSection(socle, target);
		},

		/**
		 * Enrich section with AI
		 */
		enrichSection: function(socle, target = null) {
			this.showLoading();

			const data = { socle };
			if (target) data.target = target;

			this.apiRequest('POST', '/ai/enrich', data)
				.then(result => {
					this.openAiSidebar(
						this.formatAiSuggestion(result),
						() => this.applyAiSuggestion(result)
					);
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		/**
		 * Format AI suggestion
		 */
		formatAiSuggestion: function(result) {
			let html = '<div class="bzmi-ai-suggestion">';

			if (result.confidence) {
				html += '<div class="bzmi-ai-confidence">';
				html += '<span>Confiance: ' + Math.round(result.confidence * 100) + '%</span>';
				html += '</div>';
			}

			if (result.suggestions) {
				html += '<div class="bzmi-ai-content">';

				if (typeof result.suggestions === 'string') {
					html += '<p>' + result.suggestions + '</p>';
				} else if (Array.isArray(result.suggestions)) {
					html += '<ul>';
					result.suggestions.forEach(s => {
						html += '<li>' + s + '</li>';
					});
					html += '</ul>';
				} else {
					// Object - iterate keys
					for (const [key, value] of Object.entries(result.suggestions)) {
						html += '<div class="bzmi-ai-field">';
						html += '<strong>' + key + '</strong>';
						if (Array.isArray(value)) {
							html += '<ul>';
							value.forEach(v => html += '<li>' + v + '</li>');
							html += '</ul>';
						} else {
							html += '<p>' + value + '</p>';
						}
						html += '</div>';
					}
				}

				html += '</div>';
			}

			html += '</div>';
			return html;
		},

		/**
		 * Apply AI suggestion
		 */
		applyAiSuggestion: function(result) {
			if (!result.log_id) {
				this.showNotice('Impossible d\'appliquer cette suggestion', 'error');
				return;
			}

			this.showLoading();

			this.apiRequest('POST', `/ai/logs/${result.log_id}/apply`)
				.then(() => {
					this.closeAiSidebar();
					this.showNotice('Suggestion appliqu\u00e9e', 'success');
					this.refreshPage();
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		/**
		 * Handle view log
		 */
		handleViewLog: function(e) {
			const logId = $(e.currentTarget).data('log-id');

			this.showLoading();

			this.apiRequest('GET', `/ai/logs/${logId}`)
				.then(log => {
					let content = '<div class="bzmi-log-detail">';
					content += '<p><strong>Date:</strong> ' + log.created_at + '</p>';
					content += '<p><strong>Socle:</strong> ' + log.socle + '</p>';
					content += '<p><strong>Action:</strong> ' + log.action + '</p>';

					if (log.output) {
						content += '<div class="bzmi-log-output">';
						content += '<h4>R\u00e9sultat</h4>';
						content += '<pre>' + JSON.stringify(log.output, null, 2) + '</pre>';
						content += '</div>';
					}

					content += '</div>';

					this.openModal('D\u00e9tail du log', content, null);
					this.elements.$modal.find('.bzmi-modal__save').hide();
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		/**
		 * Handle apply log
		 */
		handleApplyLog: function(e) {
			const logId = $(e.currentTarget).data('log-id');

			if (!confirm('Appliquer cette suggestion ?')) return;

			this.showLoading();

			this.apiRequest('POST', `/ai/logs/${logId}/apply`)
				.then(() => {
					this.showNotice('Suggestion appliqu\u00e9e', 'success');
					this.refreshPage();
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		// =====================================================================
		// GLOBAL ACTIONS
		// =====================================================================

		/**
		 * Handle global AI action
		 */
		handleGlobalAi: function(e) {
			const action = $(e.currentTarget).data('action');

			if (action === 'audit') {
				this.handleAiAudit();
			}
		},

		/**
		 * Handle save all
		 */
		handleSaveAll: function() {
			this.showNotice('Toutes les modifications sont sauvegard\u00e9es automatiquement', 'info');
		},

		// =====================================================================
		// NEW FOUNDATION PAGE
		// =====================================================================

		/**
		 * Handle client select
		 */
		handleClientSelect: function(e) {
			const $option = $(e.currentTarget);

			if ($option.hasClass('bzmi-client-option--disabled')) return;

			$('.bzmi-client-option').removeClass('bzmi-client-option--selected');
			$option.addClass('bzmi-client-option--selected');
		},

		/**
		 * Handle mode select
		 */
		handleModeSelect: function(e) {
			const $option = $(e.currentTarget);

			$('.bzmi-mode-option').removeClass('bzmi-mode-option--selected');
			$option.addClass('bzmi-mode-option--selected');
		},

		/**
		 * Handle create foundation
		 */
		handleCreateFoundation: function() {
			const $selectedClient = $('.bzmi-client-option--selected');
			const $selectedMode = $('.bzmi-mode-option--selected');

			if (!$selectedClient.length) {
				this.showNotice('Veuillez s\u00e9lectionner un client', 'error');
				return;
			}

			const clientId = $selectedClient.data('client-id');
			const mode = $selectedMode.data('mode') || 'existing';

			this.showLoading();

			this.apiRequest('POST', '', { client_id: clientId, company_mode: mode })
				.then(foundation => {
					window.location.href = bzmiFoundations.adminUrl +
						'admin.php?page=bzmi-foundations&action=edit&id=' + foundation.id;
				})
				.catch(this.handleError.bind(this))
				.finally(() => this.hideLoading());
		},

		// =====================================================================
		// UTILITIES
		// =====================================================================

		/**
		 * API request helper
		 */
		apiRequest: function(method, endpoint, data = null) {
			const url = this.config.apiBase + (this.config.foundationId ? '/' + this.config.foundationId : '') + endpoint;

			const options = {
				method: method,
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': this.config.nonce
				}
			};

			if (data && (method === 'POST' || method === 'PUT')) {
				options.body = JSON.stringify(data);
			}

			return fetch(url, options)
				.then(response => {
					if (!response.ok) {
						return response.json().then(err => {
							throw new Error(err.message || 'Erreur API');
						});
					}
					return response.json();
				});
		},

		/**
		 * Serialize form data
		 */
		serializeForm: function($form) {
			const data = {};
			$form.find('input, textarea, select').each(function() {
				const $input = $(this);
				const name = $input.attr('name');
				if (!name) return;

				if ($input.is(':checkbox')) {
					data[name] = $input.is(':checked') ? $input.val() : '';
				} else {
					data[name] = $input.val();
				}
			});
			return data;
		},

		/**
		 * Show loading state
		 */
		showLoading: function() {
			this.elements.$wrap.addClass('bzmi-loading');
		},

		/**
		 * Hide loading state
		 */
		hideLoading: function() {
			this.elements.$wrap.removeClass('bzmi-loading');
		},

		/**
		 * Show notice
		 */
		showNotice: function(message, type = 'info') {
			// Remove existing notices
			$('.bzmi-toast').remove();

			const $notice = $(`
				<div class="bzmi-toast bzmi-toast--${type}">
					<span class="dashicons dashicons-${type === 'success' ? 'yes-alt' : (type === 'error' ? 'warning' : 'info')}"></span>
					<span>${message}</span>
				</div>
			`);

			$('body').append($notice);

			setTimeout(() => $notice.addClass('bzmi-toast--visible'), 10);
			setTimeout(() => {
				$notice.removeClass('bzmi-toast--visible');
				setTimeout(() => $notice.remove(), 300);
			}, 3000);
		},

		/**
		 * Handle error
		 */
		handleError: function(error) {
			console.error('BZMI Foundations Error:', error);
			this.showNotice(error.message || 'Une erreur est survenue', 'error');
		},

		/**
		 * Refresh page
		 */
		refreshPage: function() {
			window.location.reload();
		}
	};

	// Initialize on DOM ready
	$(document).ready(function() {
		// Only init on foundations pages
		if ($('.bzmi-foundations-wrap').length) {
			BZMIFoundations.init();
		}
	});

	// Add toast styles dynamically
	$('<style>')
		.text(`
			.bzmi-toast {
				position: fixed;
				bottom: 20px;
				right: 20px;
				display: flex;
				align-items: center;
				gap: 10px;
				padding: 12px 20px;
				background: #fff;
				border-radius: 8px;
				box-shadow: 0 4px 12px rgba(0,0,0,0.15);
				z-index: 100001;
				transform: translateY(100px);
				opacity: 0;
				transition: all 0.3s ease;
			}
			.bzmi-toast--visible {
				transform: translateY(0);
				opacity: 1;
			}
			.bzmi-toast--success { border-left: 4px solid #00a32a; }
			.bzmi-toast--error { border-left: 4px solid #d63638; }
			.bzmi-toast--info { border-left: 4px solid #72aee6; }
			.bzmi-toast .dashicons {
				font-size: 20px;
				width: 20px;
				height: 20px;
			}
			.bzmi-toast--success .dashicons { color: #00a32a; }
			.bzmi-toast--error .dashicons { color: #d63638; }
			.bzmi-toast--info .dashicons { color: #72aee6; }
		`)
		.appendTo('head');

})(jQuery);
