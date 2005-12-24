//$Id$


addLink = function(url, doc){
    if(!doc){
        doc=document;
    }
	var s=doc.createElement('link');
	s.setAttribute('rel', 'stylesheet');
	s.setAttribute('href', url);
	doc.getElementsByTagName('head')[0].appendChild(s);
    return s;
}




