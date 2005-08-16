#!/usr/bin/python -u

#$Id$

import os
import csv
import MySQLdb
mdb=MySQLdb                             # loathe CapNames



## do i *really* need an object here? or is encapsualtion in import ok?
## ah, ok. one rastaimport for each of am/pm
class RastaImport:
    keys=[]
    f=None
    r=None

    def setup(self):
        #file open
        self.f=open("/mnt/kens/ki/proj/coop/imports/AMRoste05-06.csv" , "r")
        self.r=csv.reader(self.f,dialect='excel')
        print "Loaded file."

        #db open
        #conn=mdb.connect(user='input', passwd='test', db='coop', 
        #           host='bc', cursorclass=mdb.cursors.DictCursor) 
        #c=conn.cursor()

    def cleanInput(self,x):
        return x.replace('*','').strip()


    def getKeys(self):
         """loop through looking for the keys and the beginning of the rasta"""
         self.f.seek(0)                               # just ot be sure
         while map(self.cleanInput, self.r.next()).count('') < 10 :
                 print "Skipping header line..."

         ##skipping blanks
         while True:
                 print "Skipping BLANK line..."
                 l=map(self.cleanInput, self.r.next())
                 if l.count('') < 10:
                         break

         #YAY! got the keys
         self.keys=l


###### MAIN
if __name__ == '__main__':
    R=RastaImport()
    R.setup()
    R.getKeys()
    #ooh, i like python
    dict(zip(R.keys,map(R.cleanInput, R.r.next())))


##wow cool! though don't need now with dicts!
#[p.split() for p in l[1:3]]
