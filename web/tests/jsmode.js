

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

uinfo=a.call('test','getUserInfo', false);

pid=document.getElementsByName('enhancement_hours-parent_id')


'enhancement_hours-enhancement_project_id=10&'.replace(/(enhancement_hours-enhancement_project_id=)\d+&/, "$1666&")


re = new RegExp('foo', "g");
'foo bar baz'.replace(re, 'burp');


re= new RegExp('(enhancement_hours-enhancement_project_id=)\\d+&', "g");
'enhancement_hours-enhancement_project_id=10&'.replace(re, "$1foo&");

target_id='enhancement_hours-enhancement_project_id';
re= new RegExp('(' + target_id + '=)\\d+&', 'g');
'enhancement_hours-enhancement_project_id=10&'.replace(re, "$1foo&");

target_id='enhancement_hours-enhancement_project_id';
repval=666;
re= new RegExp("(" + target_id + "=)\\d*?(&)", "g");
'enhancement_hours-enhancement_project_id=10&'.replace(re, "$1" + repval + "$2");

function foobar(){
    target_id='enhancement_hours-enhancement_project_id';
    repval=666;
    re= new RegExp("(" + target_id + "=)\\d*?(&)", "g");
    return 'enhancement_hours-enhancement_project_id=10&'.replace(re, "$1" + repval + "$2");
}

//uinfo.printDebug('foo bar baz!', 2);

a.call('test','list_methods',false);
a.call('test','error_test',false,'this rules');

//a.call('CoopPage','printDebug',false,'testing printdebug', 2);
a.call('test','foo',false);
a.call('anotherTest','foo',false);


///////// JSOLAIT STUFF

j=jsolait
j.baseURL='http://www/lib/jsolait';
j.libURL='http://www/lib/jsolait';
jsonrpc = importModule("jsonrpc");
server = new jsonrpc.ServiceProxy(
    'http://www/coop-dev/tests/simplejsonserver.php', 
    ['foo']);
r=server.foo('bar baz');


///////// raw json

foo='{"AG":"Agrigento","AL":"Alessandria"}'
bar={"AG":"Agrigento","AL":"Alessandria"};

//// flexac xhl stuff
f=flexac;
f.config.script='../lib/flexac/flexac.php';


encodeURIComponent('foo^b=ar&baz?');

['foo','bar'].join(', ')

qo={};
qo['q'] = 'foo';
qo.bah = 'baz';


qo= {};
qa = [];

qo.q = 'bar'
qo.p = 'baz';
qo.l = 'blah';
qo.b = 'foo';

for(x in qo){
    qa.push(x + '=' + encodeURIComponent(qo[x]));
}
query = qa.join('&');


'foo&foo'


testobj= {
    bar:'baz',
    fee:'foo',
    blah: function (x)
    {
        return x;
    }
}

testobj[4] = 'aah';

testobj.foo='bar';

function foo()
{
    return testobj;
}



foo = [1, 2, 3]
foo[3] = 'blh';
foo.push('ecch');


///////////

combobox.selectBox = document.getElementsByName(selectBoxName)[0];tester

