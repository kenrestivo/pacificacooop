// $Id$
// a cheap and dirty non-rpc rpc.

sendEmailNotice = function(self,audit_id){
    ih=self.innerHTML;
    self=swapDOM(self,P({},'Sending', IMG({'src':'/images/spinner.gif'})));
    var trim = function(str){return str.replace(/^\s+|\s+$/g, "")};
    cookie=filter(function(x){return  x[0] == 'coop'},
       map(function(i){ if(typeof(i) == 'string'){return i.split('=')}}, 
           map(trim, document.cookie.split(';'))))[0][1]


    //TODO: send as a cookie header instead of passing this silly way
    d=doSimpleXMLHttpRequest('send_email.php', 
        {'audit_id': audit_id,
        'coop' : cookie});
    d.addCallback(function(data){ 
        self.innerHTML='Done'}); 
    d.addErrback(function(err) { self.innerHTML= 'Error!'}); 
    return false;
}

