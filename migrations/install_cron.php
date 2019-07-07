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

class install_cron extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['requests_cron_last_run']);
	}

	public static function depends_on()
	{
		return array('\phpbb\db\migration\data\v320\v320');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('requests_cron_last_run', 0, true)),
		);
	}
}
