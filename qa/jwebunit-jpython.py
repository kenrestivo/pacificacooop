
from pprint import pprint

def pd(thing):
    pprint(dir(thing))


### the lib setups

import sys

jwebpath='/usr/local/share/jwebunit/'
# jython doesn't like me.
libs=['lib/js.jar', 'jwebunit.jar', 'lib/httpunit.jar', 'lib/nekohtml.jar', 'lib/xercesImpl.jar', 'lib/junit.jar', 'lib/xml-apis.jar']
sys.path.extend([jwebpath+x for x in libs])

from net.sourceforge.jwebunit import WebTestCase

## let's do it now!
wtc=WebTestCase()
tc=wtc.getTestContext()
tc.setBaseUrl('http://www/coop-dev')

def choose_family(family):
    wtc.beginAt('/')
    ##TODO add asserts that this is the RIGHT page
    wtc.assertTextPresent('</html>')
    wtc.assertFormPresent()
    wtc.assertSubmitButtonPresent('login')
    wtc.selectOption('auth[uid]', family)
    wtc.assertSubmitButtonPresent('login')
    wtc.submit('login')


def enter_password():
    wtc.assertTextPresent('</html>')
    wtc.assertFormPresent()
    wtc.assertSubmitButtonPresent('login')
    wtc.assertFormElementPresent('auth[pwd]')
    wtc.setFormElement('auth[pwd]', 'tester')
    wtc.submit('login')


def main_page_ok():
    wtc.assertTextPresent "</html>")
    wtc.assertLinkPresentWithText "Log Out")
    ## shirley has no enter wtc.assertLinkPresentWithText("Enter New")
    wtc.assertLinkPresentWithText('View')


def dump_page():
    wtc.getDialog().getResponseText()


def get_response():
    return wtc.getDialog().getResponse()


## go as far as you can, so far.
def get_to_main_page(family):
    choose_family(family)
    enter_password()
    main_page_ok()




## grab misc-shit from httpunit-jscheme

