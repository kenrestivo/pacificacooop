#!/usr/bin/python -u

#$Id$

"""Imports the rasta in the old Excel/CSV format, and updates
the databse with its spiffy new contents"""


import os
import csv
from datetime import date
import MySQLdb
mdb=MySQLdb  # loathe CapNames

rasta=[]                                    # the completed am/pm minimarket

## do i *really* need an object here? or is encapsualtion in import ok?
## ah, ok. one rastaimport for each of am/pm
class RastaImport:
    r=None
    f=None
    session=''



    def __init__ (self, filename, session):
        """Loads the file, basically. 
            Loop through looking for the keys and the beginning of the rasta"""
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


        #the massive filter
        #i use get here because y['thing'] throws an exeption if not there
        l=[y for y in [dict(zip(keys,map(self._cleanInput, x))) for x in self.r]
           if y.get('Mom Name') and y.get('Dad/Partner')]

        #super butt-ugly with cheese
        for x in l:
            x['session'] = self.session 
            x['DOB'] =self._dateFix(x['DOB'])
            mom=x['Mom Name'].split()
            dad=x['Dad/Partner'].split()
            x['mom_first'] = str.join(" ", mom[0:-1])
            x['mom_last'] = mom[-1]
            x['dad_first'] = str.join(" ", dad[0:-1])
            x['dad_last'] = dad[-1]
        return l


    def _cleanInput(self,x):
        """Utility method to clean up input record"""
        return x.replace('*','').strip()

    def _dateFix(self, d):
        """Deal with Excel dates, which sometimes come up unformatted
        as integers"""
        if d.count('/') > 1:
            return d
        else:
            return date.fromordinal(
                int(d)+
                date.toordinal(date(1900,1,1))).strftime("%m/%d/%y")

####END of rastaimport class

def load(am_file, pm_file):
    """Takes AM, PM files, builds objects for them, and loads them"""
    AM=RastaImport(am_file, 'AM')
    PM=RastaImport(pm_file, 'PM')
    rasta.extend(AM.get())
    rasta.extend(PM.get())





###### MAIN
if __name__ == '__main__':
    #TODO: use argv. this is sillie
    load("/mnt/kens/ki/proj/coop/imports/AMRoster05-06.csv", 
            "/mnt/kens/ki/proj/coop/imports/PMRoster05-06.csv")


def getFamilyID(rec):
    c.execute("""select * from families where phone like '%%%s%%'
    and name like '%%%s%%' """ % (rec['Phone'], rec['Last Name']))
    r=c.fetchall()
    if c.rowcount < 1: raise NoneFound
    if c.rowcount > 1: raise TooManyFound
    return r[0]['family_id']


#db open
conn=mdb.connect(user='input', passwd='test', db='coop',
                 host='localhost', cursorclass=mdb.cursors.DictCursor) 
c=conn.cursor()

#c.execute('select * from kids where phone like "%%%s%%" and name like "%%%s%%" ' %  (importrasta.rasta[0]['Phone'], importrasta.rasta[0]['Last Name']))
