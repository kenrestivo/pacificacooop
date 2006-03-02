#!/usr/bin/python -u

#$Id$


##XXX nice idea but going nowhere. i need to use php for this

import os
import csv
from datetime import date
import MySQLdb, MySQLdb.cursors
import datetime

import dbhost


def alterTables(c):
    c= conn.cursor()
    c.execute('show tables')
    tables=c.fetchall()
    for t in tables:
        table=t['name']
        c.execute('explain %s' % (table)
        cols=c.fetchall()
        c.execute('alter table %s add column _cache%s_ varchar(255)' % (table, )) 



###### MAIN
if __name__ == '__main__':
    conn=MySQLdb.connect(user=dbhost.user, passwd=dbhost.pw, db=dbhost.db,
                         host=dbhost.host, port=dbhost.port,
                         cursorclass=MySQLdb.cursors.DictCursor) 
    alterTables(conn)

##END
