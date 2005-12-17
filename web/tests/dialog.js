// $Id$

var showDetails= function(self){
    d=document.getElementById('dialog');
    d.style.visibility='visible';
    d.style.top = self.offsetTop + 'px';
    d.style.left = (self.offsetLeft + self.offsetWidth)/2 + 'px';
    d.innerHTML = self.innerHTML;
    return false;
}

var hideDetails = function(self){
    document.getElementById('dialog').style.visibility='hidden';
}
