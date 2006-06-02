// $Id$
// a cheap and dirty non-rpc rpc.

sendEmailNotice = function(self,audit_id, urlpath){
    ih=self.innerHTML;
    self=swapDOM(self,P({},'Sending', IMG({'src':'/images/spinner.gif'})));
    var trim = function(str){
        return str.replace(/^\s+|\s+$/g, "")
    };
    var tuplesplit = function(i){ 
        if(typeof(i) == 'string'){return i.split('=')}
    };
    var findcoop= function(x){return  x[0] == 'coop'};
    try{
        cookie=filter(findcoop,
                      map(tuplesplit, 
                          map(trim, document.cookie.split(';'))))[0][1]
            } catch (e){
                self.innerHTML = 'No cookies?';
            }
    // now the ugly no-cookie way
    if(cookie == undefined){
        try{
            cookie=filter(findcoop,
                          map(tuplesplit, 
                              window.location.search.slice(1).split('&')))[0][1];
        } catch (e){
            self.innerHTML = "Can't find cookie";
        }
    }

    //TODO: send as a cookie header instead of passing this silly way
    /// XXX also need a proper path or base_url here!!
    d=doSimpleXMLHttpRequest(urlpath + '/services/send_email.php', 
        {'audit_id': audit_id,
        'coop' : cookie});
    d.addCallback(function(data){ 
        self.innerHTML='Done'}); 
    d.addErrback(function(err) { self.innerHTML= 'Error!' + err}); 
    return false;
}

