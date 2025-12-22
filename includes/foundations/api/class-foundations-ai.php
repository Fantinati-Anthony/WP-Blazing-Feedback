<?php
/**
 * Service IA pour les Fondations
 *
 * @package Blazing_Minds
 * @subpackage Foundations
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Foundations_AI
 *
 * Gère les enrichissements et suggestions IA pour les Fondations
 *
 * @since 2.0.0
 */
class BZMI_Foundations_AI {

	/**
	 * Instance du service IA principal
	 *
	 * @var BZMI_AI_Config
	 */
	private $ai;

	/**
	 * Constructeur
	 */
	public function __construct() {
		$this->ai = bzmi_ai();
	}

	/**
	 * Vérifier si l'IA est disponible
	 *
	 * @return bool
	 */
	public function is_available() {
		return $this->ai->is_enabled() && $this->ai->has_api_key();
	}

	/**
	 * Enrichir un élément de fondation
	 *
	 * @param BZMI_Foundation $foundation Fondation.
	 * @param string          $socle      Nom du socle (identity, offer, experience, execution).
	 * @param string          $target     Cible spécifique (section, id).
	 * @return array|WP_Error
	 */
	public function enrich( $foundation, $socle, $target = '' ) {
		if ( ! $this->is_available() ) {
			return new WP_Error( 'ai_unavailable', __( 'Service IA non disponible.', 'blazing-feedback' ) );
		}

		$client = $foundation->get_client();
		$company_mode = $client ? $client->company_mode : 'existing';

		$context = $foundation->get_ai_context();

		switch ( $socle ) {
			case 'identity':
				return $this->enrich_identity( $foundation, $target, $context, $company_mode );

			case 'offer':
				return $this->enrich_offer( $foundation, $target, $context, $company_mode );

			case 'experience':
				return $this->enrich_experience( $foundation, $target, $context, $company_mode );

			case 'execution':
				return $this->enrich_execution( $foundation, $target, $context, $company_mode );

			default:
				return new WP_Error( 'invalid_socle', __( 'Socle invalide.', 'blazing-feedback' ) );
		}
	}

	/**
	 * Générer des suggestions pour un champ
	 *
	 * @param BZMI_Foundation $foundation Fondation.
	 * @param string          $socle      Nom du socle.
	 * @param string          $field      Nom du champ.
	 * @param array           $context    Contexte additionnel.
	 * @return array|WP_Error
	 */
	public function suggest( $foundation, $socle, $field, $context = array() ) {
		if ( ! $this->is_available() ) {
			return new WP_Error( 'ai_unavailable', __( 'Service IA non disponible.', 'blazing-feedback' ) );
		}

		$client = $foundation->get_client();
		$company_mode = $client ? $client->company_mode : 'existing';
		$full_context = $foundation->get_ai_context();

		$prompts = $this->get_field_prompts( $socle, $field, $company_mode );
		if ( ! $prompts ) {
			return new WP_Error( 'no_prompt', __( 'Pas de prompt disponible pour ce champ.', 'blazing-feedback' ) );
		}

		$system_prompt = $this->build_system_prompt( $full_context, $company_mode );
		$user_prompt = $prompts['user'];

		// Ajouter le contexte additionnel
		if ( ! empty( $context ) ) {
			$user_prompt .= "\n\nContexte additionnel:\n" . wp_json_encode( $context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
		}

		$response = $this->ai->call_api( $system_prompt, $user_prompt );

		if ( is_wp_error( $response ) ) {
			$this->log_ai_action( $foundation->id, $socle, 'suggest', $field, $user_prompt, null, 0, $response->get_error_message() );
			return $response;
		}

		$result = array(
			'field'       => $field,
			'suggestions' => $this->parse_suggestions( $response, $prompts['format'] ),
			'confidence'  => 0.85,
		);

		$this->log_ai_action( $foundation->id, $socle, 'suggest', $field, $user_prompt, $result, 0.85 );

		return $result;
	}

	/**
	 * Auditer une fondation complète
	 *
	 * @param BZMI_Foundation $foundation Fondation.
	 * @return array|WP_Error
	 */
	public function audit( $foundation ) {
		if ( ! $this->is_available() ) {
			return new WP_Error( 'ai_unavailable', __( 'Service IA non disponible.', 'blazing-feedback' ) );
		}

		$client = $foundation->get_client();
		$company_mode = $client ? $client->company_mode : 'existing';
		$context = $foundation->get_ai_context();

		$system_prompt = "Tu es un expert en stratégie de marque et marketing. Analyse cette fondation de marque et identifie:
1. Les points forts
2. Les incohérences ou contradictions
3. Les éléments manquants critiques
4. Des recommandations d'amélioration prioritaires

Réponds en JSON avec cette structure:
{
  \"strengths\": [\"...\"],
  \"inconsistencies\": [\"...\"],
  \"missing\": [\"...\"],
  \"recommendations\": [{\"priority\": \"high|medium|low\", \"socle\": \"identity|offer|experience|execution\", \"action\": \"...\"}],
  \"overall_score\": 0-100,
  \"maturity_level\": \"emerging|developing|established|optimized\"
}";

		$user_prompt = "Analyse cette fondation de marque (mode: $company_mode):\n\n" . wp_json_encode( $context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

		$response = $this->ai->call_api( $system_prompt, $user_prompt );

		if ( is_wp_error( $response ) ) {
			$this->log_ai_action( $foundation->id, 'all', 'audit', null, $user_prompt, null, 0, $response->get_error_message() );
			return $response;
		}

		$result = json_decode( $response, true );
		if ( ! $result ) {
			$result = array(
				'raw_response' => $response,
				'parse_error'  => true,
			);
		}

		$this->log_ai_action( $foundation->id, 'all', 'audit', null, 'Audit complet', $result, isset( $result['overall_score'] ) ? $result['overall_score'] / 100 : 0.5 );

		return $result;
	}

	/**
	 * Enrichir le socle Identité
	 *
	 * @param BZMI_Foundation $foundation   Fondation.
	 * @param string          $target       Cible.
	 * @param array           $context      Contexte.
	 * @param string          $company_mode Mode entreprise.
	 * @return array|WP_Error
	 */
	private function enrich_identity( $foundation, $target, $context, $company_mode ) {
		$system_prompt = $this->build_system_prompt( $context, $company_mode );

		if ( 'brand_dna' === $target ) {
			$user_prompt = "Génère un ADN de marque complet avec:
- Mission (1-2 phrases impactantes)
- 3-5 valeurs fondamentales
- Promesse de marque
- 3-5 traits de personnalité

Réponds en JSON.";
		} elseif ( 'vision' === $target ) {
			$user_prompt = "Génère une vision de marque avec:
- Énoncé de vision inspirant
- Ambition à long terme
- 3 objectifs à 3 ans
- 3 objectifs à 5 ans

Réponds en JSON.";
		} elseif ( 'tone_voice' === $target ) {
			$user_prompt = "Génère des guidelines de ton et voix avec:
- 3-5 attributs de ton
- Style de voix (description)
- 5 choses à faire
- 5 choses à éviter
- 2-3 exemples de messages

Réponds en JSON.";
		} elseif ( 'persona' === $target ) {
			$user_prompt = "Génère un persona cible avec:
- Nom fictif
- Tranche d'âge
- Poste/Métier
- Description (2-3 phrases)
- 3-5 objectifs
- 3-5 points de douleur
- 3-5 comportements
- Canaux préférés
- Citation représentative

Réponds en JSON.";
		} else {
			return new WP_Error( 'invalid_target', __( 'Cible invalide.', 'blazing-feedback' ) );
		}

		$response = $this->ai->call_api( $system_prompt, $user_prompt );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$result = json_decode( $response, true );
		$this->log_ai_action( $foundation->id, 'identity', 'enrich', $target, $user_prompt, $result, 0.85 );

		return array(
			'target'     => $target,
			'data'       => $result,
			'confidence' => 0.85,
		);
	}

	/**
	 * Enrichir le socle Offre
	 *
	 * @param BZMI_Foundation $foundation   Fondation.
	 * @param string          $target       Cible.
	 * @param array           $context      Contexte.
	 * @param string          $company_mode Mode entreprise.
	 * @return array|WP_Error
	 */
	private function enrich_offer( $foundation, $target, $context, $company_mode ) {
		$system_prompt = $this->build_system_prompt( $context, $company_mode );

		if ( 'value_proposition' === $target ) {
			$user_prompt = "Génère une proposition de valeur unique pour cette offre. Utilise le framework:
- Pour [cible]
- Qui [besoin]
- Notre [solution]
- Apporte [bénéfice clé]
- Contrairement à [alternative]

Réponds en JSON avec 'statement' et 'breakdown'.";
		} elseif ( 'differentiation' === $target ) {
			$user_prompt = "Analyse le positionnement et génère:
- 3-5 points de différenciation uniques
- Arguments clés vs concurrents
- Niche ou segment idéal

Réponds en JSON.";
		} elseif ( 'competitor_analysis' === $target ) {
			$user_prompt = "Analyse les concurrents identifiés et génère:
- Forces et faiblesses de chacun
- Opportunités de marché non exploitées
- Menaces principales
- Recommandations de positionnement

Réponds en JSON.";
		} else {
			return new WP_Error( 'invalid_target', __( 'Cible invalide.', 'blazing-feedback' ) );
		}

		$response = $this->ai->call_api( $system_prompt, $user_prompt );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$result = json_decode( $response, true );
		$this->log_ai_action( $foundation->id, 'offer', 'enrich', $target, $user_prompt, $result, 0.85 );

		return array(
			'target'     => $target,
			'data'       => $result,
			'confidence' => 0.85,
		);
	}

	/**
	 * Enrichir le socle Expérience
	 *
	 * @param BZMI_Foundation $foundation   Fondation.
	 * @param string          $target       Cible.
	 * @param array           $context      Contexte.
	 * @param string          $company_mode Mode entreprise.
	 * @return array|WP_Error
	 */
	private function enrich_experience( $foundation, $target, $context, $company_mode ) {
		$system_prompt = $this->build_system_prompt( $context, $company_mode );

		if ( 'journey_optimization' === $target ) {
			$user_prompt = "Analyse les parcours utilisateurs et génère:
- Points de friction identifiés
- Opportunités d'amélioration par étape
- Quick wins à implémenter
- Améliorations long terme

Réponds en JSON.";
		} elseif ( 'channel_messages' === $target ) {
			$user_prompt = "Génère des messages clés adaptés pour chaque canal:
- Message principal
- Ton adapté au canal
- CTA recommandé
- Formats suggérés

Réponds en JSON avec un objet par canal.";
		} elseif ( 'touchpoint_mapping' === $target ) {
			$user_prompt = "Mappe les points de contact optimaux pour chaque étape du parcours:
- Canal recommandé
- Type de contenu
- Objectif du touchpoint
- KPI suggéré

Réponds en JSON.";
		} else {
			return new WP_Error( 'invalid_target', __( 'Cible invalide.', 'blazing-feedback' ) );
		}

		$response = $this->ai->call_api( $system_prompt, $user_prompt );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$result = json_decode( $response, true );
		$this->log_ai_action( $foundation->id, 'experience', 'enrich', $target, $user_prompt, $result, 0.85 );

		return array(
			'target'     => $target,
			'data'       => $result,
			'confidence' => 0.85,
		);
	}

	/**
	 * Enrichir le socle Exécution
	 *
	 * @param BZMI_Foundation $foundation   Fondation.
	 * @param string          $target       Cible.
	 * @param array           $context      Contexte.
	 * @param string          $company_mode Mode entreprise.
	 * @return array|WP_Error
	 */
	private function enrich_execution( $foundation, $target, $context, $company_mode ) {
		$system_prompt = $this->build_system_prompt( $context, $company_mode );

		if ( 'risk_assessment' === $target ) {
			$user_prompt = "Analyse le projet et identifie:
- Risques techniques (probabilité, impact, mitigation)
- Risques organisationnels
- Risques de délai
- Risques budgétaires
- Plan de contingence recommandé

Réponds en JSON.";
		} elseif ( 'effort_estimation' === $target ) {
			$user_prompt = "Estime l'effort nécessaire:
- Charge par livrable (jours/homme)
- Ressources recommandées
- Planning suggéré
- Buffer recommandé

Réponds en JSON.";
		} elseif ( 'gdpr_checklist' === $target ) {
			$user_prompt = "Génère une checklist RGPD basée sur le projet:
- Données personnelles concernées
- Base légale recommandée
- Consentements nécessaires
- Durées de rétention
- Mesures de sécurité

Réponds en JSON.";
		} else {
			return new WP_Error( 'invalid_target', __( 'Cible invalide.', 'blazing-feedback' ) );
		}

		$response = $this->ai->call_api( $system_prompt, $user_prompt );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$result = json_decode( $response, true );
		$this->log_ai_action( $foundation->id, 'execution', 'enrich', $target, $user_prompt, $result, 0.85 );

		return array(
			'target'     => $target,
			'data'       => $result,
			'confidence' => 0.85,
		);
	}

	/**
	 * Construire le prompt système
	 *
	 * @param array  $context      Contexte de la fondation.
	 * @param string $company_mode Mode entreprise.
	 * @return string
	 */
	private function build_system_prompt( $context, $company_mode ) {
		$mode_instruction = 'creation' === $company_mode
			? "L'entreprise est en création. Sois proactif, propose des idées créatives et guide le processus de définition."
			: "L'entreprise existe déjà. Analyse l'existant, identifie les forces et propose des optimisations.";

		return "Tu es un expert en stratégie de marque, marketing et UX. Tu aides à construire des fondations de marque solides.

{$mode_instruction}

Contexte de la marque:
" . wp_json_encode( $context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . "

Règles:
- Réponds toujours en JSON valide
- Sois concis mais complet
- Adapte le ton au contexte de la marque
- Propose des éléments actionnables";
	}

	/**
	 * Obtenir les prompts pour un champ
	 *
	 * @param string $socle        Nom du socle.
	 * @param string $field        Nom du champ.
	 * @param string $company_mode Mode entreprise.
	 * @return array|null
	 */
	private function get_field_prompts( $socle, $field, $company_mode ) {
		$prompts = array(
			'identity' => array(
				'mission' => array(
					'user'   => 'Génère 3 propositions de mission pour cette marque. Chaque mission doit être impactante, mémorable et refléter la raison d\'être de l\'entreprise.',
					'format' => 'array',
				),
				'values' => array(
					'user'   => 'Propose 5-7 valeurs fondamentales qui pourraient guider cette marque. Pour chaque valeur, donne un mot-clé et une courte explication.',
					'format' => 'array',
				),
				'promise' => array(
					'user'   => 'Génère 3 propositions de promesse de marque. Chaque promesse doit être claire, différenciante et tenir en une phrase.',
					'format' => 'array',
				),
			),
			'offer' => array(
				'value_proposition' => array(
					'user'   => 'Génère 2-3 propositions de valeur uniques pour cette offre.',
					'format' => 'array',
				),
				'benefits' => array(
					'user'   => 'Liste 5-8 bénéfices clients clés pour cette offre.',
					'format' => 'array',
				),
				'differentiation' => array(
					'user'   => 'Identifie 3-5 éléments différenciants pour cette offre par rapport à la concurrence.',
					'format' => 'array',
				),
			),
			'experience' => array(
				'key_messages' => array(
					'user'   => 'Génère 3-5 messages clés adaptés à ce canal et à l\'identité de la marque.',
					'format' => 'array',
				),
				'pain_points' => array(
					'user'   => 'Identifie 3-5 points de friction potentiels dans ce parcours utilisateur.',
					'format' => 'array',
				),
				'opportunities' => array(
					'user'   => 'Identifie 3-5 opportunités d\'amélioration pour ce parcours.',
					'format' => 'array',
				),
			),
		);

		return isset( $prompts[ $socle ][ $field ] ) ? $prompts[ $socle ][ $field ] : null;
	}

	/**
	 * Parser les suggestions de l'IA
	 *
	 * @param string $response Réponse brute.
	 * @param string $format   Format attendu.
	 * @return array
	 */
	private function parse_suggestions( $response, $format ) {
		$data = json_decode( $response, true );

		if ( ! $data ) {
			// Essayer de parser comme liste
			$lines = explode( "\n", $response );
			$data = array_filter( array_map( 'trim', $lines ) );
		}

		if ( 'array' === $format && ! is_array( $data ) ) {
			$data = array( $data );
		}

		return $data;
	}

	/**
	 * Logger une action IA
	 *
	 * @param int         $foundation_id ID de la fondation.
	 * @param string      $socle         Socle.
	 * @param string      $action        Action.
	 * @param string|null $target        Cible.
	 * @param string      $input         Input.
	 * @param mixed       $output        Output.
	 * @param float       $confidence    Score de confiance.
	 * @param string|null $error         Message d'erreur.
	 * @return void
	 */
	private function log_ai_action( $foundation_id, $socle, $action, $target, $input, $output, $confidence, $error = null ) {
		global $wpdb;

		$table = BZMI_Database::get_table_name( 'foundation_ai_logs' );

		$wpdb->insert(
			$table,
			array(
				'foundation_id'    => $foundation_id,
				'socle'            => $socle,
				'action'           => $action,
				'target_type'      => $target,
				'input'            => is_string( $input ) ? $input : wp_json_encode( $input ),
				'output'           => $output ? wp_json_encode( $output ) : null,
				'confidence_score' => $confidence,
				'error_message'    => $error,
				'created_by'       => get_current_user_id(),
				'created_at'       => current_time( 'mysql' ),
			)
		);
	}
}

/**
 * Helper pour enrichir une fondation
 *
 * @param string $socle         Nom du socle.
 * @param int    $foundation_id ID de la fondation.
 * @param string $target        Cible.
 * @return array|WP_Error
 */
function bzmi_foundation_ai_enrich( $socle, $foundation_id, $target = '' ) {
	$foundation = BZMI_Foundation::find( $foundation_id );
	if ( ! $foundation ) {
		return new WP_Error( 'not_found', __( 'Fondation introuvable.', 'blazing-feedback' ) );
	}

	$ai = new BZMI_Foundations_AI();
	return $ai->enrich( $foundation, $socle, $target );
}
