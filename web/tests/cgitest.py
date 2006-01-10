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

## hack around bug in cgi input
forminput = dict([(i.name, i.value)
                  for i in cgi.FieldStorage(keep_blank_values=True).list])


import Cookie
c=Cookie.BaseCookie()

c['foobar'] = 'test'

print c
print 'Content-Type: text/html; charset=utf-8\n'



print "hey there<br />"

print 'set these: "%s"' % (c)

from posix import environ
for j in ['%s: %s<br />' % i for i in environ.items()]:
    print j
