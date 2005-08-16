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
    session=''

    def __init__ (self, filename, session):
        self.fname=filename
        self.session=session
        self.setup()
        self.getKeys()

    def setup(self):
        """Loads the file, basically"""
        #file open
        self.f=open(self.fname, "r")
        self.r=csv.reader(self.f,dialect='excel')
        print "Loaded file."

        #db open
        #conn=mdb.connect(user='input', passwd='test', db='coop', 
        #           host='bc', cursorclass=mdb.cursors.DictCursor) 
        #c=conn.cursor()

    def _cleanInput(self,x):
        """Utility method to clean up input"""
        return x.replace('*','').strip()


    def getKeys(self):
         """loop through looking for the keys and the beginning of the rasta"""
         self.f.seek(0)                               # just ot be sure
         while map(self._cleanInput, self.r.next()).count('') < 10 :
                 print "Skipping header line..."

         ##skipping blanks
         while True:
                 print "Skipping BLANK line..."
                 l=map(self._cleanInput, self.r.next())
                 if l.count('') < 10:
                         break

         #YAY! got the keys
         self.keys=l

    def loadLine(self):
        """Iterator method, basically. Produces cleaned-up line"""
        #ooh, i like python
        l=dict(zip(self.keys,map(self._cleanInput, self.r.next())))
    	l.update({'session':self.session})
        return l

###### MAIN
if __name__ == '__main__':
    AM=RastaImport("/mnt/kens/ki/proj/coop/imports/AMRoste05-06.csv", 'AM')
    PM=RastaImport("/mnt/kens/ki/proj/coop/imports/PMRoster05-06.csv", 'PM')
	#AM.loadLine()



##wow cool! though don't need now with dicts!
#[p.split() for p in l[1:3]]
