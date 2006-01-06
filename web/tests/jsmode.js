

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


 
/// playing with oject
fubarsettings = {another : 'haw haw'};
Fubar = function (myprop)
{
    this.myprop = myprop;
    this.another = 'old value';
    this.blah = function ()
    {
        return this.myprop;
    }
    for(i in fubarsettings){
        eval('this.' +i+ ' = fubarsettings.' +i);
    }
    return this;
}
f = new Fubar('yay');

c.status.innerHTML= '<input type="text" id="testing" name="foobarbaz">';


//// attempt at json

a=A({'href':'','onclick': 'return sendEmailNotice(this,5436)'}, 'foo test');
$('statusbar').appendChild(a)


sendEmailNotice = function(self,audit_id){
    ih=self.innerHTML;
    self.innerHTML='Sending <img src="/images/spinner.gif">';
    d=doSimpleXMLHttpRequest('http://www/coop-dev/send_email.php',
        {'audit_id': audit_id});
    d.addCallback(function(data){ 
        a.removeAttributeNode(a.getAttributeNode('href'));
        self.innerHTML='Sent'}); 
    d.addErrback(function(err) { self.innerHTML= ih}); 
    return false;
}

sendEmailNotice(a, 5431);


w=window.open('http://www/coop-dev')


/////

addScript('http://www/coop-dev/lib/JsonRpc.js');
p = new JsonRpcProxy('http://www/coop-dev/tests/jsonrpctest.php',
                     ['echotest','args2Array', 'args2String', 
					  'throwError', 'throwPEARError']);

undefer(p.echotest('foo bar baz')); 


/////////
s=w.document.forms[0]['companies_auction_join-company_id']


getTitle = function(self){
    if(!self.coop_title_added){
        self.title  = self.innerHTML; //proxy.getDetails('tablename', this.value);
        self.coop_title_added = 1;
    }
}

s.options[0].addEventListener('mouseover', function(){ getTitle(this) }, true)



/////TODO
showDetails = function(self) {
    //delay a second
    // go get the stuff vis jsonproxy
    //    grab the tablename?
    // cache it so i don't always felch?
    // unhide the dialog 
}

hideDetails = function(self){
    //set dialog visibility to hidden
}

s.options[0].addEventListener('mouseover', function(){ showDetails(this) }, true)
s.options[0].addEventListener('mouseout', function(){ hideDetails(this) }, true)



showEvent = function(ev){
    ev = ev || window.event; // IE sucks.
    writeln('x:'+ev.screenX+' y:'+ev.screenY+' cx:'+ev.clientX+' cy:'+ev.clientY)}

/////////
var printKey = function(ev){
    // mozilla-only version
    writeln(ev.keyCode + ' ' + ev.type + ' ' + ev.eventPhase);
    ev.stopPropagation();
    ev.preventDefault();
    return false;
}


addScript('http://www/lib/eventutils.js', w.document);

var printKey  = function(ev){
    writeln(ev.keyCode + ' ' + ev.type + ' ' + ev.eventPhase);
    switch(ev.keyCode) 
    {
    // trap these keys
    case 13:
    case 39: 
        evt = new Evt(ev);
        evt.consume();
        writeln(evt.getSource().value);
        evt.getSource().combobox.fetchData();
        return false;
    default:
        break;
    }
    
    return true;
}



c=w.combobox_companies_auction_join_company_id;
EventUtils.addEventListener(c.searchBox, 'keyup', printKey, true);
EventUtils.addEventListener(c.searchBox, 'keydown', printKey, true);
EventUtils.addEventListener(c.searchBox, 'keypress', printKey, true);

 
c.trapKey  = function(ev){
    switch(ev.keyCode) 
    {
        // trap these keys
        case 13:
        case 39: 
        evt = new Evt(ev);
        evt.getSource().autocomplete = 0;
            evt.consume();
        this.fetchData();
        return false;
        break; 
        default:
        break;
        }
    return true;
}


function hackkeys(o) { 
    var l = new Array(); 
    for (var p in o) { 
        l.push(p) 
            }
    return l 
}

function hackvalues(o) { 
     var l = new Array(); 
     for (var p in o) { 
         l.push(o[p]);
     }
     return l;
}



/////////
addScript('http://www/coop-dev/lib/JsonRpc.js');
p = new JsonRpcProxy('http://www/coop-dev/dispatchproxy.php',
                     ['echotest', 'getPage', 'throwError', 
                      'throwPEARError', 'nothing']);

undefer(p.echotest('foo bar baz')); 
undefer(p.getPage());
undefer(p.throwError());
undefer(p.throwPEARError());
undefer(p.echotest());
undefer(p.nothing());



/// subform test!
p = new JsonRpcProxy('http://www/coop-dev/dispatchproxy.php',
                     ['getPage', 'dispatchTable']);
undefer(p.getPage());


subform = $('body').appendChild(DIV({id:'subform'},'stuff goes here'))

p.dispatchTable({action : 'add', table : 'ads'}).addCallback(function(data){ subform.innerHTML = data});


p = new JsonRpcProxy('http://www/coop-dev/dispatchproxy.php')
p.call('methodList').addCallback(function(data){p.addMethods(values(data))});




