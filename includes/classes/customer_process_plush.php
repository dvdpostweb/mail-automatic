<?php
class CustomerProcess {
	function mail_copy($customer_id)
	{
		$sql = 'select * from customer_attributes where customer_id = '.$customer_id;
		$query = tep_db_query($sql);
		$row = tep_db_fetch_array($query);
		return $row['mail_copy'];
	}
}
?> 