

# 	Copyright (C) 2005  ken restivo <ken@restivo.org>
	 
# 	This program is free software; you can redistribute it and/or modify
# 	it under the terms of the GNU General Public License as published by
# 	the Free Software Foundation; either version 2 of the License, or
# 	(at your option) any later version.
	
# 	 This program is distributed in the hope that it will be useful,
# 	 but WITHOUT ANY WARRANTY; without even the implied warranty of
# 	 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# 	 GNU General Public License for more details. 
	
# 	 You should have received a copy of the GNU General Public License
# 	 along with this program; if not, write to the Free Software
# 	 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

__version__ = """$Id$"""

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

