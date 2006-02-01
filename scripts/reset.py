#!/usr/bin/env python

#$Id$

# resets the COOP (main) db back to  live backup

from os import system,popen
from getpass import getpass

#XXX get args from shell calling this!
args = ''

filenames = [[l[0:-1] for l in popen('ls -t backups/*%s*' % (i)).readlines()][0] for i in ['defs', 'data']]

for i in filenames:
    print 'restoring %s...' % (i)
    system('bzcat %s | mysql %s' %(i, args)) 

print 'setting fake "tester" password...'
system('mysql %s -e "update users set password = md5(\'tester\')" coop' %(args))
