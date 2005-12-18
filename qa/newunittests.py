

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


##login form
f=page.getForms()[0]
f.getSelectByName('auth[uid]')
for i in s.getOptions():
    if i.asText() == 'Cooke Family':
            i.setSelected(1)
f.getInputByName('auth[pwd]').setValueAttribute('tester')
mainpage=f.getInputByName('login').click()



