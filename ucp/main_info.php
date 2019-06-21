<?php
/**
 *
 * Requests. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, Evil
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace evilsystem\requests\ucp;

/**
 * Requests UCP module info.
 */
class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\evilsystem\requests\ucp\main_module',
			'title'		=> 'UCP_REQUESTS_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'UCP_REQUESTS',
					'auth'	=> 'ext_evilsystem/requests',
					'cat'	=> array('UCP_REQUESTS_TITLE')
				),
			),
		);
	}
}
