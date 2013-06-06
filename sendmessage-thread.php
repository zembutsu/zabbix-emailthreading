#!/usr/bin/php

<?php

/*
** This program is sendmail script with ISO-2022-JP for ZABBIX.
**
** Auther: Kodai Terashima
** 
** Copyright (C) 2005-2009 ZABBIX-JP 
** This program is licenced under the GPL
**
** 'sendmail-thread.php' enhanced version 0.01
**
** Auther: Masahito Zembutsu (@zembutsu) 
** Copyriht (C) 2013 Masahito Zembutsu
** This program is licenced under the GPL
**/

$CONFIGS = parse_ini_file("/usr/local/share/zabbix/alertscripts//sendmessage-php/sendmessage-php.conf");
//$MAIL_FROM = $CONFIGS["MAIL_FROM"];

mb_language('Japanese');
mb_internal_encoding('UTF-8');

$MAIL_TO      = $argv[1];
$MAIL_SUBJECT = $argv[2];
$MAIL_MESSAGE = $argv[3];

$MAIL_HEADER  = 'MIME-Version: 1.0' . "\r\n";
$MAIL_HEADER .= 'Content-Type: text/plain; charset="iso-2022-jp"' . "\r\n";
$MAIL_HEADER .= 'Content-Transfer-Encoding: 7bit' . "\r\n";
$MAIL_HEADER .= 'Date: ' . date('r') . "\r\n";

$DATAPATH = '/opt/zabbix-messageid-manager/data';
$HOSTNAME = 'node1.pocketstudio.net';

$RECOVER = 0;

if (ereg( '(High|Disaster)]障害' ,$MAIL_SUBJECT )) {
        $MAIL_HEADER .= 'X-Priority: 1'."\r\n";
} elseif (ereg( '(Average|Warning)]障害' ,$MAIL_SUBJECT )) {
        $MAIL_HEADER .= 'X-Priority: 2'."\r\n";
} elseif (ereg( '(Average|Warning|High)]通知' ,$MAIL_SUBJECT )) {
        $MAIL_HEADER .= 'X-Priority: 5'."\r\n";
} elseif (ereg( '復旧' ,$MAIL_SUBJECT )) {
	$RECOVER = 1;
} 

if (preg_match("/^(\#)(\d+)(\:)(.*)$/",$MAIL_SUBJECT,$regex)) {
	$MAIL_SUBJECT = $regex[4];
        $id = $regex[2];
	$MAIL_HEADER .= "Trigger-ID: $id" . "\r\n";
	if ( file_exists("$DATAPATH/$MAIL_TO.$id")) {
		$rndf = fopen ("$DATAPATH/$MAIL_TO.$id", "r"); 
		$rnd = chop( fgets($rndf) );
		fclose($rndf);
		$MAIL_HEADER .= 'References: <APPLI.'.$id. '.'. $rnd .'@'.$HOSTNAME.'>' . "\r\n";

	} else {
		$rnd = rand (10000000,99999999);
		$MAIL_HEADER .= 'Message-ID: <APPLI.'.$id.'.'. $rnd .'@'.$HOSTNAME.'>' . "\r\n";
		$idf = fopen("$DATAPATH/$MAIL_TO.$id","w");
		$rt = fwrite($idf, $rnd);
		fclose($idf);
		$MAIL_HEADER .= 'X-RT: '.$rt . "\r\n";
	}
        if ($RECOVER == 1) {
                unlink ("$DATAPATH/$MAIL_TO.$id");
        }
}



// $MAIL_HEADER .= 'From: ' . $MAIL_FROM . "\r\n";

$MAIL_SUBJECT = mb_convert_encoding($MAIL_SUBJECT,"ISO-2022-JP","UTF-8");
$MAIL_MESSAGE = mb_convert_encoding($MAIL_MESSAGE,"ISO-2022-JP","UTF-8");

$MAIL_SUBJECT = '=?ISO-2022-JP?B?' . base64_encode($MAIL_SUBJECT) . '?=';

mail($MAIL_TO, $MAIL_SUBJECT, $MAIL_MESSAGE, $MAIL_HEADER);

?>
