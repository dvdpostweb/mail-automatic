<?php


class Schedule {
	
	private $db = null; //reference to our DB connection
	private $db_config = array(); //all available DB configs (e.g. test,development, production)
	private $ENV = "test"; //default (can also be one 'test', 'production')
	private $debug = false; //default (can also be one 'test', 'production')
	
	private $verbose=false;
	private $task_options;
	private $script;
	private $formating;
	
	private $mail;
	private $count;
	private $fp;
	

	
	function __construct($db, $argv) 
	{
		$this->db_config=$db;
		$this->parse_args($argv);
		tep_db_connect($this->db_config[$this->ENV]['host'], $this->db_config[$this->ENV]['user'] ,$this->db_config[$this->ENV]['password'], $this->db_config[$this->ENV]['database']);
		$this->email_process = new EmailProcess();
		$this->message = new MessageProcess();
		$this->customer = new CustomerProcess();
		
		$this->fp = fopen('./log/error.log', 'a+');
	}
	function error($message,$script)
	{
		$date = date('d/m/Y H:i:s');
		fwrite($this->fp, $date.': script_id :'.$script.' '.$message."\n");
		$mail = new PHPmailer();
		$mail->IsSMTP();
		$mail->IsHTML(true);
		$mail->Host='192.168.100.11';
		$mail->From='dvdpost@dvdpost.be';
		$mail->AddAddress('gs@dvdpost.be');
		$mail->AddReplyTo('dvdpost@dvdpost.be');	
		$mail->Subject='erreur mail automatique';
		$mail->Body=$date.': script_id :'.$script.' '.$message;
		$mail->Send();
		tep_rollback();

	}
	

	public function execute(){
		$sql='SELECT * FROM automatic_emails where status="active" and adddate(date_last_execution,frequency_days) <= date(now()) and hour(now())=exe_time ';
		$query=tep_db_query($sql);
		while($row=tep_db_fetch_array($query))
		{
			$this->count = 0;
			if(is_file('./script/'.$row['script_name']))
			{
				include_once('./script/'.$row['script_name']);
				
				$mail_id = $row['mail_id'];
				$this->mail = $this->getMail($mail_id);
				if ($this->mail !== false)
				{
					$class=explode('.',$row['script_name']);
					$class_name=$class[0];
					$depart=$this->timer();
					$script = new $class_name();
					
					$script->execute($mail_id);
					
					if($script->get_data() !== false)
					{
						while($script_row=tep_db_fetch_array($script->data))
						{
							$this->client_process($script_row,$mail_id,$row,$script);
							if($this->ENV != 'production')
					 			break;
						}
					}
					else
					{
						$this->error("erreur de script ".$row['script_name'],$row['id']);
					}
				}
				else
				{
					$this->error("mail mal chargé\n",$row['id']);
				}
			}
			else
			{
				$this->error("le script ".$row['script_name']." est manquant dans le repertoire script",$row['id']);
			}
			
			$fin=$this->timer();
			$delai=number_format($fin - $depart,7);
			$sql_update = 'update automatic_emails set date_last_execution = now(),count_mail= '.$this->count.' where id = '.$row['id'].';';
			$query_update=tep_db_query($sql_update);
			if($this->verbose==true)
			{
				echo "temps d'execution: ",$delai," secondes.\n"; 
		 	}
		}
		fclose($this->fp);
	}
	function client_process($script_row,$mail_id,$row,$script)
	{
		tep_begin();
		$mail_copy = $this->customer->mail_copy;
		$script_row = $script->add_data_row($script_row);
		$language = $script_row['customers_language'];
		$force_copy = $this->mail[$language]['force_copy'];
		
		if($script_row !== false)
		{
			if($mail_copy==1 || $force_copy==1)
				$status_history = $this->email_process->history($mail_id,$script_row);
			else
				$status_history=0;
			if($status_history !==false)
			{
				$script_row['mail_messages_sent_history_id']=$status_history;
				$formating_mail = $this->email_process->formating($this->mail,$script_row);
				
				if ($status_history !=0){
					$this->email_process->set_dico($status_history);
				}
				if($formating_mail !== false) 
				{
					if($mail_copy==1 || $force_copy==1)
					{
						$mail_sent = $this->email_process->send($formating_mail,$script_row,$this->ENV);
						$script_row['mail_messages_sent_history_id']=0;
						$formating_mail = $this->email_process->formating($this->mail,$script_row);
						if($script_row['customers_id'] > 0)
						{
						  $this->message->send($script_row['customers_id'], $this->mail[$language]['category_id'], $this->email_process->get_dico(), $mail_id , $status_history);  
						}
					}
					else
					{
						$mail_sent=true;
						$script_row['mail_messages_sent_history_id']=0;
						$formating_mail = $this->email_process->formating($this->mail,$script_row);
						if($script_row['customers_id'] > 0)
						{
						  $this->message->send($script_row['customers_id'], $this->mail[$language]['category_id'], $this->email_process->get_dico(), $mail_id);
					  }
					}
					if($mail_sent == 1)
					{
						$status = $script->post_process($script_row);
						
						if($status === false)
						{
							$this->error('erreur post script - customers_id '.$script_row['customers_id'],$row['id']);
						}
						else
						{
							tep_commit();
						}
						
					}
					else
					{
						$this->error('erreur lors de l\'envoi du mail - customers_id '.$script_row['customers_id'],$row['id']);
					}
					$this->count++;
					
				}
				else
				{
					$this->error("variable manquante : ".$this->email_process->get_key_missing(),$row['id']);
				}
			}
			else
			{
				$this->error("error d'historique de mail - customers_id ".$script_row['customers_id'],$row['id']);
			}
		
		}
		else
		{
			$this->error("erreur d'ajout de données spécifique - customers_id ".$script_row['customers_id'],$row['id']);
		}
	}
	private function getMail($mail_id)
	{
		$sql='SELECT * FROM mail_messages where mail_messages_id='.$mail_id;
		$query = tep_db_query($sql);
		while($row_mail=tep_db_fetch_array($query))	
		{
			$mail[$row_mail['language_id']]=$row_mail;
		}
		return $mail;
	}
	private  function timer()
	 {
	 $time=explode(' ',microtime());
	 return $time[1] + $time[2];
	 }
	private function parse_args($argv) {
		
		$num_args = count($argv);
		$options = array();
		for($i = 1; $i < $num_args;$i++) {
			$arg = $argv[$i];
			if(strpos($arg, '=') !== FALSE) {
				list($key, $value) = explode("=", $arg);
				$options[$key] = $value;
			if($key == 'ENV') {
				$this->ENV = $value;
			}
			if($key == 'DEBUG') {
				$this->debug = (($value=="1")?true:false);
			}
			if($key == 'VERBOSE') {
				$this->verbose =(($value=="1")?true:false);
			}
			if($key == 'EMAIL') {
				$this->email_test =$value;
			}
		}
		$this->task_options = $options;
		}		
	}
}
?>