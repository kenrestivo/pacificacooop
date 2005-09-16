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

doctor_keys = [
 'Mom Name',
 'Phone',
 'Last Name',
 'Address',
 'DOB',
 'Child',
 'Doctor',
 'Doctors Phone',
 'Doctors Number',
 'Allergies',
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
        as integers. NOTE the -2 to deal with excel/lotus bugs."""
        if d.count('/') > 1:
            return d
        else:
            return date.fromordinal(
                int(d) - 2 +
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
    id=0

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
            try:
                pid = self.choose()
            except NoneFound:
                pid=self.add()
                return pid
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
        try:
            n=raw_input('Pick the %s above (%s) or "0" for none: ' %
                        (self.pk,
                         ','.join([str(x) for x in valid])))
            if int(n) in valid:
                self.id=n
                return n
            if int(n) < 1:
                raise NoneFound
        except ValueError:
            print "you need to type a number"
        print "no, that's not OK. try again"
        return self.choose()        # can i tail recurse? will it do it?

    def update(self,pid):
        """Base virtual method"""
        pass


class Family(Adder):
    def get(self):
        """a very cheap way to get the family."""
        self.pk='family_id'
        self.id=self._get("""select * from families where phone like '%%%s%%'
        and name like '%%%s%%' """ % (self.rec['Phone'], self.rec['Last Name']))
        return self.id


        #TODO: handle the situation where the family last name is a duplicate!
    def add(self):
        """simple insert wrapper"""
        n=raw_input("insert new family %s (y/n)? " % (self.rec['Last Name']))
        if n == 'y':
            c.execute("""insert into families set name = %s, phone = %s,
                    address1 = %s, email = %s""",
                  (self.rec['Last Name'], self.rec['Phone'],
                   self.rec['Address'], self.rec['Email']))
        self.id=c.lastrowid
        return self.id

    def update(self, pid):
        """keepin' it real, y'know what i'm sayin'?"""
        try:
            new=[self.rec['Phone'], self.rec['Address'], self.rec['Email']]
            c.execute("""select * from families where family_id = %s""", (pid))
            f=c.fetchall()[0]  #assume only 1? XXX exept IndexError if wrong!
            old=[str(x) for x in [f['phone'], f['address1'], f['email']]]
            if new != old:
                print 'data changed for %s...' % (self.rec['Last Name'])
                n=raw_input('OLD [%s]\n NEW [%s]\n use new? y/n ' %
                            (','.join(old), ','.join(new)))
                if n == 'y':
                    c.execute("""update families set phone = %s,
                    address1 = %s, email = %s where family_id = %s""",
                              (self.rec['Phone'],
                               self.rec['Address'], self.rec['Email'],
                               pid))
        except KeyError:
            print 'skipping update for %s' % (self.rec['Last Name'])
            return
        return
        


class Kid(Adder):
    def __init__(self, c, rec, family_id):
        Adder.__init__(self, c, rec)
        self.family_id=family_id
        
    def get(self):
        self.pk='kid_id'
        self.id=self._get("""select * from kids where last_name like '%%%s%%'
        and soundex(first_name) = soundex('%%%s%%') """ %
                            (self.rec['Last Name'], self.rec['Child']))
        return self.id


        #TODO: handle the situation where the family last name is a duplicate!
    def add(self):
        n=raw_input("insert new kid %s %s (y/n)? " % (self.rec['Child'],
                                           self.rec['Last Name']))
        if n == 'y':
            c.execute("""insert into kids set last_name = %s, first_name = %s,
            family_id = %s, date_of_birth = %s""",
                      (self.rec['Last Name'], self.rec['Child'],
                       int(self.family_id), self._human_to_dt(self.rec['DOB'])))
        self.id=c.lastrowid
        return self.id


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
            d=f['date_of_birth']
            oldshort=['/'.join(map(str,[d.month,d.day,d.strftime('%y')]))]
            oldlong=[d.strftime('%m/%d/%y')]
        except AttributeError:
            oldlong=''
            oldshort=''
        if new != oldlong and new != oldshort:
            print 'data changed for %s %s...' % (self.rec['Child'],
                                                 self.rec['Last Name'])
            n=raw_input('OLD [%s]\n NEW [%s]\n use new? y/n ' %
                        (','.join(oldshort), ','.join(new)))
            if n == 'y':
                c.execute("""update kids set date_of_birth = %s
                 where kid_id = %s""",
                          (self._human_to_dt(self.rec['DOB']),
                           pid))
        return

    def addDoc(self, dr_id):
        """updates  doctor for this particular kid"""
        try:
            allergy=self.rec['Allergies']
        except KeyError:
            allergy=self.rec['Notes']
        c.execute("""update kids set doctor_id = %s, allergies = %s
        where kid_id = %s""",
                  (dr_id, allergy, self.id))
        return c.lastrowid



class Enrollment(Adder):
    def __init__(self, c, rec, kid_id):
        Adder.__init__(self, c, rec)
        self.kid_id=kid_id
        
    def get(self):
        self.pk='enrollment_id'
        self.id=self._get("""select * from enrollment where kid_id = %d
        and school_year = '%s' """ %
                            (self.kid_id, school_year))
        return self.id

      #TODO: handle a *change*, i.e. from am/pm

    def add(self):
        n=raw_input("insert new enrollment for %s %s %s (y/n)?" %
                    (self.rec['Child'], self.rec['Last Name'],
                     self.rec['session']))
        if n == 'y':
            c.execute("""insert into enrollment set 
            kid_id = %s, school_year = %s, am_pm_session = %s,
            start_date = %s , monday = %s, tuesday = %s,
            wednesday = %s, thursday = %s, friday = %s""",
                      tuple([self.kid_id, school_year, self.rec['session'],
                             first_day_of_school] +
                            [self.rec[i] is not '' for i in
                             ['M','Tu', 'W','Th','F']]))
        self.id=c.lastrowid
        return self.id



class Parent(Adder):
    type=None
    def __init__(self, c, rec, family_id, type):
        Adder.__init__(self, c, rec)
        self.family_id=family_id
        self.type=type
        
    def get(self):
        self.pk='parent_id'
        self.id=self._get("""select * from parents where 
        (soundex(first_name) = soundex('%%%s%%')
        or first_name like '%%%s%%') and family_id = %d""" %
                            (self.rec[self.type+'_first'],
                             self.rec[self.type+'_first'].split()[0],
                             self.family_id))
        return self.id

    def add(self):
        n=raw_input("insert new parent %s %s (y/n)?" %
                    (self.rec[self.type+'_first'],
                     self.rec[self.type+'_last']))
        if n == 'y':
            c.execute("""insert into parents set last_name = %s, first_name = %s,
                    family_id = %s, type = %s""",
                  (self.rec[self.type+'_last'], self.rec[self.type+'_first'],
                   int(self.family_id), self.type))
        self.id=c.lastrowid
        return self.id


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
        self.id=self._get("""select * from workers where parent_id = %d
        and school_year = '%s' """ %
                            (self.parent_id, school_year))
        return self.id


    def add(self):
        n=raw_input("insert new worker %s %s (y/n)? " %
                    (self.rec['Last Name'], self.rec['session']))
        if n == 'y':
            c.execute("""insert into workers set 
                        parent_id = %s, school_year = %s, am_pm_session = %s,
                        workday = %s, epod = %s""" ,
                  (self.parent_id, school_year, self.rec['session'],
                   #note: i only get ONE epod and ONE worker, sets are too hard
                   [self.days[j[0]] for j in filter(lambda i: i[1] == 'W',
                                         self.rec.items())][0],
                   [self.days[j[0]] for j in filter(lambda i: i[1] == 'E',
                                         self.rec.items())][0]))
        self.id=c.lastrowid
        return self.id



class User(Adder):
    def __init__(self, c, rec, family_id):
        Adder.__init__(self, c, rec)
        self.family_id=family_id
        
    def get(self):
        self.pk='user_id'
        self.id=self._get("""select * from users where family_id = %d """ %
                            (self.family_id))
        return self.id


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



class Doctor(Adder):
    kid_id=0
    dr_first=''
    dr_last=''
    def __init__(self, c, rec, kid_id):
        Adder.__init__(self, c, rec)
        self.kid_id=kid_id
        dr=self.rec['Doctor'].split()
        if len(dr) < 2:
            dr=self.rec['Doctor'].split('.')
        self.dr_last=dr.pop()
        self.dr_first=dr
        
    def get(self):
        self.pk='lead_id'
        self.id=self._get("""select * from leads where 
        (soundex(first_name) = soundex('%%%s%%')
        or first_name like '%%%s%%') and (soundex(last_name) = soundex('%%%s%%')
        or last_name like '%%%s%%')""" %
                            (self.dr_first[0][0], self.dr_first[0][0],
                             self.dr_last,self.dr_last))
        return self.id
                          
                             

    def add(self):
        """add a new doctor to the db"""
        print 'adding new doctor %s' % self.rec['Doctor']
        n=raw_input("insert new doctor %s (y/n)?" %
                    (self.rec['Doctor']))
        if n == 'y':
            try:
                num=self.rec['Doctors Number']
            except KeyError:
                num=self.rec['Doctors Phone']
            c.execute("""insert into leads set last_name = %s, first_name = %s,
                    phone  = %s""",
                  (self.dr_last, self.dr_first[0], num))
        self.id=c.lastrowid
        return self.id



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
        


def doctor_line(rec):
    """Driver for looping through the necessary steps to parse out a doctor"""
    f=Family(c,rec)
    family_id = f.wrapper()
    k=Kid(c,rec,family_id)
    kid_id=k.wrapper()
    d=Doctor(c,rec,kid_id)
    dr_id=d.wrapper()
    k.addDoc(dr_id)
#    print "kid %d, family %d, doctor %d, mom %d, dad/partner %d, worker %d, user %d" % (kid_id, family_id, enrol_id, mom_id, dad_id, worker_id, user_id)



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


#now the doctor doctor give me the news
    load('/mnt/kens/ki/proj/coop/imports/AMdr.csv',
         '/mnt/kens/ki/proj/coop/imports/PMdoctor.csv')
    
    load('/mnt/kens/ki/proj/coop/imports/amdoctors.csv',
         '/mnt/kens/ki/proj/coop/imports/PMdoctors.csv')


    for i in rasta: doctor_line(i)
