<?php
/**
 * Secret Unavailable Notice class.
 *
 * Tells an administrator that a stored API key can no longer be decrypted, so
 * the silent case - the settings screen looking healthy while the provider
 * receives nothing - cannot happen unannounced.
 *
 * @package RadiusTheme\SB
 */

namespace RadiusTheme\SB\Controllers\Admin\Notice;

use RadiusTheme\SB\Models\SecretStore;
use RadiusTheme\SB\Traits\SingletonTrait;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Secret Unavailable Notice class.
 */
class SecretUnavailable {

	use SingletonTrait;

	/**
	 * Class constructor.
	 */
	private function __construct() {
		add_action( 'admin_init', [ $this, 'maybe_add_notice' ] );
	}

	/**
	 * Queue the notice when at least one stored secret cannot be read.
	 *
	 * Cheap on the healthy path: the secret store is a single non-autoloaded
	 * option, and an installation with no stored credentials returns before
	 * any decryption is attempted.
	 *
	 * @return void
	 */
	public function maybe_add_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! $this->unavailable_count() ) {
			return;
		}

		add_action( 'admin_notices', [ $this, 'display_notice' ] );
	}

	/**
	 * Number of stored secrets that no available key can open.
	 *
	 * @return int
	 */
	private function unavailable_count() {
		$count = 0;

		foreach ( array_keys( SecretStore::all_ids() ) as $id ) {
			if ( SecretStore::is_unavailable( $id ) ) {
				++$count;
			}
		}

		return $count;
	}

	/**
	 * Display the admin notice.
	 *
	 * Says what happened and what to do about it. No key id, no ciphertext, no
	 * key material and no cryptographic detail: none of it helps the person
	 * reading this, and all of it is better left out of a page that gets
	 * screenshotted into support tickets.
	 *
	 * @return void
	 */
	public function display_notice() {
		$count = $this->unavailable_count();

		if ( ! $count ) {
			return;
		}

		$settings_url = admin_url( 'admin.php?page=rtsb-settings' );
		?>
		<div class="notice notice-warning">
			<p>
				<strong><?php esc_html_e( 'ShopBuilder:', 'shopbuilder' ); ?></strong>
				<?php
				/* translators: %d: number of stored API keys that cannot be read. */
				$message = _n(
					'%d saved API key can no longer be read on this site and needs to be entered again.',
					'%d saved API keys can no longer be read on this site and need to be entered again.',
					$count,
					'shopbuilder'
				);

				printf( esc_html( $message ), absint( $count ) );
				?>
			</p>
			<p>
				<?php
				esc_html_e(
					'This usually happens after a site move, or when the encryption key configured for this site changes. The saved value is still stored and will work again if the original configuration is restored, so nothing has been deleted.',
					'shopbuilder'
				);
				?>
			</p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( $settings_url ); ?>">
					<?php esc_html_e( 'Open ShopBuilder settings', 'shopbuilder' ); ?>
				</a>
			</p>
		</div>
		<?php
	}
}
