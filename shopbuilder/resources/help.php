<?php
// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}
/**
 * Get Help
 */
$iframe  = 'https://www.youtube.com/embed/videoseries?si=BRbMu5oVNJvsOtPY&amp;list=PLSR3AlpVWfs6GVZtNTXZpAfjHimBGNvCG';
$pro     = 'https://www.radiustheme.com/downloads/woocommerce-bundle/';
$doc     = 'https://www.radiustheme.com/docs/shopbuilder/getting-started/installation/';
$contact = 'https://www.radiustheme.com/contact/';
$fb      = 'https://www.facebook.com/groups/234799147426640/';
$rt      = 'https://www.radiustheme.com/';
$review  = 'https://wordpress.org/support/plugin/shopbuilder/reviews/?filter=5#new-post';
$has_pro = rtsb()->has_pro();

?>
	<style>
	.rtsb-help-wrapper {
		width: 60%;
		margin: 0 auto;
	}
	.rt-document-box {
		background-color: #fff;
		-webkit-box-shadow: 0 1px 18px 0 rgba(0,0,0,.08);
		box-shadow: 0 1px 18px 0 rgba(0,0,0,.08);
		border-radius: 4px;
		padding: 30px 20px;
		display: -webkit-box;
		display: -ms-flexbox;
		display: flex;
	}
	.rt-document-box .rt-box-icon {
		height: 30px;
		width: 30px;
		background-color: #ecf1ff;
		border-radius: 50%;
		display: -webkit-box;
		display: -ms-flexbox;
		display: flex;
		-webkit-box-align: center;
		-ms-flex-align: center;
		align-items: center;
		-webkit-box-pack: center;
		-ms-flex-pack: center;
		justify-content: center;
		-ms-flex-line-pack: center;
		align-content: center;
		margin-right: 10px;
	}
	.rt-document-box .rt-box-icon i {
		font-size: 20px;
		color: #4360ef;
	}
	.rt-document-box .rt-box-content {
		-webkit-box-flex: 1;
		-ms-flex: 1;
		flex: 1;
		display:flex;
		flex-wrap:wrap;
	}
	.rt-document-box .rt-box-content.rtsb-feature-list{
		display:block;
	}
	.rt-document-box .rt-box-content .rt-box-title {
		margin: 0 0 12px 0;
		font-size: 20px;
		color: #000;
		font-weight: 600;
	}
	.rt-document-box+.rt-document-box {
		margin-top: 30px;
	}
	body .rt-admin-btn {
		text-align: center;
		display: inline-block;
		font-size: 15px;
		font-weight: 400;
		color: #5d3dfd;
		text-decoration: none;
		padding: 9px 18px;
		border-radius: 4px;
		position: relative;
		z-index: 2;
		line-height: 1.4;
		-webkit-transition: all .3s ease-in-out;
		transition: all .3s ease-in-out;
		height: auto;
		border: 2px solid #4360ef;
		margin-top: auto;
	}
	body .rt-admin-btn:hover {
		background-color: #4360ef;
		color: #fff;
		text-decoration: none;
	}
	.rtsb-help-section .embed-wrapper {
		position: relative;
		display: block;
		width: calc(100% - 40px);
		padding: 0;
		overflow: hidden;
	}
	.rtsb-help-section .embed-wrapper::before {
		display: block;
		content: "";
		padding-top: 56.25%;
	}
	.rtsb-help-section iframe {
		position: absolute;
		top: 0;
		bottom: 0;
		left: 0;
		width: 100%;
		height: 100%;
		border: 0;
	}
	.rtsb-help-wrapper .rt-document-box .rt-box-title {
		margin-bottom: 30px;
	}
	.rtsb-help-wrapper .rt-document-box .rt-box-icon {
		margin-top: -6px;
	}
	.rtsb-help-wrapper .rtsb-help-section {
		margin-top: 30px;
	}
	.rtsb-feature-list ul {
		column-count: 2;
		column-gap: 30px;
		margin-bottom: 0;
	}
	.rtsb-feature-list ul li {
		padding: 0 0 12px;
		margin-bottom: 0;
		width: 100%;
		font-size: 14px;
	}
	.rtsb-feature-list ul li:last-child {
		padding-bottom: 0;
	}
	.rtsb-feature-list ul li i {
		color: #4C6FFF;
	}
	.rtsb-pro-feature-content {
		display: flex;
		flex-wrap: wrap;
	}
	.rtsb-pro-feature-content .rt-document-box + .rt-document-box {
		margin-left: 30px;
	}
	.rtsb-pro-feature-content .rt-document-box {
		flex: 0 0 calc(33.3333% - 60px);
		margin-top: 30px;
	}
	.rtsb-testimonials {
		display: flex;
		flex-wrap: wrap;
	}
	.rtsb-testimonials .rtsb-testimonial + .rtsb-testimonial  {
		margin-left: 30px;
	}
	.rtsb-testimonials .rtsb-testimonial  {
		flex: 0 0 calc(50% - 30px);
		display:flex;
		flex-wrap:wrap;
	}
	.rtsb-testimonial .client-info {
		display: flex;
		flex-wrap: wrap;
		font-size: 14px;
		align-items: center;
	}
	.rtsb-testimonial .client-info img {
		width: 60px;
		height: 60px;
		object-fit: cover;
		border-radius: 50%;
		margin-right: 10px;
		border: 1px solid #ddd;
		-webkit-box-shadow: 0 1px 3px rgb(0, 0, 0, 0.2);
		box-shadow: 0 1px 3px rgb(0, 0, 0, 0.2);
	}
	.rtsb-testimonial .client-info .rtsb-star {
		color: #4C6FFF;
	}
	.rtsb-testimonial .client-info .client-name {
		display: block;
		color: #000;
		font-size: 16px;
		font-weight: 600;
		margin: 8px 0 0;
	}
	.rtsb-call-to-action {
		background-size: cover;
		background-repeat: no-repeat;
		background-position: left center;
		height: 150px;
		color: #ffffff;
		margin: 30px 0;
	}
	.rtsb-call-to-action a {
		color: inherit;
		display: flex;
		flex-wrap: wrap;
		width: 100%;
		height: 100%;
		flex: 1;
		align-items: center;
		font-size: 28px;
		font-weight: 700;
		text-decoration: none;
		margin-left: 130px;
		position: relative;
		outline: none;
		-webkit-box-shadow: none;
		box-shadow: none;
	}
	.rtsb-call-to-action a::before {
		content: "";
		position: absolute;
		left: -30px;
		top: 50%;
		height: 30%;
		width: 5px;
		background: #fff;
		-webkit-transform: translateY(-50%);
		transform: translateY(-50%);
	}
	.rtsb-call-to-action:hover a {
		text-decoration: underline;
	}
	.rtsb-testimonial p {
		text-align: justify;
		flex-basis:100%;
	}
	@media all and (max-width: 1400px) {
		.rtsb-help-wrapper {
			width: 80%;
		}
	}
	@media all and (max-width: 1025px) {
		.rtsb-pro-feature-content .rt-document-box {
			flex: 0 0 calc(50% - 55px)
		}
		.rtsb-pro-feature-content .rt-document-box + .rt-document-box + .rt-document-box {
			margin-left: 0;
		}
	}
	@media all and (max-width: 991px) {
		.rtsb-help-wrapper {
			width: calc(100% - 40px);
		}
		.rtsb-call-to-action a {
			justify-content: center;
			margin-left: auto;
			margin-right: auto;
			text-align: center;
		}
		.rtsb-call-to-action a::before {
			content: none;
		}
	}
	@media all and (max-width: 600px) {
		.rt-document-box .rt-box-content .rt-box-title {
			line-height: 28px;
		}
		.rtsb-help-section .embed-wrapper {
			width: 100%;
		}
		.rtsb-feature-list ul {
			column-count: 1;
		}
		.rtsb-feature-list ul li {
			width: 100%;
		}
		.rtsb-call-to-action a {
			padding-left: 25px;
			padding-right: 25px;
			font-size: 20px;
			line-height: 28px;
			width: 80%;
		}
		.rtsb-testimonials {
			display: block;
		}
		.rtsb-testimonials .rtsb-testimonial + .rtsb-testimonial {
			margin-left: 0;
			margin-top: 30px;
			padding-top: 30px;
			border-top: 1px solid #ddd;
		}
		.rtsb-pro-feature-content .rt-document-box {
			width: 100%;
			flex: auto;
		}
		.rtsb-pro-feature-content .rt-document-box + .rt-document-box {
			margin-left: 0;
		}

		.rtsb-help-wrapper .rt-document-box {
			display: block;
			position: relative;
		}

		.rtsb-help-wrapper .rt-document-box .rt-box-icon {
			position: absolute;
			left: 20px;
			top: 30px;
			margin-top: 0;
		}

		.rt-document-box .rt-box-content .rt-box-title {
			margin-left: 45px;
		}
	}
</style>
	<div class="rtsb-help-wrapper" >
		<div class="rtsb-help-section rt-document-box">
			<div class="rt-box-icon"><i class="dashicons dashicons-media-document"></i></div>
			<div class="rt-box-content">
				<h3 class="rt-box-title">Thank you for installing ShopBuilder</h3>
				<div class="embed-wrapper">
					<iframe src="<?php echo esc_url( $iframe ); ?>" title="Shop Builder" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
				</div>
			</div>
		</div>
		<?php if ( ! $has_pro ) { ?>
			<div class="rt-document-box">
			<div class="rt-box-icon"><i class="dashicons dashicons-megaphone"></i></div>
			<div class="rt-box-content rtsb-feature-list">
				<h3 class="rt-box-title">Pro Features</h3>
				<ul>
					<li><i class="dashicons dashicons-saved"></i> AJAX Product Filter Widget.</li>
					<li><i class="dashicons dashicons-saved"></i> Product Share – Single Product Widget.</li>
					<li><i class="dashicons dashicons-saved"></i> Sales Count – Single Product Widget.</li>
					<li><i class="dashicons dashicons-saved"></i> Flash Sale Countdown – Single Product Widget.</li>
					<li><i class="dashicons dashicons-saved"></i> Size Chart – Single Product Widget.</li>
					<li><i class="dashicons dashicons-saved"></i> Quick Checkout – Single Product Widget.</li>
					<li><i class="dashicons dashicons-saved"></i> Coupon Form – Checkout Widget.</li>
					<li><i class="dashicons dashicons-saved"></i> Order Details Table.</li>
					<li><i class="dashicons dashicons-saved"></i> Order Billing Address.</li>
					<li><i class="dashicons dashicons-saved"></i>  Order Shipping Address.</li>
					<li><i class="dashicons dashicons-saved"></i> Account Dashboard.</li>
					<li><i class="dashicons dashicons-saved"></i>  Account Orders.</li>
					<li><i class="dashicons dashicons-saved"></i> Account Billing Address.</li>
					<li><i class="dashicons dashicons-saved"></i> Account Edit / Details.</li>
					<li><i class="dashicons dashicons-saved"></i> Account Order Shipping.</li>
					<li><i class="dashicons dashicons-saved"></i> Account Order Billing.</li>
					<li><i class="dashicons dashicons-saved"></i> Edit Billing Address.</li>
					<li><i class="dashicons dashicons-saved"></i> Edit Shipping Address.</li>
					<li><i class="dashicons dashicons-saved"></i> Login Register Form.</li>
					<li><i class="dashicons dashicons-saved"></i> More Feature...</li>
				</ul>
			</div>
		</div>
			<div class="rtsb-call-to-action" style="background-image: url('<?php echo esc_url( rtsb()->get_assets_uri( 'images/admin-banner.jpg' ) ); ?>')">
				<a href="<?php echo esc_url( $pro ); ?>" target="_blank" class="rt-update-pro-btn">
					Update to Pro & Get More Features
				</a>
			</div>
		<?php } ?>
		<div class="rt-document-box">
			<div class="rt-box-icon"><i class="dashicons dashicons-thumbs-up"></i></div>
			<div class="rt-box-content">
				<h3 class="rt-box-title">Happy clients of the ShopBuilder</h3>
				<div class="rtsb-testimonials">
					<div class="rtsb-testimonial">
						<p>I strongly recommend this solution for creating unique design for your next WooCommerce store. In comparison with alternatives – ShopBuilder is the best. Support is amazing and fast. Thanks for this plugin!</p>
						<div class="client-info">
							<img src="<?php echo esc_url( rtsb()->get_assets_uri( 'images/admin-testimonial/review-clients-3.png' ) ); ?>">
							<div>
								<div class="rtsb-star">
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
								</div>
								<span class="client-name">codeorlov</span>
							</div>
						</div>
					</div>
					<div class="rtsb-testimonial">
						<p>I love this plugin. It’s simple but exactly what I was looking for. Easy install, quick response from the developer. Finally, an easy way to monetize my Shop.</p>
						<div class="client-info">
							<img src="<?php echo esc_url( rtsb()->get_assets_uri( 'images/admin-testimonial/review-clients-1.png' ) ); ?>">
							<div>
								<div class="rtsb-star">
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
									<i class="dashicons dashicons-star-filled"></i>
								</div>
								<span class="client-name">Mahabub Hasan</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="rtsb-pro-feature-content">
			<div class="rt-document-box">
				<div class="rt-box-icon"><i class="dashicons dashicons-media-document"></i></div>
				<div class="rt-box-content">
					<h3 class="rt-box-title">Documentation</h3>
					<p>Get started by spending some time with the documentation we included step by step process with screenshots with video.</p>
					<a href="<?php echo esc_url( $doc ); ?>" target="_blank" class="rt-admin-btn">Documentation</a>
				</div>
			</div>
			<div class="rt-document-box">
				<div class="rt-box-icon"><i class="dashicons dashicons-sos"></i></div>
				<div class="rt-box-content">
					<h3 class="rt-box-title">Need Help?</h3>
					<p>Stuck with something? Please create a
						<a href="<?php echo esc_url( $contact ); ?>">ticket here</a> or post on <a href="<?php echo esc_url( $fb ); ?>">facebook group</a>. For emergency case join our <a href="<?php echo esc_url( $rt ); ?>">live chat</a>.</p>
					<a href="<?php echo esc_url( $contact ); ?>" target="_blank" class="rt-admin-btn">Get Support</a>
				</div>
			</div>
			<div class="rt-document-box">
				<div class="rt-box-icon"><i class="dashicons dashicons-smiley"></i></div>
				<div class="rt-box-content">
					<h3 class="rt-box-title">Happy Our Work?</h3>
					<p>If you are happy with <strong>Food Menu</strong> plugin, please add a rating. It would be glad to us.</p>
					<a href="<?php echo esc_url( $review ); ?>" class="rt-admin-btn" target="_blank">Post Review</a>
				</div>
			</div>
		</div>
	</div>
