<?php


class Automatic {
	
	private $db = null; //reference to our DB connection
	private $db_config = array(); //all available DB configs (e.g. test,development, production)
	private $ENV = "test"; //default (can also be one 'test', 'production')
	private $verbose=false;
	private $task_options;
	private $script;
	
	private $mail_id;
	private $mail_content;
	private $id;
	private $name;
	private $email_test;
	
	

	
	function __construct($db, $argv) {
		$this->db_config=$db;
		$this->parse_args($argv);
		tep_db_connect($this->db_config[$this->ENV]['host'], $this->db_config[$this->ENV]['user'] ,$this->db_config[$this->ENV]['password'], $this->db_config[$this->ENV]['database']);
		
	}
	
	
	public function execute(){
		$sql='SELECT * FROM automatic_emails where status="active" and date_format(now(),"%H")=exe_time';
		$query=tep_db_query($sql);
		while($row=tep_db_fetch_array($query))
		{
			
			include('./script/'.$row['script_name']);
			$class=explode('.',$row['script_name']);
			$class_name=$class[0];
			$this->script = new $class_name();
			$this->script->setId($row['id']);
			$this->script->verbose=$this->verbose;
			$this->script->name=$row['name'];
			$this->script->setMailId($row['mail_id']);
			$this->script->initialize();
			if(!empty($this->email_test))
				$this->script->test($this->email_test);
			else
				$this->script->execute();
			$this->script->finish();
		}
	}
	public function finish()
	{
		if($this->verbose==true)
		{
			echo "script -> ".$this->name." mail envoyé -> ".$this->getCount()."\n";
		}
		$sql='update automatic_emails set count_mail='.$this->getCount().' where id= '.$this->getId();
		tep_db_query($sql);
	}
	public function setCount()
	{
		$this->count_mails++;
	}
	public function getCount()
	{
		return $this->count_mails;
	}
	public function setId($id)
	{
		$this->id=$id;
	}
	public function getId()
	{
		return $this->id;
	}
	public function setMailId($id)
	{
		$this->mail_id=$id;
	}
	public function getMailId()
	{
		return $this->mail_id;
	}
	public function modif_attributes($html,$modif)
	{
		foreach($modif as $key => $value)
		{
			$html=str_replace($key,$value,$html);
		}
		return $html;
		
	}
	public function send_mail($to_name, $to_email_address,  $from_email_name, $from_email_address,$language,$modif){
			$email_subject=$this->mail_content[$language]['messages_title'];
 			$email_text=$this->mail_content[$language]['messages_html'];
			$email_text=$this->modif_attributes($email_text,$modif);
		    $message = new email(array('X-Mailer: osC mailer'));
		    $text = strip_tags($email_text);
		    $message->add_html($email_text, $text);
		    $message->build_message();
		    $message->send($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject);
		    $this->setCount();
	}
	public function initialize()
	{
		$mail_id=$this->getMailId();
		if(!empty($mail_id))
		{
			$sql='SELECT * FROM mail_messages where  mail_messages_id='.$mail_id;
			$query=tep_db_query($sql);
			while($row=tep_db_fetch_array($query))	
			{
				$mail[$row['language_id']]=$row;
			}
			$this->mail_content=$mail;
		}
		else
		{
			echo "error initilize \n";
		}
	}
		
	public function mail_history($customers_id,$customers_email,$languages_id,$mail_id)
	{
		$sql_insert="INSERT INTO `dvdpost_be_prod`.`mail_messages_sent_history` (`mail_messages_sent_history_id` ,`date` ,`customers_id` ,`mail_messages_id`,`language_id` ,	`mail_opened` ,	`mail_opened_date` ,`customers_email_address`)
		VALUES (NULL , now(), ".$customers_id.", '".$mail_id."', $languages_id, '0', NULL , '".$customers_email."'	);";
		
		tep_db_query($sql_insert);
		$mail_hystory_id=tep_db_insert_id();
		return $mail_hystory_id;
	}
	private function parse_args($argv) {
		
		$num_args = count($argv);
		$options = array();
		for($i = 1; $i < $num_args;$i++) {
			$arg = $argv[$i];
			if(strpos($arg, '=') !== FALSE) {
				list($key, $value) = split("=", $arg);
				$options[$key] = $value;
			if($key == 'ENV') {
				$this->ENV = $value;
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