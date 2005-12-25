#$Id$

from sqlobject import *
import dbhost

sqlhub.processConnection = connectionForURI(dbhost.connectionurl)

class Runs(SQLObject):
       run_date=DateTimeCol()
       site_name=StringCol(length=50)
       user_name=StringCol(length=255)
       base_url=StringCol(length=255)


class Pages(SQLObject):
       url=StringCol(length=255)
       title=StringCol(length=255)
       wc_report=BLOBCol()
       page_source=BLOBCol()
       page_headers=BLOBCol()
       Run=ForeignKey('Runs')

class Errors(SQLObject):
       type=StringCol(length=50)
       sysid=StringCol(length=50)
       lineno=IntCol()
       colno=IntCol()
       message=StringCol()
       Page=ForeignKey('Pages')
