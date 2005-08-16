#!/usr/bin/python -u

#$Id$

import os
import csv

f=open("/mnt/kens/ki/proj/coop/imports/AMRoste05-06.csv" , "r")
r=csv.reader(f,dialect='excel')


l=r.next()
[i.strip() for i in l]
