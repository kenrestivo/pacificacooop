#!/usr/bin/python

#$Id$

from datetime import date
import MySQLdb, MySQLdb.cursors
import datetime
import os, time



printfmt = "---------------------\n"
for s in ['wid', 'uid', 'type', 'message', 'link', 'location', 'referer', 'hostname', 'realdate']:
    printfmt += "%s\t%%(%s)s\n" % (s, s)



def print_formatted(res):
    """notice, this outputs directly to screen, rather than return value"""
    print "========= new timestamp =============="
    for r in res:
        print printfmt % r





# tail the damned drupal log
conn=MySQLdb.connect(user='coopdrupal', passwd='36a3e3ed44', db='coop_drupal',
                    host='127.0.0.1', port=3306,
                     cursorclass=MySQLdb.cursors.DictCursor) 
c=conn.cursor()


last = 0

while 1:
    if last > 0:
        r = c.execute("""select *, from_unixtime(timestamp) as realdate from watchdog where timestamp > %ld order by timestamp""" % (last))
        if c.rowcount > 0:
            print_formatted(c.fetchall())
    r=c.execute('select max(timestamp) as lasttime from watchdog');
    last = c.fetchall()[0]['lasttime']
    time.sleep(2)

