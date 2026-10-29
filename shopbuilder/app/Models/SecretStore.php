<?php
/**
 * SecretStore class file.
 *
 * Persistence for encrypted credentials, kept out of `rtsb_settings` so the
 * settings array the admin UI is built from never contains a secret at all.
 *
 * @package RadiusTheme\SB\Models
 */

namespace RadiusTheme\SB\Models;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Class SecretStore
 *
 * A flat, provider-agnostic map of secret id to envelope, stored in its own
 * non-autoloaded option. Adding a provider means storing another key here; no
 * schema or storage change is required.
 *
 * Deliberately not built on DataModel: this option must never be autoloaded,
 * and it must never be reachable through the settings API that feeds the
 * browser.
 */
class SecretStore {

	/**
	 * Option name holding the encrypted secrets.
	 *
	 * @var string
	 */
	const OPTION = 'rtsb_secrets';

	/**
	 * Per-request cache of the decoded option.
	 *
	 * @var array|null
	 */
	private static $cache;

	/**
	 * All stored envelopes, keyed by secret id.
	 *
	 * @return array
	 */
	private static function all() {
		if ( null === self::$cache ) {
			$stored      = get_option( self::OPTION, [] );
			self::$cache = is_array( $stored ) ? $stored : [];
		}

		return self::$cache;
	}

	/**
	 * Write the map back, keeping autoload disabled.
	 *
	 * @param array $secrets Secret id to envelope map.
	 *
	 * @return bool
	 */
	private static function persist( array $secrets ) {
		self::$cache = $secrets;

		if ( false === get_option( self::OPTION, false ) ) {
			return add_option( self::OPTION, $secrets, '', 'no' );
		}

		return update_option( self::OPTION, $secrets, false );
	}

	/**
	 * Every stored secret id, mapped to its envelope.
	 *
	 * Exposed so callers that need to sweep the store - the unavailable-secret
	 * notice, for instance - do not have to know the provider ids in advance.
	 *
	 * @return array
	 */
	public static function all_ids() {
		return self::all();
	}

	/**
	 * Whether a secret is configured.
	 *
	 * @param string $id Secret id, e.g. `gemini_api_key`.
	 *
	 * @return bool
	 */
	public static function has( $id ) {
		$secrets = self::all();

		return isset( $secrets[ $id ] ) && is_string( $secrets[ $id ] ) && '' !== $secrets[ $id ];
	}

	/**
	 * Raw stored envelope. Server-side callers only.
	 *
	 * @param string $id Secret id.
	 *
	 * @return string
	 */
	public static function raw( $id ) {
		$secrets = self::all();

		return isset( $secrets[ $id ] ) && is_string( $secrets[ $id ] ) ? $secrets[ $id ] : '';
	}

	/**
	 * Decrypted secret for server-side use.
	 *
	 * Returns an empty string when the secret is unset or cannot be recovered,
	 * so callers can treat "not configured" and "key unavailable" alike without
	 * having to handle an exception.
	 *
	 * @param string $id Secret id.
	 *
	 * @return string
	 */
	public static function get( $id ) {
		$raw = self::raw( $id );

		if ( '' === $raw ) {
			return '';
		}

		$opened = Secret::open( $raw );

		if ( null === $opened ) {
			return '';
		}

		/*
		 * Read-and-migrate. A value opened with a key that is no longer the
		 * preferred one is rewritten under the current key here, in the
		 * persistence layer, so the crypto primitives stay free of hidden
		 * database writes. Repeating it is harmless: the second read finds the
		 * value already current and does nothing.
		 */
		if ( ! $opened['current'] ) {
			self::rekey( $id, $opened['plaintext'] );
		}

		return $opened['plaintext'];
	}

	/**
	 * Rewrite a secret under the current preferred key.
	 *
	 * Only ever called with a plaintext this class has just recovered itself.
	 * The value is held in memory for the length of the call and never
	 * returned to a caller that did not already have it. Nothing is logged: not
	 * the plaintext, not either envelope, not the key.
	 *
	 * A failure leaves the existing envelope untouched, so a runtime that
	 * cannot encrypt degrades to "still readable with the old key" rather than
	 * losing the secret.
	 *
	 * @param string $id        Secret id.
	 * @param string $plaintext Recovered secret.
	 *
	 * @return bool Whether the stored envelope was replaced.
	 */
	public static function rekey( $id, $plaintext ) {
		if ( ! is_string( $plaintext ) || '' === $plaintext || Secret::is_sentinel( $plaintext ) ) {
			return false;
		}

		$envelope = Secret::encrypt( $plaintext );

		if ( null === $envelope ) {
			return false;
		}

		return self::set_envelope( $id, $envelope );
	}

	/**
	 * Whether a stored secret can currently be recovered.
	 *
	 * False both when nothing is stored and when an envelope exists that no
	 * available key opens; callers that need to tell those apart should use
	 * has() alongside this.
	 *
	 * @param string $id Secret id.
	 *
	 * @return bool
	 */
	public static function is_decryptable( $id ) {
		$raw = self::raw( $id );

		return '' !== $raw && Secret::is_decryptable( $raw );
	}

	/**
	 * Whether a secret is stored but unreadable with the key material on hand.
	 *
	 * @param string $id Secret id.
	 *
	 * @return bool
	 */
	public static function is_unavailable( $id ) {
		return self::has( $id ) && ! self::is_decryptable( $id );
	}

	/**
	 * Encrypt and store a secret.
	 *
	 * The existing value is left untouched when encryption is impossible, so a
	 * runtime without the Sodium API can never replace a protected secret with
	 * an unprotected one, and can never silently wipe a working secret.
	 *
	 * @param string $id        Secret id.
	 * @param string $plaintext Secret to store.
	 *
	 * @return bool Whether the secret was stored.
	 */
	public static function set( $id, $plaintext ) {
		if ( ! is_string( $id ) || '' === $id || ! is_string( $plaintext ) || '' === $plaintext ) {
			return false;
		}

		if ( Secret::is_sentinel( $plaintext ) ) {
			return false;
		}

		$envelope = Secret::encrypt( $plaintext );

		if ( null === $envelope ) {
			return false;
		}

		$secrets        = self::all();
		$secrets[ $id ] = $envelope;

		return self::persist( $secrets );
	}

	/**
	 * Store an already-encrypted envelope verbatim.
	 *
	 * Only the migration path uses this, so a value encrypted once is not
	 * re-encrypted on its way into storage.
	 *
	 * @param string $id       Secret id.
	 * @param string $envelope Envelope produced by Secret::encrypt().
	 *
	 * @return bool
	 */
	public static function set_envelope( $id, $envelope ) {
		if ( ! is_string( $id ) || '' === $id || ! Secret::is_encrypted( $envelope ) ) {
			return false;
		}

		$secrets        = self::all();
		$secrets[ $id ] = $envelope;

		return self::persist( $secrets );
	}

	/**
	 * Delete a stored secret.
	 *
	 * @param string $id Secret id.
	 *
	 * @return bool
	 */
	public static function delete( $id ) {
		$secrets = self::all();

		if ( ! array_key_exists( $id, $secrets ) ) {
			return true;
		}

		unset( $secrets[ $id ] );

		return self::persist( $secrets );
	}

	/**
	 * Drop the per-request cache. Used by tests and after migration.
	 *
	 * @return void
	 */
	public static function flush() {
		self::$cache = null;
	}
}
