<?php
/**
 * mail class file
 * 邮件发送
 */

if (!defined('IN_IDEACMS')) exit(); 

class mail extends Fn_base {
    
    /**
	 * 样式配置文件.	
	 * 
	 * @var Array
	 */
	static $config;
	
	public static function set($config) {
	    self::$config = array(
	        'SITE_MAIL_TYPE' => $config['SITE_MAIL_TYPE'],
	        'SITE_NAME'      => $config['SITE_NAME'],
	        'server'         => $config['SITE_MAIL_SERVER'],
			'port'           => $config['SITE_MAIL_PORT'],
			'auth'           => $config['SITE_MAIL_AUTH'],
			'from'           => $config['SITE_MAIL_FROM'],
			'auth_username'  => $config['SITE_MAIL_USER'],
			'auth_password'  => $config['SITE_MAIL_PASSWORD'],
			'mailsend'       => 2,
			'maildelimiter'  => 1,
			'mailusername'   => 1,
	    );
	}
	
    public static function sendmail($toemail, $subject, $message) {
		$mail      = self::$config;
		$sitename  = $mail['SITE_NAME'];
		$mail_type = $mail['SITE_MAIL_TYPE'];
		$from      = $mail['from'];
		//mail 发送模式
		if ($mail_type == 0) {
		    $headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
			$headers .= 'From: ' . $sitename . ' <'.$from.'>' . "\r\n";
			mail($toemail, $subject, $message, $headers);
			return true;
		}
		if (empty($mail['server'])) return false;
	    $cfg['server']  = $cfg['port'] = $cfg['auth'] = $cfg['from'] = $cfg['auth_username'] = $cfg['auth_password'] = '';
	    $cfg['charset'] = $charset = 'utf-8';
	    $cfg['server']  = $mail['server'];
	    $cfg['port']    = $mail['port'];
	    $cfg['auth']    = $mail['auth'] ? 1 : 0;
	    $cfg['from']    = $mail['from'];
	    $cfg['auth_username'] = $mail['auth_username'];
	    $cfg['auth_password'] = $mail['auth_password'];
		unset($mail);
	
	    $maildelimiter = "\r\n"; //换行符
	    $mailusername  = 1;
	    $cfg['port']   = $cfg['port'] ? $cfg['port'] : 25;
	
	    $email_from    = $from == '' ? '=?'.$cfg['charset'].'?B?'.base64_encode($cfg['auth_username'])."?= <".$cfg['from'].">" : (preg_match('/^(.+?) \<(.+?)\>$/',$from, $mats) ? '=?'.$cfg['charset'].'?B?'.base64_encode($mats[1])."?= <$mats[2]>" : $from);
	    $email_to      = preg_match('/^(.+?) \<(.+?)\>$/',$toemail, $mats) ? ($mailusername ? '=?'.$cfg['charset'].'?B?'.base64_encode($mats[1])."?= <$mats[2]>" : $mats[2]) : $toemail;
	    $email_subject = '=?'.$cfg['charset'].'?B?'.base64_encode(preg_replace("/[\r|\n]/", '', $subject)).'?=';
	    $email_message = chunk_split(base64_encode(str_replace("\n", "\r\n", str_replace("\r", "\n", str_replace("\r\n", "\n", str_replace("\n\r", "\r", $message))))));

	    $host          = $_SERVER['HTTP_HOST'];
	    $headers       = "From: $email_from{$maildelimiter}X-Priority: 3{$maildelimiter}X-Mailer: $host {$maildelimiter}MIME-Version: 1.0{$maildelimiter}Content-type: text/html; charset=".$cfg['charset']."{$maildelimiter}Content-Transfer-Encoding: base64{$maildelimiter}";
	
        if(!$fp = @fsockopen($cfg['server'], $cfg['port'], $errno, $errstr, 30)) {
		    runlog('SMTP', "({$cfg[server]}:{$cfg[port]}) CONNECT - Unable to connect to the SMTP server", 0);
		    return false;
	    }
	    stream_set_blocking($fp, true);
	    $lastmessage = fgets($fp, 512);
	    if(substr($lastmessage, 0, 3) != '220') {
		    runlog('SMTP', "{$cfg[server]}:{$cfg[port]} CONNECT - $lastmessage", 0);
		    return false;
	    }
	    fputs($fp, ($cfg['auth'] ? 'EHLO' : 'HELO')." uchome\r\n");
	    $lastmessage = fgets($fp, 512);
	    if(substr($lastmessage, 0, 3) != 220 && substr($lastmessage, 0, 3) != 250) {
		    runlog('SMTP', "({$cfg[server]}:{$cfg[port]}) HELO/EHLO - $lastmessage", 0);
		    return false;
	    }
	    while(1) {
		    if(substr($lastmessage, 3, 1) != '-' || empty($lastmessage)) {
			    break;
		    }
		    $lastmessage = fgets($fp, 512);
	    }
	    if($cfg['auth']) {
		    fputs($fp, "AUTH LOGIN\r\n");
		    $lastmessage = fgets($fp, 512);
		    if(substr($lastmessage, 0, 3) != 334) {
			    runlog('SMTP', "({$cfg[server]}:{$cfg[port]}) AUTH LOGIN - $lastmessage", 0);
			    return false;
		    }
		    fputs($fp, base64_encode($cfg['auth_username']) . "\r\n");
		    $lastmessage = fgets($fp, 512);
	        if(substr($lastmessage, 0, 3) != 334) {
			    runlog('SMTP',"({$cfg[server]}:{$cfg[port]}) USERNAME - $lastmessage");
			    return false;
		    }
		    fputs($fp, base64_encode($cfg['auth_password']) . "\r\n");
		    $lastmessage = fgets($fp, 512);
		    if(substr($lastmessage, 0, 3) != 235) {
			    runlog('SMTP', "({$cfg[server]}:{$cfg[port]}) PASSWORD - $lastmessage", 0);
			    return false;
		    }
		    $email_from = $cfg['from'];
	    }

	    fputs($fp, "MAIL FROM: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_from).">\r\n");
	    $lastmessage = fgets($fp, 512);
	    if(substr($lastmessage, 0, 3) != 250) {
		    fputs($fp, "MAIL FROM: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_from).">\r\n");
		    $lastmessage = fgets($fp, 512);
		    if(substr($lastmessage, 0, 3) != 250) {
			    runlog('SMTP', "({$cfg[server]}:{$cfg[port]}) MAIL FROM - $lastmessage", 0);
			    return false;
		    }
	    }

	    fputs($fp, "RCPT TO: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $toemail).">\r\n");
	    $lastmessage = fgets($fp, 512);
	    if(substr($lastmessage, 0, 3) != 250) {
		    fputs($fp, "RCPT TO: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $toemail).">\r\n");
		    $lastmessage = fgets($fp, 512);
	        runlog('SMTP', "({$cfg[server]}:{$cfg[port]}) RCPT TO - $lastmessage", 0);
		    return false;
	    }
	    fputs($fp, "DATA\r\n");
	    $lastmessage = fgets($fp, 512);
	    if(substr($lastmessage, 0, 3) != 354) {
		    runlog('SMTP', "({$cfg[server]}:{$cfg[port]}) DATA - $lastmessage", 0);
		    return false;
	    }
	    $headers .= 'Message-ID: <'.gmdate('YmdHs').'.'.substr(md5($email_message.microtime()), 0, 6).rand(100000, 999999).'@'.$_SERVER['HTTP_HOST'].">{$maildelimiter}";

	    fputs($fp, "Date: ".gmdate('r')."\r\n");
	    fputs($fp, "To: ".$email_to."\r\n");
	    fputs($fp, "Subject: ".$email_subject."\r\n");
	    fputs($fp, $headers."\r\n");
	    fputs($fp, "\r\n\r\n");
	    fputs($fp, "$email_message\r\n.\r\n");
	    $lastmessage = fgets($fp, 512);
	    if(substr($lastmessage, 0, 3) != 250) {
		    runlog('SMTP', "({$cfg[server]}:{$cfg[port]}) END - $lastmessage", 0);
		    return false;
	    }
	    fputs($fp, "QUIT\r\n");
	    return true;
    }
	
}

function runlog($mode = 'SMTP',$b = '',$c = '',$d='') {
	@file_put_contents(APP_ROOT . 'cache/mail_error.log', date('Y-m-d H:i:s') . ' ' . $b, FILE_APPEND);
}