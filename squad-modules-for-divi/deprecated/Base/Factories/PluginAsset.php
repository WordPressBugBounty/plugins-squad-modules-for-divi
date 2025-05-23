<?php // phpcs:ignore WordPress.Files.FileName

/**
 * Abstract class representing the Plugin Asset.
 *
 * @since      3.0.0
 * @deprecated 3.3.0
 * @package    DiviSquad
 * @author     The WP Squad <support@squadmodules.com>
 */

namespace DiviSquad\Base\Factories;

use DiviSquad\Base\Factories\FactoryBase\Factory;
use DiviSquad\Base\Factories\PluginAsset\AssetInterface;
use DiviSquad\Core\Supports\Polyfills\Constant;
use DiviSquad\Core\Traits\Singleton;

/**
 * Abstract class representing the Plugin Asset.
 *
 * @since      3.0.0
 * @deprecated 3.3.0
 * @package    DiviSquad
 */
final class PluginAsset extends FactoryBase\Factory {

	use Singleton;

	/**
	 * The list of registries.
	 *
	 * @var AssetInterface[]
	 */
	private static array $registries = array();

	/**
	 * Init hooks for the factory.
	 *
	 * @return void
	 */
	protected function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'add_enqueue_scripts' ), Constant::PHP_INT_MAX );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_enqueue_scripts' ), Constant::PHP_INT_MAX );
		add_filter( 'divi_squad_global_localize_data', array( $this, 'add_localize_backend_extra_data' ) );
	}

	/**
	 * Add a new item to the list of items.
	 *
	 * @param string $class_name The class name of the item to add to the list.
	 *
	 * @return void
	 */
	public function add( $class_name ) {
		if ( ! class_exists( $class_name ) ) {
			return;
		}

		$asset = new $class_name();
		if ( ! $asset instanceof PluginAsset\AssetInterface ) {
			return;
		}

		self::$registries[] = $asset;
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function add_enqueue_scripts() {
		foreach ( self::$registries as $asset ) {
			$asset->enqueue_scripts();
		}
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook_suffix Hook suffix for the current admin page.
	 *
	 * @return void
	 */
	public function add_admin_enqueue_scripts( string $hook_suffix ) {
		foreach ( self::$registries as $asset ) {
			$asset->enqueue_scripts( 'admin', $hook_suffix );
		}
	}

	/**
	 * Add localize script data.
	 *
	 * @param array $data The data to localize.
	 *
	 * @return array
	 */
	public function add_localize_backend_extra_data( array $data ): array {
		foreach ( self::$registries as $asset ) {
			$data = (array) $asset->get_localize_data( 'raw', $data );
		}

		return $data;
	}
}
