#!/usr/bin/env python

#  Copyright (C) 2004-2006  ken restivo <ken@restivo.org>
# 
#  This program is free software; you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation; either version 2 of the License, or
#  (at your option) any later version.
# 
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details. 
# 
#  You should have received a copy of the GNU General Public License
#  along with this program; if not, write to the Free Software
#  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


# $Id$

## i don't think this ever worked. it'll be replaced by civicrm

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


from posix import environ
for i in environ.items():
    page.raw_output.append('%s: %s<br />' % i )
    

from reportlab.platypus import SimpleDocTemplate, Spacer, Paragraph
from reportlab.lib.styles import ParagraphStyle,getSampleStyleSheet
from reportlab.rl_config import defaultPageSize
from reportlab.platypus.tables import Table,TableStyle
from reportlab.lib import colors
from reportlab.lib.units import inch
from reportlab.lib.enums import TA_RIGHT,TA_LEFT,TA_CENTER,TA_JUSTIFY
from reportlab.lib.pagesizes import letter
from reportlab.pdfbase.ttfonts import TTFont

import logging

from sqlobject import *

page=coop_page.Page()
page.headers['Content-Type'] = 'application/pdf'
page.headers['Content-Disposition'] = 'attachment; filename= "labeltest.pdf"'


# annoying crap reportalb wants
styles = getSampleStyleSheet()
response_code_style = ParagraphStyle(styles['Normal'])
response_code_style.alignment = TA_RIGHT


class LabelSettings:
    margins= []  #left, top, right, bottom ? s/b same as css for consistency
    row_height = 0
    col_width = 0
    row_gutter = 0
    col_gutter = 0
    #this is brain dead. i need to expose these in a web interface chooser
    text_style = None
    number_style = None
    debug = 0
    
    def __init__(self):
        pass



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
        self.c.execute('''select leads.lead_id, %s from invitations left join leads using (lead_id) where school_year = "2005-2006" order by last_name, first_name''' %(self.lq))

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
                # split them, each gets a paragraph object
                res.append([Paragraph(str(line[0]), response_code_style)]+
                           [Paragraph(p.strip(), styles['Normal'])
                            for p in line[1].split('\n')])
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


#     title="Mailing Labels",
#                             author="Pacifica Co-Op Nursery School")

    ## NOTE! if you define your own template, you have to set pagesize for each
    doc = SimpleDocTemplate(sys.stdout, pageSize=letter)
    story=[]

    t=Table(list(DBresults()), colWidths=2*inch, rowHeights=1.5*inch)
    t.setStyle(DEBUG_LAYOUT)
    story.append(t)



##### finally output stuff
    page.output_headers()
    doc.build(story)



#END
