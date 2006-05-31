#!/usr/bin/env python

# $Id$
## one of those one-off scripts i just love python for

import re

from sys import path
path.append('/mnt/kens/ki/is/python/lib')
path.append('/mnt/kens/ki/proj/coop/scripts')
path.append('/mnt/kens/ki/proj/coop/web/coop')
path.append('/mnt/kens/ki/proj/coop/web/objects')


from model import *

packages = Packages.select()


for p in packages:
    m=re.match('.*?(\d+)', p.packageNumber)
    if m != None:
        print 'changing %s to %d' % (p.packageNumber, int(m.group(1)))
        p.packageNumber = int(m.group(1))

