<?php // phpcs:ignore WordPress.Files.FileName

/**
 * Ninja Forms Processor
 *
 * Handles the retrieval and processing of Ninja Forms.
 *
 * @package DiviSquad
 * @author  The WP Squad <support@squadmodules.com>
 * @since   3.1.0
 */

namespace DiviSquad\Base\DiviBuilder\Utils\Elements\Forms\Processors;

use DiviSquad\Base\DiviBuilder\Utils\Elements\Forms\Form;

/**
 * Ninja Forms Processor
 *
 * Handles the retrieval and processing of Ninja Forms.
 *
 * @package DiviSquad
 * @since   3.1.0
 */
class NinjaForms extends Form {

	/**
	 * Get Ninja Forms.
	 *
	 * @param string $collection The type of data to collect ('id' or 'title').
	 *
	 * @return array An array of Ninja Forms data.
	 */
	public function get_forms( string $collection ): array {
		if ( ! function_exists( '\Ninja_Forms' ) ) {
			return array();
		}

		// Get all Ninja Forms.
		$forms = \Ninja_Forms()->form()->get_forms();

		if ( empty( $forms ) ) {
			return array();
		}

		return $this->process_form_data( $forms, $collection );
	}

	/**
	 * Get the ID of a Ninja Form.
	 *
	 * @param object $form The form object.
	 *
	 * @return int The form ID.
	 */
	protected function get_form_id( $form ): int {
		return $form->get_id();
	}

	/**
	 * Get the title of a Ninja Form.
	 *
	 * @param object $form The form object.
	 *
	 * @return string The form title.
	 */
	protected function get_form_title( $form ): string {
		return $form->get_setting( 'title' );
	}
}
