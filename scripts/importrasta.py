#!/usr/bin/python -u

#$Id$

"""Imports the rasta in the old Excel/CSV format, and updates
the databse with its spiffy new contents"""


import os
import csv
from datetime import date
import MySQLdb, MySQLdb.cursors


rasta=[]                                    # the completed am/pm minimarket

#TODO check these!
valid_keys = [
 'Mom Name',
 'Tu',
 'Th',
 'Phone',
 'Last Name',
 'Address',
 'M',
 'School Job',
 'DOB',
 'session',
 'F',
 'W',
 'Child',
 'Email',
 'Dad/Partner']



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
        #TODO: check that th keys are valid (validKeys), do any mapping needed
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

class TooManyFound(Exception):
    def __init__(self):
        print "too many found"

class NoneFound(Exception):
    def __init__(self):
        print 'None found...'

########end of exception classes

class Adder:
    """abstract base class for the various db objects"""
    rec={}
    c=None
    r=[]
    pk=""
    
    def __init__(self, c, rec):
        self.rec = rec
        self.c = c
        
    def wrapper(self):
        """abstract the process of choosing an existing record
        or adding a new one"""
        try:
            return self.get()
        except NoneFound:
            return self.add()
        except TooManyFound:
            return self.choose()

    def choose(self):
        """silly little chooser"""
        print '--- For this Line: ---'
        for i in self.rec.items(): print '%s: %s' %  i
        print "\n--------\nHere are the database results:\n"
        for i in self.r:
            print '-----------'
            for j in i.items(): print '%s: %s' % j
        valid=[x[self.pk] for x in self.r]
        n=input('Pick the %s above (%s): ' %
                (self.pk,
                ','.join([str(x) for x in valid])))
        if not int(n) in valid:
            print "no, that's not OK. try again"
            return self.choose()        # can i tail recurse? will it do it?
        else:
            return
        
	def _get(self, query):
      	self.pk='family_id'
        c.execute(query)
        self.r=c.fetchall()
        if c.rowcount < 1: raise NoneFound
        if c.rowcount > 1: raise TooManyFound
        return r[0][self.pk]

        
class Family(Adder):
    def get(self)
	    """a very cheap way to get the family."""
    	return self._get("""select * from families where phone like '%%%s%%'
        and name like '%%%s%%' """ % (self.rec['Phone'], self.rec['Last Name']))
    
    
        #TODO: handle the situation where the family last name is a duplicate!
    def add(self):
        """simple insert wrapper"""
        c.execute("""insert into families set name = %s, phone = %s,
                    address1 = %s, email = %s""",
                  (self.rec['Last Name'], self.rec['Phone'],
                   self.rec['Address'], self.rec['Email']))
        return c.lastrowid


#TODO: kid, enrollment, parent, get/add!


##########naked functions

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




#db open
conn=MySQLdb.connect(user='input', passwd='test', db='coop',
                 host='bc', cursorclass=MySQLdb.cursors.DictCursor) 
c=conn.cursor()

