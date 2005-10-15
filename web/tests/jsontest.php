<?php
/**
$Id$
 */


chdir('../'); // for damned test folder

include('CoopPage.php');

$cp = new CoopPage($debug);
print $cp->pageTop();

print $cp->selfURL(array('value' => 'Refresh'));

$target = $cp->selfURL(array('base'=>'jsonservertest.php',
                             'inside' => 'nothing',
                             'host' => true));
/// XXX NASTY HACK AROUND BUGS IN SELFURL!
$qm = strpos($target, '?');
$qm && $target = substr($target, 0, $qm);

printf('<p>%s</p>',$target);

foreach(array('main', 'dispatcher', 'HttpClient', 'Request', 'json') as $client){
    printf('<script type="text/javascript" src="%s?client=%s"></script>',
           $target, $client);
     }
?>

<script type="text/javascript">
function clearTarget() {
	document.getElementById('target').innerHTML = 'clear';
}


// Grab is the simplest usage of HTML_AJAX you use it to perform a request to a page and get its results back
// It can be used in either Sync mode where it returns directory or with a call back, both methods are shown below
var url = 'http://www/coop-dev/tests/junk.php';
function grabSync() {
	document.getElementById('target').innerHTML = HTML_AJAX.grab(url);
}

function grabAsync() {
	HTML_AJAX.grab(url,grabCallback);
}

function grabCallback(result) {
	document.getElementById('target').innerHTML = result;
}


// replace can operate either against a url like grab or against a remote method
// if its going to be used against a remote method defaultServerUrl needs to be set to a url that is exporting the class its trying to call
// note that replace currently always works using Sync AJAX calls, an option to perform this with Async calls may become an option at some further time
// both usages are shown below

HTML_AJAX.defaultServerUrl = '<?php echo $target; ?>';

function replaceUrl() {
	HTML_AJAX.replace('target',url);
}

function replaceFromMethod() {
	HTML_AJAX.replace('target','test','echo_string','Im a method call replacement');
}


// call is used to call a method on a remote server
// you need to set HTML_AJAX.defaultServerUrl to use it
// you might also want to set HTML_AJAX.defaultEncoding, options are Null and JSON, the server will autodetect this encoding from your content type
// but the return content type will be based on whatever the servers settings are
// You can use call in either Sync or Async mode depending on if you pass it a callback function

function callSync() {
	//HTML_AJAX.defaultEncoding = 'Null'; // set encoding to no encoding method
	document.getElementById('target').innerHTML = 
        HTML_AJAX.call('test','echo_string',false,'Im text that was echoed');
	HTML_AJAX.defaultEncoding = 'JSON'; // return it to default which is JSON
}

function callAsync() {
	HTML_AJAX.call('test','echo_string',callCallback,
                   'Im text that was echoed Async');
}

function callCallback(result) {
	document.getElementById('target').innerHTML = result;
}


function getUser() {
	//HTML_AJAX.defaultEncoding = 'Null'; // set encoding to no encoding method
	document.getElementById('target').innerHTML = 
        HTML_AJAX.call('test','getUser','useless arg');
	HTML_AJAX.defaultEncoding = 'JSON'; // return it to default which is JSON
}


</script>
<ul>
	<li><a href="javascript:void()" onClick="clearTarget()">Clear Target</a></li>
	<li><a href="javascript:void()" onClick="grabSync()">Run Sync Grab Example</a></li>
	<li><a href="javascript:void()" onClick="grabAsync()">Run Async Grab  Example</a></li>
	<li><a href="javascript:void()" onClick="replaceUrl()">Replace with content from a url</a></li>
	<li><a href="javascript:void()" onClick="replaceFromMethod()">Replace with content from a method call</a></li>
	<li><a href="javascript:void()" onClick="callSync()">Sync Call</a></li>
	<li><a href="javascript:void()" onClick="callAsync()">ASync Call</a></li>
	<li><a href="javascript:void()" onClick="getUser()">get user struct</a></li>
</ul>

<div style="white-space: pre; padding: 1em; margin: 1em; width: 600px; 
height: 300px; border: solid 2px black; overflow: auto;" 
id="target">Target</div>
</body>
</html>
