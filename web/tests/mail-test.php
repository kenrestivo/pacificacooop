<?php

//$Id$

// unit test for my vitally required includes. return an error if
// the shit has hit the fan

require_once('Mail.php');

PEAR::setErrorHandling(PEAR_ERROR_PRINT);

$recipients = 'ken@restivo.org';

$headers['From']    = 'Test User <test@pacificacoop.org>';
$headers['To']      = 'ken@restivo.org';
$headers['Subject'] = 'Test message';

$body = 'Test message. this be a test. i am testing. 
		1 2 3. check 1,2. check.';


// Create the mail object using the Mail::factory method
$mail_object =& Mail::factory('smtp', $params);

$mail_object->send($recipients, $headers, $body);


print "OK";

?>