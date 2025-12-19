/**
 * JavaScript Modules Reference
 * 
 * This directory contains reference files documenting the sections
 * of feedback-widget.js for easier navigation and maintenance.
 * 
 * The main feedback-widget.js file remains the single source of truth.
 * These files are for documentation/reference purposes.
 * 
 * @package Blazing_Feedback
 */

Modules:

- core.js (~330 lines, L1-L330)
  État, éléments, initialisation, thème
  Methods: init, applyThemeColors, moveFixedElementsToBody, cacheElements

- events.js (~370 lines, L331-L700)
  Bindage des événements
  Methods: bindEvents

- tools.js (~241 lines, L700-L940)
  Gestion des outils (click, voice, video)
  Methods: handleToolClick, startVoiceTimer, handleTranscriptionUpdate, clearVoiceRecording, startVideoTimer +1 more

- panel.js (~411 lines, L940-L1350)
  Toggle, panel open/close, tabs
  Methods: handleToggle, handleAddClick, handleVisibilityToggle, openPanel, closePanel +1 more

- selection.js (~196 lines, L975-L1170)
  Sélection d'éléments, inspecteur
  Methods: handleSelectElement, handleClearSelection, handleElementSelected, handleSelectionCleared, handleInspectorStopped +1 more

- list.js (~281 lines, L1350-L1630)
  Rendu liste, drag-drop, scroll
  Methods: renderPinsList, initDragAndDrop, updateFeedbackOrder, scrollToPin

- pins.js (~97 lines, L1594-L1690)
  Gestion des pins (placement, sélection)
  Methods: handlePinPlaced, handlePinSelected

- screenshot.js (~55 lines, L1676-L1730)
  Capture d'écran
  Methods: showScreenshotPreview, clearScreenshot, handleCaptureSuccess, handleCaptureError

- form.js (~282 lines, L1729-L2010)
  Gestion formulaire, soumission, reset
  Methods: handleCancel, handleSubmitFeedback, resetForm, setSubmitState

- details.js (~278 lines, L2013-L2290)
  Affichage détails feedback, mise à jour
  Methods: showFeedbackDetails, handleDetailChange, handleDeleteFeedback

- notifications.js (~57 lines, L2254-L2310)
  Notifications, helpers
  Methods: showNotification, escapeHtml, emitEvent

- filters.js (~93 lines, L2308-L2400)
  Filtrage par statut
  Methods: handleFilterClick, getFilteredFeedbacks, updateFilterCounts

- labels.js (~435 lines, L2356-L2790)
  Labels, config types/priorités/statuts
  Methods: updateDetailLabels, renderDetailTags, getTypeConfig, getPriorityConfig, getStatusConfig +9 more

- tags.js (~244 lines, L2397-L2640)
  Gestion des tags (ajout, suppression)
  Methods: addTag, removeTag, removeLastTag, getPredefinedTagColor, addFormTag +5 more

- validation.js (~259 lines, L2792-L3050)
  Validation de page, modales
  Methods: updateValidationSection, showValidateModal, handleValidatePage, handleRejectPage, loadPageStatus +1 more

- search.js (~301 lines, L3050-L3350)
  Recherche, modal recherche
  Methods: openSearchModal, closeSearchModal, performSearch, renderSearchResults, goToFeedback

- api.js (~201 lines, L3350-L3550)
  Appels API, chargement feedbacks
  Methods: loadExistingFeedbacks, handleFeedbacksLoaded

- mentions.js (~251 lines, L3550-L3800)
  Mentions utilisateurs
  Methods: handleMentionInput, showMentionDropdown, hideMentionDropdown, selectMention, searchUsers

- participants.js (~201 lines, L3800-L4000)
  Gestion des participants
  Methods: loadParticipants, renderParticipants, inviteParticipant

- attachments.js (~269 lines, L4000-L4268)
  Pièces jointes
  Methods: handleFileSelect, renderAttachments, removeAttachment
