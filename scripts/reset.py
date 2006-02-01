#!/usr/bin/env python

#$Id$

# resets the COOP (main) db back to  live backup

from os import system,popen


#XXX get args from shell calling this!
args = ''


data=[l[0:-1] for l in popen('ls -t backups/*data*').readlines()][0]
defs=[l[0:-1] for l in popen('ls -t backups/*defs*').readlines()][0]


for i in [defs, data]:
    system('bzcat %s | mysql %s' %(i, args)) 

system('mysql %s -e "update users set password = md5(\'tester\')" coop' %(args))
