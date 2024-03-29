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
	/** @var \phpbb\auth\auth */
	protected $authr;

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

	/** @var \phpbb\textformatter\s9e\parser */
	protected $parser;

	/** @var \phpbb\textformatter\s9e\renderer */
	protected $renderer;

	/** @var \phpbb\textformatter\s9e\utils */
	protected $utils;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth							$author				Auth object
	 * @param \phpbb\config\config						$config				Config object
	 * @param \phpbb\controller\helper					$helper				Controller helper object
	 * @param \phpbb\template\template					$template			Template object
	 * @param \phpbb\language\language					$language			Language object
	 * @param \phpbb\request\request					$request			Request object
	 * @param \phpbb\db\driver\driver_interface 		$db					Database object
	 * @param \phpbb\user								$user				User object
	 * @param string                        			$root_path          phpBB root path
     * @param string                        			$php_ext            phpEx 
	 * @param \evilsystem\requests\table				$requests_table		String
	 * @param \evilsystem\requests\table				$replies_table		String
	 * @param \phpbb\textformatter\s9e\parser			$parser				Parser object
	 * @param \phpbb\textformatter\s9e\renderer			$renderer			Renderer object
	 * 
	 */
	public function __construct(
		\phpbb\auth\auth					$auth,
		\phpbb\config\config 				$config,
		\phpbb\controller\helper 			$helper, 
		\phpbb\template\template 			$template, 
		\phpbb\language\language 			$language,

		\phpbb\request\request 				$request,
		\phpbb\db\driver\driver_interface 	$db,
		\phpbb\user 						$user,
		
		$root_path,
		$php_ext,

		$requests_table,
		$replies_table,

		\phpbb\textformatter\s9e\parser 	$parser,
		\phpbb\textformatter\s9e\renderer 	$renderer,
		\phpbb\textformatter\s9e\utils 		$utils
	)
	{
		$this->auth				= $auth;
		$this->config			= $config;
		$this->helper			= $helper;
		$this->template			= $template;
		$this->language			= $language;

		$this->request 			= $request;
		$this->db 				= $db;
		$this->user				= $user;

		$this->root_path 		= $root_path;
		$this->php_ext 			= $php_ext;

		$this->requests_table 	= $requests_table;
		$this->replies_table	= $replies_table;

		$this->parser 			= $parser;
		$this->renderer			= $renderer;
		$this->utils 			= $utils;
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
						'replies_request_id' => (int) $row['requests_id'],
					);

					/*! Find number of replies for each request */
					$sql = 'SELECT COUNT(*) as replies_count FROM ' . $this->replies_table . ' WHERE ' . $this->db->sql_build_array('SELECT', $findReplies);
					$replies_count = (int) ($this->db->sql_fetchrow($this->db->sql_query($sql)))['replies_count'];


					/*! If somebody replied, the request is in progress */
					if((int)$row['requests_status'] != 2 && $replies_count > 0) {
						$data = array(
							'requests_status' => 1,
						);	

						
						$sql = 'UPDATE ' . $this->requests_table . ' SET ' . $this->db->sql_build_array('UPDATE', $data) . ' WHERE ' .  $this->db->sql_in_set('requests_id', (int) $row['requests_id']);
						
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
						'REQUEST_STATUS'				=> $this->language->lang(($status == 0 ? 'REQUESTS_STATUS_WAITING' : ($status == 1 ? 'REQUESTS_STATUS_INPROGRESS' : 'REQUESTS_STATUS_FINISHED'))),
						'REQUEST_REPLIES'				=> $replies_count,
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
								'requests_title' 			=> $this->request->variable('title', '', true),
								'requests_type' 			=> $this->request->variable('type', '', true),
								'requests_user_id'			=> $this->user->data['user_id'],
								'requests_width'			=> $this->request->variable('width', 0),
								'requests_height'			=> $this->request->variable('height', 0),
								'requests_additional'		=> $this->request->variable('additional', '', true),
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
					'S_LINKS_ALLOWED'	=> true,
					'S_BBCODE_ALLOWED'	=> true,
					'S_BBCODE_IMG'		=> true,
					'S_BBCODE_QUOTE'	=> true,
					'S_BBCODE_FLASH'	=> true,
					'S_LINKS_ALLOWED'	=> true,

					'S_SMILIES_ALLOWED'	=> true,
				]);
				/*! End BBCode */

				/*! Select user */
				$data = array(
					'requests_id'	=> (int)$name,
				);

				$sql = 'SELECT requests_user_id FROM ' . $this->requests_table . ' WHERE ' . $this->db->sql_build_array('SELECT', $data);
				$requestUserId = ($this->db->sql_fetchrow($this->db->sql_query($sql)))['requests_user_id'];

				/*! User requests count */
				$data = array(
					'requests_user_id'	=> (int)$requestUserId,
				);

				$sql = ' SELECT COUNT(*) as requests_made FROM ' . $this->requests_table . ' WHERE ' . $this->db->sql_build_array('SELECT', $data);
				$requests_made = ($this->db->sql_fetchrow($this->db->sql_query($sql)))['requests_made'];

				/*! User replies count */
				$data = array(
					'replies_user_id'	=> (int)$requestUserId,
				);

				$sql = ' SELECT COUNT(*) as replies_made FROM ' . $this->replies_table . ' WHERE ' . $this->db->sql_build_array('SELECT', $data);
				$replies_made = ($this->db->sql_fetchrow($this->db->sql_query($sql)))['replies_made'];

				/*! Data to search by */
				$data = array(
					'requests_id'	=> (int)$name,
				);

				/*! Request */
				$sql = 'SELECT * FROM ' . $this->requests_table . ' WHERE ' . $this->db->sql_build_array('SELECT', $data);

				$request = $this->db->sql_fetchrow($this->db->sql_query($sql));

				if(!$request) {
					/*! Redirect after 3 seconds if no action is taken */
					meta_refresh(3, $this->helper->route('evilsystem_requests_controller', array('name' => 'all')));
					$message = $this->language->lang('REQUESTS_NOT_FOUND') . '<br /><br />' . $this->language->lang('REQUESTS_RETURN', '<a href="' . $this->helper->route('evilsystem_requests_controller', array('name' => 'all')) . '">', '</a>');
					trigger_error($message);
				}

				/*! Replies */
				$data = array(
					'replies_request_id'	=> $name,
				);

				/*! Get All replies for the current Request */
				$sql = 'SELECT * FROM ' . $this->replies_table . ' WHERE ' . $this->db->sql_build_array('SELECT', $data) . ' ORDER BY replies_status DESC';
				$result = $this->db->sql_query($sql);
				
				/*! Create a block of vars of type replies */
				while($row = $this->db->sql_fetchrow($result)) {

					$find = array(
						'user_id'	=> (int)$row['replies_user_id']
					);
					
					/*! Find Author of reply */
					$sql = 'SELECT * FROM ' . USERS_TABLE . ' WHERE ' . $this->db->sql_build_array('SELECT', $find);
					$author = $this->db->sql_fetchrow($this->db->sql_query($sql));
					$authorAvatar = get_user_avatar($author['user_avatar'], $author['user_avatar_type'], $author['user_avatar_width'], $author['user_avatar_height']);
					$authorRank = phpbb_get_user_rank($author, $author['user_posts']);
					
					/*! User requests count */
					$data = array(
						'requests_user_id'	=> (int)$author['user_id'],
					);

					$sql = ' SELECT COUNT(*) as requests_made FROM ' . $this->requests_table . ' WHERE ' . $this->db->sql_build_array('SELECT', $data);
					$authorRequestsMade = ($this->db->sql_fetchrow($this->db->sql_query($sql)))['requests_made'];

					/*! User replies count */
					$data = array(
						'replies_user_id'	=> $author['user_id'],
					);

					$sql = ' SELECT COUNT(*) as replies_made FROM ' . $this->replies_table . ' WHERE ' . $this->db->sql_build_array('SELECT', $data);
					$authorRepliesMade = ($this->db->sql_fetchrow($this->db->sql_query($sql)))['replies_made'];

					/*! Assign block of variables */
					$this->template->assign_block_vars('reply', array(
						'REPLY_AUTHOR'					=> $author['username'],
						'REPLY_AUTHOR_ID'				=> $author['user_id'],
						'REPLY_AUTHOR_COLOR'			=> $author['user_colour'],
						'REPLY_AUTHOR_AVATAR'			=> $authorAvatar,
						'REPLY_AUTHOR_RANK'				=> $authorRank['img'] ?: $authorRank['title'],
						'REPLY_AUTHOR_POSTS'			=> $author['user_posts'],
						'REPLY_AUTHOR_REQUESTSMADE'		=> $authorRequestsMade,
						'REPLY_AUTHOR_REPLIESMADE'		=> $authorRepliesMade,
						'REPLY_ADDITIONAL'				=> $this->renderer->render($row['replies_additional']),
						'REPLY_STATUS'					=> $row['replies_status'],
						'REPLY_URL_APPROVE'				=> $this->user->page['root_script_path'] . strstr($this->user->page['page_name'], 'r') . '/' . $row['replies_id']  . '/approve',
						'REPLY_URL_DISAPPROVE'			=> $this->user->page['root_script_path'] . strstr($this->user->page['page_name'], 'r') . '/' . $row['replies_id']  . '/disapprove',
						'REPLY_URL_EDIT'				=> $this->user->page['root_script_path'] . strstr($this->user->page['page_name'], 'r') . '/' . $row['replies_id']  . '/modify',
					));
				}

				/*! Author */
				$data = array(
					'user_id' => $request['requests_user_id'],
				);

				$sql = 'SELECT * FROM ' . USERS_TABLE . ' WHERE ' . $this->db->sql_build_array('SELECT', $data);
				$author = $this->db->sql_fetchrow($this->db->sql_query($sql));
				
				$authorAvatar = get_user_avatar($author['user_avatar'], $author['user_avatar_type'], $author['user_avatar_width'], $author['user_avatar_height']);
				$authorRank = phpbb_get_user_rank($author, $author['user_posts']);

				/*! Assing basic variables */
				$this->template->assign_vars(array(
					'REQUEST_ID'						=> $request['requests_id'],
					'REQUEST_AUTHOR'					=> $author['username'],
					'REQUEST_AUTHOR_ID'					=> $author['user_id'],
					'REQUEST_AUTHOR_REPLIESMADE'		=> $replies_made,
					'REQUEST_AUTHOR_REQUESTSMADE'		=> $requests_made,
					'REQUEST_AUTHOR_COLOR'				=> $author['user_colour'],
					'REQUEST_AUTHOR_AVATAR'				=> $authorAvatar,
					'REQUEST_AUTHOR_RANK'				=> $authorRank['img'] ?: $authorRank['title'],
					'REQUEST_AUTHOR_POSTS'				=> $author['user_posts'],
					'REQUEST_TITLE'						=> $request['requests_title'],
					'REQUEST_TYPE'						=> $request['requests_type'],
					'REQUEST_ADDITIONAL'				=> $request['requests_additional'],
					'REQUEST_WIDTH'						=> $request['requests_width'],
					'REQUEST_HEIGHT'					=> $request['requests_height'],
					'REQUEST_STATUS'					=> (int)$request['requests_status'],
					'REQUEST_IS_AUTHOR'					=> $author['user_id'] == $this->user->data['user_id'],
					'REQUEST_IS_APPROVED'				=> (int)$request['requests_status'] == 2,
					'REQUEST_CAN_EDIT'					=> $request['requests_user_id'] == $this->user->data['user_id'] || $this->auth->acl_get('m_new_evilsystem_requests'),
					'REQUEST_HAS_PERMISSION'			=> $this->auth->acl_get('m_new_evilsystem_requests'),
					'REQUEST_EDIT_URL'					=> $this->user->page['root_script_path'] . strstr($this->user->page['page_name'], 'r') . '/modify',
				));

				/*! If is user registered */	
				if($this->user->data['is_registered']) {
					/*! Add CSRF */
					add_form_key('postform');

					$errors = array();
					
					/*! Check if request is post */
					if($this->request->is_set_post('submit')) {

						$check = array(
							'requests_id'	=> (int) $name,
						);
						$sql = 'SELECT requests_status FROM ' . $this->requests_table . ' WHERE ' . $this->db->sql_build_array('SELECT', $check);

						$requestStatus = ($this->db->sql_fetchrow($this->db->sql_query($sql)))['requests_status'];

						if((int)$requestStatus > 1) {
							/*! Redirect after 3 seconds if no action is taken */
							meta_refresh(3, $this->helper->route('evilsystem_requests_controller', array('name' => 'all')));
							$message = $this->language->lang('REQUESTS_REQUEST_ALREADY_APPROVED') . '<br /><br />' . $this->language->lang('REQUESTS_RETURN', '<a href="' . $this->helper->route('evilsystem_requests_controller', array('name' => 'all')) . '">', '</a>');
							trigger_error($message);

							break;
						}

						$data = array(
							'replies_user_id'		=> (int) $this->user->data['user_id'],
							'replies_request_id'	=> (int) $name,
							'replies_additional'	=> $this->parser->parse($this->request->variable('message', '', true))
						);

						$sql = 'INSERT INTO ' . $this->replies_table . ' ' . $this->db->sql_build_array('INSERT', $data);
						$this->db->sql_query($sql);

						/*! Redirect after 3 seconds if no action is taken */
						meta_refresh(3, $this->helper->route('evilsystem_requests_controller', array('name' => 'all')));
						$message = $this->language->lang('REQUESTS_REQUEST_ADDED') . '<br /><br />' . $this->language->lang('REQUESTS_RETURN', '<a href="' . $this->helper->route('evilsystem_requests_controller', array('name' => 'all')) . '">', '</a>');
						trigger_error($message);
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

	public function approve($name, $id) {
		$data = array(
			'replies_status' => 1,
		);

		/*! Update Replies table */
		$sql = 'UPDATE ' . $this->replies_table . ' SET ' . $this->db->sql_build_array('UPDATE', $data) . ' WHERE ' . $this->db->sql_in_set('replies_id', $id);
		$this->db->sql_query($sql); 	

		$data = array(
			'requests_status'	=> 2,
		);

		$sql = 'UPDATE ' . $this->requests_table . ' SET ' . $this->db->sql_build_array('UPDATE', $data) . ' WHERE ' . $this->db->sql_in_set('requests_id', $name);
		$this->db->sql_query($sql);

		/*! Redirect after 3 seconds if no action is taken */
		meta_refresh(3, $this->helper->route('evilsystem_requests_controller', array('name' => $name)));
		$message = $this->language->lang('REQUESTS_APPROVED') . '<br /><br />' . $this->language->lang('REQUESTS_APPROVED_RETURN', '<a href="' . $this->helper->route('evilsystem_requests_controller', array('name' => $name)) . '">', '</a>');
		trigger_error($message);
		
		return $this->helper->render('requests_reply.html', $name);
	}

	public function disapprove($name, $id) {
		$data = array(
			'replies_status' => 0,
		);

		/*! Update Replies table */
		$sql = 'UPDATE ' . $this->replies_table . ' SET ' . $this->db->sql_build_array('UPDATE', $data) . ' WHERE ' . $this->db->sql_in_set('replies_id', $id);
		$this->db->sql_query($sql); 	

		$data = array(
			'requests_status'	=> 1,
		);

		$sql = 'UPDATE ' . $this->requests_table . ' SET ' . $this->db->sql_build_array('UPDATE', $data) . ' WHERE ' . $this->db->sql_in_set('requests_id', $name);
		$this->db->sql_query($sql);

		/*! Redirect after 3 seconds if no action is taken */
		meta_refresh(3, $this->helper->route('evilsystem_requests_controller', array('name' => $name)));
		$message = $this->language->lang('REQUESTS_DISAPPROVED') . '<br /><br />' . $this->language->lang('REQUESTS_DISAPPROVED_RETURN', '<a href="' . $this->helper->route('evilsystem_requests_controller', array('name' => $name)) . '">', '</a>');
		trigger_error($message);
		
		return $this->helper->render('requests_reply.html', $name);
	}

	public function edit($name) {
		$renderer = null;

		$sql = 'SELECT * FROM ' . $this->requests_table . ' WHERE ' . $this->db->sql_in_set('requests_id', (int) $name);
		$request = $this->db->sql_fetchrow($this->db->sql_query($sql));

		$this->template->assign_vars(array(
			'REQUEST_TITLE'			=> $request['requests_title'],
			'REQUEST_TYPE'			=> $request['requests_type'],
			'REQUEST_WIDTH'			=> $request['requests_width'],
			'REQUEST_HEIGHT'		=> $request['requests_height'],
			'REQUEST_ADDITIONAL'	=> $request['requests_additional'],
		));

		/*! If is user registered */	
		if(
			$this->user->data['is_registered'] && (int)$request['requests_status'] != 2 && 
			($this->user->data['user_id'] == (int) $request['requests_user_id'] || $this->auth->acl_get('u_new_evilsystem_requests') == 1)
		) {
			/*! Add CSRF */
			add_form_key('requests_edit');

			$errors = array();
			
			/*! Check if request is post */
			if($this->request->is_set_post('submit')) {
				
				// Test if the submitted form is valid
				if (!check_form_key('requests_edit'))
				{
					$errors[] = $this->language->lang('FORM_INVALID');
				}

				/*! Check if no errors are met */
				if(empty($errors)) {


					/*! Prepare Data */
					$data = array(
						'requests_title' 			=> $this->request->variable('title', '', true),
						'requests_type' 			=> $this->request->variable('type', '', true),
						'requests_width'			=> $this->request->variable('width', 0),
						'requests_height'			=> $this->request->variable('height', 0),
						'requests_additional'		=> $this->request->variable('additional', '', true),
					);

					/*! Form query */
					$sql = 'UPDATE '. $this->requests_table .' SET ' . $this->db->sql_build_array('UPDATE', $data) . ' WHERE ' . $this->db->sql_in_set('requests_id', (int) $name);

					/*! Execute Query */
					$result = $this->db->sql_query($sql);

					/*! Redirect after 3 seconds if no action is taken */
					meta_refresh(3, $this->helper->route('evilsystem_requests_controller', array('name' => 'all')));
					$message = $this->language->lang('REQUESTS_REQUEST_EDITED') . '<br /><br />' . $this->language->lang('REQUESTS_RETURN', '<a href="' . $this->helper->route('evilsystem_requests_controller', array('name' => 'all')) . '">', '</a>');
					trigger_error($message);
				}

				$s_errors = !empty($errors);
				
				$this->template->assign_vars(array(
					'S_ERROR'		=> $s_errors,
					'ERROR_MSG'		=> $s_errors ? implode('<br />', $errors) : '',
				));
			}

			/*! Render Page */
			$renderer = $this->helper->render('requests_edit.html', $name);
		} else

			/*! User is not registered, redirect to all servers if he attempts to get to /servers/add by url */
			redirect($this->helper->route('evilsystem_requests_controller', array('name' => 'all')));

		return $renderer;
	}

	public function reply_edit($name, $id) {
		$renderer = null;

		/*! BBCode */
		require_once $this->root_path . 'includes/functions_display.' . $this->php_ext;
		require_once $this->root_path . 'includes/functions_posting.' . $this->php_ext;

		$this->language->add_lang('posting');

		display_custom_bbcodes();
		generate_smilies('inline', 0);

		$this->template->assign_vars([
			'S_LINKS_ALLOWED'	=> true,
			'S_BBCODE_ALLOWED'	=> true,
			'S_BBCODE_IMG'		=> true,
			'S_BBCODE_QUOTE'	=> true,
			'S_BBCODE_FLASH'	=> true,
			'S_LINKS_ALLOWED'	=> true,

			'S_SMILIES_ALLOWED'	=> true,
		]);
		/*! End BBCode */

		$sql = 'SELECT * FROM ' . $this->replies_table . ' WHERE ' . $this->db->sql_in_set('replies_id', (int) $id);
		$reply = $this->db->sql_fetchrow($this->db->sql_query($sql));

		$sql = 'SELECT * FROM ' . $this->requests_table . ' WHERE ' . $this->db->sql_in_set('requests_id', (int) $name);
		$request = $this->db->sql_fetchrow($this->db->sql_query($sql));

		$this->template->assign_vars(array(
			'REPLY_ADDITIONAL' => $this->utils->unparse($reply['replies_additional']),
		));

		/*! If is user registered */	
		if(
			$this->user->data['is_registered'] && (int)$request['requests_status'] != 2 && 
			($this->user->data['user_id'] == (int) $reply['replies_user_id'] || $this->auth->acl_get('u_new_evilsystem_requests') == 1)
		) {
			/*! Add CSRF */
			add_form_key('postform');

			$errors = array();
			
			/*! Check if request is post */
			if($this->request->is_set_post('submit')) {
				
				// Test if the submitted form is valid
				if (!check_form_key('postform'))
				{
					$errors[] = $this->language->lang('FORM_INVALID');
				}

				/*! Check if no errors are met */
				if(empty($errors)) {


					/*! Prepare Data */
					$data = array(
						'replies_additional' 	=> $this->parser->parse($this->request->variable('message', '', true)),
					);

					/*! Form query */
					$sql = 'UPDATE '. $this->replies_table .' SET ' . $this->db->sql_build_array('UPDATE', $data) . ' WHERE ' . $this->db->sql_in_set('replies_request_id', (int) $name);

					/*! Execute Query */
					$result = $this->db->sql_query($sql);

					/*! Redirect after 3 seconds if no action is taken */
					meta_refresh(3, $this->helper->route('evilsystem_requests_controller', array('name' => 'all')));
					$message = $this->language->lang('REQUESTS_REPLY_EDITED') . '<br /><br />' . $this->language->lang('REQUESTS_RETURN', '<a href="' . $this->helper->route('evilsystem_requests_controller', array('name' => 'all')) . '">', '</a>');
					trigger_error($message);
				}

				$s_errors = !empty($errors);
				
				$this->template->assign_vars(array(
					'S_ERROR'		=> $s_errors,
					'ERROR_MSG'		=> $s_errors ? implode('<br />', $errors) : '',
				));
			}

			/*! Render Page */
			$renderer = $this->helper->render('requests_reply_edit.html', $name);
		} else

			/*! User is not registered, redirect to all servers if he attempts to get to /servers/add by url */
			redirect($this->helper->route('evilsystem_requests_controller', array('name' => 'all')));

		return $renderer;
	}
}
