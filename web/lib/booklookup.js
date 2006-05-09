/* $Id$

	Copyright (C) 2006  ken restivo <ken@restivo.org>
	 
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	 This program is distributed in the hope that it will be useful,
	 but WITHOUT ANY WARRANTY; without even the implied warranty of
	 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 GNU General Public License for more details. 
	
	 You should have received a copy of the GNU General Public License
	 along with this program; if not, write to the Free Software
	 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

// goes to get the book info from amazon, based on the isbn
function bookLookup(isbn_name, baseurl, access_key){
    var isbn = document.getElementsByName(isbn_name).item(0);

    // convention "table-field" must be followed!
    var table = isbn_name.split('-')[0];

    // first pre-process any cuecat input
    if(isbn.value.match(/\./) != null){
        isbn.value = translate(isbn.value);
    }

    isbn.value = isbn.value.replace(/[^0-9X]/g,'');
    status = document.getElementById('status-'+ isbn_name);
    status.innerHTML = 'Searching...';

    d=doSimpleXMLHttpRequest(baseurl + '/amazon-hack.php', 
        {'Service':'AWSECommerceService',
         'AWSAccessKeyId': access_key,
         'Operation': 'ItemLookup',
         'IdType': 'ASIN',
         'ItemId': isbn.value,
         'ResponseGroup': 'Small'})

    d.addCallback(
        function(data){
            r=d.results[0].responseXML.documentElement
            // textContent doesn't work with for iterations, have to do [i]
            a = [];
            i=0;
            while(i < r.getElementsByTagName('Author').length){
                a.push(r.getElementsByTagName('Author')[i].textContent);
                i++;
            }
            isbn.form[table + '-authors'].value = a.join(', ');
            isbn.form[table + '-title'].value = ''; // clear out first
            isbn.form[table + '-title'].value = r.getElementsByTagName('Title').item(0).textContent;
            if(isbn.form[table + '-title'].value != ''){
                status.innerHTML = 'Found it!';
            } else {
                status.innerHTML = 'Try again: No such ISBN number';
            }
        });
    d.addErrback(
        function(data){
        status.innerHTML = 'ERROR: Could not find ISBN. Did you mistype it?';
         });
}

// to add the event listeners.
// doing it as onchange/onkeyup/onkeydown via attributes doesn't work
// so i'm explicitly addeventlisterer'ing here instead.
function startBookListener(isbn_name, base_url, access_key)
{
    isbn = document.getElementsByName(isbn_name).item(0);

    EventUtils.addEventListener(
        isbn, 'change', 
        function(ev){ return bookLookup(isbn_name, base_url, access_key) }, 
        true);

    EventUtils.addEventListener(
        isbn, 'keyup', 
        function(ev){ return trapKey(ev,isbn_name, base_url, access_key) }, 
        true);


    EventUtils.addEventListener(
        isbn, 'keydown', 
        function(ev){ return trapKey(ev,isbn_name, base_url, access_key) }, 
        true);


    EventUtils.addEventListener(
        isbn, 'keypress', 
        function(ev){ return trapKey(ev,isbn_name, base_url, access_key) }, 
        true);


}


/// from kenflex. keeps the return key from submitting the form
function trapKey(ev, isbn_name, base_url, access_key){
    var evt = new Evt(ev);
    
    switch(ev.keyCode)
    {
        // trap these keys
    case 13:
    case 39:
        evt.consume();
        if(evt.getType() == 'keydown'){
            bookLookup(isbn_name, base_url, access_key)
                }
        return false;
        break;
    default:
        break;
    }
    return true;
}

