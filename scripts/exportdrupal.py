#!/usr/bin/python


import csv

from sys import path
path.append('/mnt/kens/ki/is/python/lib')
path.append('/mnt/kens/ki/proj/coop/scripts')
path.append('/mnt/kens/ki/proj/coop/web')
path.append('/mnt/kens/ki/proj/coop/web/objects')




civicrmfields=['familyID', 'name', 'phone', 'address1', 'email']






from model import *



f=open('/tmp/foobar.txt', 'w')
w=csv.writer(f)



r=Families.select()

##TODO: parse out the ",pacifica 94044" shit

for rec in r:
    w.writerow([getattr(rec, i) for i in civicrmfields])



f.close



##[[getattr(rec, i) for i in civicrmfields] for rec in r]

