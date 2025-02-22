<?php
/**
 * Template file for the Divi requirements page.
 *
 * @since   3.0.0
 *
 * @author  The WP Squad <support@squadmodules.com>
 * @package DiviSquad
 * @var array|string $args Arguments passed to the template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

if ( wp_doing_ajax() ) {
	die( 'Access forbidden from AJAX request.' );
}

use DiviSquad\Core\Supports\Media\Image;

// Load the image class.
$divi_squad_image = new Image( divi_squad()->get_path( '/build/admin/images/logos' ) );

// Check if image is validated.
if ( is_wp_error( $divi_squad_image->is_path_validated() ) ) {
	return;
}

// Verify current plugin life type.
$divi_squad_plugin_life_type = divi_squad()->is_dev() ? 'nightly' : 'stable';

/**
 * Filter the plugin life type.
 *
 * @since 3.2.3
 *
 * @param string $divi_squad_plugin_life_type The plugin life type.
 */
$divi_squad_plugin_life_type = apply_filters( 'divi_squad_plugin_life_type', $divi_squad_plugin_life_type );

?>

<main id="squad-modules-app" class="squad-modules-app squad-components">
	<div class="app-wrapper">
		<div class="app-header">
			<div class="app-title">
				<div class="title-wrapper">
					<?php $divi_squad_requirements_logo = $divi_squad_image->get_image( 'divi-squad-default.png', 'png' ); ?>
					<?php if ( ! is_wp_error( $divi_squad_requirements_logo ) ) : ?>
						<img class='logo' alt='Divi Squad' src="<?php echo esc_url( $divi_squad_requirements_logo, array( 'data' ) ); ?>"/>
					<?php endif; ?>

					<h1 class="title">
						<?php esc_html_e( 'Divi Squad', 'squad-modules-for-divi' ); ?>
					</h1>

					<ul class='badges'>
						<?php
							/**
							 * Fires after the badges in the requirements page.
							 *
							 * @since 3.2.3
							 *
							 * @param string $divi_squad_plugin_life_type The plugin life type.
							 */
							do_action( 'divi_squad_menu_badges', $divi_squad_plugin_life_type );
						?>
					</ul>
				</div>
			</div>
		</div>
		<div class="wrapper-container">
			<div class="requirements-wrapper">
				<div class="requirements-content">
					<h2><?php esc_html_e( 'Requirements', 'squad-modules-for-divi' ); ?></h2>
					<div class="notice-container">
						<?php print_r( $args ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r ?>
					</div>
					<div class="requirements-info">
						<div class="requirements-section">
							<h3><?php esc_html_e( 'Minimum Requirements', 'squad-modules-for-divi' ); ?></h3>
							<ul>
								<li>
									<strong><?php esc_html_e( 'Divi Theme/Builder:', 'squad-modules-for-divi' ); ?></strong>
									<?php
									// translators: %s is the required version.
									echo esc_html( sprintf( __( 'Version %s or higher', 'squad-modules-for-divi' ), divi_squad()->get_option( 'RequiresDIVI', '4.14.0' ) ) );
									?>
								</li>
								<li>
									<strong><?php esc_html_e( 'WordPress:', 'squad-modules-for-divi' ); ?></strong>
									<?php
									// translators: %s is the required version.
									echo esc_html( sprintf( __( 'Version %s or higher', 'squad-modules-for-divi' ), '5.8' ) );
									?>
								</li>
								<li>
									<strong><?php esc_html_e( 'PHP:', 'squad-modules-for-divi' ); ?></strong>
									<?php
									// translators: %s is the required version.
									echo esc_html( sprintf( __( 'Version %s or higher', 'squad-modules-for-divi' ), '7.4' ) );
									?>
								</li>
							</ul>
						</div>

						<div class="requirements-section">
							<h3><?php esc_html_e( 'Need Help?', 'squad-modules-for-divi' ); ?></h3>
							<p>
								<?php esc_html_e( 'If you need assistance with installation or have questions, please visit our support center:', 'squad-modules-for-divi' ); ?>
							</p>
							<p>
								<a href="https://squadmodules.com/support" class="button button-primary squad-button fill-button" target="_blank" style="max-width: fit-content;">
									<?php esc_html_e( 'Visit Support Center', 'squad-modules-for-divi' ); ?>
								</a>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>
