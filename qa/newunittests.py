#$Id$


##to load in jython:
## import sys
## sys.path.append('/mnt/kens/ki/proj/coop/qa')
## import newunittests
## newunittests.main()


htmlunitdir = '/usr/local/share/htmlunit'

import os,sys
for i in os.listdir(htmlunitdir):
    sys.path.append('/'.join([htmlunitdir, i]))


import com.gargoylesoftware.htmlunit 

#from doesn't seem to work in py 2.1
htmlunit = com.gargoylesoftware.htmlunit

from java.net import URL


## some utility funcs

def usableSelect(sel, val):
    for i in sel.getOptions():
        if i.asText() == val:
            i.setSelected(1)
            return
    #throw Exception(val + ' is not present in selectbox!')

#TODO: don't click on links to same page, go find out what this page is


class CoopTest:
    """test the website. page is the current page. mainpage is homepage."""
    wc = None
    page= None 
    mainpage= None
    url= ""
    username= ""

    def __init__(self, url, username):
        self.url = url
        self.username = username
    
    def setUp(self):
        self.wc = htmlunit.WebClient(htmlunit.BrowserVersion.MOZILLA_1_0, )
        self.wc.setRedirectEnabled(1)

    def run(self):
        """let's go"""
        self.getToMainPage()
        self.visitSubPages()


    def getToMainPage(self):
        self.setUp()
        self.getLoginPage()
        self.logIn()


    def getLoginPage(self):    
        """get to the first URL. make sure we have at least that"""
        self.page= self.wc.getPage(URL(self.url))
        self.pageLoaded()
        assert(1 == len(self.page.getForms()))
        self.logIn()


    def logIn(self):
        """check that i might already be logged in, and log in if not"""
        try:
            f=self.page.getForms()[0]
            f.getInputByName('auth[pwd]').setValueAttribute('tester')
            usableSelect(f.getSelectByName('auth[uid]'), self.username)
            self.mainpage=f.getInputByName('login').click()
        except KeyError:
            self.mainpage=self.page


    def visitSubPages(self):
        """iterate through all the links on themainpage, and swap page"""
        for i in self.getAllLinks():
            self.page = i.click()
            self.pageLoaded()



    def pageLoaded(self):
        print 'Checking load of [%s] ...' % (self.page.getWebResponse().getUrl(), )
        assert(1 == self.page.getWebResponse().getContentAsString().count('</html>'))

            
    def getAllLinks(self):
        """htmlunit has no such function as getalllinks. so, here it is"""
        return [i for i in self.mainpage.getAllHtmlChildElements() if i.getTagName() == 'a' and i.getHrefAttribute().count('@') < 1]


    def specialAudit(self):
        """little utility to check for javascript ugliness in audit page"""
        aud=[a for a in self.getAllLinks() if a.getHrefAttribute().count('audit')][0]
        audpage=aud.click()



##mainpage.getWebResponse().getUrl()
###### MAIN ######


def ManyVisitHack(url):
    """runs multiple families in one url"""
    usersToTest= ['Bartlett Family', 'Restivo Family', 'Cooke Family',
                  'Teacher Sandy', 'Shirley']
    for u in usersToTest:
        print 'Starting user %s (%s)...' % (u, url)
        CoopTest(url, u).run()
    

def main():
    ManyVisitHack('http://www/coop-dev')


    
## do this. it's good.
if __name__ == '__main__':
    main()
