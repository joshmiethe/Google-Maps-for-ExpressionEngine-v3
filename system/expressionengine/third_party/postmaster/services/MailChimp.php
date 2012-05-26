<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * MailChimp
 *
 * Allows you to email create/send campaigns using MailChimp
 *
 * @package		Postmaster
 * @subpackage	Services
 * @author		Justin Kimbrell
 * @copyright	Copyright (c) 2012, Objective HTML
 * @link 		http://www.objectivehtml.com/postmaster
 * @version		1.0.0
 * @build		20120412
 */

require_once APPPATH.'third_party/postmaster/libraries/Postmaster_service.php';

class Mailchimp_postmaster_service extends Postmaster_service {

	public $name = 'MailChimp';
	public $url  = '';

	public $fields = array(
		'api_key' => array(
			'label' => 'API Key',
			'id'	=> 'mailchimp_api_key'
		)
	);

	public $description = '
	<p>Easy Email Newsletters
MailChimp helps you design email newsletters, share them on social networks, integrate with services you already use, and track your results. It\'s like your own personal publishing platform.</p>

	<h4>Links</h4>

	<ul>
		<li><a href="http://mailchimp.com/features/">Feature</a></li>
		<li><a href="http://mailchimp.com/pricing/">Pricing</a></li>
		<li><a href="http://kb.mailchimp.com/">Support</a></li>
		<li><a href="https://login.mailchimp.com/">Login</a></li>
	</ul>';

	public function __construct()
	{
		parent::__construct();
	}

	public function send($parsed_object, $parcel)
	{
		$settings = $this->get_settings($parcel->settings);

		foreach($settings->list_id as $list_id)
		{
			$response = $this->create_campaign($list_id, $parsed_object, $parcel);	
			
			if(empty($response))
			{
				show_error('Something has gone wrong. Your email campaign has not been created.');
			}

			$response = $this->send_campaign($settings->api_key, $response);
		}

		return new Postmaster_Service_Response(array(
			'status'     => $response ? POSTMASTER_SUCCESS : POSTMASTER_FAILED,
			'parcel_id'  => $parcel->id,
			'channel_id' => $parcel->channel_id,
			'author_id'  => $parcel->entry->author_id,
			'entry_id'   => $parcel->entry->entry_id,
			'gmt_date'   => $this->now,
			'service'    => $parcel->service,
			'to_name'    => $parsed_object->to_name,
			'to_email'   => $parsed_object->to_email,
			'from_name'  => $parsed_object->from_name,
			'from_email' => $parsed_object->from_email,
			'cc'         => $parsed_object->cc,
			'bcc'        => $parsed_object->bcc,
			'subject'    => $parsed_object->subject,
			'message'    => $parsed_object->message,
			'parcel'     => $parcel
		));
	}

	public function display_settings($settings, $parcel)
	{
		$html = $this->build_table($settings, $this->fields);

		$html .= '
		<h3>Mailing Lists</h3>
		<p>Select all of the following lists in which you want to use to send your campaign. <a href="#" class="mailchimp-refresh">Refresh the List</a></p>';

		$html .= $this->display_mailing_lists($settings, $parcel);

		return $html;
	}

	public function display_mailing_lists($settings, $parcel)
	{
		
		$settings = $this->get_settings($settings);

		$url = $this->call_url('get_lists');

		$html = "
		<script type=\"text/javascript\">
			$(document).ready(function() {

				var url = '".$url."';

				$('#mailchimp_api_key').blur(function() {
					$('.mailchimp-refresh').click();
				});

				$('.mailchimp-refresh').click(function() {

					var apiKey = $('#mailchimp_api_key').val();

					$.get(url+'&api_key='+apiKey+'&ajax=1', function(data) {
						$('#mailchimp-lists tbody').html(data);
					});

					return false;
				});

			});
		</script>

		<table class=\"mainTable\" id=\"mailchimp-lists\" cellspacing=\"0\" cellpadding=\"0\">
			<thead>
				<tr>
					<th></th>
					<th>Name</th>
					<th>Date Created</th>
					<th>Subscriber Count</th>
					<th>New Subscriber</th>
					<th>Campaign Count</th>
				</tr>
			</thead>
			<tbody>";

			$lists = $this->get_lists($settings->api_key);

			$html .= $this->list_rows($lists, $settings);

		$html .= '
			</tbody>
		</table>';

		return $html;
	}

	public function list_rows($lists, $settings)
	{
		$html = NULL;

		if(!isset($parcel->service))
		{
			$parcel->service = 'MailChimp';
		}

		if($parcel->service == $this->name && $lists->total > 0)
		{
			foreach($lists->data as $list)
			{
				$checked = in_array($list->id, isset($settings->list_id) ? $settings->list_id : array()) ? 'checked="checked"' : NULL;

				$html .= '
				<tr>
					<td><input type="checkbox" name="setting['.$this->name.'][list_id][]" value="'.$list->id.'" '.$checked.' /></td>
					<td>'.$list->name.'</td>
					<td>'.date('F j Y', strtotime($list->date_created)).'</td>
					<td>'.$list->stats->member_count.'</td>
					<td>'.$list->stats->member_count_since_send.'</td>
					<td>'.$list->stats->campaign_count.'</td>
				</tr>';
			}
		}
		else
		{
			$empty_message = 'You have no lists associated with your MailChimp account. If you are sure you have created lists, make sure your API key is correct and <a href="#" class="mailchimp-refresh">Refresh the List</a>.';

			$api_message = 'You have not entered a MailChimp API. Be sure to enter your API key and <a href="#" class="mailchimp-refresh">Refresh the List</a>.';

			$message = !empty($settings->api_key) ? $empty_message : $api_message;

			$html .= '
			<tr>
				<td></td>
				<td colspan="6"><p>'.$message.'</p></td>
			</tr>';
		}		

		return $html;
	}

	public function api_url($api_key, $method, $params = array())
	{
		$params['apikey'] = $api_key;

		$url = 'http://<dc>.api.mailchimp.com/1.3/?output=json&method='.$method.'&'.http_build_query($params);
		
		return str_replace('<dc>', substr($api_key, strpos($api_key, '-')+1), $url);
	}

	public function create_campaign($list_id, $parsed_object, $parcel)
	{
		$settings = $parcel->settings->{$this->name};

		$params = array(
			'type'    => 'regular',
			'options' => array(
				'list_id'    => $list_id,
				'subject'    => $parsed_object->subject,
				'from_email' => $parsed_object->from_email,
				'from_name'  => $parsed_object->from_name,
				'to_name'    => $parsed_object->to_name,
				'title'		 => $parcel->entry->title
			),
			'content' => array(
				'html' => $parsed_object->message,
				'text' => strip_tags($parsed_object->message)
			)
		);

		$url = $this->api_url($settings->api_key, 'campaignCreate');

		return json_decode($this->curl->simple_post($url, $params));
	}

	public function send_campaign($api_key, $cid)
	{
		$url = $this->api_url($api_key, 'campaignSendNow', array('cid' => $cid));
		
		return $this->get($url); 
	}

	public function get_lists($api_key, $ajax = FALSE)
	{
		$url = $this->api_url($api_key, 'lists');

		$lists = $this->get($url);

		if($lists == NULL)
		{
			$lists = (object) array(
				'total' => 0,
				'data'  => array()
			);
		}

		if(!(bool) $ajax)
		{
			return $lists;
		}

		exit($this->list_rows($lists, (object) array(
			'list_id' => array(),
			'api_key' => $api_key
		)));
	}

	private function get($url)
	{
		return json_decode($this->curl->simple_get($url));
	}

	public function default_settings()
	{
		return (object) array(
			'api_key' => ''
		);
	}

}