<?php // phpcs:ignore WordPress.Files.FileName

/**
 * Interface for the Notice class.
 *
 * @since      2.0.0
 * @deprecated 3.3.3
 * @package    DiviSquad
 * @author     The WP Squad <support@squadmodules.com>
 */

namespace DiviSquad\Base\Factories\AdminNotice;

/**
 * Notice Interface.
 *
 * @since      2.0.0
 * @deprecated 3.3.3
 * @package    DiviSquad
 */
interface NoticeInterface {

	/**
	 * Say that current notice can view or not.
	 *
	 * @return bool
	 */
	public function can_render_it();

	/**
	 * Add the CSS classes for the body tag in the admin.
	 *
	 * @return string
	 */
	public function get_body_classes();

	/**
	 * Get the template arguments
	 *
	 * @return array
	 */
	public function get_template_args();

	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public function get_template();
}
