

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


w=window.open('http://www/coop-dev');
w=window.open('http://www/coop-dev?auth[uid]=8&auth[pwd]=tester');
w.location = 'http://www/coop-dev?auth[uid]=8&auth[pwd]=tester';

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
p = new JsonRpcProxy('http://www/coop-dev/tests/dispatchproxy.php',
                     ['echotest', 'getPage', 'throwError', 
                      'throwPEARError', 'nothing']);

undefer(p.echotest('foo bar baz')); 
undefer(p.getPage());
undefer(p.throwError());
undefer(p.throwPEARError());
undefer(p.echotest());
undefer(p.nothing());



/// subform test!
p = new JsonRpcProxy('http://www/coop-dev/tests/dispatchproxy.php',
                     ['getPage', 'dispatchTable']);
undefer(p.getPage());


n=window.open('http://www/nothing')
subform=n.document.getElementsByTagName('body')[0].appendChild(DIV({id:'subform'},'stuff goes here'))

p.dispatchTable({action : 'add', table : 'ads'}).addCallback(function(data){ subform.innerHTML = data});


p = new JsonRpcProxy('http://www/coop-dev/tests/dispatchproxy.php')
p.call('methodList').addCallback(function(data){p.addMethods(values(data))});




////////// just for testing
processCustomSelect = function(selectbox, target_id, showtext) { 
    log('selectbox: ' + selectbox.name + ' targetid ' + target_id);
    edlink = document.getElementById("subedit-" + selectbox.name); 
    if (!edlink) { 
        log('ERROR hey no edlink subedit-' + selectbox.name);
        return; 
    } 
    editperms = eval("editperms_" + selectbox.name.replace(/-/g, "_")); 
    if (selectbox.value > 0 && editperms[selectbox.value]) { 
        edlink.className = ""; 
        re = new RegExp("(" + target_id + "=)\\d*?(&)", "g"); 
        edlink.href = edlink.href.replace(re, "$1" + selectbox.value + "$2"); 
        if (showtext) { 
            edlink.innerHTML = "Edit " + 
                selectbox.options[selectbox.selectedIndex].text; 
        } 
    } else { 
        edlink.className = "hidden"; 
    } 
}

w.processCustomSelect(w.document.forms[0]['companies_income_join-company_id'], 
                      'companies-company_id')

try{
    window.__proto__;
}
catch(error){
    println(typeof error);
}



try{
    throw 'up';
}
catch(e){
    println(typeof e);
}


// doesn't work, but might
/^\[xpconnect wrapped native/.test(window.toString())


if(window instanceOf Object){
    return true;
}


window instanceof Object
window instanceof Array


pprint(window)
property_names(window)


edlink = document.getElementById('subedit-invitations-lead_id');
edlink.parentNode.removeChild(edlink);



//get windows, using XUUL, emacs:
// though it doensn't stay updated if you change the windows, oddly
windows= [];
en=Components.classes["@mozilla.org/appshell/window-mediator;1"].getService(Components.interfaces.nsIWindowMediator).getEnumerator("")
while(en.hasMoreElements()) {
    windows.push(en.getNext());
}



w.combobox_invitations_lead_id.selectBox.cleanBox = function ()
{
    for ( i=this.length; this.length> 0; i--) {
        // UNLESS it is selected!! XXX this causes an endless loop
        if(this.selectedIndex != i){
            this.remove(i);
        }
    }
}



edlink = w.document.getElementById('subedit-invitations-family_id');
ed = w.document.forms[0]['invitations-family_id'];



eph=w.combobox_invitations_lead_id.editpermshidden;
eph=w.document.getElementById('editperms-invitations-family_id');

eph.value;
eph.decoded;
eval(eph.value);

blah={"2":false, "3":true}

ed.addEventListener('keyup',function(ev){processCustomSelect(this, 'families-family_id', 0)}, false);

// MUCH simpler way of doing regexps!
'foobah'.replace(/foo(\w+)/g, '$1ee')


//////////
sb=w.combobox_invitations_lead_id.selectBox;
saver=sb.options[sb.selectedIndex];
sv=sb.combobox.editpermshidden.decoded[saver.value];
saveperms = {saver.value: sv};

for ( i=sb.length; sb.length> 0; i--) {
    sb.remove(i);
}
sb.options[0] = saver;
sb.selectedIndex = 0;


////// 
foo={'bar': 'baz','fart':'belch'};
bar={'aah': 'pook'};
extend(foo, bar);
foo=update(foo, bar);



filter(function(x){return  x[0] == 'coop'},
       map(function(i){ if(typeof(i) == 'string'){return i.split('=')}}, 
           map(trim, w.document.cookie.split(';'))))[0][1]
    

    
setRequestHeader!


var strtrim = function(str) {
	return str.replace(/^\s*|\s*$/g, "");
};
function strtrim() 
{
    return this.replace(/^\s+/,'').replace(/\s+$/,'');
}


' foobar '.replace(/^\s*|\s*$/g, "");


var cookiesplit = function(i){if(i typeof String) return i.split('=')};
cookie=filter(function(x){return  x[0] == 'coop'},
              map(cookiesplit, 
                      map(trim, w.document.cookie.split(';'))))[0][1]

'foobar'.replace('/f/j', 'a')

var trim = function(str){return str.replace(/^\s+|\s+$/g, "")};

// doesn't work in javascript, alas
tt=function(str){str.replace(/^\s+|\s+$/g, "")};
tt(' foo ');

tuplesplit = function(i){ 
        if(typeof(i) == 'string'){return i.split('=')}
    };

map(tuplesplit, w.location.search.slice(1).split('&'));

'fooa'.slice(1)
 
Math.round(Math.ceil(40/10)/ 5) * 5



for(cell in w.document.getElementsByTagName('TD')){
    for(child in cells.childNodes){
            if(child.nodeName=="P") { 
                //trim here 
                child.innerHTML = 'foo!';
            } 
    } 
}

///// the stuff for my library thing

addScript('http://www/coop-dev/lib/MochiKit/MochiKit.js')

d=doSimpleXMLHttpRequest('http://www/coop-dev/amazon-hack.php', 
    {'Service':'AWSECommerceService',
     'AWSAccessKeyId': '0F1YJJRT1KE6VF2DVQ02',
     'Operation': 'ItemLookup',
     'IdType': 'ASIN',
     'ItemId': '0596000081',
     'ResponseGroup': 'Small'})

r=d.results[0].responseXML.documentElement

// textContent doesn't work with for iterations, have to do [i]
a = [];
i=0;
while(i < r.getElementsByTagName('Author').length){
    a.push(r.getElementsByTagName('Author')[i].textContent);
    i++;
}
authors = a.join(', ');
title = r.getElementsByTagName('Title')[0].textContent;


'0-393-28935-222-X2224'.replace(/[^0-9X]/g,'');


'foo-bar-baz'.match('\.')


