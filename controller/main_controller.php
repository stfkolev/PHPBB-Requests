<?php
/**
 *
 * Requests. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, Evil
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace evilsystem\requests\controller;

/**
 * Requests main controller.
 */
class main_controller
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \evilsystem\requests\table */
	protected $requests_table;

	/** @var \evilsystem\requests\table */
	protected $replies_table;
	
	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $root_path;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config						$config				Config object
	 * @param \phpbb\controller\helper					$helper				Controller helper object
	 * @param \phpbb\template\template					$template			Template object
	 * @param \phpbb\language\language					$language			Language object
	 * @param \phpbb\request\request					$request			Request object
	 * @param \phpbb\db\driver\driver_interface 		$db					Database object
	 * @param \phpbb\user								$user				User object
	 * @param string                        			$root_path          phpBB root path
     * @param string                        			$php_ext            phpEx 
	 * @param \evilsystem\requests\table				$mods_table			String
	 * @param \evilsystem\requests\table				$servers_table		String
	 * 
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper, 
		\phpbb\template\template $template, 
		\phpbb\language\language $language,

		\phpbb\request\request $request,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\user $user,
		
		$root_path,
		$php_ext,

		$requests_table,
		$replies_table
	)
	{
		$this->config			= $config;
		$this->helper			= $helper;
		$this->template			= $template;
		$this->language			= $language;

		$this->request 			= $request;
		$this->db 				= $db;
		$this->user				= $user;

		$this->root_path = $root_path;
		$this->php_ext = $php_ext;

		$this->requests_table 	= $requests_table;
		$this->replies_table	= $replies_table;
	}

	/**
	 * Controller handler for route /requests/{name}
	 *
	 * @param string $name
	 *
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function handle($name)
	{
		$renderer = null;
		switch($name) {
			case 'all': {

				/*! Requests Counter */
				$counter = 0;

				/*! Prepare query */
				$sql = 'SELECT * FROM ' . $this->requests_table;

				/*! Execute query */
				$result = $this->db->sql_query($sql);

				/*! Each row indifferently */
				while($row = $this->db->sql_fetchrow($result)) {

					/*! Array for author */
					$findUser = array(
						'user_id' => $row['requests_user_id'],
					);

					/*! Array for replies */
					$findReplies = array(
						'replies_request_id' => $row['requests_id'],
					);

					/*! Find number of replies for each request */
					$sql = 'SELECT COUNT(*) as replies_count FROM ' . $this->replies_table . ' WHERE ' . $this->db->sql_build_array('SELECT', $findReplies);
					$replies_count = $this->db->sql_fetchrow($this->db->sql_query($sql));

					/*! If somebody replied, the request is in progress */
					if($replies_count['replies_count'] > 0 && $row['requests_status'] != 2) {
						$data = array(
							'requests_status' => 1,
						);
						
						$sql = 'UPDATE ' . $this->requests_table . ' SET ' . $this->db->sql_build_array('UPDATE', $data);

						/*! Set current to progress */
						$row['requests_status'] = 1;

						/*! Execute Query */
						$this->db->sql_query($sql);
					}

					/*! Find Author username */
					$sql = 'SELECT * FROM ' . USERS_TABLE . ' WHERE ' . $this->db->sql_build_array('SELECT', $findUser);
					$author = $this->db->sql_fetchrow($this->db->sql_query($sql));

					$status = $this->db->sql_escape($row['requests_status']);

					/*! Assign block of variables */
					$this->template->assign_block_vars('request', array(
						'REQUEST_AUTHOR_ID'				=> $author['user_id'],
						'REQUEST_AUTHOR_COLOUR'			=> $author['user_colour'],
						'REQUEST_ID'					=> $this->db->sql_escape($row['requests_id']),
						'REQUEST_TITLE'					=> $this->db->sql_escape($row['requests_title']),
						'REQUEST_AUTHOR'				=> $author['username'],
						'REQUEST_TYPE'					=> $this->db->sql_escape($row['requests_type']),
						'REQUEST_STATUS'				=> ($status == 0 ? 'Waiting' : ($status == 1 ? 'In Progress' : 'Finished')),
						'REQUEST_REPLIES'				=> $replies_count['replies_count'],
					));

					/*! Increment requests count */
					$counter++;
				}

				$this->template->assign_vars(array(
					'REQUESTS_COUNT' => $counter
				));

				/*! Render the template */
				$renderer = $this->helper->render('requests_body.html', $name);
				break;
			}
			case 'make': {

				/*! If is user registered */	
				if($this->user->data['is_registered']) {
					/*! Add CSRF */
					add_form_key('requests_make');

					$errors = array();
					
					/*! Check if request is post */
					if($this->request->is_set_post('submit')) {
						
						// Test if the submitted form is valid
						if (!check_form_key('requests_make'))
						{
							$errors[] = $this->language->lang('FORM_INVALID');
						}

						/*! Check if no errors are met */
						if(empty($errors)) {

							/*! Prepare Data */
							$data = array(
								'requests_title' 			=> $this->request->variable('title', ''),
								'requests_type' 			=> $this->request->variable('type', ''),
								'requests_user_id'			=> $this->user->data['user_id'],
								'requests_width'			=> $this->request->variable('width', 0),
								'requests_height'			=> $this->request->variable('height', 0),
								'requests_additional'		=> $this->request->variable('additional', ''),
								'requests_status'			=> 0,
							);

							/*! Form query */
							$sql = 'INSERT INTO '. $this->requests_table .' ' . $this->db->sql_build_array('INSERT', $data);

							/*! Execute Query */
							$result = $this->db->sql_query($sql);

							/*! Redirect after 3 seconds if no action is taken */
							meta_refresh(3, $this->helper->route('evilsystem_requests_controller', array('name' => 'all')));
							$message = $this->language->lang('REQUESTS_REQUEST_ADDED') . '<br /><br />' . $this->language->lang('REQUESTS_RETURN', '<a href="' . $this->helper->route('evilsystem_requests_controller', array('name' => 'all')) . '">', '</a>');
							trigger_error($message);
						}

						$s_errors = !empty($errors);
						
						$this->template->assign_vars(array(
							'S_ERROR'		=> $s_errors,
							'ERROR_MSG'		=> $s_errors ? implode('<br />', $errors) : '',
						));
					}

					/*! Render Page */
					$renderer = $this->helper->render('requests_make.html', $name);
				} else

					/*! User is not registered, redirect to all servers if he attempts to get to /servers/add by url */
					redirect($this->helper->route('evilsystem_requests_controller', array('name' => 'all')));
	
				break;
			}
			case $name: {
				
				/*! BBCode */
				require_once $this->root_path . 'includes/functions_display.' . $this->php_ext;
				require_once $this->root_path . 'includes/functions_posting.' . $this->php_ext;

				$this->language->add_lang('posting');

				display_custom_bbcodes();
				generate_smilies('inline', 0);

				$this->template->assign_vars([
					'S_BBCODE_IMG'		=> true,
					'S_BBCODE_QUOTE'	=> true,
					'S_BBCODE_FLASH'	=> true,
					'S_LINKS_ALLOWED'	=> true,

					'S_SMILIES_ALLOWED'	=> true,
				]);
				/*! End BBCode */

				/*! Data to search by */
				$data = array(
					'requests_id'	=> $name,
				);

				/*! Request */
				$sql = 'SELECT * FROM ' . $this->requests_table . ' WHERE ' . $this->db->sql_build_array('SELECT', $data);

				$request = $this->db->sql_fetchrow($this->db->sql_query($sql));

				/*! Replies */
				$data = array(
					'replies_request_id'	=> $name,
				);

				/*! Get All replies for the current Request */
				$sql = 'SELECT * FROM ' . $this->replies_table . ' WHERE ' . $this->db->sql_build_array('SELECT', $data);
				$result = $this->db->sql_query($sql);
				
				/*! Create a block of vars of type replies */
				while($row = $this->db->sql_fetchrow($result)) {

					$find = array(
						'user_id'	=> $row['replies_user_id']
					);

					/*! Find Author of reply */
					$sql = 'SELECT * FROM ' . USERS_TABLE . ' WHERE ' . $this->db->sql_build_array('SELECT', $find);
					$author = $this->db->sql_fetchrow($this->db->sql_query($sql));

					/*! Assign block of variables */
					$this->template->assign_block_vars('reply', array(
						'REPLY_AUTHOR'					=> $author['username'],
						'REPLY_AUTHOR_COLOUR'			=> $author['user_colour'],
						'REPLY_ADDITIONAL'				=> $row['replies_additional']
					));
				}

				/*! Author */
				$data = array(
					'user_id' => $request['requests_user_id'],
				);

				$sql = 'SELECT * FROM ' . USERS_TABLE . ' WHERE ' . $this->db->sql_build_array('SELECT', $data);
				$author = $this->db->sql_fetchrow($this->db->sql_query($sql));

				var_dump($request);

				/*! Assing basic variables */
				$this->template->assign_vars(array(
					'REQUEST_ID'				=> $request['requests_id'],
					'REQUEST_AUTHOR'			=> $author['username'],
					'REQUEST_AUTHOR_COLOR'		=> $author['user_colour'],
					'REQUEST_TITLE'				=> $request['requests_title'],
					'REQUEST_TYPE'				=> $request['requests_type'],
					'REQUEST_ADDITIONAL'		=> $request['requests_additional'],
					'REQUEST_WIDTH'				=> $request['requests_width'],
					'REQUEST_HEIGHT'			=> $request['requests_height'],
					'REQUEST_STATUS'			=> $request['requests_status']
				));

				/*! If is user registered */	
				if($this->user->data['is_registered']) {
					/*! Add CSRF */
					add_form_key('replies_add');

					$errors = array();
					
					/*! Check if request is post */
					if($this->request->is_set_post('submit')) {
						// Add replies record
					}

				} else 
					/*! User is not registered, redirect to all servers if he attempts to get to /servers/add by url */
					redirect($this->helper->route('evilsystem_requests_controller', array('name' => 'all')));

				$renderer = $this->helper->render('requests_reply.html', $name);
				break;
			}
		}

		return $renderer;
	}
}
