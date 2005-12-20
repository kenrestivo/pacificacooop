#$Id$


"""to load in jython:
import sys
sys.path.append('/mnt/kens/ki/proj/coop/qa')
import newunittests
newunittests.main()
"""


htmlunitdir = '/usr/local/share/htmlunit'

import os,sys
for i in os.listdir(htmlunitdir):
    sys.path.append('/'.join([htmlunitdir, i]))


#python imports
from random import random
from math import floor


#java imports
import com.gargoylesoftware.htmlunit 
import org.apache.xerces
import org.xml
from java.net import URL

#from doesn't seem to work in py 2.1
htmlunit = com.gargoylesoftware.htmlunit




## some utility funcs

def usableSelect(sel, val):
    for i in sel.getOptions():
        if i.asText() == val:
            i.setSelected(1)
            return
    #throw Exception(val + ' is not present in selectbox!')

#TODO: don't click on links to same page, go find out what this page is


class simpleErrorHandler(org.xml.sax.ErrorHandler):
    """ just prints the data as provided by xerces. nothin fancy"""
    def error(self, ex):
        self._printError('error', ex)
    def warning(self, ex):
        self._printError('warning', ex)
    def fatalError(self, ex):
        self._printError('FATAL', ex)
    def _printError(self,type, ex):
        print '%s on %s:%s col %d line %d: %s' % (type, ex.getSystemId(), ex.getPublicId(), ex.getColumnNumber(), ex.getLineNumber(), ex.getMessage())





class CoopTest:
    """test the website. page is the current page. mainpage is homepage."""
    wc = None
    page= None 
    parser = None
    mainlinks=[]
    url= ""
    username= ""
    loggedin = 0
    
    def __init__(self, url, username):
        self.url = url
        self.username = username
    
    def setUp(self):
        """this is redundant to __init__, but i'm too scared to change it"""
        self.wc = htmlunit.WebClient(htmlunit.BrowserVersion.MOZILLA_1_0, )
        self.wc.setRedirectEnabled(1)
        self.parser=org.apache.xerces.parsers.DOMParser()
        self.parser.setFeature("http://xml.org/sax/features/validation", 1)
        self.parser.setErrorHandler(simpleErrorHandler())


    def run(self):
        """let's go"""
        self.getToMainPage()
        self.visitSubPages()


    def getToMainPage(self):
        self.setUp()
        self.getLoginPage()
        self.logIn()
        self.mainlinks=self.getAllLinks()
        

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
            self.page=f.getInputByName('login').click()
        except KeyError:
            print 'Already logged in?'


    def visitSubPages(self):
        """iterate through all the links on themainpage, and swap page"""
        assert(len(self.mainlinks) > 0)
        for i in self.mainlinks:
            self.page = i.click()
            self.pageLoaded()
            self.tryOperationsOnPage()


    def pageLoaded(self):
        print 'Checking load of [%s] ...' % (self.getURL(), )
        assert(1 == self.page.getWebResponse().getContentAsString().count('</html>'))
        self.validate()

            
    def getAllLinks(self):
        """htmlunit has no such function as getalllinks. so, here it is"""
        return [i for i in self.page.getAllHtmlChildElements() if i.getTagName() == 'a' and i.getHrefAttribute().count('@') < 1 and i.getHrefAttribute().count('index.php') < 1]


    def specialAudit(self):
        """little utility to check for javascript ugliness in audit page"""
        aud=[a for a in self.getAllLinks() if a.getHrefAttribute().count('audit')][0]
        audpage=aud.click()


    def tryOperationsOnPage(self):
        print 'Diving down into links on [%s]...' % (self.getURL(),)
        operations= ['Edit', 'Enter New', 'Delete', 'Details']
        for i in operations:
            self.pickRandomLink(i)

    def pickRandomLink(self, kind):
        links=[i for i in self.getAllLinks() if i.asText() == kind]
        index = int(floor(len(links) * random()))
        if len(links) > 0:
            print 'From %s trying %d of %d %s links...' % (self.getURL(),
                                                           index, len(links),
                                                           kind)
            self.page = links[index].click()
            self.pageLoaded()


    def getURL(self):
        """gets the url of the current page's web response
        whatever the hell that means"""
        return self.page.getWebResponse().getUrl()


    def validate(self):
        """very simple, straightforward dom parsing. reject bad html"""
        self.parser.parse(org.xml.sax.InputSource(self.page.getWebResponse().getContentAsStream()))
        d=self.parser.getDocument()
        return d

        
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
    print 'All tests succeeded! Yay!'

    
## do this. it's good.
if __name__ == '__main__':
    main()
