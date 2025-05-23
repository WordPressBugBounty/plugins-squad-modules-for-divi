<?php // phpcs:ignore WordPress.Files.FileName

/**
 * Asset Registration Trait
 *
 * Handles registration and enqueueing of scripts and styles.
 *
 * @since   3.3.0
 * @package DiviSquad
 */

namespace DiviSquad\Core\Traits\Assets;

use DiviSquad\Utils\WP as WPUtil;
use RuntimeException;

/**
 * Asset Registration Trait
 *
 * @since 3.3.0
 */
trait Registration {

	/**
	 * List of registered scripts
	 *
	 * @var array<string, array{handle: string, data: array{path: string, version: string, dependencies: array<string>}}>
	 */
	private array $registered_scripts = array();

	/**
	 * List of registered styles
	 *
	 * @var array<string, array{handle: string, data: array{path: string, version: string, dependencies: array<string>}, media: string}>
	 */
	private array $registered_styles = array();

	/**
	 * Register a script with WordPress
	 *
	 * @param string                                                                                                                          $handle Script identifier.
	 * @param array{file: string, path?: string, prod_file?: string, dev_file?: string, pattern?: string, ext?: string, deps?: array<string>} $config Asset configuration.
	 * @param array{in_footer?: bool, strategy?: 'defer'|'async'|null}                                                                        $args   Additional arguments.
	 */
	protected function register_wp_script( string $handle, array $config, array $args = array() ): bool {
		try {
			/**
			 * Filters whether to register a script or not.
			 *
			 * @since 3.4.0
			 *
			 * @param bool   $register Whether to register the script. Default true.
			 * @param string $handle   The handle name of the script.
			 * @param array  $config   The asset configuration.
			 * @param array  $args     Additional registration arguments.
			 */
			$should_register = (bool) apply_filters( 'divi_squad_should_register_script', true, $handle, $config, $args );

			if ( ! $should_register ) {
				return false;
			}

			$external    = $config['external'] ?? false;
			$no_prefix   = $config['no_prefix'] ?? false;
			$deps        = $config['deps'] ?? array();
			$asset_data  = $this->process_asset_config( $config, $deps );
			$full_handle = ( $no_prefix || $external ) ? $handle : $this->get_prefixed_handle( $handle );

			/**
			 * Filters the asset data after processing.
			 *
			 * @since 3.4.0
			 *
			 * @param array  $asset_data Processed asset data.
			 * @param string $handle     The handle name of the script.
			 * @param array  $config     The asset configuration.
			 */
			$asset_data = apply_filters( 'divi_squad_processed_script_asset_data', $asset_data, $handle, $config );

			$version   = $asset_data['version'];
			$in_footer = $args['in_footer'] ?? true;
			$strategy  = $args['strategy'] ?? ( $in_footer ? 'defer' : 'async' );

			/**
			 * Filters the strategy attribute for the script.
			 *
			 * @since 3.4.0
			 *
			 * @param string|null $strategy    The loading strategy ('defer', 'async', or null).
			 * @param string      $handle      The handle name of the script.
			 * @param string      $full_handle The full handle name of the script.
			 * @param array       $config      The asset configuration.
			 */
			$strategy = apply_filters( 'divi_squad_script_strategy', $strategy, $handle, $full_handle, $config );

			/**
			 * Filters the in_footer setting for the script.
			 *
			 * @since 3.4.0
			 *
			 * @param bool   $in_footer   Whether to load the script in the footer.
			 * @param string $handle      The handle name of the script.
			 * @param string $full_handle The full handle name of the script.
			 * @param array  $config      The asset configuration.
			 */
			$in_footer = apply_filters( 'divi_squad_script_in_footer', $in_footer, $handle, $full_handle, $config );

			/**
			 * Filters the dependencies for the script.
			 *
			 * @since 3.4.0
			 *
			 * @param array<string> $dependencies The dependencies for the script.
			 * @param string        $full_handle  The full handle name of the script.
			 * @param array         $config       The asset configuration.
			 */
			$dependencies = apply_filters( 'divi_squad_script_dependencies', $asset_data['dependencies'], $full_handle, $config );

			/**
			 * Filters the version for the script.
			 *
			 * @since 3.4.0
			 *
			 * @param string $version     The version of the script.
			 * @param string $full_handle The full handle name of the script.
			 * @param array  $config      The asset configuration.
			 */
			$version = apply_filters( 'divi_squad_script_version', $version, $full_handle, $config );

			/**
			 * Filters the URL for the script.
			 *
			 * @since 3.4.0
			 *
			 * @param string $url         The URL of the script.
			 * @param string $full_handle The full handle name of the script.
			 * @param array  $config      The asset configuration.
			 */
			$url = apply_filters( 'divi_squad_script_url', $asset_data['url'], $full_handle, $config );

			/**
			 * Fires before a script is registered
			 *
			 * @since 3.4.0
			 *
			 * @param string $full_handle  The full handle name of the script.
			 * @param string $url          The URL of the script.
			 * @param array  $dependencies The dependencies for the script.
			 * @param string $version      The version of the script.
			 * @param array  $args         Additional args for wp_register_script.
			 */
			do_action(
				'divi_squad_before_script_registered',
				$full_handle,
				$url,
				$dependencies,
				$version,
				array(
					'in_footer' => $in_footer,
					'strategy'  => $strategy,
				)
			);

			wp_register_script(
				$full_handle,
				$url,
				$dependencies,
				$version,
				array(
					'in_footer' => $in_footer,
					'strategy'  => $strategy,
				)
			);

			/**
			 * Filters the text domain for script localization.
			 *
			 * @since 3.4.0
			 *
			 * @param string $script_text_domain The text domain for script localization.
			 * @param string $full_handle        The full handle name of the script.
			 * @param array  $config             The asset configuration.
			 */
			$script_text_domain = apply_filters( 'divi_squad_script_text_domain', divi_squad()->get_name(), $full_handle, $config );

			WPUtil::set_script_translations( $full_handle, $script_text_domain );

			$this->registered_scripts[ $handle ] = array(
				'handle' => $full_handle,
				'data'   => $asset_data,
			);

			/**
			 * Fires after a script is registered
			 *
			 * @since 3.4.0
			 *
			 * @param string $full_handle Full handle name.
			 * @param array  $asset_data  Asset data.
			 */
			do_action( 'divi_squad_script_registered', $full_handle, $asset_data );

			return true;
		} catch ( RuntimeException $e ) {
			/**
			 * Fires when script registration fails
			 *
			 * @since 3.4.0
			 *
			 * @param string           $handle    Script handle.
			 * @param array            $config    Asset configuration.
			 * @param RuntimeException $exception The exception that occurred.
			 */
			do_action( 'divi_squad_script_registration_failed', $handle, $config, $e );

			divi_squad()->log_error( $e, sprintf( 'Failed to register script: %s', $handle ) );

			return false;
		}
	}

	/**
	 * Register a stylesheet with WordPress
	 *
	 * @param string                                                                                                                          $handle Style identifier.
	 * @param array{file: string, path?: string, prod_file?: string, dev_file?: string, pattern?: string, ext?: string, deps?: array<string>} $config Asset configuration.
	 * @param string                                                                                                                          $media  Media type.
	 */
	protected function register_wp_style( string $handle, array $config, string $media = 'all' ): bool {
		try {
			/**
			 * Filters whether to register a style or not.
			 *
			 * @since 3.4.0
			 *
			 * @param bool   $register Whether to register the style. Default true.
			 * @param string $handle   The handle name of the style.
			 * @param array  $config   The asset configuration.
			 * @param string $media    The media type.
			 */
			$should_register = (bool) apply_filters( 'divi_squad_should_register_style', true, $handle, $config, $media );

			if ( ! $should_register ) {
				return false;
			}

			$external    = $config['external'] ?? false;
			$no_prefix   = $config['no_prefix'] ?? false;
			$deps        = $config['deps'] ?? array();
			$asset_data  = $this->process_asset_config( $config, $deps );
			$full_handle = ( $no_prefix || $external ) ? $handle : $this->get_prefixed_handle( $handle );

			/**
			 * Filters the asset data after processing.
			 *
			 * @since 3.4.0
			 *
			 * @param array  $asset_data Processed asset data.
			 * @param string $handle     The handle name of the style.
			 * @param array  $config     The asset configuration.
			 */
			$asset_data = apply_filters( 'divi_squad_processed_style_asset_data', $asset_data, $handle, $config );

			/**
			 * Filters the dependencies for the style.
			 *
			 * @since 3.4.0
			 *
			 * @param array<string> $dependencies The dependencies for the style.
			 * @param string        $full_handle  The full handle name of the style.
			 * @param array         $config       The asset configuration.
			 */
			$dependencies = apply_filters( 'divi_squad_style_dependencies', $asset_data['dependencies'], $full_handle, $config );

			/**
			 * Filters the version for the style.
			 *
			 * @since 3.4.0
			 *
			 * @param string $version     The version of the style.
			 * @param string $full_handle The full handle name of the style.
			 * @param array  $config      The asset configuration.
			 */
			$version = apply_filters( 'divi_squad_style_version', $asset_data['version'], $full_handle, $config );

			/**
			 * Filters the media type for the style.
			 *
			 * @since 3.4.0
			 *
			 * @param string $media       The media type of the style.
			 * @param string $full_handle The full handle name of the style.
			 * @param array  $config      The asset configuration.
			 */
			$filtered_media = apply_filters( 'divi_squad_style_media', $media, $full_handle, $config );

			/**
			 * Filters the URL for the style.
			 *
			 * @since 3.4.0
			 *
			 * @param string $url         The URL of the style.
			 * @param string $full_handle The full handle name of the style.
			 * @param array  $config      The asset configuration.
			 */
			$url = apply_filters( 'divi_squad_style_url', $asset_data['url'], $full_handle, $config );

			/**
			 * Fires before a style is registered
			 *
			 * @since 3.4.0
			 *
			 * @param string $full_handle  The full handle name of the style.
			 * @param string $url          The URL of the style.
			 * @param array  $dependencies The dependencies for the style.
			 * @param string $version      The version of the style.
			 * @param string $media        The media type of the style.
			 */
			do_action( 'divi_squad_before_style_registered', $full_handle, $url, $dependencies, $version, $filtered_media );

			wp_register_style(
				$full_handle,
				$url,
				$dependencies,
				$version,
				$filtered_media
			);

			$this->registered_styles[ $handle ] = array(
				'handle' => $full_handle,
				'data'   => $asset_data,
				'media'  => $filtered_media,
			);

			/**
			 * Fires after a style is registered
			 *
			 * @since 3.4.0
			 *
			 * @param string $full_handle Full handle name.
			 * @param array  $asset_data  Asset data.
			 * @param string $media       Media type.
			 */
			do_action( 'divi_squad_style_registered', $full_handle, $asset_data, $filtered_media );

			return true;
		} catch ( RuntimeException $e ) {
			/**
			 * Fires when style registration fails
			 *
			 * @since 3.4.0
			 *
			 * @param string           $handle    Style handle.
			 * @param array            $config    Asset configuration.
			 * @param RuntimeException $exception The exception that occurred.
			 */
			do_action( 'divi_squad_style_registration_failed', $handle, $config, $e );

			divi_squad()->log_error( $e, sprintf( 'Failed to register style: %s', $handle ) );

			return false;
		}
	}

	/**
	 * Get prefixed handle name
	 *
	 * @param string $handle Asset handle.
	 */
	protected function get_prefixed_handle( string $handle ): string {
		/**
		 * Filters the prefix for asset handles.
		 *
		 * @since 3.3.0
		 *
		 * @param string $handle_prefix The prefix for asset handles.
		 * @param string $handle        The asset handle.
		 */
		$handle_prefix = apply_filters( 'divi_squad_assets_handle_prefix', 'squad', $handle );

		if ( '' === $handle_prefix ) {
			return $handle;
		}

		return sprintf( '%s-%s', $handle_prefix, $handle );
	}
}
