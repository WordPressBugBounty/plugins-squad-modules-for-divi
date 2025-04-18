<?php // phpcs:ignore WordPress.Files.FileName

/**
 * REST API Routes for Extensions
 *
 * This file contains the Extensions class which handles REST API endpoints
 * for managing Divi Squad extensions.
 *
 * @since   1.0.0
 * @author  The WP Squad <support@squadmodules.com>
 * @package DiviSquad
 */

namespace DiviSquad\Rest_API_Routes\Version1;

use DiviSquad\Rest_API_Routes\Base_Route;
use Exception;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Extensions REST API Route Handler
 *
 * Manages REST API endpoints for Divi Squad extensions, including
 * retrieving available, active, and inactive extensions, as well as
 * updating the list of active extensions.
 *
 * @since   1.0.0
 * @package DiviSquad
 */
class Extensions extends Base_Route {

	/**
	 * Key for active extensions in memory.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const ACTIVE_EXTENSIONS_KEY = 'active_extensions';

	/**
	 * Key for inactive extensions in memory.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const INACTIVE_EXTENSIONS_KEY = 'inactive_extensions';

	/**
	 * Key for active extension version in memory.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const ACTIVE_EXTENSION_VERSION_KEY = 'active_extension_version';

	/**
	 * Get available routes for the Extensions API.
	 *
	 * @since 1.0.0
	 * @return array<string, array<int, array<string, list<$this|string>|string>>>
	 */
	public function get_routes(): array {
		return array(
			'/extensions'          => array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_extensions' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
			),
			'/extensions/active'   => array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_active_extensions' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_active_extensions' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
			),
			'/extensions/inactive' => array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_inactive_extensions' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
			),
		);
	}

	/**
	 * Check if the current user has admin permissions.
	 *
	 * @since 1.0.0
	 *
	 * @return bool|WP_Error True if the request has admin access, WP_Error object otherwise.
	 */
	public function check_admin_permissions() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You do not have permissions to perform this action.', 'squad-modules-for-divi' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Get all registered extensions.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response Response containing all registered extensions.
	 */
	public function get_extensions( WP_REST_Request $request ): WP_REST_Response {
		$extensions = divi_squad()->extensions->get_registered_list();
		$extensions = array_map( array( $this, 'format_extension' ), $extensions );

		return rest_ensure_response( array_values( $extensions ) );
	}

	/**
	 * Get active extensions list.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response Response containing active extensions.
	 */
	public function get_active_extensions( WP_REST_Request $request ): WP_REST_Response {
		return rest_ensure_response( $this->get_extension_names( static::ACTIVE_EXTENSIONS_KEY ) );
	}

	/**
	 * Get inactive extensions list.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response Response containing inactive extensions.
	 */
	public function get_inactive_extensions( WP_REST_Request $request ): WP_REST_Response {
		return rest_ensure_response( $this->get_extension_names( static::INACTIVE_EXTENSIONS_KEY ) );
	}

	/**
	 * Get extension names from memory.
	 *
	 * Retrieves either active or inactive extension names from the plugin's memory.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The key to retrieve from memory ('active_extensions' or 'inactive_extensions').
	 *
	 * @return array<string> List of extension names.
	 */
	protected function get_extension_names( string $key ): array {
		$current = divi_squad()->memory->get( $key );
		if ( ! is_array( $current ) ) {
			$defaults = $this->get_default_extensions( $key );
			$current  = array_column( $defaults, 'name' );
		}

		return array_values( $current );
	}

	/**
	 * Get default extensions based on the provided key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The key to determine which default extensions to retrieve.
	 *
	 * @return array<string, mixed> List of default extensions.
	 */
	private function get_default_extensions( string $key ): array {
		return static::ACTIVE_EXTENSIONS_KEY === $key
			? divi_squad()->extensions->get_default_registries()
			: divi_squad()->extensions->get_inactive_registries();
	}

	/**
	 * Update active extensions list.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response on success, WP_Error on failure.
	 */
	public function update_active_extensions( WP_REST_Request $request ) {
		try {
			$active_extensions = $request->get_json_params();

			if ( ! is_array( $active_extensions ) ) {
				return new WP_Error(
					'invalid_data',
					esc_html__( 'Invalid data format. Expected array of extension names.', 'squad-modules-for-divi' ),
					array( 'status' => 400 )
				);
			}

			$active_extensions   = array_values( array_map( 'sanitize_text_field', $active_extensions ) );
			$all_extension_names = array_column( divi_squad()->extensions->get_registered_list(), 'name' );
			$invalid_extensions  = array_diff( $active_extensions, $all_extension_names );

			if ( count( $invalid_extensions ) > 0 ) {
				$error_message = sprintf(
				/* translators: %s: comma-separated list of invalid extension names */
					esc_html__( 'Invalid extension names provided: %s', 'squad-modules-for-divi' ),
					implode( ', ', $invalid_extensions )
				);

				// Send an error report.
				divi_squad()->log_error(
					new Exception( $error_message ),
					'An error message from lite extensions rest api.'
				);

				// Send error message to the frontend.
				return new WP_Error(
					'invalid_extension',
					$error_message,
					array( 'status' => 400 )
				);
			}

			$inactive_extensions = array_values( array_diff( $all_extension_names, $active_extensions ) );

			$this->update_extension_memory( $active_extensions, $inactive_extensions );

			return rest_ensure_response(
				array(
					'code'    => 'success',
					'message' => __( 'The list of active extensions has been updated.', 'squad-modules-for-divi' ),
				)
			);
		} catch ( Exception $e ) {
			divi_squad()->log_error( $e, 'Failed to update active extensions' );

			return new WP_Error(
				'update_failed',
				__( 'Failed to update active extensions. Please try again.', 'squad-modules-for-divi' ),
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * Update extension memory with active and inactive extensions.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string> $active_extensions   List of active extensions.
	 * @param array<string> $inactive_extensions List of inactive extensions.
	 *
	 * @return void
	 */
	protected function update_extension_memory( array $active_extensions, array $inactive_extensions ): void {
		divi_squad()->memory->set( static::ACTIVE_EXTENSIONS_KEY, $active_extensions );
		divi_squad()->memory->set( static::INACTIVE_EXTENSIONS_KEY, $inactive_extensions );
		divi_squad()->memory->set( static::ACTIVE_EXTENSION_VERSION_KEY, divi_squad()->get_version() );
	}

	/**
	 * Format a single extension's data.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $extension Extension data to format.
	 *
	 * @return array<string, mixed> Formatted extension data.
	 */
	private function format_extension( array $extension ): array {
		return array(
			'name'               => $extension['name'] ?? '',
			'label'              => $extension['label'] ?? '',
			'description'        => $extension['description'] ?? '',
			'release_version'    => $extension['release_version'] ?? '',
			'last_modified'      => $extension['last_modified'] ?? array(),
			'is_default_active'  => $extension['is_default_active'] ?? false,
			'is_premium_feature' => $extension['is_premium_feature'] ?? false,
			'category'           => $extension['category'] ?? '',
			'category_title'     => $extension['category_title'] ?? '',
		);
	}
}
