#$Id$

from sqlobject import *
import dbhost

sqlhub.processConnection = connectionForURI(dbhost.connectionurl)

class SessionInfo(SQLObject):
       session_id=StringCol(length=32)
       ip_addr=StringCol(length=20)
       entered=DateTimeCol(default=None)
       updated=DateTimeCol(default=None)
       User=ForeignKey('Users')
       vars=BLOBCol()
       class sqlmeta:
              idName='session_id'
              idType = str




# class JobDescriptions(SQLObject):
#        class sqlmeta:
#               fromDatabase = True
#               idName='job_description_id'

