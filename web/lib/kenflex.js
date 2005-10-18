/*
        $Id$
        Copyright (c) 2005 ken restivo <ken@restivo.org>
        based on flexac

 * +-------------------------------------------------------------------+
 * | This file is part of flexac                                       |
 * | Copyright (c) 2005 Claudio Cicali <claudio@cicali.org>            |
 * +-------------------------------------------------------------------+
 * | flexac is free software; you can redistribute it and/or           |
 * | modify it under the terms of the GNU General Public License       |
 * | as published by the Free Software Foundation; either version 2    |
 * | of the License, or (at your option) any later version.            |
 * | flexac is distributed in the hope that it will be useful,         |
 * | but WITHOUT ANY WARRANTY; without even the implied warranty of    |
 * | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the     |
 * | GNU General Public License for more details.                      |
 * | You should have received a copy of the GNU General Public License |
 * | along with this program; if not, write to the:                    |
 * | Free Software Foundation, Inc., 59 Temple Place - Suite 330,      |
 * |                           Boston, MA 02111-1307, USA.             |
 * +-------------------------------------------------------------------+
 * | Authors: Claudio Cicali <claudio@cicali.org>                      |
 * +-------------------------------------------------------------------+
*/

flexac.configure = function()
{
  flexac.config = new flexacConfiguration();

  // Script to invoke. Parameters are "q" (query string) and "p" (plugin)
  flexac.config.script          = "flexac.php";
  // HTTP method used to invoke the script
  flexac.config.scriptMethod    = "GET";
  // Timeout (in ms) after which the list is automatically hidden
  flexac.config.hideTimeout     = 15000;
  // Timeout (in ms) after which the search is began
  flexac.config.acTimeout       = 250;
  // How many items have to stay visibile in the list
  flexac.config.listMaxItem     = 10;
  // Change the input when a choice has been made ? (adding the flexacChoose class)
  flexac.config.notifyChoose    = true;
  // Change the input when a searching... ? (adding the flexacSearching class)  
  flexac.config.notifySearching = true;
}

flexac.onInputKeyUp = function ( hEvent )
{
  if (flexac.hideTimer != null) 
    clearTimeout(flexac.hideTimer);
    
  flexac.hideTimer = setTimeout("flexac.hideList()", flexac.config.hideTimeout);
  
  if (flexac.input.value.length < 2 && flexac.listShown)
    flexac.hideList();      
}

flexac.onInputKeyDown = function ( hEvent )
{
  if(!hEvent) 
    hEvent = window.event;
    
  switch(hEvent.keyCode)
  {
    case 37: // left arrow
    case 39: // right arrow
    case 33: // page up  
    case 34: // page down  
    case 36: // home  
    case 35: // end
    case 27: // esc
    case 16: // shift  
    case 17: // ctrl  
    case 18: // alt  
    case 20: // caps lock
    case 38: // up arrow
    case 40: // down arrow
      break;
    default:
      if (flexac.config.notifyChoose)
        flexac.input.className = flexac.input.className.replace(/flexacChoose/, "");    
      break;
  }
    
  switch(hEvent.keyCode)
  {
    case 13:
    case 39: // Enter & Right
      flexac.setValue();
      flexac.clearList();
      flexac.hideList();
      hEvent.returnValue = false;
      break;
      
    case 40: // Down 
      if (!flexac.listShown)
      {
        if (flexac.acTimer != null) 
          clearTimeout(flexac.acTimer);
        flexac.autoComplete();    
        flexac.selectCurrent();
        break;
      }

      if (flexac.container && flexac.list.hasChildNodes())
      {
        if (flexac.activeItem == null)
        {
          try {
            flexac.activeItem = flexac.list.childNodes.item(0).firstChild;
            flexac.activeItemIdx = 0;
            flexac.windowMax = flexac.config.listMaxItem - 1;
            flexac.windowMin = 0;
          }
          catch(e) {}
        }
        else
        {
          try {
            nextItem = flexac.list.childNodes.item(flexac.activeItemIdx + 1).firstChild;
            flexac.unselectCurrent();
            flexac.activeItem = nextItem;
            flexac.activeItemIdx++;
          } 
          catch(e) {}
        }
      }
      flexac.selectCurrent();
      break;

    case 37: // Left
      break;
              
    case 38: // Up
      if (!flexac.listShown)
        break;
        
      if (flexac.container && flexac.list.hasChildNodes())
      {
        if (flexac.activeItem != null)
        {
          flexac.unselectCurrent();
          if (flexac.activeItemIdx > 0)
          {
            flexac.activeItemIdx--;
            flexac.activeItem = flexac.list.childNodes.item(flexac.activeItemIdx).firstChild;
          }
        }
      }
      flexac.selectCurrent();
      break;

    case 27: // Esc
      if (!flexac.listShown)
        break;
      flexac.clearList();
      flexac.hideList();
      break;
      
    case 9: // TAB
      flexac.hideList();
      flexac.onInputBlur();
      break;
                        
    default:  
      if (flexac.acTimer != null) 
        clearTimeout(flexac.acTimer);
      flexac.acTimer = setTimeout("flexac.autoComplete()", flexac.config.acTimeout);    
      break;
  }
  
  return hEvent.returnValue;
}

flexac.setValue = function ()
{
    
  if (flexac.config.notifyChoose)
  flexac.input.className += " flexacChoose";
  
  flexac.debug.value = key;
  
  
  var fields = document.getElementsByTagName( 'INPUT' );
  for (f in fields)
  {
      if (fields[f].name == parentName + "_id")
      {
          hidden = fields[f];
          break;
      }
  }
}

flexac.getMatchingElementKey = function(idx)
{
  var i=0;
  for (match in flexac.matches)
  {
    if (i == idx)
      return match;
    i++;
  }
}

flexac.onFormSubmit = function (hEvent)
{
  if( hEvent == null )
    hEvent = window.event;

  if (flexac.input.form.bLocked)
  {
    if (hEvent.preventDefault)
      hEvent.preventDefault()
    hEvent.returnValue = false
    return false;
  }       
}

flexac.onInputBlur = function ( hEvent )
{
  if (flexac.listShown)
    return;
    
  if (flexac.input.form)
    flexac.input.form.bLocked = false;

  delete flexac.activeItem;
  flexac.activeItemIdx = 0;
  if (flexac.container)
  {
    flexac.clearList();
    flexac.container.parentNode.removeChild(flexac.container);
    flexac.container = null;
  }
}

flexac.autoComplete = function ()
{
  var pattern = flexac.input.value.toLowerCase().trim();
  if (pattern.length < 2)
    return;
    
  if (flexac.config.notifyChoose)
    flexac.input.className = flexac.input.className.replace(/flexacChoose/, "");    
  if (flexac.config.notifySearching)
    flexac.input.className += " flexacSearching";
  
  if (flexac.xhr)
  {
    flexac.xhr.abort();
    delete flexac.xhr;
  }
    
  flexac.xhr = new XHConn();
  
  if (!flexac.xhr) alert("Your browser is too old. Install Firefox (http://www.getfirefox.com).");
  
  if (flexac.cache)
    flexac.feedListFromCache();
  else
  {
      qo= {};
      qo.q = pattern;
      qo.p = flexac.plugin;
      qo.l = flexac.searchCacheLimit;
      qo.b = flexac.searchBeginsWith;

      qa = [];
      for(x in qo){
          qa.push(x + '=' + encodeURIComponent(qo[x]));
      }
      query = qa.join('&');

      if (!flexac.xhr.connect(flexac.config.script, 
                              flexac.config.scriptMethod, 
                              query,
                              flexac.feedListFromServer))
          alert("Failed connecting");
  }
}

flexac.feedListFromCache = function ()
{
  flexac.matches = flexac.cache;
  flexac.feedList();
}

flexac.feedListFromServer = function (data)
{
  if (data.status.toString() != "200")
  {
    alert("Can't connect [" + data.status.toString() + "]");
    return;
  }
    
  eval("flexac.matches = " + data.responseText);

  if (!flexac.matches)
  {
    flexac.hideList();
    return;
  }
  
  if (flexac.searchCacheLimit > 0)
    flexac.cache = flexac.matches;
  
  flexac.feedList();
}

flexac.feedList = function()
{
  if (flexac.config.notifySearching)
    flexac.input.className = flexac.input.className.replace(/flexacSearching/, "");
    
  flexac.clearList();
        
  flexac.activeItem = null;
  flexac.activeItemIdx = 0;
  
  var pattern = flexac.input.value.toLowerCase().trim();
  hItem = null;
  for (match in flexac.matches)
  {
    idx = flexac.matches[match].toLowerCase().indexOf(pattern);

    if (idx != -1 || flexac.matches[match] == "???")
    {
        // insert the item
        flexac.selectbox.options[flexac.selectbox.length]= 
            new Option(match, matches[match]);    
        
    }
  }
  
  
  
}

flexac.onItemClick = function(hEvent)
{
    //UNNEDED?

  return false;
}

flexac.onItemMouseOver = function(hEvent)
{
    //UNNEDED?
}

flexac.showList = function()
{
    //UNNEDED?
  flexac.listShown = true;
}

flexac.hideList = function()
{
    //UNNEDED?

}

flexac.selectCurrent = function()
{
    //TODO
}

flexac.scrollListDown = function()
{
    //UNNEDED?

}

flexac.scrollListUp = function()
{
    //UNNEDED?

}

flexac.unselectCurrent = function()
{
    //UNNEDED?
}

flexac.clearList = function ()
{
    //TODO
}


function flexacConfiguration()
{
  this.hideTimeout = 0;
}

// Limit is optional
function flexacOn(hInput, plugin, beginsWith, limit)
{
  hInput.autocomplete = 'off';

  //NO. not here. it'll blow away my custom url. flexac.configure();
  flexac.plugin = plugin;  
  flexac.input = hInput;

  flexac.searchBeginsWith = beginsWith ? "1" : "0";
  
  flexac.searchCacheLimit = 0;
  if (limit)
    flexac.searchCacheLimit = limit;
  
  // Clear cache
  
  if (flexac.cache)
    delete flexac.cache;
    
  flexac.cache = null;
    
  // first, remove the event handler if any (it is mandatory for IE to work well)
  if (hInput.attachEvent)
  {
    hInput.detachEvent('onkeyup',   flexac.onInputKeyUp);
    hInput.detachEvent('onkeydown', flexac.onInputKeyDown);
    hInput.detachEvent('onblur',    flexac.onInputBlur);
    hInput.attachEvent('onkeyup',   flexac.onInputKeyUp);
    hInput.attachEvent('onkeydown', flexac.onInputKeyDown);
    hInput.attachEvent('onblur',    flexac.onInputBlur);
  }
  else 
  if (hInput.addEventListener)
  {
    hInput.removeEventListener('keyup',   flexac.onInputKeyUp,   false);
    hInput.removeEventListener('keydown', flexac.onInputKeyDown, false);
    hInput.removeEventListener('blur',    flexac.onInputBlur,    false);
    hInput.addEventListener('keyup',   flexac.onInputKeyUp,   false);
    hInput.addEventListener('keydown', flexac.onInputKeyDown, false);
    hInput.addEventListener('blur',    flexac.onInputBlur,    false);
  }

  flexac.debug = document.getElementById("debug");
/*      
  //allow backspace to work in IE
  if (typeof obj.selectionStart == 'undefined' && evt.keyCode == 8) 
  { obj.value = obj.value.substr(0,obj.value.length-1); }

*/
}

//
//  This script was created
//  by Mircho Mirev
//  mo /mo@momche.net/
flexac.getOffsetParam = function( hElement, sParam, hLimitParent )
{
  var nRes = 0
  if (hLimitParent == null)
  {
    hLimitParent = document.body.parentElement
  }
  while (hElement != hLimitParent)
  {
    nRes += eval( 'hElement.' + sParam )
    if( !hElement.offsetParent ) { break }
    hElement = hElement.offsetParent
  }
  return nRes;
}

/** XHConn - Simple XMLHTTP Interface - brad@xkr.us - 2005-01-24             **
 ** Code licensed under Creative Commons Attribution-ShareAlike License      **
 ** http://creativecommons.org/licenses/by-sa/2.0/                           **/
function XHConn()
{
  var xmlhttp;
  var active;
  try { xmlhttp = new ActiveXObject("Msxml2.XMLHTTP"); }
  catch (e) { try { xmlhttp = new ActiveXObject("Microsoft.XMLHTTP"); }
  catch (e) { try { xmlhttp = new XMLHttpRequest(); }
  catch (e) { xmlhttp = false; }}}
  if (!xmlhttp) return null;
  this.connect = function(sURL, sMethod, sVars, fnDone)
  {
    if (!xmlhttp) return false;
    sMethod = sMethod.toUpperCase();

    try {
      if (sMethod == "GET")
      {
        xmlhttp.open(sMethod, sURL+"?"+sVars, true);
        sVars = "";
      }
      else
      {
        xmlhttp.open(sMethod, sURL, true);
        xmlhttp.setRequestHeader("Method", "POST "+sURL+" HTTP/1.1");
        xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      }
      xmlhttp.onreadystatechange = function(){ if (xmlhttp.readyState == 4) {
        fnDone(xmlhttp); }};
      xmlhttp.send(sVars);
    }
    catch(z) { return false; }
    return true;
  };
  this.abort = function()
  {
    try {
      //xmlhttp.abort();
    }
    catch(z) { return false; }
  }
  
  return this;
}

// From
// http://www.developingskills.com/ds.php?article=jstrim&page=1
function strtrim() 
{
  return this.replace(/^\s+/,'').replace(/\s+$/,'');
}

String.prototype.trim = strtrim;

function debug(msg)
{
  document.getElementById("debug").innerHTML = msg;
}

function flexac()
{
}
