#$Id$


"""to load in jython:
import sys
sys.path.append('/mnt/kens/ki/proj/coop/qa')
import newunittests
newunittests.main()
#or
run=newunittests.Runner()
run.ManyVisitHack('http://www/coop-dev/')
"""


htmlunitdir = '/usr/local/share/htmlunit'

import os,sys
for i in os.listdir(htmlunitdir):
    sys.path.append('/'.join([htmlunitdir, i]))


#python imports
from random import random
from math import floor
import struct
from shutil import copyfile

#java imports
import com.gargoylesoftware.htmlunit 
import org.apache.xerces
import org.xml
from java.net import URL
import java.lang, java.io
from org.apache.commons.httpclient import *
from org.apache.commons.httpclient.methods.multipart import *

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

def allTags(d, type):
    l=[]
    nl=d.getElementsByTagName(type)
    nl.getLength()
    for i in range(0, nl.getLength()):
        l.append(nl.item(i))
    return l


def tagInfo(d, tagname):
    for i in allTags(d, tagname):
        print '   id: %s' % (i.getAttribute('id'))
        print '   class: %s' % (i.getAttribute('class'))
        print '   text: "%s"'  % (i.getTextContent())



class simpleErrorHandler(org.xml.sax.ErrorHandler):
     """ just prints the data as provided by xerces. nothin fancy"""
     wc = None
     def __init__(self, ct):
         self.ct = ct
     def error(self, ex):
         self._printError('error', ex)
        #self.ct.dumpHTML()
        #raise Exception('validation error')
     def warning(self, ex):
         self._printError('warning', ex)
     def fatalError(self, ex):
         self._printError('FATAL', ex)
     def _printError(self,type, ex):
         er= '%s on line %d col %d %s:%s: %s' % (type,  ex.getLineNumber(), ex.getColumnNumber(), ex.getSystemId(), ex.getPublicId(), ex.getMessage())
         print er
         self.ct.logError(er)




class CoopTest:
    """test the website. page is the current page. mainpage is homepage."""
    wc = None
    page= None 
    parser = None
    mainlinks=[]
    url= ""
    validator_url = 'http://localhost/w3c-markup-validator/check'
    username= ""
    loggedin = 0
    logfp = None
    errnum = 0
    
    def __init__(self, url, username, validator_url=None, logfp="", errnum=0):
        self.url = url
        self.username = username
        self.logfp = logfp
        self.errnum=errnum
        if validator_url:
            self.validator_url = validator_url


    def setUp(self):
        """this is redundant to __init__, but i'm too scared to change it"""
        self.wc = htmlunit.WebClient(htmlunit.BrowserVersion.MOZILLA_1_0, )
        self.wc.setRedirectEnabled(1)
        self.wc.setTimeout(60000)
        self.parser=org.apache.xerces.parsers.DOMParser()
        self.parser.setErrorHandler(simpleErrorHandler(self))


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
            self.page= self.retryClick(f.getInputByName('login'))
        except KeyError:
            print 'Already logged in?'


    def visitSubPages(self):
        """iterate through all the links on themainpage, and swap page"""
        assert(len(self.mainlinks) > 0)
        for i in self.mainlinks:
            self.page = self.retryClick(i)
            self.pageLoaded()
            self.tryOperationsOnPage()


    def pageLoaded(self):
        print 'Checking load of [%s] ...' % (self.getURL(), )
        self.dumpHTML()
        assert(1 == self.page.getWebResponse().getContentAsString().count('</html>'))
        if self.validator_url:
            self.validateMarkup()


            
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
            self.page = self.retryClick(links[index])
            self.pageLoaded()


    def getURL(self):
        """gets the url of the current page's web response
        whatever the hell that means"""
        return '%s' % (self.page.getWebResponse().getUrl())


    def dumpHTML(self):
        #print self.page.getWebResponse().getContentAsString()        
        self.saveStream('tmp.html', self.page.getWebResponse().getContentAsStream())


    def retryClick(self, obj):
        while 1:
            try:
                res = obj.click()
            except org.apache.commons.httpclient.NoHttpResponseException:
                self.logError('no response! retrying')
                continue
            break
        return res


    def saveStream(self, outfile, ins):
        #java sucks, it has a binary mode
        outs=open(outfile, 'wb')
        c=0
        try:
            ins.reset()
        except java.io.IOException:
            pass
        while c != -1:
            c=ins.read()
            if c < 0:
                break
            outs.write(struct.pack("<B", c))
        try:
            ins.reset()
        except java.io.IOException:
            pass
        outs.close()
        ins.close()


    def logError(self, er):
        self.errnum = self.errnum + 1
        self.logfp.write('%d %s [%s] %s\n' % (self.errnum, self.username, self.getURL(), er))
        self.logfp.flush()


    def saveDumpFile(self):
        copyfile('tmp.html', '%d-death.html' % (self.errnum))


    def validateMarkup(self):
        """very simple, straightforward dom parsing. reject bad html"""
        print 'Validating markup...'
        gm =  self.postMultipartFile()
        try:
            self.parser.parse(org.xml.sax.InputSource(open('w3ctmp.html', 'rb')))
        except org.xml.sax.SAXParseException:
            pass
        result_doc = self.parser.getDocument()
        if [i.getAttribute('class') for i in allTags(result_doc, 'h2')].count('valid') < 1:
            self.validationError()


    def validationError(self):
        print 'VALIDATION ERROR'
        self.logError('validation error')
        self.saveDumpFile()
        self.logfp.write('%d %s [%s]\n' % (self.errnum, self.username, self.getURL()))
        copyfile('w3ctmp.html', '%d-w3c_report.html' %(self.errnum))


    def postMultipartFile(self):
        wr=self.page.getWebResponse()
        htc=HttpClient()
        gm=methods.MultipartPostMethod(self.validator_url)
        fps=FilePartSource(self.getURL(), java.io.File('tmp.html'))
        gm.addPart(FilePart('uploaded_file', fps, wr.getContentType(), wr.getContentCharSet()))
        gm.addParameter('ss', '1')
        gm.addParameter('sp', '1')
        retryhandler = DefaultMethodRetryHandler()
        retryhandler.setRequestSentRetryEnabled(0)
        retryhandler.setRetryCount(3)
        gm.setMethodRetryHandler(retryhandler)
        try:
            statusCode = htc.executeMethod(gm)
            if statusCode != HttpStatus.SC_OK:
                print "Failed to connect: " + gm.getStatusLine()
                raise Exception
            rs=gm.getResponseBodyAsStream()
            self.saveStream('w3ctmp.html', rs)
            return  gm
        except IOException(e):
            print "Failed to upload file."
            e.printStackTrace()
            gm.releaseConnection();
            


class Runner:
    errnum  = 0
    ct=None

    def ManyVisitHack(self,url, validate='http://localhost/w3c-markup-validator/check', logfile="tests.log"):
        """runs multiple families in one url"""
        usersToTest= ['Bartlett Family', 'Restivo Family', 'Cooke Family',
                      'Teacher Sandy', 'Shirley']
        fp=open(logfile, 'a')
        fp.write('================\n')
        for u in usersToTest:
            print 'Starting user %s (%s)...' % (u, url)
            self.ct = CoopTest(url, u, logfp=fp,  errnum=self.errnum)
            self.ct.run()
            self.errnum = self.ct.errnum
        print 'All tests succeeded! Yay!'
        fp.write('done\n')
        fp.close()
    
    
    def force_page(self, urlbase, urlmore, username, fp=None):
        """utility for validating one particular long url""" 
        ct=CoopTest(urlbase, username,  logfp=open('forcepagetest.log', 'w'))
        ct.getToMainPage()
        ct.page=ct.wc.getPage(URL('/'.join((urlbase,urlmore))))
        ct.pageLoaded()
        print 'page loaded successfully!'
    


##mainpage.getWebResponse().getUrl()
###### MAIN ######

def main():
    Runner().ManyVisitHack('http://www/coop-live')

    
## do this. it's good.
## TODO: getopt parsing, to take url and validation as args
if __name__ == '__main__':
    main()
