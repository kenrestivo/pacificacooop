#$Id$

from sqlobject import *
import dbhost

sqlhub.processConnection = connectionForURI(dbhost.connectionurl)

class SessionInfo(SQLObject):
       ip_addr=StringCol(length=20)
       entered=DateTimeCol()
       updated=DateTimeCol()
       User=ForeignKey('Users')
       vars=BLOBCol()
       class sqlmeta:
              idName='session_id'




     

