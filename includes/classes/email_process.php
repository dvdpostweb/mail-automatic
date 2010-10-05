<?php
class EmailProcess {
	var $dico;
	var $key_missing;
	
	function __construct() {
	}

	function formating($mails,$data)
	{
		$mail = $mails[$data['customers_language']];
		$mail['messages_html'] = $this->format($mail['messages_html'],$data);
		$mail['messages_subject'] = $this->format($mail['messages_subject'],$data,false);
		if($mail['messages_html'] === false || $mail['messages_subject'] === false)
			return false;
		return $mail; 
	}
	function format($text,$data,$set_dico = true)
	{
		if ($set_dico === true)
		{
			$this->dico='';
		}
		preg_match_all("/\\$\\$\\$(\w+)\\$\\$\\$/", $text, $extract_variable);
		foreach ($extract_variable[1] as $item)
		{
			if(!isset($data[$item]))
			{
				$this->key_missing=$item;
				return false;
			}
			$text = str_replace('$$$'.$item.'$$$',$data[$item],$text);
			if($set_dico == true)
			{
				if (strpos($this->dico, $item) === false) 
				{
					$this->dico.= '$$$'.$item.'$$$'.':::'.$data[$item].';;;';
				}
			}
		}
		return $text;
	}
	function history($mail_id, $data)
	{
		$email = $data['customers_email'];
		
		$sql_insert="INSERT INTO `mail_messages_sent_history` (`mail_messages_sent_history_id` ,`date` ,`customers_id` ,`mail_messages_id`,`language_id` ,	`mail_opened` ,	`mail_opened_date` ,`customers_email_address`)	VALUES (NULL , now(), ".$data['customers_id'].", ".$mail_id.", ".$data['customers_language'].", '0', NULL , '".$email."');";
		$status = tep_db_query($sql_insert);
		if ($status == false)
		 return false;
		else
		return tep_db_insert_id();
	}
	function get_key_missing()
	{
		return $this->key_missing;
	}
	function set_dico($id)
	{
		$sql_up="update mail_messages_sent_history set `Lstvariable` = '". addslashes($this->dico)."' where  mail_messages_sent_history_id = ".$id;
		tep_db_query($sql_up);
		
	}

	function send($formating_mail,$data,$env)
	{
		$recipient = ($env == 'production') ? $data['customers_email'] : 'gs@dvdpost.be';
		$mail = new PHPmailer();
		$mail->IsSMTP();
		$mail->IsHTML(true);
		$mail->Host='192.168.100.11';
		$mail->From='dvdpost@dvdpost.be';
		$mail->FromName='DVDPost';
		$mail->AddAddress($recipient);
		$mail->AddReplyTo('dvdpost@dvdpost.be');	
		$mail->Subject=$formating_mail['messages_subject'];
		$mail->Body=$formating_mail['messages_html'];

		if(!$mail->Send()){ //Teste si le return code est ok.
		  echo $mail->ErrorInfo; //Affiche le message d'erreur (ATTENTION:voir section 7)
			return false;
		}
		else{	  
		  return true;
		}
		$mail->SmtpClose();
		unset($mail);
	}

	function get_dico()
	{
		return $this->dico;
	}
		
	
}
?>