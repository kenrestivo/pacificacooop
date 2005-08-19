#!/usr/bin/python -u

#$Id$

"""Imports the rasta in the old Excel/CSV format, and updates
the databse with its spiffy new contents"""


import os
import csv
#import MySQLdb
#mdb=MySQLdb                             # loathe CapNames

l=[]                                    # the completed am/pm minimarket

## do i *really* need an object here? or is encapsualtion in import ok?
## ah, ok. one rastaimport for each of am/pm
class RastaImport:
    r=None
    f=None
    session=''



    def __init__ (self, filename, session):
        """Loads the file, basically. 
            loop through looking for the keys and the beginning of the rasta"""
        fname=filename
        self.session=session

        self.f=open(fname, "r")
        self.r=csv.reader(self.f,dialect='excel')
        print "Loaded file."


    def get(self):
        self.f.seek(0)                               # just ot be sure
        while map(self._cleanInput, self.r.next()).count('') < 10 :
            print "Skipping header line..."

        ##skipping blanks
        ##TODO: it'd be nice to make this a one-liner to find the first
        ##non-blank line and make it the key.
        while True:
            print "Skipping BLANK line..."
            l=map(self._cleanInput, self.r.next())
            if l.count('') < 10:
                break

        #YAY! got the keys
        keys=l

        l=[dict(zip(keys,map(self._cleanInput, x))) for x in self.r]
        for x in l:
            x.update({'session': self.session}) # ahck. side-effect
            mom=x.get('Mom Name').split()
            dad=x.get('Dad/Partner').split()
            x.update({'mom_first': str.join(" ", mom[0:-1])})
            x.update({'mom_last': mom[-1]})
            x.update({'dad_first': str.join(" ", dad[0:-1])})
            x.update({'dad_last': dad[-1]})
        return l


    def _cleanInput(self,x):
        """Utility method to clean up input record"""
        return x.replace('*','').strip()



###### MAIN
if __name__ == '__main__':
    #TODO: use argv. this is sillie
    AM=RastaImport("/mnt/kens/ki/proj/coop/imports/AMRoster05-06.csv", 'AM')
    PM=RastaImport("/mnt/kens/ki/proj/coop/imports/PMRoster05-06.csv", 'PM')
    l.extend(AM.get())
    l.extend(PM.get())

##wow cool! though don't need now with dicts!
#[p.split() for p in l[1:3]]

#moms=[i.get('Mom Name') for i in l]
#[str.join(" ", m.split()[0:-1]) for m in moms]



#db open
#conn=mdb.connect(user='input', passwd='test', db='coop', 
#           host='bc', cursorclass=mdb.cursors.DictCursor) 
#c=conn.cursor()

#l.update({'session':session}) 
