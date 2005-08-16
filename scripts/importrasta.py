#!/usr/bin/python -u

#$Id$

import os
import csv

f=open("/mnt/kens/ki/proj/coop/imports/AMRoste05-06.csv" , "r")
r=csv.reader(f,dialect='excel')

#ooh, i like python
dict(zip(keys,[i.strip() for i in r.next()]))



##wow cool!
#[p.split() for p in l[1:3]]
