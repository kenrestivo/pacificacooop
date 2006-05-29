#!/usr/bin/python


import csv

from sys import path
path.append('/mnt/kens/ki/is/python/lib')
path.append('/mnt/kens/ki/proj/coop/scripts')
path.append('/mnt/kens/ki/proj/coop/web')
path.append('/mnt/kens/ki/proj/coop/web/objects')




civicrmfields=['families.family_id', 'families.name',
               'phone', 'address1', 'email', 'user_id']



from model import *


r=Families.select()
c=r[0]._connection.getConnection().cursor()



c.execute('select %s from families left join users using (family_id)' % (','.join(civicrmfields)))


##TODO: parse out the ",pacifica 94044" shit

f=open('/mnt/kens/ki/proj/coop/imports/families-export.txt', 'w')
w=csv.writer(f)
w.writerow(civicrmfields)
for rec in c:
    w.writerow(rec)



f.close



##[[getattr(rec, i) for i in civicrmfields] for rec in r]

