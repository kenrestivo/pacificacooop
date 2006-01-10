#!/usr/bin/env python



import os
import sys

#housebreaking wee-wee pads, needed bfore i import stuffi need
try:
    mydir=os.path.dirname(__file__)
    sys.path.append(mydir)
except NameError:
    mydir=os.getcwd()
    pass

sys.path.insert(0, '/'.join((mydir,'../')))
os.chdir(mydir)


#jeez, second step in finding settings
import dbhost
try:
    sys.path.insert(1,dbhost.sitepackages)
except AttributeError:
    pass



import cgi
from posix import environ

## hack around bug in cgi input
forminput = dict([(i.name, i.value)
                  for i in cgi.FieldStorage(keep_blank_values=True).list])

debug = []
output = []
headers = []

import Cookie

if environ.has_key('HTTP_COOKIE'):
    recv_cookies=Cookie.BaseCookie(environ['HTTP_COOKIE'])
    recv_cookie_dict=dict([(i[0],i[1].value) for i in recv_cookies.items()])
    debug.append('found cookies: %s' % (recv_cookie_dict))

if not (environ.has_key('HTTP_COOKIE') and recv_cookies.has_key('foobar')):
    new_cookies=Cookie.BaseCookie()
    new_cookies['foobar'] = 'test'
    headers.append(repr(new_cookies))
    debug.append('new cookies: "%s<br />"' % (str(new_cookies)))



headers.append('Content-Type: text/html; charset=utf-8\n')



for j in ['%s: %s<br />' % i for i in environ.items()]:
    debug.append(j)







##### finally output stuff
for i in  headers:
    print i
print
for i in debug:
    print i
for i in output:
    print i
