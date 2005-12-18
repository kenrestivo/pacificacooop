

#$Id$

htmlunitdir = '/usr/local/share/htmlunit'

import os,sys
for i in os.listdir(htmlunitdir):
    sys.path.append('/'.join([htmlunitdir, i]))


import com.gargoylesoftware.htmlunit 

#from doesn't seem to work in py 2.1
htmlunit = com.gargoylesoftware.htmlunit

from java.net import URL


wc = htmlunit.WebClient(htmlunit.BrowserVersion.MOZILLA_1_0, )
page= wc.getPage(URL('http://htmlunit.sourceforge.net'))
page.getTitleText()

#assertEquals("htmlunit - Welcome to HtmlUnit", page.getTitleText() )

