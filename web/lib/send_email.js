// $Id$
// a cheap and dirty non-rpc rpc.

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

