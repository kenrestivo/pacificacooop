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



#####end of setup?



import cgi
import coop_page


page=coop_page.Page()
page.headers['Content-Type'] = 'text/html; charset=utf-8'
page.template_name  = 'debugtest'


from posix import environ
for i in environ.items():
    page.raw_output.append('%s: %s<br />' % i )
    





##### finally output stuff
#page.render_raw(True)
page.render_template()


#END
