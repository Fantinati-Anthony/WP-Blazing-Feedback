/**
 * Admin Options Manager JavaScript
 *
 * @package WP_Visual_Feedback_Hub
 * @since 1.1.0
 */

(function($) {
    'use strict';

    var WPVFHOptionsAdmin = {
        searchTimeout: null,

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initSortable();
            this.initTabsSortable();
            this.initColorPickers();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            var self = this;

            // Add new item
            $(document).on('click', '.wpvfh-add-item-btn', function() {
                self.addNewItem($(this).data('type'));
            });

            // Expand/collapse card
            $(document).on('click', '.wpvfh-card-header', function(e) {
                // Don't toggle if clicking on interactive elements
                if ($(e.target).closest('.wpvfh-toggle, .wpvfh-delete-item-btn, .wpvfh-drag-handle').length) {
                    return;
                }
                self.toggleCard($(this).closest('.wpvfh-option-card'));
            });

            // Expand button
            $(document).on('click', '.wpvfh-expand-btn', function(e) {
                e.stopPropagation();
                self.toggleCard($(this).closest('.wpvfh-option-card'));
            });

            // Enable/disable toggle
            $(document).on('change', '.wpvfh-enabled-toggle', function(e) {
                e.stopPropagation();
                var $card = $(this).closest('.wpvfh-option-card');
                if ($(this).is(':checked')) {
                    $card.removeClass('wpvfh-disabled');
                } else {
                    $card.addClass('wpvfh-disabled');
                }
            });

            // Display mode selector
            $(document).on('change', '.wpvfh-display-mode-selector input[type="radio"]', function() {
                var $card = $(this).closest('.wpvfh-option-card');
                var mode = $(this).val();

                // Update selected class
                $card.find('.wpvfh-radio-card').removeClass('selected');
                $(this).closest('.wpvfh-radio-card').addClass('selected');

                // Show/hide emoji row
                if (mode === 'emoji') {
                    $card.find('.wpvfh-emoji-row').slideDown(200);
                } else {
                    $card.find('.wpvfh-emoji-row').slideUp(200);
                }

                self.updatePreview($card);
            });

            // Save item
            $(document).on('click', '.wpvfh-save-item-btn', function() {
                self.saveItem($(this).closest('.wpvfh-option-card'));
            });

            // Delete item
            $(document).on('click', '.wpvfh-delete-item-btn', function(e) {
                e.stopPropagation();
                if (confirm(wpvfhOptionsAdmin.i18n.confirmDelete)) {
                    self.deleteItem($(this).closest('.wpvfh-option-card'));
                }
            });

            // Update preview on input change
            $(document).on('input change', '.wpvfh-emoji-input, .wpvfh-label-input', function() {
                self.updatePreview($(this).closest('.wpvfh-option-card'));
            });

            // Access control search
            $(document).on('input', '.wpvfh-access-search', function() {
                var $input = $(this);
                var $card = $input.closest('.wpvfh-option-card');
                self.searchUsersRoles($card, $input.val());
            });

            // Access control focus/blur
            $(document).on('focus', '.wpvfh-access-search', function() {
                var $card = $(this).closest('.wpvfh-option-card');
                self.searchUsersRoles($card, $(this).val());
            });

            // Hide dropdown on click outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.wpvfh-access-search-wrapper').length) {
                    $('.wpvfh-access-dropdown').hide();
                }
            });

            // Select access item
            $(document).on('click', '.wpvfh-access-dropdown-item', function() {
                var $card = $(this).closest('.wpvfh-option-card');
                self.addAccessTag($card, $(this).data('type'), $(this).data('id'), $(this).find('.label').text());
            });

            // Remove access tag
            $(document).on('click', '.wpvfh-access-tag-remove', function(e) {
                e.stopPropagation();
                var $tag = $(this).closest('.wpvfh-access-tag');
                var $card = $tag.closest('.wpvfh-option-card');
                $tag.remove();
                self.updateAccessHiddenFields($card);
            });

            // Save on Enter key in label input
            $(document).on('keypress', '.wpvfh-label-input', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    self.saveItem($(this).closest('.wpvfh-option-card'));
                }
            });

            // Add new group button
            $(document).on('click', '.wpvfh-add-group-btn', function(e) {
                e.preventDefault();
                self.openNewGroupModal();
            });

            // Close modal
            $(document).on('click', '.wpvfh-modal-close, .wpvfh-modal-cancel', function() {
                self.closeNewGroupModal();
            });

            // Close modal on backdrop click
            $(document).on('click', '.wpvfh-modal', function(e) {
                if ($(e.target).hasClass('wpvfh-modal')) {
                    self.closeNewGroupModal();
                }
            });

            // Create group button
            $(document).on('click', '.wpvfh-create-group-btn', function() {
                self.createCustomGroup();
            });

            // Enter key in new group name input
            $(document).on('keypress', '#wpvfh-new-group-name', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    self.createCustomGroup();
                }
            });

            // Delete group (tab delete button)
            $(document).on('click', '.wpvfh-tab-delete', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $tab = $(this).closest('.nav-tab');
                var slug = $tab.data('tab');
                self.deleteCustomGroup(slug);
            });

            // ESC key to close modal and emoji picker
            $(document).on('keydown', function(e) {
                if (e.which === 27) {
                    self.closeNewGroupModal();
                    self.closeEmojiPicker();
                }
            });

            // Emoji picker button click
            $(document).on('click', '.wpvfh-emoji-picker-btn, .wpvfh-emoji-input', function(e) {
                e.stopPropagation();
                var $btn = $(this).closest('.wpvfh-emoji-input-wrapper').find('.wpvfh-emoji-picker-btn');
                self.toggleEmojiPicker($btn);
            });

            // Emoji tab click
            $(document).on('click', '.wpvfh-emoji-tab', function(e) {
                e.stopPropagation();
                var category = $(this).data('category');
                self.switchEmojiCategory(category);
            });

            // Emoji item click
            $(document).on('click', '.wpvfh-emoji-item', function(e) {
                e.stopPropagation();
                var emoji = $(this).text();
                self.selectEmoji(emoji);
            });

            // Close emoji picker on click outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.wpvfh-emoji-picker, .wpvfh-emoji-picker-btn, .wpvfh-emoji-input').length) {
                    self.closeEmojiPicker();
                }
            });

            // Group settings toggle button
            $(document).on('click', '.wpvfh-group-settings-btn', function(e) {
                e.preventDefault();
                var $panel = $(this).closest('.wpvfh-group-settings-panel');
                self.toggleGroupSettings($panel);
            });

            // Group enabled toggle
            $(document).on('change', '.wpvfh-group-enabled', function() {
                var $panel = $(this).closest('.wpvfh-group-settings-panel');
                if ($(this).is(':checked')) {
                    $panel.removeClass('disabled');
                } else {
                    $panel.addClass('disabled');
                }
                // Auto-save on toggle
                self.saveGroupSettings($panel);
            });

            // Group required toggle
            $(document).on('change', '.wpvfh-group-required', function() {
                var $panel = $(this).closest('.wpvfh-group-settings-panel');
                // Auto-save on toggle
                self.saveGroupSettings($panel);
            });

            // Show in sidebar toggle
            $(document).on('change', '.wpvfh-group-show-in-sidebar', function() {
                var $panel = $(this).closest('.wpvfh-group-settings-panel');
                // Auto-save on toggle
                self.saveGroupSettings($panel);
            });

            // Rename group button
            $(document).on('click', '.wpvfh-rename-group-btn', function(e) {
                e.preventDefault();
                var $title = $(this).closest('.wpvfh-group-title');
                self.startRenameGroup($title);
            });

            // Rename group input blur/enter
            $(document).on('blur', '.wpvfh-group-name-input', function() {
                var $title = $(this).closest('.wpvfh-group-title');
                self.finishRenameGroup($title);
            });

            $(document).on('keydown', '.wpvfh-group-name-input', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $(this).blur();
                }
                if (e.which === 27) {
                    e.preventDefault();
                    var $title = $(this).closest('.wpvfh-group-title');
                    self.cancelRenameGroup($title);
                }
            });

            // Save group settings button
            $(document).on('click', '.wpvfh-save-group-settings-btn', function(e) {
                e.preventDefault();
                var $panel = $(this).closest('.wpvfh-group-settings-panel');
                self.saveGroupSettings($panel);
            });

            // Group access search
            $(document).on('input', '.wpvfh-group-access-search', function() {
                var $wrapper = $(this).closest('.wpvfh-access-search-wrapper');
                var $panel = $(this).closest('.wpvfh-group-settings-panel');
                var query = $(this).val().trim();

                if (query.length < 2) {
                    $wrapper.find('.wpvfh-access-dropdown').hide();
                    return;
                }

                self.searchUsersRoles(query, $wrapper, function(item) {
                    self.addGroupAccessTag($panel, item);
                });
            });

            // Remove group access tag
            $(document).on('click', '.wpvfh-group-access-tags .wpvfh-access-tag-remove', function(e) {
                e.preventDefault();
                var $tag = $(this).closest('.wpvfh-access-tag');
                var $panel = $tag.closest('.wpvfh-group-settings-panel');
                $tag.remove();
                self.updateGroupAccessHiddenFields($panel);
            });
        },

        /**
         * Toggle card expand/collapse
         */
        toggleCard: function($card) {
            var $body = $card.find('.wpvfh-card-body');

            if ($card.hasClass('expanded')) {
                $body.slideUp(200);
                $card.removeClass('expanded');
            } else {
                $body.slideDown(200);
                $card.addClass('expanded');

                // Initialize color picker if not done
                var $colorInput = $card.find('.wpvfh-color-input');
                if (!$colorInput.hasClass('wp-color-picker')) {
                    this.initColorPickerForCard($card);
                }
            }
        },

        /**
         * Initialize sortable
         */
        initSortable: function() {
            var self = this;

            $('.wpvfh-items-list').sortable({
                handle: '.wpvfh-drag-handle',
                placeholder: 'ui-sortable-placeholder',
                axis: 'y',
                tolerance: 'pointer',
                update: function(event, ui) {
                    self.saveOrder($(this));
                }
            });
        },

        /**
         * Initialize tabs sortable (for metadata groups reordering)
         */
        initTabsSortable: function() {
            var self = this;

            $('.wpvfh-nav-tabs').sortable({
                items: '.nav-tab:not(.wpvfh-add-group-btn)',
                axis: 'x',
                tolerance: 'pointer',
                cursor: 'grab',
                placeholder: 'wpvfh-tab-sortable-placeholder',
                helper: function(e, el) {
                    return el.clone().addClass('wpvfh-tab-dragging');
                },
                start: function(e, ui) {
                    ui.placeholder.width(ui.item.outerWidth());
                    ui.placeholder.height(ui.item.outerHeight());
                },
                update: function(event, ui) {
                    self.saveTabsOrder();
                }
            });
        },

        /**
         * Save tabs order via AJAX
         */
        saveTabsOrder: function() {
            var order = [];

            $('.wpvfh-nav-tabs .nav-tab:not(.wpvfh-add-group-btn)').each(function() {
                var slug = $(this).data('tab');
                if (slug) {
                    order.push(slug);
                }
            });

            $.post(wpvfhOptionsAdmin.ajaxUrl, {
                action: 'wpvfh_save_groups_order',
                nonce: wpvfhOptionsAdmin.nonce,
                order: order
            }).done(function(response) {
                if (response.success) {
                    // Optionally show a brief notification
                    console.log('Tabs order saved');
                } else {
                    console.error('Error saving tabs order:', response.data);
                }
            });
        },

        /**
         * Initialize color pickers for all cards
         */
        initColorPickers: function() {
            var self = this;

            // Only init for expanded cards or visible ones
            $('.wpvfh-option-card.expanded').each(function() {
                self.initColorPickerForCard($(this));
            });
        },

        /**
         * Initialize color picker for a specific card
         */
        initColorPickerForCard: function($card) {
            var self = this;
            var $input = $card.find('.wpvfh-color-input');

            if ($input.length && !$input.hasClass('wp-color-picker')) {
                $input.wpColorPicker({
                    change: function(event, ui) {
                        setTimeout(function() {
                            self.updatePreview($card);
                            // Update the color dot in radio selector
                            $card.find('.wpvfh-radio-dot').css('background-color', ui.color.toString());
                        }, 10);
                    }
                });
            }
        },

        /**
         * Add new item
         */
        addNewItem: function(type) {
            var template = $('#wpvfh-item-template-' + type).html();
            var $list = $('.wpvfh-items-list[data-type="' + type + '"]');

            $list.prepend(template);

            var $newCard = $list.find('.wpvfh-new-item').first();

            // Expand the new card
            $newCard.addClass('expanded');
            $newCard.find('.wpvfh-card-body').show();

            // Initialize color picker
            this.initColorPickerForCard($newCard);

            // Focus on label input
            $newCard.find('.wpvfh-label-input').focus();

            // Scroll to new card
            $('html, body').animate({
                scrollTop: $newCard.offset().top - 100
            }, 300);
        },

        /**
         * Save item
         */
        saveItem: function($card) {
            var self = this;
            var $list = $card.closest('.wpvfh-items-list');
            var type = $list.data('type');
            var isNew = $card.hasClass('wpvfh-new-item');

            // Gather data
            var data = {
                action: 'wpvfh_save_option_item',
                nonce: wpvfhOptionsAdmin.nonce,
                option_type: type,
                item_id: $card.data('id') || '',
                label: $card.find('.wpvfh-label-input').val(),
                emoji: $card.find('.wpvfh-emoji-input').val() || '📌',
                color: $card.find('.wpvfh-color-input').val() || '#666666',
                display_mode: $card.find('.wpvfh-display-mode-selector input:checked').val() || 'emoji',
                enabled: $card.find('.wpvfh-enabled-toggle').is(':checked') ? 'true' : 'false',
                is_treated: $card.find('.wpvfh-is-treated-toggle').is(':checked') ? 'true' : 'false',
                ai_prompt: $card.find('.wpvfh-ai-prompt').val() || '',
                is_new: isNew ? 'true' : 'false'
            };

            // Gather allowed roles and users
            var allowedRoles = [];
            var allowedUsers = [];
            $card.find('.wpvfh-access-tag').each(function() {
                if ($(this).data('type') === 'role') {
                    allowedRoles.push($(this).data('id'));
                } else if ($(this).data('type') === 'user') {
                    allowedUsers.push($(this).data('id'));
                }
            });
            data.allowed_roles = allowedRoles;
            data.allowed_users = allowedUsers;

            // Validate
            if (!data.label.trim()) {
                alert('Le label est requis.');
                $card.find('.wpvfh-label-input').focus();
                return;
            }

            $card.addClass('saving');
            var $btn = $card.find('.wpvfh-save-item-btn');
            $btn.prop('disabled', true).find('.dashicons').removeClass('dashicons-saved').addClass('dashicons-update spin');

            $.post(wpvfhOptionsAdmin.ajaxUrl, data, function(response) {
                $card.removeClass('saving');
                $btn.prop('disabled', false).find('.dashicons').removeClass('dashicons-update spin').addClass('dashicons-saved');

                if (response.success) {
                    // Update card data
                    if (response.data && response.data.item) {
                        $card.data('id', response.data.item.id);
                        $card.attr('data-id', response.data.item.id);
                    }

                    $card.removeClass('wpvfh-new-item');
                    $card.addClass('wpvfh-just-added');

                    setTimeout(function() {
                        $card.removeClass('wpvfh-just-added');
                    }, 2000);
                } else {
                    alert(response.data || wpvfhOptionsAdmin.i18n.error);
                }
            }).fail(function() {
                $card.removeClass('saving');
                $btn.prop('disabled', false).find('.dashicons').removeClass('dashicons-update spin').addClass('dashicons-saved');
                alert(wpvfhOptionsAdmin.i18n.error);
            });
        },

        /**
         * Delete item
         */
        deleteItem: function($card) {
            var $list = $card.closest('.wpvfh-items-list');
            var type = $list.data('type');
            var itemId = $card.data('id');

            // If new item (not saved yet), just remove
            if ($card.hasClass('wpvfh-new-item') || !itemId) {
                $card.slideUp(200, function() {
                    $(this).remove();
                });
                return;
            }

            $card.addClass('saving');

            $.post(wpvfhOptionsAdmin.ajaxUrl, {
                action: 'wpvfh_delete_option_item',
                nonce: wpvfhOptionsAdmin.nonce,
                option_type: type,
                item_id: itemId
            }, function(response) {
                if (response.success) {
                    $card.slideUp(200, function() {
                        $(this).remove();
                    });
                } else {
                    $card.removeClass('saving');
                    alert(response.data || wpvfhOptionsAdmin.i18n.error);
                }
            }).fail(function() {
                $card.removeClass('saving');
                alert(wpvfhOptionsAdmin.i18n.error);
            });
        },

        /**
         * Save order
         */
        saveOrder: function($list) {
            var type = $list.data('type');
            var order = [];

            $list.find('.wpvfh-option-card').each(function() {
                var id = $(this).data('id');
                if (id) {
                    order.push(id);
                }
            });

            $.post(wpvfhOptionsAdmin.ajaxUrl, {
                action: 'wpvfh_save_options_order',
                nonce: wpvfhOptionsAdmin.nonce,
                option_type: type,
                order: order
            });
        },

        /**
         * Update preview in card header
         */
        updatePreview: function($card) {
            var label = $card.find('.wpvfh-label-input').val() || 'Nouveau';
            var color = $card.find('.wpvfh-color-input').val() || '#666666';
            var emoji = $card.find('.wpvfh-emoji-input').val() || '📌';
            var displayMode = $card.find('.wpvfh-display-mode-selector input:checked').val() || 'emoji';

            var $preview = $card.find('.wpvfh-card-preview');

            // Update label
            $preview.find('.wpvfh-preview-label').text(label);

            // Update display mode
            if (displayMode === 'emoji') {
                $preview.find('.wpvfh-preview-emoji').text(emoji).show();
                $preview.find('.wpvfh-preview-dot').hide();
            } else {
                $preview.find('.wpvfh-preview-emoji').hide();
                $preview.find('.wpvfh-preview-dot').css('background-color', color).show();
            }

            // Update radio card emoji preview
            $card.find('.wpvfh-radio-icon').text(emoji);
        },

        /**
         * Search users and roles
         */
        searchUsersRoles: function($card, query) {
            var self = this;
            var $dropdown = $card.find('.wpvfh-access-dropdown');

            // Clear previous timeout
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }

            // Show dropdown with loading or show roles immediately
            if (!query) {
                // Show roles only
                this.showRolesDropdown($card);
                return;
            }

            this.searchTimeout = setTimeout(function() {
                $.post(wpvfhOptionsAdmin.ajaxUrl, {
                    action: 'wpvfh_search_users_roles',
                    nonce: wpvfhOptionsAdmin.nonce,
                    search: query
                }, function(response) {
                    if (response.success) {
                        self.renderDropdown($card, response.data);
                    }
                });
            }, 300);
        },

        /**
         * Show roles dropdown (no search)
         */
        showRolesDropdown: function($card) {
            var roles = wpvfhOptionsAdmin.roles || {};
            var items = [];

            for (var slug in roles) {
                items.push({
                    type: 'role',
                    id: slug,
                    label: roles[slug],
                    icon: '👥'
                });
            }

            this.renderDropdown($card, items);
        },

        /**
         * Render dropdown with results
         */
        renderDropdown: function($card, items) {
            var $dropdown = $card.find('.wpvfh-access-dropdown');
            var existingTags = [];

            // Get existing tags
            $card.find('.wpvfh-access-tag').each(function() {
                existingTags.push($(this).data('type') + '_' + $(this).data('id'));
            });

            var html = '';

            if (items.length === 0) {
                html = '<div class="wpvfh-access-dropdown-empty">' + wpvfhOptionsAdmin.i18n.noResults + '</div>';
            } else {
                items.forEach(function(item) {
                    // Skip if already added
                    if (existingTags.indexOf(item.type + '_' + item.id) !== -1) {
                        return;
                    }

                    var typeBadge = item.type === 'role' ? 'Rôle' : 'Utilisateur';
                    html += '<div class="wpvfh-access-dropdown-item" data-type="' + item.type + '" data-id="' + item.id + '">';
                    html += '<span class="icon">' + item.icon + '</span>';
                    html += '<span class="label">' + item.label + '</span>';
                    html += '<span class="type-badge">' + typeBadge + '</span>';
                    html += '</div>';
                });

                if (html === '') {
                    html = '<div class="wpvfh-access-dropdown-empty">' + wpvfhOptionsAdmin.i18n.noResults + '</div>';
                }
            }

            $dropdown.html(html).show();
        },

        /**
         * Add access tag
         */
        addAccessTag: function($card, type, id, label) {
            var icon = type === 'role' ? '👥' : '👤';
            var $tags = $card.find('.wpvfh-access-tags');

            var html = '<span class="wpvfh-access-tag" data-type="' + type + '" data-id="' + id + '">';
            html += icon + ' ' + label;
            html += '<button type="button" class="wpvfh-access-tag-remove">&times;</button>';
            html += '</span>';

            $tags.append(html);

            // Clear search and hide dropdown
            $card.find('.wpvfh-access-search').val('');
            $card.find('.wpvfh-access-dropdown').hide();

            // Update hidden fields
            this.updateAccessHiddenFields($card);
        },

        /**
         * Update hidden fields for access control
         */
        updateAccessHiddenFields: function($card) {
            var roles = [];
            var users = [];

            $card.find('.wpvfh-access-tag').each(function() {
                if ($(this).data('type') === 'role') {
                    roles.push($(this).data('id'));
                } else {
                    users.push($(this).data('id'));
                }
            });

            $card.find('.wpvfh-allowed-roles').val(roles.join(','));
            $card.find('.wpvfh-allowed-users').val(users.join(','));
        },

        /**
         * Open new group modal
         */
        openNewGroupModal: function() {
            var $modal = $('#wpvfh-new-group-modal');
            $modal.addClass('active');
            $modal.find('#wpvfh-new-group-name').val('').focus();
        },

        /**
         * Close new group modal
         */
        closeNewGroupModal: function() {
            var $modal = $('#wpvfh-new-group-modal');
            $modal.removeClass('active');
            $modal.find('#wpvfh-new-group-name').val('');
        },

        /**
         * Create custom group via AJAX
         */
        createCustomGroup: function() {
            var self = this;
            var $modal = $('#wpvfh-new-group-modal');
            var $input = $modal.find('#wpvfh-new-group-name');
            var $btn = $modal.find('.wpvfh-create-group-btn');
            var name = $input.val().trim();

            if (!name) {
                $input.focus();
                return;
            }

            // Disable button and show loading
            $btn.prop('disabled', true).text('Création...');

            $.post(wpvfhOptionsAdmin.ajaxUrl, {
                action: 'wpvfh_create_custom_group',
                nonce: wpvfhOptionsAdmin.nonce,
                name: name
            }, function(response) {
                $btn.prop('disabled', false).text('Créer');

                if (response.success) {
                    // Reload the page to show the new tab
                    window.location.href = wpvfhOptionsAdmin.adminUrl + '?page=' + (wpvfhOptionsAdmin.pageSlug || 'bzmi-metadata') + '&tab=' + response.data.slug;
                } else {
                    alert(response.data || wpvfhOptionsAdmin.i18n.error);
                }
            }).fail(function() {
                $btn.prop('disabled', false).text('Créer');
                alert(wpvfhOptionsAdmin.i18n.error);
            });
        },

        /**
         * Delete custom group via AJAX
         */
        deleteCustomGroup: function(slug) {
            var self = this;

            if (!confirm('Êtes-vous sûr de vouloir supprimer ce groupe de métadatas ? Cette action supprimera également toutes les métadatas de ce groupe.')) {
                return;
            }

            $.post(wpvfhOptionsAdmin.ajaxUrl, {
                action: 'wpvfh_delete_custom_group',
                nonce: wpvfhOptionsAdmin.nonce,
                slug: slug
            }, function(response) {
                if (response.success) {
                    // Redirect to statuses tab
                    window.location.href = wpvfhOptionsAdmin.adminUrl + '?page=' + (wpvfhOptionsAdmin.pageSlug || 'bzmi-metadata') + '&tab=statuses';
                } else {
                    alert(response.data || wpvfhOptionsAdmin.i18n.error);
                }
            }).fail(function() {
                alert(wpvfhOptionsAdmin.i18n.error);
            });
        },

        /**
         * Current emoji picker target input
         */
        currentEmojiTarget: null,

        /**
         * Toggle emoji picker
         */
        toggleEmojiPicker: function($btn) {
            var $picker = $('#wpvfh-emoji-picker');
            var $input = $btn.closest('.wpvfh-emoji-input-wrapper').find('.wpvfh-emoji-input');

            if ($picker.hasClass('active') && this.currentEmojiTarget && this.currentEmojiTarget[0] === $input[0]) {
                this.closeEmojiPicker();
                return;
            }

            this.currentEmojiTarget = $input;

            // Position the picker near the button
            var btnOffset = $btn.offset();
            var btnHeight = $btn.outerHeight();

            $picker.css({
                top: btnOffset.top + btnHeight + 5,
                left: btnOffset.left - 260 // Position to the left of the button
            });

            // Make sure it doesn't go off screen
            var windowWidth = $(window).width();
            var pickerWidth = $picker.outerWidth();
            var pickerLeft = parseInt($picker.css('left'));

            if (pickerLeft < 10) {
                $picker.css('left', 10);
            }
            if (pickerLeft + pickerWidth > windowWidth - 10) {
                $picker.css('left', windowWidth - pickerWidth - 10);
            }

            $picker.addClass('active');

            // Reset to first category
            this.switchEmojiCategory('smileys');
        },

        /**
         * Close emoji picker
         */
        closeEmojiPicker: function() {
            $('#wpvfh-emoji-picker').removeClass('active');
            this.currentEmojiTarget = null;
        },

        /**
         * Switch emoji category
         */
        switchEmojiCategory: function(category) {
            var $picker = $('#wpvfh-emoji-picker');

            // Update active tab
            $picker.find('.wpvfh-emoji-tab').removeClass('active');
            $picker.find('.wpvfh-emoji-tab[data-category="' + category + '"]').addClass('active');

            // Show the correct grid
            $picker.find('.wpvfh-emoji-grid').hide();
            $picker.find('.wpvfh-emoji-grid[data-category="' + category + '"]').show();
        },

        /**
         * Select emoji
         */
        selectEmoji: function(emoji) {
            if (this.currentEmojiTarget) {
                this.currentEmojiTarget.val(emoji);

                // Update preview
                var $card = this.currentEmojiTarget.closest('.wpvfh-option-card');
                this.updatePreview($card);

                // Update the radio icon too
                $card.find('.wpvfh-radio-icon').text(emoji);
            }

            this.closeEmojiPicker();
        },

        /**
         * Toggle group settings panel
         */
        toggleGroupSettings: function($panel) {
            var $body = $panel.find('.wpvfh-group-settings-body');
            if ($panel.hasClass('settings-open')) {
                $body.slideUp(200);
                $panel.removeClass('settings-open');
            } else {
                $body.slideDown(200);
                $panel.addClass('settings-open');
            }
        },

        /**
         * Start renaming a group
         */
        startRenameGroup: function($title) {
            var $display = $title.find('.wpvfh-group-name-display');
            var $input = $title.find('.wpvfh-group-name-input');
            var $btn = $title.find('.wpvfh-rename-group-btn');

            $input.data('original', $input.val());
            $display.hide();
            $btn.hide();
            $input.show().focus().select();
        },

        /**
         * Cancel renaming a group
         */
        cancelRenameGroup: function($title) {
            var $display = $title.find('.wpvfh-group-name-display');
            var $input = $title.find('.wpvfh-group-name-input');
            var $btn = $title.find('.wpvfh-rename-group-btn');

            $input.val($input.data('original'));
            $input.hide();
            $display.show();
            $btn.show();
        },

        /**
         * Finish renaming a group
         */
        finishRenameGroup: function($title) {
            var self = this;
            var $display = $title.find('.wpvfh-group-name-display');
            var $input = $title.find('.wpvfh-group-name-input');
            var $btn = $title.find('.wpvfh-rename-group-btn');
            var $panel = $title.closest('.wpvfh-group-settings-panel');
            var slug = $panel.data('group');
            var newName = $input.val().trim();
            var originalName = $input.data('original');

            if (!newName || newName === originalName) {
                self.cancelRenameGroup($title);
                return;
            }

            $.post(wpvfhOptionsAdmin.ajaxUrl, {
                action: 'wpvfh_rename_custom_group',
                nonce: wpvfhOptionsAdmin.nonce,
                slug: slug,
                name: newName
            }, function(response) {
                if (response.success) {
                    $display.text(newName);
                    // Update tab name
                    $('.nav-tab[data-tab="' + slug + '"]').contents().first().replaceWith(newName);
                } else {
                    alert(response.data || wpvfhOptionsAdmin.i18n.error);
                    $input.val(originalName);
                }
                $input.hide();
                $display.show();
                $btn.show();
            }).fail(function() {
                alert(wpvfhOptionsAdmin.i18n.error);
                $input.val(originalName);
                $input.hide();
                $display.show();
                $btn.show();
            });
        },

        /**
         * Save group settings
         */
        saveGroupSettings: function($panel) {
            var self = this;
            var slug = $panel.data('group');
            var enabled = $panel.find('.wpvfh-group-enabled').is(':checked');
            var required = $panel.find('.wpvfh-group-required').is(':checked');
            var showInSidebar = $panel.find('.wpvfh-group-show-in-sidebar').is(':checked');
            var hideEmptySections = $panel.find('.wpvfh-group-hide-empty-sections').is(':checked');
            var aiPrompt = $panel.find('.wpvfh-group-ai-prompt').val();
            var allowedRoles = $panel.find('.wpvfh-group-allowed-roles').val();
            var allowedUsers = $panel.find('.wpvfh-group-allowed-users').val();

            var $btn = $panel.find('.wpvfh-save-group-settings-btn');
            var $icon = $btn.find('.dashicons');
            $icon.removeClass('dashicons-saved').addClass('dashicons-update spin');

            $.post(wpvfhOptionsAdmin.ajaxUrl, {
                action: 'wpvfh_save_group_settings',
                nonce: wpvfhOptionsAdmin.nonce,
                slug: slug,
                enabled: enabled ? 'true' : 'false',
                required: required ? 'true' : 'false',
                show_in_sidebar: showInSidebar ? 'true' : 'false',
                hide_empty_sections: hideEmptySections ? 'true' : 'false',
                ai_prompt: aiPrompt,
                allowed_roles: allowedRoles,
                allowed_users: allowedUsers
            }, function(response) {
                $icon.removeClass('dashicons-update spin').addClass('dashicons-saved');
                if (!response.success) {
                    alert(response.data || wpvfhOptionsAdmin.i18n.error);
                }
            }).fail(function() {
                $icon.removeClass('dashicons-update spin').addClass('dashicons-saved');
                alert(wpvfhOptionsAdmin.i18n.error);
            });
        },

        /**
         * Add access tag for group
         */
        addGroupAccessTag: function($panel, item) {
            var $tags = $panel.find('.wpvfh-group-access-tags');
            var $search = $panel.find('.wpvfh-group-access-search');
            var $dropdown = $panel.find('.wpvfh-access-dropdown');

            // Check if already exists
            if ($tags.find('.wpvfh-access-tag[data-type="' + item.type + '"][data-id="' + item.id + '"]').length) {
                $search.val('');
                $dropdown.hide();
                return;
            }

            var $tag = $('<span class="wpvfh-access-tag" data-type="' + item.type + '" data-id="' + item.id + '">' +
                item.label +
                '<button type="button" class="wpvfh-access-tag-remove">&times;</button>' +
                '</span>');

            $tags.append($tag);
            $search.val('');
            $dropdown.hide();

            this.updateGroupAccessHiddenFields($panel);
        },

        /**
         * Update hidden fields for group access control
         */
        updateGroupAccessHiddenFields: function($panel) {
            var roles = [];
            var users = [];

            $panel.find('.wpvfh-group-access-tags .wpvfh-access-tag').each(function() {
                if ($(this).data('type') === 'role') {
                    roles.push($(this).data('id'));
                } else {
                    users.push($(this).data('id'));
                }
            });

            $panel.find('.wpvfh-group-allowed-roles').val(roles.join(','));
            $panel.find('.wpvfh-group-allowed-users').val(users.join(','));
        }
    };

    // Add CSS for spinning icon
    $('<style>.dashicons.spin { animation: wpvfh-spin 1s linear infinite; } @keyframes wpvfh-spin { 100% { transform: rotate(360deg); } }</style>').appendTo('head');

    // Initialize on document ready
    $(document).ready(function() {
        WPVFHOptionsAdmin.init();
    });

})(jQuery);
