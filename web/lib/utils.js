//$Id$

addScript = function(url, doc){
    if(!doc){
        doc=document;
    }
	var s=doc.createElement('script');
	s.setAttribute('type', 'text/javascript');
	s.setAttribute('src', url);
	doc.getElementsByTagName('body')[0].appendChild(s);
}

getEl = function(id, win){
    if(!win){
        win=window;
    }
    return win.document.getElementById(id);
}
