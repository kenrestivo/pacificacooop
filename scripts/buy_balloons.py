#!/usr/bin/env python


from sys import path
path.append('/mnt/kens/ki/is/python/lib')
path.append('/mnt/kens/ki/proj/coop/scripts')
path.append('/mnt/kens/ki/proj/coop/web')
path.append('/mnt/kens/ki/proj/coop/web/objects')


from model import *


## i love python. one-off script to manually "buy" all of the balloons
## since this year we have no idea who bought which one exactly


packages = Packages.select(AND(Packages.q.packageTypeID == 4,
                               Packages.q.schoolYear == '2005-2006'))


for p in packages:
    print "adding purchase for <%s> in amount of <%0.02f>..." % (
        p.packageTitle, p.packageValue)
    pur=AuctionPurchases(packageID = p.packageID,
                         incomeID=717,
                         springfestAttendeeID = 792,
                         packageSalePrice = p.packageValue)

