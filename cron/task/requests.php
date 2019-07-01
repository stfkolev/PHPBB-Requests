<?php
/**
 *
 * Requests. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, Evil
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace evilsystem\requests\cron\task;

/**
 * Refresh servers cron task.
 */
class requests extends \phpbb\cron\task\base
{
	/**
	 * How often we run the cron (in seconds).
	 * @var int
	 */
	protected $cron_frequency = 604800;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\config\config */
    protected $config;
	
	/** @var \evilsystem\requests\table */
	protected $requests_table;

	/** @var \evilsystem\requests\table */
	protected $replies_table;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface 				$db					Database object
	 * @param \phpbb\config\config 								$config 			Config object
	 * @param \evilsystem\requests\table						$requests_table		String
	 * @param \evilsystem\requests\table						$replies_table		String
	 */
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		$requests_table,
		$replies_table
	)
	{
		$this->db				= $db;
		$this->config 			= $config;
		$this->requests_table 	= $requests_table;
		$this->replies_table 	= $replies_table;
	}

	/**
	 * Runs this cron task.
	 *
	 * @return void
	 */
	public function run()
	{	
		var_dump('asdasd');
		$sql = 'SELECT * FROM ' . $this->requests_table;

		$result = $this->db->sql_query($sql);
		
		if($result) {
			while($row = $this->db->sql_fetchrow($result)) {
				if($row['requests_status'] > 1) {
					$data = array(
						$row['requests_id'],
					);

					/*! Delete replies to the request */
					$sql = 'DELETE FROM ' . $this->replies_table . ' WHERE ' . $this->db->sql_in_set('replies_request_id', $data); 
					$this->db->sql_query($sql);

					/*! Delete request */
					$sql = 'DELETE FROM ' . $this->requests_table . ' WHERE ' . $this->db->sql_in_set('requests_id', $data);
					$this->db->sql_query($sql);
				}
			}
		}

        $this->db->sql_freeresult($result);

		// Update the cron task run time here if it hasn't
		// already been done by your cron actions.
		$this->config->set('requests_cron_last_run', time(), false);
	}

	/**
	 * Returns whether this cron task can run, given current board configuration.
	 *
	 * For example, a cron task that prunes forums can only run when
	 * forum pruning is enabled.
	 *
	 * @return bool
	 */
	public function is_runnable()
	{
		return true;
	}

	/**
	 * Returns whether this cron task should run now, because enough time
	 * has passed since it was last run.
	 *
	 * @return bool
	 */
	public function should_run()
	{
		return $this->config['requests_cron_last_run'] < time() - $this->cron_frequency;
	}
}