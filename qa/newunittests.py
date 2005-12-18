

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

#TODO: don't click on links to same page, go find out what this page is
def getAllLinks(page):
    return [i for i in page.getAllHtmlChildElements() if i.getTagName() == 'a']


##login form
f=page.getForms()[0]
f.getInputByName('auth[pwd]').setValueAttribute('tester')
usableSelect(f.getSelectByName('auth[uid]'), 'Cooke Family')
mainpage=f.getInputByName('login').click()



## my special audit thing
[a for a in getAllLinks(mainpage) if a.getHrefAttribute().count('audit')][0].click()

