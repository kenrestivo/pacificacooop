#!/usr/bin/python


import csv

from sys import path
path.append('/mnt/kens/ki/is/python/lib')
path.append('/mnt/kens/ki/proj/coop/scripts')
path.append('/mnt/kens/ki/proj/coop/web/coop')
path.append('/mnt/kens/ki/proj/coop/web/objects')




civicrmfields=['families.family_id', 'users.name',
               'phone', 'address1', 'email', 'user_id']

drupalfields=['families.family_id', 'users.name', 'email',
              'user_id', 'password']


from model import *


r=Families.select()
c=r[0]._connection.getConnection().cursor()





##TODO: parse out the ",pacifica 94044" shit

c.execute('select %s from families left join users using (family_id)' % (','.join(civicrmfields)))

f=open('/mnt/kens/ki/proj/coop/imports/families-export.csv', 'w')

w=csv.writer(f)

w.writerow(civicrmfields)

for rec in c:
    w.writerow(rec)

f.close()


#### REMEMBER TO EXPORT USING THE LIVE SITE!!!!!
### pretty useless to have "tester" as the password, you know
c.execute('select %s from families left join users using (family_id) where email is not null' % (','.join(drupalfields)))

f=open('/mnt/kens/ki/proj/coop/imports/users-export.csv', 'w')

w=csv.writer(f)

w.writerow(drupalfields)

for rec in c:
    w.writerow(rec)

f.close()



##[[getattr(rec, i) for i in civicrmfields] for rec in r]


