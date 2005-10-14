

clearTarget();

enumerateWindows();

scope(Shell.enumWins[0])

a.defaultServerUrl = 'http://www/coop-dev/tests/jsonservertest.php';
a.defaultEncoding = 'JSON'; // return it to default which is JSON

a.call('test','echo_string',callCallback,
               'Im text that was echoed Async');


document.getElementById('target').innerHTML = 
a.call('test','echo_string',false,'this rules');



document.getElementById('target').innerHTML = 'foo bar baz';




