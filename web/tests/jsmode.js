

clearTarget();

enumerateWindows();

scope(Shell.enumWins[0])

// in the javascript shell, do a=HTML_AJAX

a.defaultServerUrl = 'http://www/coop-dev/tests/jsonservertest.php';
a.defaultEncoding = 'JSON'; // return it to default which is JSON



document.getElementById('target').innerHTML = a.call('test',
                                                     'echo_string',
                                                     false,
                                                     'this rules');


// no need to document.getelement, since the callcallback does that 4u
a.call('test','echo_string',callCallback, 'echo this asynchronously');


document.getElementById('target').innerHTML = 'foo bar baz';


a.call('test','echo_string',false,'this rules');

uinfo=a.call('test','userinfo', false, 'useless arg');

pid=document.getElementsByName('enhancement_hours-parent_id')
