<?php
/**
 *
 * Requests. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, Evil
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace evilsystem\requests\migrations;

class install_sample_schema extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return array('\phpbb\db\migration\data\v320\v320');
	}

	/**
	 * Update database schema.
	 *
	 * https://area51.phpbb.com/docs/dev/3.2.x/migrations/schema_changes.html
	 *	add_tables: Add tables
	 *	drop_tables: Drop tables
	 *	add_columns: Add columns to a table
	 *	drop_columns: Removing/Dropping columns
	 *	change_columns: Column changes (only type, not name)
	 *	add_primary_keys: adding primary keys
	 *	add_unique_index: adding an unique index
	 *	add_index: adding an index (can be column:index_size if you need to provide size)
	 *	drop_keys: Dropping keys
	 *
	 * This sample migration adds a new column to the users table.
 	* It also adds an example of a new table that can hold new data.
	 *
	 * @return array Array of schema changes
	 */
	public function update_schema()
	{
		return array(
			'add_tables'		=> array(
				$this->table_prefix . 'evilsystem_requests_table'	=> array(
					'COLUMNS'		=> array(
						'requests_id'			=> array('UINT', null, 'auto_increment'),
						'requests_user_id'		=> array('UINT', null),

						'requests_title'		=> array('VCHAR:255', ''),
						'requests_type'			=> array('VCHAR:255', ''),

						'requests_additional'	=> array('TEXT', ''),

						'requests_width'		=> array('UINT', null),
						'requests_height'		=> array('UINT', null),

						'requests_status'		=> array('UINT', null)
					),
					'PRIMARY_KEY'	=> 'requests_id',
				),

				$this->table_prefix . 'evilsystem_requests_replies'	=> array(
					'COLUMNS'		=> array(
						'replies_id'			=> array('UINT', null, 'auto_increment'),
						'replies_request_id'	=> array('UINT', null),
						'replies_user_id'		=> array('UINT', null),

						'replies_additional'	=> array('TEXT', ''),
						'replies_status'		=> array('UINT', null),
					),
					'PRIMARY_KEY'	=> 'replies_id',
				),
			),
		);
	}

	/**
	 * Revert database schema changes. This method is almost always required
	 * to revert the changes made above by update_schema.
	 *
	 * https://area51.phpbb.com/docs/dev/3.2.x/migrations/schema_changes.html
	 *	add_tables: Add tables
	 *	drop_tables: Drop tables
	 *	add_columns: Add columns to a table
	 *	drop_columns: Removing/Dropping columns
	 *	change_columns: Column changes (only type, not name)
	 *	add_primary_keys: adding primary keys
	 *	add_unique_index: adding an unique index
	 *	add_index: adding an index (can be column:index_size if you need to provide size)
	 *	drop_keys: Dropping keys
	 *
	 * This sample migration removes the column that was added the users table in update_schema.
	 * It also removes the table that was added in update_schema.
	 *
	 * @return array Array of schema changes
	 */
	public function revert_schema()
	{
		return array(
			'drop_tables'		=> array(
				$this->table_prefix . 'evilsystem_requests_table',
				$this->table_prefix . 'evilsystem_requests_replies',
			),
		);
	}
}
