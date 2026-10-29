<?php

namespace RadiusTheme\SB\Helpers;

// Do not allow directly accessing this file.
use RadiusTheme\SB\Models\DataModel;
use RadiusTheme\SB\Models\ExtraSettings;
use RadiusTheme\SB\Models\Secret;
use RadiusTheme\SB\Models\SecretStore;
use RadiusTheme\SB\Models\TemplateSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Migration class.
 *
 * Runs one-off data migrations between plugin versions.
 */
class Migration {

	/**
	 * Version recorded the first time this installation ran the plugin.
	 *
	 * @var string|false
	 */
	private static $first_instilled;

	/**
	 * Hook the migration runner.
	 *
	 * @return void
	 */
	public static function init() {
		self::$first_instilled = ExtraSettings::instance()->get_option( 'first_instilled_version', false );
		add_action( 'init', [ __CLASS__, 'the_migration' ], 5 );
	}

	/**
	 * @return void
	 */
	public static function the_migration() {
		/*
		 * Runs ahead of the version short-circuit below, and ahead of the other
		 * steps' early returns. A credential sitting in plaintext is a live
		 * exposure, so it must not wait for an unrelated migration to finish
		 * first, and it must not be skipped on an install whose recorded
		 * version already matches. It is idempotent and returns immediately
		 * once there is nothing left to move.
		 */
		self::secrets_migration_v_3_4_3();

		if ( self::$first_instilled && version_compare( self::$first_instilled, RTSB_VERSION, '==' ) ) {
			return;
		}
		$v_2_0_4 = ExtraSettings::instance()->get_option( 'settings_migration_v_2_0_4', false );
		if ( ! $v_2_0_4 ) {
			self::settings_migration_plugin_v_2_0_4();
			ExtraSettings::instance()->set_option( 'settings_migration_v_2_0_4', RTSB_VERSION );
			return;
		}

		$v_2_1_16 = ExtraSettings::instance()->get_option( 'template_settings_migration_v_2_1_16', false );
		if ( ! $v_2_1_16 ) {
			self::template_settings_migration_plugin_v_2_1_16();
			ExtraSettings::instance()->set_option( 'template_settings_migration_v_2_1_16', RTSB_VERSION );
			return;
		}
	}

	/**
	 * Secret ids that may still hold plaintext in `rtsb_settings`.
	 *
	 * Shaped section id to block id to secret ids. Pro registers its own
	 * through the filter, so a new provider never needs this list edited here
	 * and Free stays functional on its own.
	 *
	 * @return array
	 */
	private static function plaintext_secret_map() {
		return (array) apply_filters(
			'rtsb/secrets/legacy_plaintext_map',
			[
				'general' => [
					'ai_implementation' => [ 'gpt_api_key', 'gemini_api_key', 'deepseek_api_key' ],
				],
			]
		);
	}

	/**
	 * Move plaintext credentials out of `rtsb_settings` into the encrypted store.
	 *
	 * Ordering is deliberate and the whole point of the routine: the encrypted
	 * copy is written and then read back before the plaintext is removed. If
	 * encryption is unavailable, or the write fails, or the read-back does not
	 * match, the plaintext is left exactly where it is - the installation keeps
	 * working on the legacy path and the migration retries on a later request.
	 *
	 * Idempotent: once a block holds no plaintext secrets, nothing is written.
	 * Nothing here is logged, and no secret appears in any return value.
	 *
	 * @return void
	 */
	public static function secrets_migration_v_3_4_3() {
		if ( ! Secret::is_available() ) {
			return;
		}

		foreach ( self::plaintext_secret_map() as $section_id => $blocks ) {
			$section = DataModel::source()->get_option( $section_id, [], false );

			if ( ! is_array( $section ) || empty( $blocks ) ) {
				continue;
			}

			$changed = false;

			foreach ( (array) $blocks as $block_id => $secret_ids ) {
				if ( empty( $section[ $block_id ] ) || ! is_array( $section[ $block_id ] ) ) {
					continue;
				}

				foreach ( (array) $secret_ids as $secret_id ) {
					if ( ! array_key_exists( $secret_id, $section[ $block_id ] ) ) {
						continue;
					}

					$plaintext = $section[ $block_id ][ $secret_id ];

					// Empty slot, an already-migrated envelope, or a sentinel that
					// leaked in: nothing to protect, so just stop carrying it.
					if ( ! is_string( $plaintext ) || '' === $plaintext
						|| Secret::is_encrypted( $plaintext ) || Secret::is_sentinel( $plaintext ) ) {
						unset( $section[ $block_id ][ $secret_id ] );
						$changed = true;

						continue;
					}

					if ( ! SecretStore::set( $secret_id, $plaintext ) ) {
						continue;
					}

					// Read back through the full decrypt path before destroying
					// the only remaining copy.
					if ( ! hash_equals( $plaintext, SecretStore::get( $secret_id ) ) ) {
						continue;
					}

					unset( $section[ $block_id ][ $secret_id ] );
					$changed = true;
				}
			}

			if ( $changed ) {
				DataModel::source()->set_option( $section_id, $section );
			}
		}
	}

	/**
	 * Drop a template option whose builder post no longer exists.
	 *
	 * @param string $name Option name ending in the builder post id.
	 *
	 * @return void
	 */
	private static function remove_option_for_missing_builder_v_2_0_4( $name ) {
		$array   = explode( '_', $name );
		$post_id = end( $array );
		if ( ! is_numeric( $post_id ) ) {
			return;
		}
		if ( get_post_status( $post_id ) ) {
			return;
		}
		TemplateSettings::instance()->delete_option( $name );
	}
	/**
	 * @return void
	 */
	public static function settings_migration_plugin_v_2_0_4() {
		global $wpdb;
		// Prefixes to search for.
		$prefixes = [
			'rtsb_tb_template',
			'rtsb_template_specific',
			'rtsb_template_for',
		];

		// Array to store matching options.
		// Loop through each prefix.
		foreach ( $prefixes as $prefix ) {
			// Run a direct database query to fetch option names and values.
			$query               = $wpdb->prepare( "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like( $prefix ) . '%' );
			$options_with_prefix = $wpdb->get_results( $query, OBJECT ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( $options_with_prefix ) {
				foreach ( $options_with_prefix as $option ) {
					$value = maybe_unserialize( $option->option_value );
					if ( ! empty( $value ) ) {
						 TemplateSettings::instance()->set_option( $option->option_name, $value );
					}
					delete_option( $option->option_name );
					self::remove_option_for_missing_builder_v_2_0_4( $option->option_name );
				}
			}
		}

		// Extra Setings.
		$extra_prefixes = [
			'rtsb_version',
			'rtsb_wishlist_db_version',
			'rtsb_compare_db_version',
			'rtsbpro_version',
		];
		// Array to store matching options.
		// Loop through each prefix.
		foreach ( $extra_prefixes as $prefix ) {
			// Run a direct database query to fetch option names and values.
			$query               = $wpdb->prepare( "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like( $prefix ) . '%' );
			$options_with_prefix = $wpdb->get_results( $query, OBJECT ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( $options_with_prefix ) {
				foreach ( $options_with_prefix as $option ) {
					$value = maybe_unserialize( $option->option_value );
					if ( ! empty( $value ) ) {
						ExtraSettings::instance()->set_option( $option->option_name, $value );
					}
					delete_option( $option->option_name );
				}
			}
		}
	}

	/**
	 * @return void
	 */
	private static function template_settings_migration_plugin_v_2_1_16() {
		$args     = [
			'post_type'   => BuilderFns::$post_type_tb,
			'numberposts' => -1,
			'fields'      => 'ids',
		];
		$posts_id = get_posts( $args );
		if ( ! empty( $posts_id ) ) {
			foreach ( $posts_id as $id ) {
				$template_type = BuilderFns::builder_type( $id );
				$options_name  = BuilderFns::option_name_by_template_id( $id );
				$product_ids   = TemplateSettings::instance()->get_option( $options_name );
				if ( 'product' === $template_type && ! empty( $product_ids ) ) {
					update_post_meta( $id, '_is_product_page_template_for', 'specific_products' );
				}
			}
		}
	}
}
