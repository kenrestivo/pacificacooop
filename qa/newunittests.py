

#$Id$

htmlunitdir = '/usr/scratch/htmlunit-1.7/lib/'

import os,sys
for i in os.listdir(htmlunitdir):
    sys.path.append('/'.join([htmlunitdir, i]))


import com.gargoylesoftware.htmlunit 

#from doesn't seem to work in py 2.1
htmlunit = com.gargoylesoftware.htmlunit

from java.net import URL

## batteries NOT INCLUDED!
sys.path.append('/usr/scratch/commons-httpclient-3.0-rc4/commons-httpclient-3.0-rc4.jar')


wc = htmlunit.WebClient(htmlunit.BrowserVersion.MOZILLA_1_0, )
url = URL('http://htmlunit.sourceforge.net')
page= wc.getPage(url)
page.getTitleText()

#assertEquals("htmlunit - Welcome to HtmlUnit", page.getTitleText() )

