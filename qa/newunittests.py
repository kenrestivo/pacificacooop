

#$Id$

htmlunitdir = '/usr/local/share/htmlunit'

import os,sys
for i in os.listdir(htmlunitdir):
    sys.path.append('/'.join([htmlunitdir, i]))


import com.gargoylesoftware.htmlunit 

#from doesn't seem to work in py 2.1
htmlunit = com.gargoylesoftware.htmlunit

from java.net import URL
import unittest


## some utility funcs

def usableSelect(sel, val):
    for i in sel.getOptions():
        if i.asText() == val:
            i.setSelected(1)
            return
    #throw Exception(val + ' is not present in selectbox!')

#TODO: don't click on links to same page, go find out what this page is


class Test_Coop(unittest.TestCase):
    """test the website. page is the current page. mainpage is homepage."""
    wc = None
    page= None 
    mainpage= None
    url= 'http://www/coop-dev'))
    
    def setUp(self):
        self.wc = htmlunit.WebClient(htmlunit.BrowserVersion.MOZILLA_1_0, )

    def testGetLoginPage(self):
        self.page= self.wc.getPage(URL(self.url))
        pageLoaded()

    def testLogIn(self):
        """ i CHECK FIRST!! i might already be logged in"""
        try:
            f=self.page.getForms()[0]
            f.getInputByName('auth[pwd]').setValueAttribute('tester')
            usableSelect(f.getSelectByName('auth[uid]'), 'Cooke Family')
            self.mainpage=f.getInputByName('login').click()
        except KeyError:
            self.mainpage=self.page


    def testSubPages(self):
        for i in self.getAllLinks():
            i.click()
            pageLoaded()



    def pageLoaded(self):
            self.assertEqual(1, self.page.getWebResponse().getContentAsString().count('</html>'))

            
    def getAllLinks(self):
        """htmlunit has no such function as getalllinks. so, here it is"""
        return [i for i in self.page.getAllHtmlChildElements() if i.getTagName() == 'a']


    def specialAudit(self):
        """little utility to check for javascript ugliness in audit page"""
        aud=[a for a in self.getAllLinks() if a.getHrefAttribute().count('audit')][0]
        audpage=aud.click()




##mainpage.getWebResponse().getUrl()
###### MAIN ######


## do this. it's good.
if __name__ == '__main__':
    unittest.TextTestRunner(verbosity=2).run(unittest.makeSuite(Test_Coop))
