#!/usr/bin/python -u

#$Id$

"""Imports the rasta in the old Excel/CSV format, and updates
the databse with its spiffy new contents"""


import os
import csv
from datetime import date
import MySQLdb, MySQLdb.cursors
import datetime


rasta=[]                                    # the completed am/pm minimarket

required_keys = [
 'Mom Name',
 'Tu',
 'Th',
 'Phone',
 'Last Name',
 'Address',
 'M',
 'School Job',
 'DOB',
 'F',
 'W',
 'Child',
 'Email',
 'Dad/Partner']

#TODO check these!
valid_keys = required_keys + ['session']


school_year = '2005-2006'
first_day_of_school = '2005-09-12'
blank_count = 10

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
        while map(self._cleanInput, self.r.next()).count('') < blank_count :
            print "Skipping header line..."

        ##skipping blanks
        ##TODO: it'd be nice to make this a one-liner to find the first
        ##non-blank line and make it the key.
        while True:
            print "Skipping BLANK line..."
            l=map(self._cleanInput, self.r.next())
            if l.count('') < blank_count:
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
        pass


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
        pid=0
        update=1
        try:
            pid = self.get()
        except NoneFound:
            pid = self.add()
            update=0
        except TooManyFound:
            pid = self.choose()
        update and self.update(pid)
        return pid

    def _get(self, query):
        """utility used by get() methods of subclasses"""
        c.execute(query)
        self.r=c.fetchall()
        if c.rowcount < 1: raise NoneFound
        if c.rowcount > 1: raise TooManyFound
        return self.r[0][self.pk]

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
        if int(n) in valid:
            return n
        print "no, that's not OK. try again"
        return self.choose()        # can i tail recurse? will it do it?

    def update(self,pid):
        """Base virtual method"""
        pass


class Family(Adder):
    def get(self):
        """a very cheap way to get the family."""
        self.pk='family_id'
        return self._get("""select * from families where phone like '%%%s%%'
        and name like '%%%s%%' """ % (self.rec['Phone'], self.rec['Last Name']))


        #TODO: handle the situation where the family last name is a duplicate!
    def add(self):
        """simple insert wrapper"""
        print "inserting new family %s" % (self.rec['Last Name'])
        c.execute("""insert into families set name = %s, phone = %s,
                    address1 = %s, email = %s""",
                  (self.rec['Last Name'], self.rec['Phone'],
                   self.rec['Address'], self.rec['Email']))
        return c.lastrowid

    def update(self, pid):
        """keepin' it real, y'know what i'm sayin'?"""
        new=[self.rec['Phone'], self.rec['Address'], self.rec['Email']]
        c.execute("""select * from families where family_id = %s""", (pid))
        f=c.fetchall()[0]  #assume only 1? XXX exept IndexError if wrong!
        old=[str(x) for x in [f['phone'], f['address1'], f['email']]]
        if new != old:
            n=raw_input('OLD [%s]\n NEW [%s]\n use new? y/n ' %
                        (','.join(old), ','.join(new)))
            if n == 'y':
                c.execute("""update families set phone = %s,
                address1 = %s, email = %s where family_id = %s""",
                          (self.rec['Phone'],
                           self.rec['Address'], self.rec['Email'],
                           pid))
        return
        


class Kid(Adder):
    def __init__(self, c, rec, family_id):
        Adder.__init__(self, c, rec)
        self.family_id=family_id
        
    def get(self):
        self.pk='kid_id'
        return self._get("""select * from kids where last_name like '%%%s%%'
        and soundex(first_name) = soundex('%%%s%%') """ %
                            (self.rec['Last Name'], self.rec['Child']))


        #TODO: handle the situation where the family last name is a duplicate!
    def add(self):
        print "inserting new kid %s %s" % (self.rec['Child'],
                                           self.rec['Last Name'])
        c.execute("""insert into kids set last_name = %s, first_name = %s,
                    family_id = %s, date_of_birth = %s""",
                  (self.rec['Last Name'], self.rec['Child'],
                   int(self.family_id), self._human_to_dt(self.rec['DOB'])))
        return c.lastrowid


    def _human_to_dt(self,dob):
        d=map(int, dob.split('/'))
        d.insert(0,d.pop())             # date wants y,m,d
        if d[0] < 1900: d[0]+=1900
        if d[0] < 1950: d[0]+=100
        return datetime.date(*d)


    def update(self, pid):
        """keepin' it real, y'know what i'm sayin'?"""
        new=[self.rec['DOB']]
        c.execute("""select * from kids where kid_id = %s""", (pid))
        f=c.fetchall()[0]  #assume only 1? XXX exept IndexError if wrong!
        try:
            old=[f['date_of_birth'].strftime('%m/%d/%y')]
        except AttributeError:
            old=''
        if new != old:
            n=raw_input('OLD [%s]\n NEW [%s]\n use new? y/n ' %
                        (','.join(old), ','.join(new)))
            if n == 'y':
                c.execute("""update kids set date_of_birth = %s
                 where kid_id = %s""",
                          (self._human_to_dt(self.rec['DOB']),
                           pid))
        return



class Enrollment(Adder):
    def __init__(self, c, rec, kid_id):
        Adder.__init__(self, c, rec)
        self.kid_id=kid_id
        
    def get(self):
        self.pk='enrollment_id'
        return self._get("""select * from enrollment where kid_id = %d
        and school_year = '%s' """ %
                            (self.kid_id, school_year))

      #TODO: handle a *change*, i.e. from am/pm

    def add(self):
        c.execute("""insert into enrollment set 
                        kid_id = %s, school_year = %s, am_pm_session = %s,
                        start_date = %s , monday = %s, tuesday = %s,
                        wednesday = %s, thursday = %s, friday = %s""",
                  tuple([self.kid_id, school_year, self.rec['session'],
                         first_day_of_school] +
                         [self.rec[i] is not '' for i in
                          ['M','Tu', 'W','Th','F']]))
        return c.lastrowid



class Parent(Adder):
    type=None
    def __init__(self, c, rec, family_id, type):
        Adder.__init__(self, c, rec)
        self.family_id=family_id
        self.type=type
        
    def get(self):
        self.pk='parent_id'
        return self._get("""select * from parents where 
        (soundex(first_name) = soundex('%%%s%%')
        or first_name like '%%%s%%') and family_id = %d""" %
                            (self.rec[self.type+'_first'],
                             self.rec[self.type+'_first'].split()[0],
                             self.family_id))

    def add(self):
        print "inserting new parent %s %s" % (self.rec[self.type+'_first'],
                                           self.rec[self.type+'_last'])
        c.execute("""insert into parents set last_name = %s, first_name = %s,
                    family_id = %s, type = %s""",
                  (self.rec[self.type+'_last'], self.rec[self.type+'_first'],
                   int(self.family_id), self.type))
        return c.lastrowid


class Worker(Adder):
    days={'M': 'Monday',
          'Tu': 'Tuesday',
          'W': 'Wednesday',
          'Th': 'Thursday',
          'F' : 'Friday'}
    def __init__(self, c, rec, parent_id):
        Adder.__init__(self, c, rec)
        self.parent_id=parent_id
        
    def get(self):
        self.pk='worker_id'
        return self._get("""select * from workers where parent_id = %d
        and school_year = '%s' """ %
                            (self.parent_id, school_year))


    def add(self):
        c.execute("""insert into workers set 
                        parent_id = %s, school_year = %s, am_pm_session = %s,
                        workday = %s, epod = %s""" ,
                  (self.parent_id, school_year, self.rec['session'],
                   #note: i only get ONE epod and ONE worker, sets are too hard
                   [self.days[j[0]] for j in filter(lambda i: i[1] == 'W',
                                         self.rec.items())][0],
                   [self.days[j[0]] for j in filter(lambda i: i[1] == 'E',
                                         self.rec.items())][0]))
        return c.lastrowid



class User(Adder):
    def __init__(self, c, rec, family_id):
        Adder.__init__(self, c, rec)
        self.family_id=family_id
        
    def get(self):
        self.pk='user_id'
        return self._get("""select * from users where family_id = %d """ %
                            (self.family_id))


    def add(self):
        print "inserting new user for %s" % (self.rec['Last Name'])
        c.execute("""insert into users set 
                        family_id = %s, name = %s""",
                  (self.family_id, self.rec['Last Name']+ ' Family'))
        uid=c.lastrowid
        c.execute("""insert into users_groups_join set
        user_id = %s, group_id = 1""",
                  (uid))
        return uid



##########naked functions

def line(rec):
    """Driver for looping through the necessary steps to parse out a line"""
    f=Family(c,rec)
    family_id = f.wrapper()
    k=Kid(c,rec,family_id)
    kid_id=k.wrapper()
    e=Enrollment(c,rec,kid_id)
    enrol_id=e.wrapper()
    m=Parent(c,rec,family_id, 'mom')
    mom_id=m.wrapper()
    d=Parent(c,rec,family_id, 'dad')
    dad_id=d.wrapper()
    w=Worker(c,rec,mom_id)
    worker_id=w.wrapper()
    u=User(c,rec,family_id)
    user_id=u.wrapper()
    print "kid %d, family %d, enrollment %d, mom %d, dad/partner %d, worker %d, user %d" % (kid_id, family_id, enrol_id, mom_id, dad_id, worker_id, user_id)
        


def load(am_file, pm_file):
    """Takes AM, PM files, builds objects for them, and loads them"""
    AM=RastaImport(am_file, 'AM')
    PM=RastaImport(pm_file, 'PM')
    rasta.extend(AM.get())
    rasta.extend(PM.get())



###### MAIN
if __name__ == '__main__':
    conn=MySQLdb.connect(user='input', passwd='test', db='coop',
                         host='127.0.0.1', port=2299,
                         cursorclass=MySQLdb.cursors.DictCursor) 
    c=conn.cursor()
    
    load("/mnt/kens/ki/proj/coop/imports/AMRoster05-06.csv", 
         "/mnt/kens/ki/proj/coop/imports/PMRoster05-06.csv")
    
    for i in rasta: line(i)

#dict([(i['School Job'], i['Last Name']) for i in ir.rasta])
