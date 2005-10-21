/*
        $Id$
        Copyright (c) 2005 ken restivo <ken@restivo.org>
        based on flexac

 * +-------------------------------------------------------------------+
 * | This file was part of flexac                                       |
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

//GLOBALS
combobox.serverPage = 'lib/flexac/kenflex.php';


combobox.cleanBox =
function (box)
{
    for ( i=box.length; box.length> 0; i--) {
        box.remove(i);
    }

}


// loop through matches, put them in the box
combobox.populateBox =
function ()
{
    combobox.cleanBox(combobox.selectBox);
    
    for (match in combobox.matches)
    {
        combobox.selectBox.options.add(new Option(combobox.matches[match], 
                                                  match));
    }

    combobox.selectBox.focus();
}


// temporarily store the data in the object, then call populateBox to insert it
combobox.fetchDataCallback = 
function(data)
{
  if (!data || data.status.toString() != "200")
  {
    combobox.status.innerHTML= "Can't connect [" + data.status.toString() + "]";
    return;
  }

  if(!data.responseText){
      //XXX is this right?
      combobox.status.innerHTML= 'No data returned.';
      return;
  }

    
  //XXX this is global! should it be? or should i have multiple comboboxes?
  eval("combobox.matches = " + data.responseText);

  if (!combobox.matches)
  {
      combobox.status.innerHTML= 'No matches for "' + 
          combobox.searchBox.value + '".';
      combobox.searchBox.focus();
      combobox.searchBox.select();
      return;
  }
  
  combobox.status.innerHTML = 'Done';
  
  combobox.populateBox();

}

combobox.fetchData = 
function ()
{
    // XXX is this right?
    if(!combobox.searchBox.value || combobox.searchBox.value.length < 2){
        combobox.status.innerHTML = 'Type at least 2 characters to search.';
        return;
    }

    combobox.status.innerHTML = 'Searching..';

    if (combobox.xhr)
    {
        combobox.xhr.abort();
        delete combobox.xhr;
    }
    
    combobox.xhr = new XHConn();
  
    if (!combobox.xhr){
        alert("Your browser is too old. Install Firefox (http://www.getfirefox.com).");
    }
  

    qo= {};
    qo.q = combobox.searchBox.value;
    qo.f = combobox.linkTableName;

    qa = [];
    for(x in qo){
        //XXX note! this encodeuricomponent is not available in IE < 5.5!
        qa.push(x + '=' + encodeURIComponent(qo[x]));
    }
    if(combobox.SID){
        qa.push(combobox.SID);
    }
    query = qa.join('&');

    if (!combobox.xhr.connect(combobox.serverPage, 
                              'GET', 
                              query,
                              combobox.fetchDataCallback))
    alert("Failed connecting");
 
}


///////////////
// GLOBAL functions
function setStatus(text)
{
    combobox.status.innerHTML = text;

}


function coopSearch(caller, searchBoxName, selectBoxName, linkTableName)
{
    // TODO: create a NEW combobox here, with above params
    // this is a damned constructor

    // fetch the fields i need
    // TODO: make this use caller.form.getelements, no?
    // to handle more than one
    combobox.searchBox = document.getElementsByName(searchBoxName)[0];
    combobox.selectBox = document.getElementsByName(selectBoxName)[0];
    combobox.status = document.getElementById('status-' + selectBoxName);
    combobox.linkTableName = linkTableName;

    ///combobox.searchBox.addEventListener[onchange] = setStatus('');

    // just to be sure
    combobox.searchBox.autocomplete = 'off';

    //  fetch data
    combobox.fetchData();
    

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
        /// XXX why is this commented out?
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

/// WHYis this necessary?
function combobox()
{

}
