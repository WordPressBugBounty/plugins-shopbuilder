<?php
/**
 * Secret class file.
 *
 * Authenticated encryption for credentials that have to be recovered later,
 * such as AI provider API keys. Uses the Sodium API WordPress guarantees
 * (native extension, or core's bundled sodium_compat polyfill), so nothing is
 * bundled and no Composer dependency is added.
 *
 * @package RadiusTheme\SB\Models
 */

namespace RadiusTheme\SB\Models;

use Throwable;
use SodiumException;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Class Secret
 *
 * Owns encryption, decryption, key acquisition, key identification, envelope
 * parsing, encryption detection, configured-state helpers and safe failure.
 *
 * Nothing in this class ever writes a secret, a key or a ciphertext to a log,
 * an exception message or a return value destined for the browser.
 */
class Secret {

	/**
	 * Envelope prefix, including the format version.
	 *
	 * @var string
	 */
	const PREFIX = 'rtsb:v1:';

	/**
	 * Namespace every envelope version shares.
	 *
	 * Anything carrying this but not a readable version is an envelope this
	 * build cannot open - never plaintext to be passed to a provider.
	 *
	 * @var string
	 */
	const NAMESPACE_PREFIX = 'rtsb:';

	/**
	 * Option holding the plugin-owned key. Never autoloaded, never exposed
	 * through the settings API.
	 *
	 * @var string
	 */
	const KEY_OPTION = 'rtsb_secret_key';

	/**
	 * Submitted by the browser to mean "keep whatever is already stored".
	 *
	 * Never a valid secret: `is_sentinel()` rejects it before persistence, so
	 * it can never itself become the stored value.
	 *
	 * @var string
	 */
	const KEEP = '__RTSB_SECRET_KEEP__';

	/**
	 * Submitted by the browser to mean "delete the stored secret".
	 *
	 * @var string
	 */
	const CLEAR = '__RTSB_SECRET_CLEAR__';

	/**
	 * Placeholder shown in place of a configured secret.
	 *
	 * @var string
	 */
	const MASK = '••••••••';

	/**
	 * Memoised raw key material, so the option is read once per request.
	 *
	 * @var string|null
	 */
	private static $key;

	/**
	 * Whether the runtime can perform authenticated encryption.
	 *
	 * The Sodium API is guaranteed from WordPress 5.2 upwards — core requires
	 * `wp-includes/sodium_compat/autoload.php` from `compat.php` whenever the
	 * native extension is missing, long before plugins load. This guard exists
	 * only so a pathologically broken install degrades to "secret unavailable"
	 * instead of fataling.
	 *
	 * @return bool
	 */
	public static function is_available() {
		return function_exists( 'sodium_crypto_secretbox' )
			&& function_exists( 'sodium_crypto_secretbox_open' )
			&& function_exists( 'sodium_crypto_generichash' )
			&& function_exists( 'random_bytes' )
			&& defined( 'SODIUM_CRYPTO_SECRETBOX_NONCEBYTES' );
	}

	/**
	 * Whether a value is one of the browser-facing control sentinels.
	 *
	 * @param mixed $value Candidate value.
	 *
	 * @return bool
	 */
	public static function is_sentinel( $value ) {
		return is_string( $value ) && in_array( $value, [ self::KEEP, self::CLEAR, self::MASK ], true );
	}

	/**
	 * Whether a stored value carries this class's envelope.
	 *
	 * @param mixed $value Stored value.
	 *
	 * @return bool
	 */
	public static function is_encrypted( $value ) {
		return is_string( $value ) && 0 === strpos( $value, self::PREFIX );
	}

	/**
	 * Encrypt a plaintext secret into a storable envelope.
	 *
	 * Idempotent: an already-encrypted value is returned untouched, so a
	 * settings round-trip (for example `Installation::set_default_options()`)
	 * can never double-encrypt.
	 *
	 * @param string $plaintext Secret to protect.
	 *
	 * @return string|null Envelope, or null when the secret cannot be protected.
	 */
	public static function encrypt( $plaintext ) {
		if ( ! is_string( $plaintext ) || '' === $plaintext ) {
			return null;
		}

		if ( self::is_encrypted( $plaintext ) ) {
			return $plaintext;
		}

		if ( self::is_sentinel( $plaintext ) || ! self::is_available() ) {
			return null;
		}

		$key = self::key();

		if ( null === $key ) {
			return null;
		}

		try {
			$nonce      = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
			$ciphertext = sodium_crypto_secretbox( $plaintext, $nonce, $key );
		} catch ( SodiumException $e ) {
			return null;
		} catch ( Throwable $e ) {
			return null;
		}

		return self::PREFIX . self::key_id( $key ) . ':' . base64_encode( $nonce . $ciphertext ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Recover a secret for server-side use.
	 *
	 * A value without the envelope is returned verbatim: installations that
	 * have not been migrated yet still hold plaintext, and they must keep
	 * working. Every failure mode collapses to null — "secret unavailable" —
	 * rather than an exception or a partial value.
	 *
	 * @param mixed $value Stored value.
	 *
	 * @return string|null Plaintext, or null when unavailable.
	 */
	public static function decrypt( $value ) {
		$opened = self::open( $value );

		return null === $opened ? null : $opened['plaintext'];
	}

	/**
	 * Recover a secret and report which key opened it.
	 *
	 * Purely cryptographic: it reads key material but never writes anything.
	 * The `current` flag tells the persistence layer whether the value is
	 * still protected by the preferred key, which is what drives the lazy
	 * re-key in SecretStore.
	 *
	 * @param mixed $value Stored value.
	 *
	 * @return array|null { @type string $plaintext, @type bool $current } or null when unavailable.
	 */
	public static function open( $value ) {
		if ( ! is_string( $value ) || '' === $value ) {
			return null;
		}

		if ( ! self::is_encrypted( $value ) ) {
			/*
			 * An envelope from a format version this build cannot read - for
			 * example after a downgrade - must fail closed. Returning it as
			 * plaintext would hand ciphertext to a provider as if it were the
			 * credential.
			 */
			if ( 0 === strpos( $value, self::NAMESPACE_PREFIX ) ) {
				return null;
			}

			if ( self::is_sentinel( $value ) ) {
				return null;
			}

			// Legacy plaintext, pre-migration. There is no envelope to re-key;
			// moving it into the store is the migration's job, not this one's.
			return [
				'plaintext' => $value,
				'current'   => true,
			];
		}

		$parts = self::parse( $value );

		if ( null === $parts || ! self::is_available() ) {
			return null;
		}

		/*
		 * Only the candidate whose id matches the envelope is tried. Sweeping
		 * every key would turn a mismatch into a guessing loop and would blur
		 * "wrong key" into "tampered payload"; both must stay distinguishable
		 * and both must fail closed.
		 */
		$candidate = null;

		foreach ( self::candidate_keys() as $entry ) {
			if ( hash_equals( $entry['id'], $parts['key_id'] ) ) {
				$candidate = $entry;
				break;
			}
		}

		if ( null === $candidate ) {
			return null;
		}

		try {
			$nonce      = substr( $parts['payload'], 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
			$ciphertext = substr( $parts['payload'], SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
			$plaintext  = sodium_crypto_secretbox_open( $ciphertext, $nonce, $candidate['key'] );
		} catch ( SodiumException $e ) {
			return null;
		} catch ( Throwable $e ) {
			return null;
		}

		// Wrong nonce, truncated or tampered payload all land here.
		if ( ! is_string( $plaintext ) ) {
			return null;
		}

		return [
			'plaintext' => $plaintext,
			'current'   => (bool) $candidate['current'],
		];
	}

	/**
	 * Whether a stored value can be recovered with the key material on hand.
	 *
	 * @param mixed $value Stored value.
	 *
	 * @return bool
	 */
	public static function is_decryptable( $value ) {
		return null !== self::open( $value );
	}

	/**
	 * Key material that already exists, newest preference first.
	 *
	 * Deliberately never creates a key. Generating one here would mean an
	 * attempt to read an old envelope could mint a replacement key, and the
	 * envelope that could not be opened a moment ago would become permanently
	 * unopenable. Creation stays in key(), which runs when the caller actually
	 * needs a key to encrypt with.
	 *
	 * @return array List of { @type string $id, @type string $key, @type bool $current }.
	 */
	private static function candidate_keys() {
		/*
		 * key() is deliberately not used here: it creates the plugin-owned key
		 * when none exists. Resolving the two sources directly keeps this path
		 * strictly read-only while preserving the same precedence.
		 */
		$constant = self::key_from_constant();
		$option   = self::existing_option_key();

		$candidates = [];

		if ( null !== $constant ) {
			$candidates[] = [
				'id'      => self::key_id( $constant ),
				'key'     => $constant,
				'current' => true,
			];
		}

		if ( null !== $option && ( null === $constant || ! hash_equals( $option, $constant ) ) ) {
			$candidates[] = [
				'id'      => self::key_id( $option ),
				'key'     => $option,
				// Current only when no valid constant outranks it.
				'current' => null === $constant,
			];
		}

		return $candidates;
	}

	/**
	 * Read the plugin-owned key without creating one.
	 *
	 * @return string|null Raw 32-byte key, or null when the option is absent or unusable.
	 */
	private static function existing_option_key() {
		$stored = get_option( self::KEY_OPTION, '' );

		if ( ! is_string( $stored ) || '' === $stored ) {
			return null;
		}

		$decoded = base64_decode( $stored, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

		return is_string( $decoded ) && 32 === strlen( $decoded ) ? $decoded : null;
	}

	/**
	 * Split an envelope into its key id and binary payload.
	 *
	 * @param string $value Envelope.
	 *
	 * @return array|null { @type string $key_id, @type string $payload } or null when malformed.
	 */
	private static function parse( $value ) {
		$body  = substr( $value, strlen( self::PREFIX ) );
		$split = strpos( (string) $body, ':' );

		if ( false === $split || 0 === $split ) {
			return null;
		}

		$key_id  = substr( $body, 0, $split );
		$encoded = substr( $body, $split + 1 );

		if ( ! preg_match( '/^[a-f0-9]{8,64}$/', $key_id ) || '' === $encoded ) {
			return null;
		}

		$payload = base64_decode( $encoded, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

		if ( ! is_string( $payload ) ) {
			return null;
		}

		// Nonce plus at least the Poly1305 authentication tag.
		$minimum = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES;

		if ( strlen( $payload ) <= $minimum ) {
			return null;
		}

		return [
			'key_id'  => $key_id,
			'payload' => $payload,
		];
	}

	/**
	 * Stable, non-reversible fingerprint identifying the key in use.
	 *
	 * Stored in the envelope so a changed key source is detected rather than
	 * producing garbage. Domain-separated and truncated: it identifies a key,
	 * it is not usable as one.
	 *
	 * @param string $key Raw 32-byte key.
	 *
	 * @return string 16 lowercase hex characters.
	 */
	public static function key_id( $key ) {
		if ( ! function_exists( 'sodium_crypto_generichash' ) ) {
			return str_repeat( '0', 16 );
		}

		return substr( bin2hex( sodium_crypto_generichash( 'rtsb-secret-key-id:v1:' . $key, '', 16 ) ), 0, 16 );
	}

	/**
	 * Acquire the 32-byte encryption key.
	 *
	 * Precedence, deliberately excluding `wp_salt()`: that function is
	 * pluggable and runs through the `salt` filter, so any other plugin could
	 * change it and silently strand every stored secret.
	 *
	 * 1. `RTSB_SECRET_KEY`, when it parses to exactly 32 bytes.
	 * 2. A plugin-owned random key in a dedicated non-autoloaded option.
	 *
	 * @return string|null Raw 32-byte key, or null when none can be obtained.
	 */
	public static function key() {
		if ( null !== self::$key ) {
			return '' === self::$key ? null : self::$key;
		}

		$key = self::key_from_constant();

		if ( null === $key ) {
			$key = self::key_from_option();
		}

		self::$key = null === $key ? '' : $key;

		return $key;
	}

	/**
	 * Parse `RTSB_SECRET_KEY` if it is defined and well formed.
	 *
	 * Two formats are accepted, both carrying a full 256-bit key:
	 * 64 hexadecimal characters, or Base64 that decodes to exactly 32 bytes.
	 * Anything else is rejected rather than stretched into a key, so a
	 * truncated or typo'd constant cannot silently weaken the cipher. A
	 * rejected constant falls through to the plugin-owned key; the envelope's
	 * key id then reports the mismatch instead of returning wrong plaintext.
	 *
	 * @return string|null Raw 32-byte key, or null when absent or invalid.
	 */
	private static function key_from_constant() {
		if ( ! defined( 'RTSB_SECRET_KEY' ) ) {
			return null;
		}

		$raw = constant( 'RTSB_SECRET_KEY' );

		if ( ! is_string( $raw ) ) {
			return null;
		}

		$raw = trim( $raw );

		if ( 64 === strlen( $raw ) && preg_match( '/^[a-fA-F0-9]{64}$/', $raw ) ) {
			$decoded = hex2bin( strtolower( $raw ) );

			return is_string( $decoded ) && 32 === strlen( $decoded ) ? $decoded : null;
		}

		$decoded = base64_decode( $raw, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

		return is_string( $decoded ) && 32 === strlen( $decoded ) ? $decoded : null;
	}

	/**
	 * Read, or create once, the plugin-owned random key.
	 *
	 * Stored Base64 encoded in its own option with autoload disabled, so it is
	 * never pulled into `alloptions` on front-end requests and never travels
	 * through the settings API.
	 *
	 * @return string|null Raw 32-byte key, or null when one cannot be created.
	 */
	private static function key_from_option() {
		$existing = self::existing_option_key();

		if ( null !== $existing ) {
			return $existing;
		}

		if ( ! function_exists( 'random_bytes' ) ) {
			return null;
		}

		try {
			$key = random_bytes( 32 );
		} catch ( Throwable $e ) {
			return null;
		}

		$encoded = base64_encode( $key ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

		// add_option() so a concurrent creator cannot overwrite a key that is
		// already protecting stored data; update_option() only for the repair
		// case where the option exists but holds an unusable value.
		if ( ! add_option( self::KEY_OPTION, $encoded, '', 'no' ) ) {
			$existing = get_option( self::KEY_OPTION, '' );
			$decoded  = is_string( $existing ) ? base64_decode( $existing, true ) : false; // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

			if ( is_string( $decoded ) && 32 === strlen( $decoded ) ) {
				return $decoded;
			}

			update_option( self::KEY_OPTION, $encoded, false );
		}

		return $key;
	}

	/**
	 * Drop the memoised key.
	 *
	 * Used by tests and by the key-repair path; not needed in normal request
	 * flow, where the key is read at most once.
	 *
	 * @return void
	 */
	public static function flush() {
		self::$key = null;
	}

	/**
	 * Browser-safe description of a stored secret.
	 *
	 * Returns metadata only. Neither the plaintext nor the ciphertext is
	 * included, by construction rather than by filtering.
	 *
	 * @param mixed $stored Stored value.
	 *
	 * @return array { @type bool $configured, @type string $value }
	 */
	public static function state( $stored ) {
		$present = is_string( $stored ) && '' !== $stored && ! self::is_sentinel( $stored );

		if ( ! $present ) {
			return [
				'configured'  => false,
				'unavailable' => false,
				'value'       => '',
			];
		}

		/*
		 * A stored value that no key on hand can open must not be advertised as
		 * configured. Reporting it that way is how a key-source change turns
		 * into a silent outage: the screen says the credential is set while the
		 * provider receives nothing.
		 *
		 * `value` stays empty so the field offers an input the administrator can
		 * type into. The ciphertext is deliberately left on disk - the original
		 * key may come back - and is never surfaced here.
		 */
		if ( ! self::is_decryptable( $stored ) ) {
			return [
				'configured'  => false,
				'unavailable' => true,
				'value'       => '',
			];
		}

		return [
			'configured'  => true,
			'unavailable' => false,
			'value'       => self::KEEP,
		];
	}
}
