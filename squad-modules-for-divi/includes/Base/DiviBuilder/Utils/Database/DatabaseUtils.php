<?php // phpcs:disable WordPress.Files.FileName, WordPress.NamingConventions.ValidVariableName

/**
 * Database utilities for table management.
 *
 * @since   3.1.0
 * @author  The WP Squad <support@squadmodules.com>
 * @package DiviSquad
 */

namespace DiviSquad\Base\DiviBuilder\Utils\Database;

use Throwable;

/**
 * Database utilities class for managing table structures.
 *
 * @since 3.1.0
 */
class DatabaseUtils {

	/**
	 * Generate SQL CREATE TABLE statement from schema.
	 *
	 * @since  3.1.0
	 *
	 * @param string $table_name Table name to generate SQL for.
	 * @param array  $schema     Table schema definition.
	 *
	 * @return string Generated SQL statement.
	 */
	public static function generate_create_table_sql( string $table_name, array $schema ): string {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$columns = array();
		$indices = array();

		foreach ( $schema as $column_name => $definition ) {
			$columns[] = self::generate_column_definition( $column_name, $definition );

			if ( ! empty( $definition['index'] ) || ! empty( $definition['unique'] ) ) {
				$indices[] = self::generate_index_definition( $column_name, $definition );
			}
		}

		// Add unique composite index for meta_key and post_type if they exist in schema.
		if ( isset( $schema['meta_key'] ) && isset( $schema['post_type'] ) ) {
			$indices[] = 'UNIQUE KEY meta_key_post_type (meta_key, post_type)';
		}

		return sprintf(
			"CREATE TABLE IF NOT EXISTS `%s` (\n\t%s%s\n) %s",
			$table_name,
			implode( ",\n\t", $columns ),
			$indices ? ",\n\t" . implode( ",\n\t", $indices ) : '',
			$charset_collate
		);
	}

	/**
	 * Generate column definition SQL.
	 *
	 * @since  3.1.0
	 *
	 * @param string $column_name Column name.
	 * @param array  $definition  Column definition array.
	 *
	 * @return string Column definition SQL.
	 */
	private static function generate_column_definition( string $column_name, array $definition ): string {
		$parts = array( '`' . $column_name . '`' );

		// Type with optional length.
		$type = $definition['type'];
		if ( ! empty( $definition['length'] ) ) {
			$type .= "({$definition['length']})";
		}
		$parts[] = $type;

		// Unsigned attribute (must come before NOT NULL).
		if ( ! empty( $definition['unsigned'] ) ) {
			$parts[] = 'UNSIGNED';
		}

		// Nullable.
		$parts[] = empty( $definition['nullable'] ) ? 'NOT NULL' : 'NULL';

		// Default value.
		if ( isset( $definition['default'] ) ) {
			if ( 'CURRENT_TIMESTAMP' === $definition['default'] ) {
				$parts[] = 'DEFAULT CURRENT_TIMESTAMP';
			} else {
				$parts[] = "DEFAULT '{$definition['default']}'";
			}
		}

		// Auto increment.
		if ( ! empty( $definition['auto_increment'] ) ) {
			$parts[] = 'AUTO_INCREMENT';
		}

		// Primary key.
		if ( ! empty( $definition['primary'] ) ) {
			$parts[] = 'PRIMARY KEY';
		}

		// On Update (for TIMESTAMP fields).
		if ( isset( $definition['on_update'] ) && 'CURRENT_TIMESTAMP' === $definition['on_update'] ) {
			$parts[] = 'ON UPDATE CURRENT_TIMESTAMP';
		}

		return implode( ' ', $parts );
	}

	/**
	 * Generate index definition SQL.
	 *
	 * @since  3.1.0
	 *
	 * @param string $column_name Column name.
	 * @param array  $definition  Column definition array.
	 *
	 * @return string Index definition SQL.
	 */
	private static function generate_index_definition( string $column_name, array $definition ): string {
		$type = ! empty( $definition['unique'] ) ? 'UNIQUE' : '';
		return trim( sprintf( '%s KEY `idx_%s` (`%s`)', $type, $column_name, $column_name ) );
	}

	/**
	 * Verify if a table exists and create it if it doesn't.
	 *
	 * @since  3.1.0
	 *
	 * @param string $table_name Table name to verify/create.
	 * @param array  $schema     Table schema.
	 *
	 * @return bool   True if table exists or was created successfully.
	 */
	public static function verify_and_create_table( string $table_name, array $schema ): bool {
		try {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			// Generate and execute creation SQL.
			$sql = self::generate_create_table_sql( $table_name, $schema );
			dbDelta( $sql );
			return true;
		} catch ( Throwable $e ) {
			divi_squad()->log_debug( "Error creating table {$table_name}: " . $e->getMessage() );
			return false;
		}
	}
}
