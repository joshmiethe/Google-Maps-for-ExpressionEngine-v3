<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Data Utility
 *
 * A data utility for performing varies common task
 *
 * @package		Channel Data
 * @subpackage	Libraries
 * @category	Library
 * @author		Justin Kimbrell
 * @copyright	Copyright (c) 2012, Justin Kimbrell
 * @link 		http://www.objectivehtml.com/libraries/channel_data
 * @version		0.6.10
 * @build		20120418
 */
 
class Channel_data_utility {
	
	/**
	 * Add a prefix to an result array or a single row.
	 * Must pass an array.
	 *
	 * @access	public
	 * @param	string	The prefix
	 * @param	array	The data to prefix
	 * @param	string	The delimiting value
	 * @return	array
	 */
	 public function add_prefix($prefix, $data, $delimeter = ':')
	 {
	 	$new_data = array();
	 	
	 	if(!empty($prefix))
	 	{
		 	foreach($data as $data_index => $data_value)
		 	{
		 		if(is_array($data_value))
		 		{
		 			$new_row = array();
		 			
		 			foreach($data_value as $inner_index => $inner_value)
		 			{
		 				$new_row[$prefix . $delimeter . $inner_index] = $inner_value;
		 			}
		 			
		 			$new_data[$data_index] = $new_row;
		 		}
		 		else
		 		{
		 			$new_data[$prefix . $delimeter . $data_index] = $data_value;
		 		}
		 	}
	 	}
	 	else
	 	{
	 		$new_data = $data;
	 	}
	 	
	 	return $new_data;	
	 }

	/**
	 * Merge an array to any nested array. Useful for merging data into arrays
	 * before they are used to parse the templates.
	 *
	 * @access	public
	 * @param	array	The array to merge
	 * @param	array	The subject and data to be returned
	 * @param	string	The starting point
	 * @param	string	The ending point
	 * @return	array
	 */
	 public function merge_array($array, $subject, $start = 0, $stop = FALSE)
	 {
	 	if($stop === FALSE)
	 	{
	 		$stop = count($subject);
	 	}

	 	for($y=$start; $y < $stop; $y++)
	 	{
	 		if(isset($subject[$y]))
	 		{
	 			$subject[$y] = array_merge($subject[$y], $array);
	 		}
	 		else
	 		{	
	 			$subject[$y] = $array;
	 		}
	 	}

	 	return $subject;
	 }
}