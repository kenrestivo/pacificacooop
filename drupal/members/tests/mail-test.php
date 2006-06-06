<?php

//$Id$

// unit test for my vitally required includes. return an error if
// the shit has hit the fan

$debug = 4;

chdir('../');
require_once('CoopPage.php');
require_once('Mail.php');

PEAR::setErrorHandling(PEAR_ERROR_PRINT);

$recipients = 'ken@restivo.org,krestivo@restivo.org';

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


// trap ugly adresses
PEAR::pushErrorHandling(PEAR_ERROR_RETURN); // BEGINNING OF TRY
$return = $mail_object->send('invalid address', $headers, $body);
PEAR::popErrorHandling(); // not really catch, more like END OF TRY

confessObj($return, "return value [ $err ]");

print "Expect error test OK";

//PEAR::raiseError('testing that error handling returns now', 111);

?>