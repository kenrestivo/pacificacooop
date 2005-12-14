#!/usr/bin/env python2.4
#$Id$


import sys
sys.path.insert(0, '/mnt/www/restivo/lib')

from phpserialize.PHPSerialize import PHPSerialize
from phpserialize.PHPUnserialize import PHPUnserialize



## go get it
sys.path.insert(0, '/mnt/kens/ki/proj/coop/web/objects')
sys.path.insert(0, '/mnt/kens/ki/proj/coop/web')
from  model import *

as=SessionInfo.get(434)

inpython=PHPUnserialize().unserialize(as.vars)
