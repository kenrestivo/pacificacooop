#!/usr/bin/python -u

#$Id$

"""Imports the rasta in the old Excel/CSV format, and updates
the databse with its spiffy new contents"""


import os
from datetime import date
import MySQLdb, MySQLdb.cursors
import datetime
import re



def fix(line):
    return re.sub('^--.*','' , re.sub('\\\G', '',line))



conn=MySQLdb.connect(user='input', passwd='test', db='coop',
                     host='127.0.0.1', port=3306,
                     cursorclass=MySQLdb.cursors.DictCursor) 
c=conn.cursor()


c.execute("""set @school_year := '2005-2006', @ticket_price := 30,
@ad_text := 'ad valued at', @ticket_text := 'to Springfest valued at',
@cash_text := 'cash'""")


f=open('/mnt/kens/ki/proj/coop/web/sql/thankyouneeded.sql')

c.execute(''.join([fix(line) for line in f.readlines()]))


for o in c.fetchall():
    print '%4d      %s' % (o['id'], o['id_name'])
