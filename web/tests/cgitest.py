#!/usr/bin/env python



import os
import sys



#only for test and maint dirs
os.chdir('../')
sys.path.insert(0, os.getcwd())

#housebreaking wee-wee pads, needed bfore i import stuffi need
sys.path.append(os.getcwd()+'/site-packages')
sys.path.append(os.getcwd()+'/lib')


#####end of setup?


import coop_page


page=coop_page.Page()
page.headers['Content-Type'] = 'text/html; charset=utf-8'
page.template_name  = 'debugtest'

# for reloading
if page.session.sid:
    page.raw_output.append('<a href="?coop=%s">refresh</a>' %(page.session.sid))


for i in sys.path:
    page.raw_output.append(i+'<br>')

#just some data so we know it worked
from posix import environ
for i in environ.items():
    page.raw_output.append('%s: %s<br />' % i )
    




##### finally output stuff
page.render_raw(True)
#page.render_template()


#END
