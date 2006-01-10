#!/usr/bin/env python



import os
import sys

#housebreaking wee-wee pads, needed bfore i import stuffi need
try:
    mydir=os.path.dirname(__file__)
    #ONLY FOR /test dir!
    mydir = mydir + '/..'
    sys.path.append(mydir)
except NameError:
    mydir=os.getcwd()
    pass

sys.path.insert(0,'/'.join((mydir,'lib')))
sys.path.insert(0,'/'.join((mydir,'objects')))
os.chdir(mydir)


#jeez, second step in finding settings
import dbhost
try:
    sys.path.insert(1,dbhost.sitepackages)
except AttributeError:
    pass

import model

from phpserialize.PHPSerialize import PHPSerialize
from phpserialize.PHPUnserialize import PHPUnserialize

phpun=PHPUnserialize()
phpize=PHPSerialize()


#####end of setup?

debug = []
output = []
headers = []

import cgi
from posix import environ

## hack around bug in cgi input
forminput = dict([(i.name, i.value)
                  for i in cgi.FieldStorage(keep_blank_values=True).list])

import Cookie

if environ.has_key('HTTP_COOKIE'):
    recv_cookies=Cookie.BaseCookie(environ['HTTP_COOKIE'])
    recv_cookie_dict=dict([(i[0],i[1].value) for i in recv_cookies.items()])
    debug.append('found cookies: %s' % (recv_cookie_dict))
    session = phpun.session_decode(model.SessionInfo.get(recv_cookie_dict['coop']).vars)
    #TODO: cache the db object too-- will need to save the session later

if not (environ.has_key('HTTP_COOKIE') and recv_cookies.has_key('foobar')):
    new_cookies=Cookie.BaseCookie()
    newid = 'XXXtestnewsession'
    new_cookies['coop'] = newid
    #TODO: save the  new session!
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
