#!/usr/bin/env python2.4
#$Id$


import sys
sys.path.insert(0, '/mnt/www/restivo/lib')

from phpserialize.PHPSerialize import PHPSerialize
from phpserialize.PHPUnserialize import PHPUnserialize



## go get it
sys.path.insert(0, '/mnt/kens/ki/proj/coop/web/objects')
sys.path.insert(0, '/mnt/kens/ki/proj/coop/web')
import model




as2=model.SessionInfo.selectBy(session_id = '827ea3347b547e97b3e51710af714812')


inpython=PHPUnserialize().session_decode(as.vars)
