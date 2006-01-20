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
    

from reportlab.platypus import SimpleDocTemplate, Spacer, Paragraph
from reportlab.lib.styles import getSampleStyleSheet
from reportlab.rl_config import defaultPageSize
from reportlab.platypus.tables import Table,TableStyle
from reportlab.lib import colors
from reportlab.lib.units import inch
from reportlab.lib.styles import getSampleStyleSheet

import logging

from sqlobject import *

page=coop_page.Page()
page.headers['Content-Type'] = 'application/pdf'
page.headers['Content-Disposition'] = 'attachment; filename= "labeltest.pdf"'


# annoying crap reportalb wants
styles = getSampleStyleSheet()
style = styles["Normal"]


def getData():
    """note data must be multiple of 3, need to PAD IT OUT!!"""
    n=3
    rawdata = range(0, 105)
    return [rawdata[i:i+n] for i in range(0, len(rawdata), n)]


class DBresults:
    """step through the result set"""
    c=None
    step = 0
    lq = '''concat_ws("\n"
,concat_ws(" " , leads.salutation, leads.first_name, leads.last_name)
,if(length(leads.title)>0, leads.title, null)
,if(length(leads.company)>0, leads.company, null)
,if(length(leads.address1)>0, leads.address1, null)
,if(length(leads.address2)>0, leads.address2, null)
,concat_ws(" ", concat(leads.city, ", ", leads.state), leads.zip, if(leads.country != "USA", leads.country, ""))
) as lead_label'''
    limit=""

    def __init__(self, step=3):
        self.step = step
        self.c=sqlhub.getConnection().getConnection().cursor()
        self.c.execute('''select %s from invitations left join leads using (lead_id) where school_year = "2005-2006" order by invitations.lead_id''' %(self.lq))

    def __iter__(self):
        return self

    def next(self):
        """this pads the db results out to the number of steps required"""
        res = []
        flag = 0
        while len(res) < self.step:
            line=self.c.fetchone()
            logging.debug(line)
            if line == None or line == '':
                logging.info('END, got a none')
                flag = 1
            elif len(line) < 1:
                logging.warning('hey, less than 1')
            else :
                res.append(Paragraph(line[0], style))
            if len(res) < self.step and flag > 0:
                raise StopIteration
        return res


### i hate the intending
if __name__ == '__main__':

#rendering!
    DEBUG_LAYOUT = TableStyle( [('OUTLINE', (0,0), (-1,-1), 0.25, colors.red),
                                ('INNERGRID', (0,0), (-1,-1), 0.25, colors.black),
                                ('ALIGN', (0,0), (-1,-1), 'LEFT'),
                                ('VALIGN', (0,0), (-1,-1), 'MIDDLE')] )

    doc = SimpleDocTemplate(sys.stdout)
    story=[]

    t=Table([i for i in DBresults()], colWidths=2*inch, rowHeights=1.5*inch)
    t.setStyle(DEBUG_LAYOUT)
    story.append(t)



##### finally output stuff
    page.output_headers()
    doc.build(story)



#END
