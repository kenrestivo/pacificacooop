#!/usr/bin/env python2.4
#$Id$


import sys
sys.path.insert(0, '/mnt/www/restivo/lib')

from phpserialize.PHPSerialize import PHPSerialize
from phpserialize.PHPUnserialize import PHPUnserialize



## go get it
import model

as=SessionInfo.get(434)

inpython=PHPUnserialize().unserialize(as.vars)
