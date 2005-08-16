#!/usr/bin/python -u

#$Id$

import os
import csv
#import MySQLdb
#mdb=MySQLdb                             # loathe CapNames



## do i *really* need an object here? or is encapsualtion in import ok?
## ah, ok. one rastaimport for each of am/pm
class RastaImport:
    keys=[]
    f=None
    r=None
    fname=''

    def __init__ (self, filename, type):
        self.fname=filename
        self.setup()
        self.getKeys()

    def setup(self):
        #file open
        self.f=open(self.fname, "r")
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

    def loadLine(self):
        #ooh, i like python
        return dict(zip(self.keys,map(self.cleanInput, self.r.next()))) 
        

###### MAIN
if __name__ == '__main__':
    R=RastaImport("AMRoste05-06.csv", 'AM')
    R.loadLine()


##wow cool! though don't need now with dicts!
#[p.split() for p in l[1:3]]
