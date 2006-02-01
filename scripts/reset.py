#!/usr/bin/env python

#$Id$

# resets the COOP (main) db back to  live backup

import sys
from os import system,popen
from getpass import getpass

#get args from shell
args = ' '.join(sys.argv[1:])

filenames = [[l[0:-1] for l in popen('ls -t backups/*%s*' % (i)).readlines()][0] for i in ['defs', 'data']]

print 'Type MySQL password'
pw=getpass()

for i in filenames:
    print 'restoring from %s...' % (i)
    system('bzcat %s | mysql -p%s %s' %(i, pw, args)) 

print 'setting fake "tester" password...'
system('mysql -p%s %s -e "update users set password = md5(\'tester\')" coop' %(pw,args))
