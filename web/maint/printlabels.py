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



import coop_page


page=coop_page.Page()
page.headers['Content-Type'] = 'text/html; charset=utf-8'
page.template_name  = 'debugtest'


from posix import environ
for i in environ.items():
    page.raw_output.append('%s: %s<br />' % i )
    

from reportlab.platypus import SimpleDocTemplate, Spacer
from reportlab.lib.styles import getSampleStyleSheet
from reportlab.rl_config import defaultPageSize
from reportlab.platypus.tables import Table,TableStyle
from reportlab.lib import colors
from reportlab.lib.units import inch


page=coop_page.Page()
page.headers['Content-Type'] = 'application/pdf'
page.headers['Content-Disposition'] = 'attachment; filename= "labeltest.pdf"'

    
    
doc = SimpleDocTemplate(sys.stdout)
story=[]

## note data must be multiple of 3, need to PAD IT OUT!!
n=3
rawdata = range(0, 105)
data = [rawdata[i:i + n] for i in range(0, len(rawdata), n)]

DEBUG_LAYOUT = TableStyle( [('OUTLINE', (0,0), (-1,-1), 0.25, colors.red),
                            ('INNERGRID', (0,0), (-1,-1), 0.25, colors.black),
                            ('ALIGN', (0,0), (-1,-1), 'LEFT'),
                            ('VALIGN', (0,0), (-1,-1), 'MIDDLE')] )

t=Table(data, colWidths=2*inch, rowHeights=1.5*inch)
t.setStyle(DEBUG_LAYOUT)
story.append(t)
    




##### finally output stuff
page.output_headers()
doc.build(story)



#END
