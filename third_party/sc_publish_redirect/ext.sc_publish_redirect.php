<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Sassafras Consulting Publish Redirect Extension class
*
* @package			SC_Publish_Redirect
* @version			1.0.1
* @author			Andrew Gunstone ~ <andrew@thirststudios.com>
* @link				http://sassafrasconsulting.com.au/software/publish-redirect/
* @license			http://creativecommons.org/licenses/by-sa/3.0/
*/

/**
 * Changelog
 * Version 1.0.1 20101104
 * --------------------
 * Changed priority for extension because it conflicted with Structure Module
 * 
 * Version 1.0 20100111
 * --------------------
 * Initial public release
 */

class Sc_publish_redirect_ext
{
	/**
	* Extension settings
	*
	* @var	array
	*/
	var $settings = array();

	/**
	* Extension name
	*
	* @var	string
	*/
	var $name = 'SC Publish Redirect';

	/**
	* Extension version
	*
	* @var	string
	*/
	var $version = '1.0.1';

	/**
	* Extension description
	*
	* @var	string
	*/
	var $description = 'Redirect publish/edit pages back to themselves';

	/**
	* Do settings exist?
	*
	* @var	bool
	*/
	var $settings_exist = 'y';
	
	/**
	* Documentation link
	*
	* @var	string
	*/
	var $docs_url = 'http://sassafrasconsulting.com.au/software/publish-redirect/';

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @author		Andrew Gunstone <andrew@thirststudios.com>
	 * @copyright	Copyright (c) 2010 Sassafras Consulting Pty Ltd
	 * @access		Public
	 */
	function Sc_publish_redirect_ext($settings = '')
	{
		$this->EE =& get_instance();

		$this->settings = $settings;
	}
	/* End of Sc_publish_redirect_ext */


	/**
	 * Settings
	 *
	 * @author		Andrew Gunstone <andrew@thirststudios.com>
	 * @copyright	Copyright (c) 2010 Sassafras Consulting Pty Ltd
	 * @access		Public
	 */
	function settings()
	{

		$this->EE->lang->loadfile('sc_publish_redirect');
		
		$settings = array();

		$settings['redirect_select'] = array('s', array(
			'self' => $this->EE->lang->line('option_self'),
			'edit' => $this->EE->lang->line('option_edit'),
			'publish' => $this->EE->lang->line('option_publish'),
			'custom_url' => $this->EE->lang->line('option_custom')), 
			'self'
		);
    	$settings['redirect_url'] = array('t', array('rows' => 1), "");
    	$settings['success_flash'] = array('t', array('rows' => 1), $this->EE->lang->line('success_message'));

    	return $settings;
	}
	/* End of Settings */


	/**
	 * Entry submission redirect hook
	 *
	 * @author		Andrew Gunstone <andrew@thirststudios.com>
	 * @copyright	Copyright (c) 2010 Sassafras Consulting Pty Ltd
	 * @access		Public
	 */
	function entry_submission_redirect($entry_id, $meta, $data, $cp_call)
	{
		if ($cp_call)
		{
			$this->EE->lang->loadfile('sc_publish_redirect');
	
			// Set the success message
			if (isset($this->settings['success_flash']) AND trim($this->settings['success_flash']) != "")
				$this->EE->session->set_flashdata('message_success', $this->settings['success_flash']);
			else
				$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('success_message'));
			
			// Set available parsing variables
			$variables = Array(
				Array(
				'base' => BASE,
				'entry_id' => $entry_id,
				'channel_id' => $meta['channel_id']
				)
			);
			
			// Load template class
			$this->EE->load->library('template');
			
			// Set the return url based on the extension settings
			$redirect_select = '';
			if (isset($this->settings['redirect_select']))
				$redirect_select = $this->settings['redirect_select'];
				
			switch($redirect_select) 
			{
				case 'self':
					$return_url = '{base}&D=cp&C=content_publish&M=entry_form&channel_id={channel_id}&entry_id={entry_id}';
					break;
				case 'edit':
					$return_url = '{base}&D=cp&C=content_edit';
					break;
				case 'publish':
					$return_url = '{base}&D=cp&C=content_publish';
					break;
				case 'custom_url':
					$return_url = $this->settings['redirect_url'];
					break;
				default:
					$return_url = '{base}&D=cp&C=content_publish&M=entry_form&channel_id={channel_id}&entry_id={entry_id}';
					break;
			}
			
			// Replace variables
			$r = $this->EE->template->parse_variables($return_url, $variables);
	
			return $r;
		}
	}
	/* End of Entry_submission_redirect */


	/**
	 * Activate this extension
	 *
	 * @author		Andrew Gunstone <andrew@thirststudios.com>
	 * @copyright	Copyright (c) 2010 Sassafras Consulting Pty Ltd
	 * @access		Public
	 */
	function activate_extension()
	{
		// data to insert
		$data = 
			array(
				'class'		=> __CLASS__,
				'method'	=> 'entry_submission_redirect',
				'hook'		=> 'entry_submission_redirect',
				'priority'	=> 5,
				'version'	=> $this->version,
				'enabled'	=> 'y',
				'settings'	=> ''
			);
		
		// insert in database
		$this->EE->db->insert('exp_extensions', $data);
	}
	/* End of Activate_extension */


	/**
	 * Update this extension
	 *
	 * @author		Andrew Gunstone <andrew@thirststudios.com>
	 * @copyright	Copyright (c) 2010 Sassafras Consulting Pty Ltd
	 * @access		Public
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
		
		// Init data array
		$data = array();
		
		// Add version to data array
		$data['version'] = $this->version;

		// Update records using data array
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->update('exp_extensions', $data);
	}
	/* End of update_extension */


	/**
	 * Disable this extension
	 *
	 * @author		Andrew Gunstone <andrew@thirststudios.com>
	 * @copyright	Copyright (c) 2010 Sassafras Consulting Pty Ltd
	 * @access		Public
	 */
	function disable_extension()
	{
		// Delete records
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('exp_extensions');
	}
	/* End of disable_extension */
	 
}
// END CLASS

/* End of file ext.sc_publish_redirect.php */