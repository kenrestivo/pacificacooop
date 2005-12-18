

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
page= wc.getPage(URL('http://www/coop-dev'))

page.getTitleText()
#assertEquals("htmlunit - Welcome to HtmlUnit", page.getTitleText() )

def usableSelect(sel, val):
    for i in sel.getOptions():
        if i.asText() == val:
            i.setSelected(1)
            return
    #throw Exception(val + ' is not present in selectbox!')

##login form
f=page.getForms()[0]
f.getInputByName('auth[pwd]').setValueAttribute('tester')
mainpage=f.getInputByName('login').click()


[i for i in mainpage.getAllHtmlChildElements() ]
