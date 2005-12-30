/*
        $Id$
        Copyright (c) 2005 ken restivo <ken@restivo.org>
        based on flexac, but thorougly re-done

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
comboboxsettings = {serverPage: 'lib/flexac/kenflex.php'};

function Combobox (searchBoxName, selectBoxName, linkTableName)
{
    
    this.trapKey  = function(ev){
        switch(ev.keyCode) 
        {
            // trap these keys
        case 13:
        case 39: 
            evt = new Evt(ev);
            evt.consume();
            this.fetchData();
            return false;
        default:
            break;
        }
        return true;
    }


    // loop through matches, put them in the box
    this.populateBox =
        function ()
        {
            this.selectBox.cleanBox();
            
            for (match in this.matches.data)
            {
                o=new Option(this.matches.data[match], match);
/*                 o.addEventListener('mouseover', function(){showDetails(this)},  */
/*                                    true); */
                this.selectBox.options.add(o);
            }
            
            this.selectBox.focus();

        }
    
    this.populateEditperms = 
        function ()
        {
            eval(this.editpermsname + ' =  this.matches.editperms');
            
        }
    
// temporarily store the data in the object, then call populateBox to insert it
    this.fetchDataCallback = 
        function(data)
        {
            if (!data || data.status.toString() != "200")
            {
                this.status.innerHTML= "Can't connect [" + data.status.toString() + "]";
    return;
            }
            
            if(!data.responseText){
                //XXX is this right?
                this.status.innerHTML= 'No data returned.';
                return;
            }
            
            //NOTE! must eval the this.matches too, otherwise 'invalid label'
            eval('this.matches = ' + data.responseText);
            
            
            if (!this.matches)
            {
                this.status.innerHTML= 'No matches for "' + 
                    this.searchBox.value + '".';
                this.searchBox.focus();
                this.searchBox.select();
                return;
            }
            
            //TODO: put the count found in here
            this.status.innerHTML = 'Done';
            
            this.populateBox();

            this.populateEditperms();
        }

  
    
    this.fetchData = 
        function ()
        {
            var self = this; // CRITICAL for callbacks!
            
            // XXX is this right?
            if(!this.searchBox.value || this.searchBox.value.length < 2){
                this.status.innerHTML = 'Type at least 2 characters to search.';
                return;
            }
            
            this.status.innerHTML = 'Searching..';
            
            if (this.xhr)
            {
                this.xhr.abort();
                delete this.xhr;
    }
            
            this.xhr = new XHConn();
            
            if (!this.xhr || typeof encodeURIComponent == 'undefined'){
                alert("Your browser is too old. Install Firefox (http://www.getfirefox.com).");
            }
            
            
            qo= {};
            qo.q = this.searchBox.value;
            qo.f = this.linkTableName;
            
            qa = [];
            for(x in qo){
                //XXX note! this encodeuricomponent is not available in IE < 5.5!
                qa.push(x + '=' + encodeURIComponent(qo[x]));
            }
            if(this.SID){
                qa.push(this.SID);
            }
            query = qa.join('&');
            
            if (!this.xhr.connect(this.serverPage, 
                                  'GET', 
                                  query,
                                  function(data){ self.fetchDataCallback(data)} ))
            this.status.innerHTML = "Failed connecting";
            
        }
    
/// CONSTRUCTOR    
    
// to handle more than one
    this.searchBox = document.getElementsByName(searchBoxName)[0];
    this.selectBox = document.getElementsByName(selectBoxName)[0];
    this.status = document.getElementById('status-' + selectBoxName);
    this.editpermsname = 'editperms_' + selectBoxName.replace(/-/g, '_');

    this.linkTableName = linkTableName;
    
    
// just to be sure
    this.searchBox.autocomplete = 'off';
    
    
//get the settings!
    for(i in comboboxsettings){
        eval('this.' +i+ ' = comboboxsettings.' +i);
    }

    // utility function i'll need later
    this.selectBox.cleanBox = function ()
        {
            for ( i=this.length; this.length> 0; i--) {
                // UNLESS it is selected!! XXX this causes an endless loop
                //if(this.selectedIndex != i){
                this.remove(i);
                    //}
            }
            
        }
    var self = this; // needed for callbacks and reflection!
    this.selectBox.combobox=self;
    this.searchBox.combobox=self;
    this.status.combobox=self;

    // add my callbacks for key handling
    EventUtils.addEventListener(this.searchBox, 'keyup', function(ev){ return self.trapKey(ev)}, true);
    EventUtils.addEventListener(this.searchBox, 'keydown', function(ev){ return self.trapKey(ev)}, true);
    EventUtils.addEventListener(this.searchBox, 'keypress', function(ev){ return self.trapKey(ev)}, true);


} // end Combobox constructor





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
//yeah, that's nice. wtf is it necessary for?
//he doesn't appear to use it anywhere!
function strtrim() 
{
    return this.replace(/^\s+/,'').replace(/\s+$/,'');
}

String.prototype.trim = strtrim;

function debug(msg)
{
    document.getElementById("debug").innerHTML = msg;
}

