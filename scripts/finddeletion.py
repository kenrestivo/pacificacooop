#!/usr/bin/env python

#$Id$

# resets the COOP (main) db back to  live backup

import sys
from os import system,popen
from getpass import getpass


def restore(file):
    print 'restoring from %s...' % (i)
    system('bzcat %s | mysql -p%s %s' %(file, pw, args)) 
    print 'setting fake "tester" password...'
    system('mysql -p%s %s -e "update users set password = md5(\'tester\')" coop' %(pw,args))



######MAIN

#get args from shell
args = ' '.join(sys.argv[1:])


# get it in a format i can use: pair defs and data
res= dict(defs=[], data=[])
for i in res.keys():
    res[i] = [l[0:-1] for l in popen('ls -t /mnt/kens/ki/proj/coop/sql/backups/*%s*' % (i)).readlines()]


print 'Type MySQL password'
pw=getpass()


# now restore them selectively, and inspect them.
for n in range(0,len(res['data'])):
    for i in res.keys():
        restore(res[i][n])



#finally, restore the latest one [0]
for i in res.keys():
    restore(res[i][0])
