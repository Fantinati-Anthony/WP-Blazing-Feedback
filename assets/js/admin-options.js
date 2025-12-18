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
        }
    };

    // Add CSS for spinning icon
    $('<style>.dashicons.spin { animation: wpvfh-spin 1s linear infinite; } @keyframes wpvfh-spin { 100% { transform: rotate(360deg); } }</style>').appendTo('head');

    // Initialize on document ready
    $(document).ready(function() {
        WPVFHOptionsAdmin.init();
    });

})(jQuery);
