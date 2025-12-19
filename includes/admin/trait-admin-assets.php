<?php
/**
 * Trait pour la gestion des assets admin (styles CSS)
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait de gestion des assets admin
 *
 * @since 1.9.0
 */
trait WPVFH_Admin_Assets {

	/**
	 * Charger les styles admin
	 *
	 * @since 1.0.0
	 * @param string $hook Page actuelle
	 * @return void
	 */
	public static function enqueue_admin_styles( $hook ) {
		// Charger sur toutes les pages du plugin
		if ( strpos( $hook, 'wpvfh' ) !== false || get_current_screen()->post_type === 'visual_feedback' ) {
			wp_add_inline_style( 'wp-admin', self::get_admin_inline_styles() );

			// Charger la bibliothèque de médias sur la page des paramètres
			if ( strpos( $hook, 'wpvfh-settings' ) !== false ) {
				wp_enqueue_media();
			}
		}
	}

	/**
	 * Styles CSS inline pour l'admin
	 *
	 * @since 1.0.0
	 * @return string
	 */
	private static function get_admin_inline_styles() {
		return '
			.wpvfh-dashboard-wrap {
				max-width: 1200px;
			}
			.wpvfh-stats-grid {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
				gap: 20px;
				margin: 20px 0;
			}
			.wpvfh-stat-card {
				background: #fff;
				border: 1px solid #ccd0d4;
				border-radius: 4px;
				padding: 20px;
				text-align: center;
			}
			.wpvfh-stat-card h3 {
				margin: 0 0 10px;
				color: #1d2327;
			}
			.wpvfh-stat-number {
				font-size: 36px;
				font-weight: 600;
				color: #2271b1;
				line-height: 1;
			}
			.wpvfh-stat-label {
				color: #50575e;
				margin-top: 5px;
			}
			.wpvfh-status-new { color: #3498db; }
			.wpvfh-status-in_progress { color: #f39c12; }
			.wpvfh-status-resolved { color: #27ae60; }
			.wpvfh-status-rejected { color: #e74c3c; }
			.wpvfh-recent-feedbacks {
				background: #fff;
				border: 1px solid #ccd0d4;
				border-radius: 4px;
				margin: 20px 0;
			}
			.wpvfh-recent-feedbacks h3 {
				padding: 15px 20px;
				margin: 0;
				border-bottom: 1px solid #ccd0d4;
			}
			.wpvfh-feedback-list {
				padding: 0;
				margin: 0;
				list-style: none;
			}
			.wpvfh-feedback-item {
				padding: 15px 20px;
				border-bottom: 1px solid #f0f0f1;
				display: flex;
				align-items: center;
				gap: 15px;
			}
			.wpvfh-feedback-item:last-child {
				border-bottom: none;
			}
			.wpvfh-feedback-avatar {
				flex-shrink: 0;
			}
			.wpvfh-feedback-avatar img {
				border-radius: 50%;
			}
			.wpvfh-feedback-content {
				flex: 1;
				min-width: 0;
			}
			.wpvfh-feedback-title {
				font-weight: 500;
				margin: 0 0 5px;
			}
			.wpvfh-feedback-meta {
				color: #50575e;
				font-size: 13px;
			}
			.wpvfh-feedback-status {
				flex-shrink: 0;
			}
			.wpvfh-status-badge {
				display: inline-block;
				padding: 3px 8px;
				border-radius: 3px;
				font-size: 12px;
				font-weight: 500;
			}
			.wpvfh-badge-new { background: #e3f2fd; color: #1565c0; }
			.wpvfh-badge-in_progress { background: #fff3e0; color: #ef6c00; }
			.wpvfh-badge-resolved { background: #e8f5e9; color: #2e7d32; }
			.wpvfh-badge-rejected { background: #ffebee; color: #c62828; }
			.wpvfh-quick-actions {
				display: flex;
				gap: 10px;
				margin-top: 20px;
			}
		';
	}
}
