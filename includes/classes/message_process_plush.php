<?php
class MessageProcess {
	function send($customer_id, $category_id, $data,$mail_id=0, $mail_history_id=0)
	{
		if ($mail_history_id==0)
			$mail_history_id = "null";
		if ($mail_id==0)
			$mail_id = "null";
		$sql_insert = "INSERT INTO `tickets` (`created_at`, `updated_at`, `category_ticket_id`, `remove`, `customer_id`) VALUES(now(), now(), ".$category_id.", 0, ".$customer_id.")";
		tep_db_query($sql_insert);
		$insert_id = tep_db_insert_id();
		$sql_insert2 = "INSERT INTO `message_tickets` (`created_at`, `updated_at`, `is_read`, `mail_id`, `ticket_id`, `user_id`, `data`, `mail_history_id`)";
		$sql_insert2 .= " VALUES(now(), now(), 0, ".$mail_id.", ".$insert_id;
		$sql_insert2 .= ", 55, '".addslashes($data)."', ".$mail_history_id.")";
		echo $sql_insert2;
		tep_db_query($sql_insert2);
	}
}
?> 