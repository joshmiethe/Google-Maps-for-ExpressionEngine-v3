<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Postmaster
 * 
 * @package		Postmaster
 * @author		Justin Kimbrell
 * @copyright	Copyright (c) 2012, Objective HTML
 * @link 		http://www.objectivehtml.com/postmaster
 * @version		1.0.97
 * @build		20120415
 */

require 'config/postmaster_config.php';

if(!defined('POSTMASTER_VERSION'))
{	
	define('POSTMASTER_VERSION', $config['postmaster_version']);
}

class Postmaster_upd {

    public $version = POSTMASTER_VERSION;
	public $mod_name;
	public $ext_name;
	public $mcp_name;
	
	private $tables = array(
		'postmaster_editor_settings' => array(
			'key'	=> array(
				'type'				=> 'varchar',
				'constraint'		=> 100,
				'primary_key'		=> TRUE
			),
			'value'	=> array(
				'type'			=> 'text'
			)
		),
		'postmaster_parcels' 	=> array(
			'id'	=> array(
				'type'				=> 'int',
				'constraint'		=> 100,
				'primary_key'		=> TRUE,
				'auto_increment'	=> TRUE
			),
			'channel_id' => array(
				'type'			=> 'int',
				'constraint' 	=> 100
			),
			'categories' => array(
				'type'	=> 'text'
			),
			'member_groups' => array(
				'type'	=> 'text'
			),
			'trigger'  => array(
				'type' 		 => 'varchar',
				'constraint' => 100
			),
			'service'  => array(
				'type'       => 'varchar',
				'constraint' => 250
			),
			'statuses' => array(
				'type'	=> 'varchar',
				'constraint' => 100
			),
			'to_name' => array(
				'type'	=> 'text'
			),
			'to_email' => array(
				'type'	=> 'text'
			),
			'from_name' => array(
				'type'	=> 'text'
			),
			'from_email' => array(
				'type'	=> 'text'
			),
			'cc' => array(
				'type'	=> 'text'
			),
			'bcc' => array(
				'type'	=> 'text'
			),
			'subject' => array(
				'type'	=> 'text'
			),
			'message'	=> array(
				'type'	=> 'longtext'
			),
			'settings' => array(
				'type'	=> 'longtext'
			),
			'extra_conditionals' => array(
				'type'	=> 'text'
			),
			'post_date_specific'  => array(
				'type' => 'text'
			),
			'post_date_relative'  => array(
				'type' => 'text'
			),
			'send_every'  => array(
				'type' => 'varchar',
				'constraint' => 100
			)
		),
		'postmaster_queue' 	=> array(
			'id'	=> array(
				'type'				=> 'int',
				'constraint'		=> 100,
				'primary_key'		=> TRUE,
				'auto_increment'	=> TRUE
			),
			'parcel_id' => array(
				'type'			=> 'int',
				'constraint' 	=> 100
			),
			'channel_id' => array(
				'type'			=> 'int',
				'constraint' 	=> 100
			),
			'author_id' => array(
				'type'			=> 'int',
				'constraint' 	=> 100
			),
			'entry_id' => array(
				'type'			=> 'int',
				'constraint' 	=> 100
			),
			'gmt_date' => array(
				'type'			=> 'int',
				'constraint' 	=> 100
			),
			'gmt_send_date' => array(
				'type'			=> 'int',
				'constraint' 	=> 100
			),
			'service'  => array(
				'type'       => 'varchar',
				'constraint' => 250
			),
			'to_name' => array(
				'type'	=> 'text'
			),
			'to_email' => array(
				'type'	=> 'text'
			),
			'from_name' => array(
				'type'	=> 'text'
			),
			'from_email' => array(
				'type'	=> 'text'
			),
			'cc' => array(
				'type'	=> 'text'
			),
			'bcc' => array(
				'type'	=> 'text'
			),
			'subject' => array(
				'type'	=> 'text'
			),
			'message'	=> array(
				'type'	=> 'longtext'
			),
			'send_every'  => array(
				'type' => 'varchar',
				'constraint' => 100
			)
		),
		'postmaster_mailbox' 	=> array(
			'id'	=> array(
				'type'				=> 'int',
				'constraint'		=> 100,
				'primary_key'		=> TRUE,
				'auto_increment'	=> TRUE
			),
			'parcel_id' => array(
				'type'			=> 'int',
				'constraint' 	=> 100
			),
			'channel_id' => array(
				'type'			=> 'int',
				'constraint' 	=> 100
			),
			'author_id' => array(
				'type'			=> 'int',
				'constraint' 	=> 100
			),
			'entry_id' => array(
				'type'			=> 'int',
				'constraint' 	=> 100
			),
			'gmt_date' => array(
				'type'			=> 'int',
				'constraint' 	=> 100
			),
			'service'  => array(
				'type'       => 'varchar',
				'constraint' => 250
			),
			'to_name' => array(
				'type'	=> 'text'
			),
			'to_email' => array(
				'type'	=> 'text'
			),
			'from_name' => array(
				'type'	=> 'text'
			),
			'from_email' => array(
				'type'	=> 'text'
			),
			'cc' => array(
				'type'	=> 'text'
			),
			'bcc' => array(
				'type'	=> 'text'
			),
			'subject' => array(
				'type'	=> 'text'
			),
			'message'	=> array(
				'type'	=> 'longtext'
			),
			'status'  => array(
				'type'       => 'varchar',
				'constraint' => 250
			),
			'parcel'  => array(
				'type'       => 'longtext'
			)
		),
		'postmaster_blacklist' 	=> array(
			'id'	=> array(
				'type'				=> 'int',
				'constraint'		=> 100,
				'primary_key'		=> TRUE,
				'auto_increment'	=> TRUE
			),
			'gmt_date' => array(
				'type'			=> 'int',
				'constraint' 	=> 100
			),
			'ip_address' => array(
				'type'			=> 'varchar',
				'constraint' 	=> 100
			),
			'email' => array(
				'type'			=> 'varchar',
				'constraint' 	=> 250
			)
		),
	);
	
	private $actions = array(
		array(
		    'class'     => 'Postmaster_mcp',
		    'method'    => 'save_settings'
		),
		array(
		    'class'     => 'Postmaster_mcp',
		    'method'    => 'save_editor_settings'
		),
		array(
		    'class'     => 'Postmaster_mcp',
		    'method'    => 'create_parcel_action'
		),
		array(
		    'class'     => 'Postmaster_mcp',
		    'method'    => 'edit_parcel_action'
		),
		array(
		    'class'     => 'Postmaster_mcp',
		    'method'    => 'delete_parcel_action'
		),
		array(
		    'class'     => 'Postmaster_mcp',
		    'method'    => 'duplicate_parcel_action'
		),
		array(
		    'class'     => 'Postmaster_mcp',
		    'method'    => 'parser'
		),
		array(
		    'class'     => 'Postmaster_mcp',
		    'method'    => 'send_email'
		),
		array(
		    'class'     => 'Postmaster_mcp',
		    'method'    => 'blacklist'
		),
		array(
		    'class'     => 'Postmaster_mcp',
		    'method'    => 'unsubscribe'
		),
		array(
		    'class'     => 'Postmaster_mcp',
		    'method'    => 'call'
		),
		array(
		    'class'     => 'Postmaster_mcp',
		    'method'    => 'template'
		)
	);
	
	private $hooks = array(
		array('entry_submission_ready', 'entry_submission_ready'),
		array('entry_submission_end', 'entry_submission_end')
	);
	
    public function __construct()
    {
        // Make a local reference to the ExpressionEngine super object
        $this->EE =& get_instance();
        
        $this->mod_name 	= str_replace('_upd', '', __CLASS__);
        $this->ext_name		= $this->mod_name . '_ext';
        $this->mcp_name		= $this->mod_name . '_mcp';
    }
	
	public function install()
	{	
		$this->EE->load->dbforge();
		
		//create tables from $this->tables array
		$this->EE->load->library('Data_forge');
		
		$this->EE->data_forge->update_tables($this->tables);
		
		$data = array(
	        'module_name' => $this->mod_name,
	        'module_version' => $this->version,
	        'has_cp_backend' => 'y',
	        'has_publish_fields' => 'n'
	    );
	    	
	    $this->EE->db->insert('modules', $data);
	    	    	    
		foreach ($this->hooks as $row)
		{
			$this->EE->db->insert(
				'extensions',
				array(
					'class' 	=> $this->ext_name,
					'method' 	=> $row[0],
					'hook' 		=> ( ! isset($row[1])) ? $row[0] : $row[1],
					'settings' 	=> ( ! isset($row[2])) ? '' : $row[2],
					'priority' 	=> ( ! isset($row[3])) ? 10 : $row[3],
					'version' 	=> $this->version,
					'enabled' 	=> 'y',
				)
			);
		}
		
		foreach($this->actions as $action)
			$this->EE->db->insert('actions', $action);
		
		$this->_set_defaults();
				
		return TRUE;
	}
	
	public function update($current = '')
	{
		if(version_compare($current, POSTMASTER_VERSION, '<'))
		{
			require_once APPPATH.'third_party/postmaster/libraries/Data_forge.php';

			$this->EE->data_forge = new Data_forge();
			$this->EE->data_forge->update_tables($this->tables);

			foreach($this->actions as $action)
			{
				$this->EE->db->where($action);
				$existing = $this->EE->db->get('actions');

				if($existing->num_rows() == 0)
				{
					$this->EE->db->insert('actions', $action);
				}
			}
			
			foreach($this->hooks as $row)
			{
				$this->EE->db->where(array(
					'class'  => $this->ext_name,
					'method'  => $row[0],
					'hook' => $row[1]
				));
				
				$existing = $this->EE->db->get('extensions');

				if($existing->num_rows() == 0)
				{
					$this->EE->db->insert(
						'extensions',
						array(
							'class' 	=> $this->ext_name,
							'method' 	=> $row[0],
							'hook' 		=> ( ! isset($row[1])) ? $row[0] : $row[1],
							'settings' 	=> ( ! isset($row[2])) ? '' : $row[2],
							'priority' 	=> ( ! isset($row[3])) ? 10 : $row[3],
							'version' 	=> $this->version,
							'enabled' 	=> 'y',
						)
					);
				}
			}
		}

	    return TRUE;
	}
	
	public function uninstall()
	{
		$this->EE->load->dbforge();
		
		$this->EE->db->delete('modules', array('module_name' => $this->mod_name));
		$this->EE->db->delete('extensions', array('class' => $this->ext_name));		
		$this->EE->db->delete('actions', array('class' => $this->mod_name));
		
		$this->EE->db->delete('actions', array('class' => $this->mod_name));
		$this->EE->db->delete('actions', array('class' => $this->mcp_name));
		
		foreach(array_keys($this->tables) as $table)
		{
			$this->EE->dbforge->drop_table($table);
		}
			
		return TRUE;
	}
	
	private function _set_defaults()
	{ 
		$text_editor = array(
			array(
				'key' 	=> 'value',
				'value'	=> ''
			),
			array(
				'key' 	=> 'interval',
				'value'	=> '500'
			),
			array(
				'key' 	=> 'mode',
				'value'	=> 'htmlmixed'
			),
			array(
				'key' 	=> 'theme',
				'value'	=> 'rubyblue'
			),
			array(
				'key' 	=> 'indentUnit',
				'value'	=> 2
			),
			array(
				'key' 	=> 'smartUnit',
				'value'	=> 'true'
			),
			array(
				'key' 	=> 'tabSize',
				'value'	=> 4
			),
			array(
				'key' 	=> 'indentWithTabs',
				'value'	=> 'false'
			),
			array(
				'key' 	=> 'electricChars',
				'value'	=> 'true'
			),
			array(
				'key' 	=> 'autoClearEmptyLines',
				'value'	=> 'false'
			),
			array(
				'key' 	=> 'keyMap',
				'value'	=> 'default'
			),
			array(
				'key' 	=> 'lineWrapping',
				'value'	=> 'true'
			),
			array(
				'key' 	=> 'lineNumbers',
				'value'	=> 'true'
			),
			array(
				'key' 	=> 'firstLineNumber',
				'value'	=> 1
			),
			array(
				'key' 	=> 'gutter',
				'value'	=> 'true'
			),
			array(
				'key' 	=> 'fixedGutter',
				'value'	=> 'false'
			),
			array(
				'key' 	=> 'matchBrackets',
				'value'	=> 'true'
			),
			array(
				'key' 	=> 'pollInterval',
				'value'	=> 100
			),
			array(
				'key' 	=> 'undoDepth',
				'value'	=> 40
			),
			array(
				'key' 	=> 'height',
				'value'	=> '500px'
			)
		);

		foreach($text_editor as $row)
		{
			$this->EE->db->insert('postmaster_editor_settings', $row);
		}
	}
}