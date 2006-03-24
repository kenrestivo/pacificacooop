#!/usr/bin/env python

#$Id$

# resets the COOP (main) db back to  live backup

import sys
from os import system,popen
from getpass import getpass
import MySQLdb, MySQLdb.cursors

##globs
school_year = '2005-2006'
testvalues = [290, 246, 1655, 448, 486]





######MAIN

#get args from shell
args = ' '.join(sys.argv[1:])


# get it in a format i can use: pair defs and data
res= dict(defs=[], data=[])
for i in res.keys():
    res[i] = [l[0:-1] for l in popen('ls -t /mnt/kens/ki/proj/coop/sql/backups/*%s*' % (i)).readlines()]


print 'Type MySQL password'
pw=getpass()


##TODO: trap keyboard interrupt

# now restore them selectively, and inspect them.
found = dict()
for n in range(0,len(res['data'])):
    for i in res.keys():
        print 'restoring from %s (%s)...' % (i, res[i][n])
        system('bzcat %s | mysql -p%s %s' %(res[i][n], pw, args))
    print 'setting fake "tester" password...'
    system('mysql -p%s %s -e "update users set password = md5(\'tester\')" coop' %(pw,args))
   ### test the results
    conn=MySQLdb.connect(user='input', passwd='test', db='coop',
                     host='127.0.0.1', port=2299,
                     cursorclass=MySQLdb.cursors.DictCursor) 
    c=conn.cursor()
    found[res['data'][n]] = dict()
    for t in testvalues:
        c.execute("""select * from sponsorships where lead_id = %d and school_year = '%s'""" % (t,school_year))
        found[res['data'][n]][t] =  len(c.fetchall())
    conn.close()

    
        


