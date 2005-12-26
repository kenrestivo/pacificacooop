#$Id$

import cgi
import cgitb
cgitb.enable()

#cgitb.enable(display=0, logdir="/tmp")
#os.chdir('/mnt/kens/ki/proj/giftfair/web')

#settings which do not transfer to cvs

#execfile('/mnt/kens/ki/proj/giftfair/web/list.py', {'__file__': '/mnt/kens/ki/proj/giftfair/web/list.py'})

#execfile('/f1/content/coastsidegift/htdocs/list.py', {'__file__': '/f1/content/coastsidegift/htdocs/list.py'})                                                       

##TODO: check dev!
connectionurl=     'mysql://input:test@bc:3306/coop'

# need this everywhere
sitepackages = '/mnt/kens/ki/proj/coop/web/site-packages'


