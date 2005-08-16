#!/usr/bin/python -u

#$Id$

import os
import csv
import MySQLdb
mdb=MySQLdb                             # loathe CapNames


#file open
f=open("/mnt/kens/ki/proj/coop/imports/AMRoste05-06.csv" , "r")
r=csv.reader(f,dialect='excel')

#db open
conn=mdb.connect(user='input', passwd='test', db='coop', 
                host='bc', cursorclass=mdb.cursors.DictCursor)
c=conn.cursor()

def cleanInput(x):
     return x.replace('*','').strip()


##loop through looking for the keys and the beginning of the rasta
f.seek(0)                               # just ot be sure
while map(cleanInput, r.next()).count('') < 10 :
        print "Skipping header line..."

##skipping blanks
while True:
    l=map(cleanInput, r.next())
    if l.count('') < 10:
        break
    else:
        print "Skipping BLANK line..."



#YAY! got the keys
keys=l

#ooh, i like python
dict(zip(keys,[cleanInput(i) for i in r.next()]))



##wow cool!
#[p.split() for p in l[1:3]]
