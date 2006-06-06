#!/usr/bin/env python2.4
#$Id$


import sys
sys.path.insert(0, '/mnt/kens/ki/proj/coop/web/lib')

from phpserialize.PHPSerialize import PHPSerialize
from phpserialize.PHPUnserialize import PHPUnserialize

phpun=PHPUnserialize()
phpize=PHPSerialize()


## go get it
sys.path.insert(0, '/mnt/kens/ki/proj/coop/web/objects')
sys.path.insert(0, '/mnt/kens/ki/proj/coop/web')
import model



as=model.SessionInfo.get('827ea3347b547e97b3e51710af714812')


inpython=phpun.session_decode(as.vars)

print inpython['auth']
print inpython['cpVars']['stack']
