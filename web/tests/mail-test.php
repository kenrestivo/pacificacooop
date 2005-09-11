<?php

//$Id$

// unit test for my vitally required includes. return an error if
// the shit has hit the fan
chdir('../');
require_once('Mail.php');
require_once('CoopPage.php');

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


print "RAW test OK\n";

$cp = new CoopPage(4);
$cp->mailError('testing through cooppage', 
               'this is a test thru cooppage mailerror');
print "COOPAGE test OK\n";

?>