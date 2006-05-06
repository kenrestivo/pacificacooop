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
        var evt = new Evt(ev);
        
        // if i am the byid, wipe the search, and vice versa
        if(typeof this.byID != 'undefined'){
            var src =  evt.getSource();
            if(src == this.searchBox) {
                this.byID.value = '';
            }
            if(src == this.byID){
                this.searchBox.value = '';
            }
        }
        
        switch(ev.keyCode) 
        {
            // trap these keys
            case 13:
            case 39: 
                evt.consume();
                if(evt.getType() == 'keydown'){
                    this.fetchData();
                }
            return false;
            break;
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
            //NOTE! must eval the this.matches too, otherwise 'invalid label'
            epm = this.matches.editperms;
            this.editpermshidden.decoded = update(this.editpermshidden.decoded, 
                                                  eval(epm));

            // CLEAR OUT value! i am *not* going to unserialise this
            this.editpermshidden.value  =  ""; 
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
            
            //TODO: use mochikit json stuff
            eval('this.matches = ' + data.responseText);
            
            //TODO: put the count found in here
            this.status.innerHTML = 'Done';
            
            if (this.matches.data.length < 1)
            {
                this.status.innerHTML= 'No matches for "' + 
                    this.searchBox.value + '".';
                this.searchBox.focus();
                this.searchBox.select();
                return;
            }
            
            
            this.populateBox();

            this.populateEditperms();
        }

  
    
    this.fetchData = 
        function ()
        {
            var self = this; // CRITICAL for callbacks!

            qo= {};
            qo.f = this.linkTableName;

            if(typeof this.byID != 'undefined' && 
               this.byID.value > 0)
            {
                qo.i = this.byID.value;
            } else {
                // XXX is this right?
                if(!this.searchBox.value || this.searchBox.value.length < 2)
                {
                    this.status.innerHTML = 'Type at least 2 characters to search.';
                    return;
                }
                qo.q = this.searchBox.value;
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

    this.setTraps = function(obj)
        {

            EventUtils.addEventListener(
                obj, 'keyup', 
                function(ev){ return self.trapKey(ev) }, 
                true);
            EventUtils.addEventListener(
                obj, 'keydown', 
                function(ev){ return self.trapKey(ev) }, 
                true);
            EventUtils.addEventListener(
                obj, 'keypress', 
                function(ev){ return self.trapKey(ev) }, 
                true);


        }


/// CONSTRUCTOR    
    
    // to handle more than one
    this.searchBox = document.getElementsByName(searchBoxName)[0];
    this.selectBox = document.getElementsByName(selectBoxName)[0];
    this.status = document.getElementById('status-' + selectBoxName);
    this.editpermshidden = document.getElementById('editperms-' + selectBoxName);
    this.byID = document.getElementsByName('byID-' + selectBoxName)[0];

    this.linkTableName = linkTableName;
    
    
    // just to be sure
    this.searchBox.setAttribute('autocomplete', 'Off');
    this.selectBox.setAttribute('autocomplete', 'Off');

    
    //get the settings!
    for(i in comboboxsettings){
        eval('this.' +i+ ' = comboboxsettings.' +i);
    }

    var self = this; // needed for callbacks and reflection!
    this.selectBox.combobox=self;
    this.searchBox.combobox=self;
    this.status.combobox=self;

    if(self.editpermshidden.value){
        eval('self.editpermshidden.decoded = ' +self.editpermshidden.value);
        self.editpermshidden.value = ""; // so i don't submit it
    }


    
    // add my callbacks for key handling
    this.setTraps(this.searchBox);
    
    if(typeof this.byID != 'undefined'){
        this.byID.setAttribute('autocomplete', 'Off');
        this.setTraps(this.byID);
    }

    // utility function i'll need later
    this.selectBox.cleanBox = function ()
        {

            try{
            saver=this.options[this.selectedIndex];
            saveperms = {};

            saveperms[String(saver.value)] = self.editpermshidden.decoded[saver.value];
            
            for ( i=this.length; this.length> 0; i--) {
                this.remove(i);
            }

            this.options[0] = saver;
            this.selectedIndex = 0;
            
            } catch(e){
                //XXX do something dude!
            }

        }





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

