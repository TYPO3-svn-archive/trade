<?php
/*
 * Created on 17-Mar-06
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates

 $this->conf['newline']='\r\n';
 $this->conf['emailFrom']='joe@here.com';
 $headers  = 'MIME-Version: 1.0' . $this->conf['newline'];
///if ($this->conf['plainTextEmails']==1) {
	$headers .= 'Content-type: text/plain; charset=iso-8859-1' . $this->conf['newline'];
//} else {
	//$headers .= 'Content-type: text/html; charset=iso-8859-1' . $this->conf['newline'];
//}
 
 
$headers .= 'From: '.$this->conf['emailFrom'] . $this->conf['newline'];
$headers .='Reply-To: '.$this->conf['emailFrom'] . $this->conf['newline'];
$headers .='Return-Path: '.$this->conf['emailFrom'] .$this->conf['newline'];
mail("syntithenai@gmail.com",'header testing','this a as dvasdf alkjlskdjfwlkej test ',$headers);
$to = 'nobody@example.com'; $subject = 'the subject'; $message = 'hello'; $headers = 'From: webmaster@example.com' . "\r\n" . 'Reply-To: webmaster@example.com' . "\r\n" . 'X-Mailer: PHP/' . phpversion(); mail($to, $subject, $message, $headers);					
 
 */
 
 
 $nl='\n';
 $to      = 'stever@localhost,syntithenai@gmail.com';
$subject = 'the subject';
$message = 'hello';
$headers = 'From: webmaster@example.com' . $nl .
    'Reply-To: webmaster@example.com' . $nl .
    'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);
?>
