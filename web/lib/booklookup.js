/* $Id$

*/

//TODO: a status area! like in kenflex status-books-isbn

function bookLookup(isbn_name, baseurl, access_key){
    isbn = document.getElementsByName(isbn_name).item(0);
    isbn.value = isbn.value.replace(/[^0-9X]/g,'');
    status = document.getElementById('status-books-isbn');
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
            isbn.form['books-authors'].value = a.join(', ');
            isbn.form['books-title'].value = ''; // clear out first
            isbn.form['books-title'].value = r.getElementsByTagName('Title').item(0).textContent;
            if(isbn.form['books-title'].value != ''){
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
