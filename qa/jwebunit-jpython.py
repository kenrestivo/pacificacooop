
from pprint import pprint

def pd(thing):
    pprint(dir(thing))


### the lib setups

import sys

jwebpath='/usr/local/share/jwebunit/'
# jython doesn't like me.
libs=['jwebunit.jar', 'lib/httpunit.jar', 'lib/nekohtml.jar', 'lib/xercesImpl.jar', 'lib/junit.jar', 'lib/xml-apis.jar', 'lib/js.jar']
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

