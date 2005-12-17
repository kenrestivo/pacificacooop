// $Id$

var showDetails= function(ev){
    ev = ev || window.event; // IE sucks.
    self=ev.target;
    d=document.getElementById('dialog');
    d.className = 'dialog';
    d.style.top = ev.clientY + 'px';
    d.style.left = ev.clientX + 'px';
    d.innerHTML = self.innerHTML;
    return false;
}

var hideDetails = function(self){
    document.getElementById('dialog').className = 'hidden';
}


showEvent = function(ev){
    ev = ev || window.event; // IE sucks.
    alert('x:'+ev.screenX+' y:'+ev.screenY+' cx:'+ev.clientX+' cy:'+ev.clientY)}
