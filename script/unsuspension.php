<?php

class Unsuspension extends Script {
	var $data;
	function __construct() {
		parent::__construct();
	}
	public function execute()
	{
		$sql_data='select customer_id as customers_id, concat(customers_firstname," ",customers_lastname) customers_name,customers_email_address,customers_gender, date_format(date_added,"%d/%m/%Y") suspension_start, date_format(date_added,"%Y-%m-%d") suspension_start_diff,datediff(s.date_end,date_added) diff,date_format(date_end,"%Y-%m-%d") suspension_end_diff,date_format(date_end,"%d/%m/%Y") suspension_end, if (date_end < customers_abo_validityto, date_format(customers_abo_validityto,"%d/%m/%Y"), date_format(date_end,"%d/%m/%Y")) next_reconduction_date,customers_language, customers_email_address as customers_email  from suspensions s join customers c on c.customers_id = s.customer_id where status = "holidays" and date(adddate(date_end,-7)) = date(now()) and customers_abo = 1 and customers_abo_suspended =1 having diff >7';
		$this->data = tep_db_query($sql_data);
	}
	function add_data_row($data)
	{
		$data['suspension_duration'] = $this->distance_of_time_in_words(strtotime($data["suspension_start_diff"]),strtotime($data["suspension_end_diff"]),$data);
		if (strtoupper($data['customers_gender'])=='F')
		{
			$key='TEXT_FEMALE_GENDER_'.$data['customers_language']; 			
		}
		else
		{
			$key='TEXT_MALE_GENDER_'.$data['customers_language']; 			
		}
		$data['gender_simple'] = $this->get_key($key);
		
		return $data;
	}
	
	/*function post_process($data)
	{
		return true;
	}*/


	function distance_of_time_in_words($from_time,$to_time = 0, $data, $include_seconds = false) {
		$dm = $distance_in_minutes = abs(($from_time - $to_time))/60;
		$ds = $distance_in_seconds = abs(($from_time - $to_time));
		switch ($distance_in_minutes) {
			case $dm > 0 && $dm < 1:
			if($include_seconds == false) {
				if ($dm == 0) {
					return 'less than a minute';
				} else {
					return '1 minute';
				}
			} else {
				switch ($distance_of_seconds) {
					case $ds > 0 && $ds < 4:
						return 'less than 5 seconds';
						break;
					case $ds > 5 && $ds < 9:
						return 'less than 10 seconds';
						break;
					case $ds > 10 && $ds < 19:
						return 'less than 20 seconds';
						break;
					case $ds > 20 && $ds < 39:
						return 'half a minute';
						break;
					case $ds > 40 && $ds < 59:
						return 'less than a minute';
						break;
					default:
						return '1 minute';
					break;
				}
			}
			break;
			
			case $dm > 2 && $dm < 44:
				return round($dm) . ' minutes';
				break;
			case $dm > 45 && $dm < 89:
				return 'about 1 hour';
				break;
			case $dm > 90 && $dm < 1439:
				return 'about ' . round($dm / 60.0) . ' hours';
				break;
			case $dm > 1440 && $dm < 2879:
				return '1 day';
				break;
			case $dm >= 2880 && $dm < 43199:
				return $this->get_key("TEXT_DE_".$data['customers_language']).' '.round($dm / 1440) . ' '.$this->get_key("PERIOD_DAY_".$data['customers_language']);
				break;
			case $dm >= 43200 && $dm < 86399:
				return $this->get_key("TEXT_D_".$data['customers_language']).'1 '.$this->get_key("PERIOD_MONTH_".$data['customers_language']);
				break;
			case $dm >= 86400 && $dm < 525599:
				return $this->get_key("TEXT_DE_".$data['customers_language']).' '.round($dm / 43200) . ' '.$this->get_key("PERIOD_MONTH_".$data['customers_language']);
				break;
			case $dm >= 525600 && $dm < 1051199:
				return $this->get_key("TEXT_D_".$data['customers_language']).'1 '.$this->get_key("PERIOD_YEAR_".$data['customers_language']);
				break;
			default:
				return 'over ' . round($dm / 525600) . ' '.$this->get_key("PERIOD_YEAR_".$data['customers_language']);
			break;
		}
	}

}
?>