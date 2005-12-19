// $Id$



infoPopup = {
    'def': null
}

var showDetails= function(self, x, y){
    d=document.getElementById('dialog');
    d.style.top = (y - 20) + 'px';
    d.style.left = x + 'px';
    d.innerHTML = ' event at x: ' + x +  'y:' +y;
    d.className = 'dialog';
    return false;
}

var delayDetails = function(ev){
    ev = ev || window.event; // IE sucks.
    if(infoPopup.def){
        infoPopup.def.cancel();
    }
    infoPopup.def = callLater(0.5, showDetails, clone(ev.target), 
                              ev.clientX, ev.clientY);
}

var  hideDetails = function(self){
    if(infoPopup.def){
        infoPopup.def.cancel();
    }
    document.getElementById('dialog').className = 'hidden';
}


printEvent = function (ev){
    return 'EV x:'+ev.screenX+' y:'+ev.screenY+' cx:'+ev.clientX+' cy:'+ev.clientY;

}


showEvent = function(ev){
    ev = ev || window.event; // IE sucks.
    alert(printEvent(ev));
}
