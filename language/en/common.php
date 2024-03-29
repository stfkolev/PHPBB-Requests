<?php
/**
 *
 * Requests. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, Evil
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(

	'REQUESTS_HELLO'							=> 'Hello %s!',
	'REQUESTS_GOODBYE'							=> 'Goodbye %s!',

	'EVILSYSTEM_REQUESTS_NOTIFICATION'			=> 'Requests notification',

	'REQUESTS_PAGE'								=> 'Requests',
	'REQUESTS_PAGE_MAKE'						=> 'Make Request',
	'REQUESTS_PAGE_EDIT'						=> 'Edit Request',
	'REPLIES_PAGE_EDIT'						=> 'Edit Reply',
	'VIEWING_EVILSYSTEM_REQUESTS'				=> 'Viewing Requests page',

	/*! Errors */
	'NO_REQUESTS_ATM'							=> 'Currently there are no requests in our database!',

	/*! Page Language */
	'REQUESTS_COUNT'							=> '%d requests',
	'REQUESTS_MAKE_REQUEST'						=> 'Make Request',
	'REQUESTS_EDIT_REQUEST'						=> 'Edit Request',
	'REQUESTS_PAGINATION'						=> 'Page <strong>%d</strong> of <strong>%d</strong>',
	'REQUESTS_REQUEST_ADDED'					=> 'Your request has been added to our database successfully!',
	'REQUESTS_REQUEST_EDITED'					=> 'Your request has been modified successfully!',
	'REQUESTS_REPLY_EDITED'						=> 'Your reply has been modified successfully!',
	'REQUESTS_RETURN'							=> '%sReturn to all requests%s',
	'REQUESTS_APPROVED'							=> 'You approved the reply!',
	'REQUESTS_APPROVED_RETURN'					=> '%sReturn to the request%s',
	'REQUESTS_DISAPPROVED'						=> 'You disapproved the reply!',
	'REQUESTS_DISAPPROVED_RETURN'				=> '%sReturn to the request%s',
	'REQUESTS_NOT_FOUND'						=> 'Could not find this request!',
	'REQUESTS_REQUEST_ALREADY_APPROVED'			=> 'The request is already approved!',
	'REQUESTS_REQ_MADE'							=> 'Requests',
	'REQUESTS_REPL_MADE'						=> 'Replies',
	'REQUESTS_POSTS_MADE'						=> 'Posts',
	'REQUESTS_APPROVE'							=> 'Approve',
	'REQUESTS_DISAPPROVE'						=> 'Disapprove',
	'REQUESTS_EDIT'								=> 'Modify',

	/*! Request Status */
	'REQUESTS_STATUS_WAITING'					=> 'Waiting',
	'REQUESTS_STATUS_INPROGRESS'				=> 'In Progress',
	'REQUESTS_STATUS_FINISHED'					=> 'Finished',

	/*! Requests Table (Main Page) */
	'REQUESTS_TABLE_TITLE'						=> 'Title',
	'REQUESTS_TABLE_AUTHOR'						=> 'Author',
	'REQUESTS_TABLE_TYPE'						=> 'Type',
	'REQUESTS_TABLE_REPLIES'					=> 'Replies',
	'REQUESTS_TABLE_STATUS'						=> 'Status',

	/*! Requests Form (Make Request Page) */
	'REQUESTS_FORM_TITLE'						=> 'Title',
	'REQUESTS_FORM_TITLE_DESC'					=> 'Enter short description of what you want in one sentence',
	'REQUESTS_FORM_TYPE'						=> 'Type',
	'REQUESTS_FORM_TYPE_DESC'					=> 'What kind of artwork you want? (Banner, Avatar, etc)',
	'REQUESTS_FORM_WIDTH'						=> 'Width',
	'REQUESTS_FORM_WIDTH_DESC'					=> 'The width of the artwork (min. 0, max. 8192)',
	'REQUESTS_FORM_HEIGHT'						=> 'Height',
	'REQUESTS_FORM_HEIGHT_DESC'					=> 'The height of the artwork (min. 0, max. 8192)',
	'REQUESTS_FORM_ADDITIONAL'					=> 'Additional',
	'REQUESTS_FORM_ADDITIONAL_DESC'				=> 'If you want to add something like topic, animations, here is the place',

	/*! Reply Form Modify */
	'REPLIES_FORM_ADDITIONAL'					=> 'Additional',
	'REPLIES_FORM_ADDITIONAL_DESC'				=> 'If you want to change something you might missclicked',

	/*! Requests Reply (View Request) */
	'REQUESTS_VIEW_TYPE'						=> 'Type of the request',
	'REQUESTS_VIEW_WIDTH'						=> 'Width of the artwork',
	'REQUESTS_VIEW_HEIGHT'						=> 'Height of the artwork',
	'REQUESTS_VIEW_ADDITIONAL'					=> 'Additional information',

));
