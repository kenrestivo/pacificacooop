// $Id$
// a cheap and dirty non-rpc rpc.

sendEmailNotice = function(self,audit_id){
    ih=self.innerHTML;
    self=swapDOM(self,P({},'Sending', IMG({'src':'/images/spinner.gif'})));
    d=doSimpleXMLHttpRequest('send_email.php', {'audit_id': audit_id});
    d.addCallback(function(data){ 
        self.innerHTML='Done'}); 
    d.addErrback(function(err) { self.innerHTML= 'Error!'}); 
    return false;
}

