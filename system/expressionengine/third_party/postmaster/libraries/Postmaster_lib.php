<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Postmaster Library
 * 
 * @package		Postmaster
 * @subpackage	Libraries
 * @author		Justin Kimbrell
 * @copyright	Copyright (c) 2012, Objective HTML
 * @link 		http://www.objectivehtml.com/postmaster
 * @version		1.0.971
 * @build		20120417
 */

if(!defined('POSTMASTER_SUCCESS'))
{
	define('POSTMASTER_SUCCESS', 'success');
}

if(!defined('POSTMASTER_FAILED'))
{
	define('POSTMASTER_FAILED', 'failed');
}

require_once APPPATH.'libraries/Template.php';

class Postmaster_lib {
	
	public $service_suffix = '_postmaster_service';

	public function __construct()
	{
		$this->EE =& get_instance();

		$this->EE->load->driver('channel_data');
	}

	public function add_to_queue($parsed_object, $parcel, $date = FALSE)
	{

		$this->EE->db->where(array(
			'parcel_id'     => $parcel->id,
			'channel_id'    => $parcel->channel_id,
			'author_id'     => $parcel->entry->author_id,
			'entry_id'      => $parcel->entry->entry_id,
		));

		$existing = $this->EE->db->get('postmaster_queue');

		if($existing->num_rows() == 0)
		{
			if(!$date)
			{
				$date = $this->get_send_date($parsed_object);
			}

			$data = array(
				'parcel_id'     => $parcel->id,
				'channel_id'    => $parcel->channel_id,
				'author_id'     => $parcel->entry->author_id,
				'entry_id'      => $parcel->entry->entry_id,
				'gmt_date'      => $this->EE->localize->now,
				'gmt_send_date' => $date,
				'service'       => $parcel->service,
				'to_name'       => $parsed_object->to_name,
				'to_email'      => $parsed_object->to_email,
				'from_name'     => $parsed_object->from_name,
				'from_email'    => $parsed_object->from_email,
				'cc'            => $parsed_object->cc,
				'bcc'           => $parsed_object->bcc,
				'subject'       => $parsed_object->subject,
				'message'       => $parsed_object->message,
				'send_every'    => $parsed_object->send_every
			);

			$this->EE->db->insert('postmaster_queue', $data);
		}
	}

	public function blacklist($email)
	{
		$this->unsubscribe($email);

		if(!$this->is_blacklisted($email))
		{
			$this->EE->db->insert('postmaster_blacklist', array(
				'gmt_date'   => $this->EE->localize->now,
				'ip_address' => $this->EE->input->ip_address(),
				'email'      => $email
			));
		}
	}

	public function is_blacklisted($email)
	{
		$this->db->where('email', $email);
		$existing = $this->db->get('postmaster_blacklist');

		if($existing->num_rows() == 0)
		{
			return FALSE;
		}

		return TRUE;
	}

	public function convert_data($data, $index)
	{
		$array = array();

		foreach($data->result() as $row)
		{
			$array[$row->$index] = $row;
		}
		
		return $array;
	}

	public function convert_string($string, $pool)
	{
		$explode_array = explode('|', $string);

		$array  = array();
		
		foreach($explode_array as $index => $row)
		{
			if(isset($pool[$row]))
			{
				$array[] = $pool[$row];
			}
		}

		return $array;
	}

	public function delete($id)
	{
		$this->EE->db->where('id', $id);
		$this->EE->db->delete('postmaster_parcels');
	}

	public function duplicate($id)
	{
		$entry = $this->EE->channel_data->get('postmaster_parcels', array(
			'where' => array(
				'id' => $id
			)
		))->row_array();

		unset($entry['id']);

		$this->EE->db->insert('postmaster_parcels', $entry);
	}

	public function get_email_queue($start = 'now', $end = FALSE)
	{
		if($start == 'now')
		{
			$start = $this->EE->localize->now;
		}

		$this->EE->db->where('gmt_send_date <=', $start);

		if($end)
		{
			$this->EE->db->where('gmt_send_date >=', $end);
		}

		return $this->EE->db->get('postmaster_queue');
	}

	public function get_editor_settings_json()
	{
		$settings = $this->get_editor_settings();

		foreach($settings as $index => $setting)
		{
			if($setting == 'true' || $setting == 'false')
			{
				$settings[$index] = $setting == 'true' ? TRUE : FALSE;
			}

			if(preg_match("/\d/", $setting))
			{
				$settings[$index] = (int) $setting;
			}
		}

		return json_encode($settings);
	}

	public function get_entry($entry_id)
	{
		$entry = $this->EE->channel_data->get_channel_entry($entry_id);
		
		if($entry->num_rows() == 1)
		{
			return $entry->row();
		}

		return FALSE;
	}

	public function get_themes()
	{
		$this->EE->load->helper('directory');

		$directory = $this->EE->theme_loader->theme_path().'third_party/postmaster/css/themes/';

		$themes = array();

		foreach(directory_map($directory) as $theme)
		{
			$name = str_replace('.css', '', $theme);

			$this->EE->theme_loader->css('themes/'.$name);

			$themes[] = (object) array(
				'value' => $name,
				'name'  => ucfirst($name)
			);
		}

		return $themes;
	}

	public function get_parcel($id)
	{
		$this->EE->db->where('id', $id);

		return $this->EE->db->get('postmaster_parcels')->row();
	}

	public function get_parcels()
	{
		$parcels = $this->EE->db->get('postmaster_parcels')->result();

		$channels      = $this->convert_data($this->EE->channel_data->get_channels(), 'channel_id');
		$categories    = $this->convert_data($this->EE->channel_data->get_categories(), 'cat_id');
		$member_groups = $this->convert_data($this->EE->channel_data->get_member_groups(), 'group_id');
		
		foreach($parcels as $index => $parcel)
		{
			if(isset($channels[$parcel->channel_id]))
			{
				$parcels[$index]->channel_name 	 = $channels[$parcel->channel_id]->channel_name;
			}
			else
			{
				$parcels[$index]->channel_name = '<i>This channel no longer exists.</i>';
			}

			$parcels[$index]->trigger 		 = explode('|', $parcel->trigger);
			$parcels[$index]->categories  	 = $this->convert_string($parcel->categories, $categories);
			$parcels[$index]->member_groups  = $this->convert_string($parcel->member_groups, $member_groups);
			$parcels[$index]->statuses  	 = $parcel->statuses != NULL ? explode('|', $parcel->statuses) : array();
			$parcels[$index]->settings 		 = json_decode($parcel->settings);
			$parcels[$index]->edit_url		 = $this->cp_url('edit_parcel') . '&id='.$parcel->id;
			$parcels[$index]->duplicate_url	 = $this->cp_url('duplicate_parcel_action') . '&id='.$parcel->id.'&return=index';
			$parcels[$index]->delete_url	 = $this->cp_url('delete_parcel_action') . '&id='.$parcel->id.'&return=index';
		}

		return $parcels;
	}

	public function get_send_date($parsed_object)
	{
		$send_date = $parsed_object->post_date_specific;
		$send_date = !empty($send_date) ? strtotime($send_date) : $this->EE->localize->now;

		if(!empty($parsed_object->post_date_relative))
		{
			$send_date = strtotime($parsed_object->post_date_relative, $send_date);
		}

		return $this->EE->localize->set_localized_time($send_date);
	}

	public function get_editor_settings($key = FALSE)
	{
		$settings 		 = $this->EE->db->get('postmaster_editor_settings')->result();
		$settings_array  = array();

		foreach($settings as $setting)
		{
			$settings_array[$setting->key] = $setting->value;
		}

		if($key)
		{
			return $settings_array[$key];
		}

		return $settings_array;
	}

	public function load_service($name)
	{
		require_once APPPATH . 'third_party/postmaster/libraries/Postmaster_service.php';
		require_once APPPATH . 'third_party/postmaster/services/'.ucfirst($name).'.php';

		$class = $name.$this->service_suffix;

		return new $class;
	}

	public function parse($parcel)
	{
		if(!isset($this->EE->TMPL))
		{
			require_once APPPATH.'/libraries/Template.php';
		}
		
		$this->EE->load->library('typography');
		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_fields');

		$fields = $this->EE->api_channel_fields->fetch_custom_channel_fields();

		//var_dump($fields);exit();

		$entry  = (array) $parcel->entry;
		$member = $this->EE->channel_data->get_member(isset($entry['author_id']) ? $entry['author_id'] : 0)->row_array();
		$member = $this->EE->channel_data->utility->add_prefix('member', $member);
		
		foreach(array('password', 'salt', 'crypt_key', 'unique_id') as $var)
		{
			unset($member[$var]);
		}

		$fields = array(
			'to_name',
			'to_email',
			'from_name',
			'from_email',
			'cc',
			'bcc',
			'subject',
			'message',
			'post_date_specific',
			'post_date_relative',
			'send_every',
			'extra_conditionals'
		);

		$parse_object = (object) array();

		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_fields');

		//$installed_fieldtypes = $this->EE->api_channel_fields->fetch_installed_fieldtypes();
		if(isset($parcel->entry->channel_id))
		{
			$channel_fields 	  = $this->EE->channel_data->get_channel_fields($parcel->entry->channel_id)->result();
	
			foreach($channel_fields as $index => $field)
			{
				$channel_fields[$field->field_name] = $field;
				unset($channel_fields[$index]);
			}
		}
		
		foreach($fields as $field)
		{
			if(!empty($parcel->$field))
			{
				$this->EE->TMPL = new EE_Template();
				$this->EE->TMPL->template = $parcel->$field;					
				$this->EE->TMPL->template = $this->EE->TMPL->parse_globals($this->EE->TMPL->template);
					
				$vars = $this->EE->functions->assign_variables($this->EE->TMPL->template);

				foreach($vars['var_single'] as $single_var)
				{
					$params = $this->EE->functions->assign_parameters($single_var);

					$single_var_array = explode(' ', $single_var);
					
					$field_name = str_replace('parcel:', '', $single_var_array[0]);
				
					$entry = FALSE;

					if(isset($channel_fields[$field_name]))
					{
						$field_type = $channel_fields[$field_name]->field_type;
						$field_id   = $channel_fields[$field_name]->field_id;
						$data       = $parcel->entry->$field_name;

						if($this->EE->api_channel_fields->setup_handler($field_id))
						{
							$this->EE->db->select('*');
							$this->EE->db->join('channel_titles', 'channel_titles.entry_id = channel_data.entry_id');
							$this->EE->db->join('channels', 'channel_data.channel_id = channels.channel_id');
							$row = $this->EE->db->get_where('channel_data', array('channel_data.entry_id' => $parcel->entry->entry_id))->row_array();
					
							$this->EE->api_channel_fields->apply('_init', array(array('row' => $row)));

							// Preprocess
							$data = $this->EE->api_channel_fields->apply('pre_process', array($row['field_id_'.$field_id]));

							$entry = $this->EE->api_channel_fields->apply('replace_tag', array($data, $params, FALSE));

							$this->EE->TMPL->template = $this->EE->TMPL->swap_var_single($single_var, $entry, $this->EE->TMPL->template );
						}
					}
				}

				$pair_vars = array();

				foreach($vars['var_pair'] as $pair_var => $params)
				{
					$pair_var_array = explode(' ', $pair_var);
					
					$field_name = str_replace('parcel:', '', $pair_var_array[0]);
					$offset = 0;

					while (($end = strpos($this->EE->TMPL->template, LD.'/parcel:'.$field_name.RD, $offset)) !== FALSE)
					{
						if (preg_match("/".LD."parcel:{$field_name}(.*?)".RD."(.*?)".LD.'\/parcel:'.$field_name.RD."/s", $this->EE->TMPL->template, $matches, 0, $offset))
						{
							$chunk  = $matches[0];
							$params = $matches[1];
							$inner  = $matches[2];

							// We might've sandwiched a single tag - no good, check again (:sigh:)
							if ((strpos($chunk, LD.$field_name, 1) !== FALSE) && preg_match_all("/".LD."parcel:{$field_name}(.*?)".RD."/s", $chunk, $match))
							{
								// Let's start at the end
								$idx = count($match[0]) - 1;
								$tag = $match[0][$idx];
								
								// Reassign the parameter
								$params = $match[1][$idx];

								// Cut the chunk at the last opening tag (PHP5 could do this with strrpos :-( )
								while (strpos($chunk, $tag, 1) !== FALSE)
								{
									$chunk = substr($chunk, 1);
									$chunk = strstr($chunk, LD.$field_name);
									$inner = substr($chunk, strlen($tag), -strlen(LD.'/'.$field_name.RD));
								}
							}
							
							$pair_vars[$field_name] = array($inner, $this->EE->functions->assign_parameters($params), $chunk);
						}
						
						$offset = $end + 1;
					}

					foreach($pair_vars as $field_name => $pair_var)
					{																
						if(isset($channel_fields[$field_name]))
						{
							$field_type = $channel_fields[$field_name]->field_type;
							$field_id   = $channel_fields[$field_name]->field_id;

							$data       = $parcel->entry->$field_name;

							if($this->EE->api_channel_fields->setup_handler($field_id))
							{
								$entry = $this->EE->api_channel_fields->apply('replace_tag', array($data, $pair_var[1], $pair_var[0]));

								$this->EE->TMPL->template = str_replace($pair_var[2], $entry, $this->EE->TMPL->template);
							}
						}
					}

					$entry = FALSE;
				}

				$entry  = $this->EE->channel_data->utility->add_prefix('parcel', $parcel->entry);

				$entry  = array_merge($member, $entry);
				
				$entry['parcel:author'] = isset($member['member:screen_name']) ? $member['member:screen_name'] : (isset($member['member:username']) ? $member['member:username'] : NULL);
				
				$this->EE->TMPL->template = $this->EE->TMPL->parse_variables_row($this->EE->TMPL->template, $entry);
				$this->EE->TMPL->parse($this->EE->TMPL->template);
				
				$parcel->$field 	  = $this->EE->TMPL->template;
				$parse_object->$field = $parcel->$field;	
			}
			else
			{
				$parse_object->$field = $parcel->$field;
			}
		}
	
		return $parse_object;
	}

	public function remove_from_queue($id)
	{
		$this->EE->db->where('id', $id);
		$this->EE->db->delete('postmaster_queue');
	}

	public function save_editor_settings($settings)
	{
		foreach($settings as $key => $value)
		{
			$this->EE->db->where('key', $key);
			$this->EE->db->update('postmaster_editor_settings', array(
				'value' => $value
			));
		}
	}

	public function save_response($response)
	{
		if(is_object($response->parcel))
		{
			$response->parcel = json_encode($response->parcel);
		}

		$this->EE->db->insert('postmaster_mailbox', (array) $response);
	}

	public function send($parsed_object, $parcel, $ignore_date = FALSE)
	{
		$service   = $this->load_service($parcel->service);
		$send_date = $this->get_send_date($parsed_object);

		if($this->validate_emails($parsed_object->to_email))
		{
			if($ignore_date || $send_date <= $this->EE->localize->now)
			{
				$response = $service->send($parsed_object, $parcel);
				$this->save_response($response);

				if(!empty($parsed_object->send_every))
				{
					$gmt_date = $this->EE->localize->set_localized_time(strtotime($parsed_object->send_every, $this->EE->localize->now));
					$this->add_to_queue($parsed_object, $parcel, $gmt_date);
				}
			}
			else
			{
				$this->add_to_queue($parsed_object, $parcel);
			}
		}
		else
		{
			$this->unsubscribe($parsed_object->to_email);
		}
	}

	public function send_from_queue($row)
	{
		$parcel           = $this->get_parcel($row->parcel_id);
		$parcel->entry    = $this->get_entry($row->entry_id);
		$parcel->settings = json_decode($parcel->settings);

		$parsed_object = $this->parse($parcel);

		$this->send($parsed_object, $parcel, TRUE);
		$this->remove_from_queue($row->id);
	}

	public function unsubscribe($email)
	{
		$this->EE->db->where('to_email', $email);
		$this->EE->db->delete('postmaster_queue');
	}

	public function validate_channel_entry($entry_id, $meta, $data)
	{
		$this->EE->TMPL = new EE_Template();

		$parcels = $this->get_parcels();

		foreach($parcels as $index => $parcel)
		{
			$entry_data = isset($data['revision_post']) ? $data['revision_post'] : $data;

			if($parcel->channel_id == $data['channel_id'])
			{
				$entry_data['category'] = isset($entry_data['category']) ? $entry_data['category'] : array();

				if($this->validate_trigger($parcel->trigger))
				{
					if($this->validate_categories($entry_data['category'], $parcel->categories))
					{
						if($this->validate_member($meta['author_id'], $parcel->member_groups))
						{
							if($this->validate_status($meta['status'], $parcel->statuses))
							{
								$parcel->entry = $this->EE->channel_data->get_channel_entry($entry_id)->row();
								
								$parsed_object = $this->parse($parcel);
								
								$parsed_object->settings = $parcels[$index]->settings;

								if($this->validate_conditionals($parsed_object->extra_conditionals))
								{
									$this->send($parsed_object, $parcel);
								}
							}
						}
					}
				}
			}
		}
	}

	public function validate_conditionals($extra_conditionals)
	{
		$extra_conditionals = trim(strtoupper($extra_conditionals));

		if(empty($extra_conditionals) || $extra_conditionals == 'TRUE')
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	public function validate_categories($subject, $valid_categories)
	{
		$valid = 0;

		foreach($valid_categories as $category)
		{
			if(count($subject) > 0 && in_array($category->cat_id, $subject))
			{
				$valid++;
			}
		}
		
		return count($valid_categories) == $valid ? TRUE : FALSE;
	}

	public function validate_email($emails = '')
	{
		if(!is_array($emails))
		{
			$emails = array($emails);
		}

		foreach($emails as $email)
		{
			$this->EE->db->or_where('email', $email);
		}

		$this->EE->db->from('postmaster_blacklist');

		if($this->EE->db->count_all_results() == 0)
		{
			return TRUE;
		}

		return FALSE;
	}

	public function validate_emails($emails)
	{
		$emails = explode(',', $emails);

		foreach($emails as $index => $email)
		{
			$emails[$index] = trim($email);
		}

		return $this->validate_email($emails);
	}

	public function validate_member($subject, $valid_members)
	{
		$valid  = FALSE;
		$member = $this->EE->channel_data->get_member($subject)->row();

		foreach($valid_members as $member)
		{
			if($member->group_id == $member->group_id)
			{
				$valid = TRUE;
			}
		}

		return $valid;
	}
	
	public function validate_status($subject, $statuses)
	{
		$valid = FALSE;

		foreach($statuses as $status)
		{
			if($subject == $status)
			{
				$valid = TRUE;
			}
		}

		return $valid;
	}

	public function validate_trigger($triggers)
	{
		if(is_string($triggers))
		{
			$triggers = explode('|', $triggers);
		}

		$entry_trigger = $this->EE->session->cache('postmaster', 'entry_trigger');

		$valid  	   = FALSE;

		if(in_array($entry_trigger, $triggers))
		{
			$valid = TRUE;
		}

		return $valid;
	}

	public function cp_url($method = 'index', $useAmp = FALSE)
	{
		if(!defined('BASE'))
		{
			define('BASE', '');
		}

		$amp  = !$useAmp ? AMP : '&';

		$file = substr(BASE, 0, strpos(BASE, '?'));
		$file = str_replace($file, '', $_SERVER['PHP_SELF']) . BASE;

		$url  = $file .$amp. '&C=addons_modules' .$amp . 'M=show_module_cp' . $amp . 'module=postmaster' . $amp . 'method=' . $method;

		return str_replace(AMP, $amp, $url);
	}	
	
	public function current_url($append = '', $value = '')
	{
		$http = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
		
		$port = $_SERVER['SERVER_PORT'] == '80' || $_SERVER['SERVER_PORT'] == '443' ? NULL : ':' . $_SERVER['SERVER_PORT'];
		
		if(!isset($_SERVER['SCRIPT_URI']))
		{				
			 $_SERVER['SCRIPT_URI'] = $http . $_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI'];
		}
		
		$base_url = $http . $_SERVER['HTTP_HOST'];
		
		if(!empty($append))
		{
			$base_url .= '?'.$append.'='.$value;
		}
		
		return $base_url;
	}

	public function create_parcel($parcel)
	{
		$this->EE->db->insert('postmaster_parcels', $parcel);
	}

	public function edit_parcel($parcel, $id)
	{
		$this->EE->db->where('id', $id);
		$this->EE->db->update('postmaster_parcels', $parcel);
	}
}