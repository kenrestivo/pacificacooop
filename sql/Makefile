#$Id$
#makefile for sql databases

# Copyright (C) 2003  ken restivo <ken@restivo.org>
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.                                  #
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


DBHOST=bc
MASTERHOST=bc
DBNAME=coop
TEMPFILE=/tmp/export.sql
DATE=${shell date +%Y.%m.%d.%H.%M.%S}
DATAFILE=backups/coopbackup-data$(DATE)
DEFFILE=backups/coopbackup-defs$(DATE)
TABS=cooptabs$(DATE)
DB=coop
DBPORT=3306
DBUSER=ken
TABLES=ins veh lic kids enrol parents kpglue keglue

all::
	@echo "not so fast... get specific"
	@egrep "^[a-z]*:" Makefile | awk '{print $$1}' 

create::
	mysql -u $(DBUSER) -p -h $(DBHOST) -P $(DBPORT) mysql < definition.sql

dummy::
	mysql -u $(DBUSER) -p -h $(DBHOST) -P $(DBPORT) $(DBNAME) < dummydata.sql
	

reports::
	mysql -u $(DBUSER) -p -h $(DBHOST) -P $(DBPORT) $(DBNAME) < queries.sql

#XXX this is fucking bro$(DBUSER). why? it works from shell, but not from makefile
tabdump:
	-(mkdir backups; cd backups)
	(mkdir  $(TABS); cd $(TABS))
	for i in $(TABLES); do \
		echo "===== exproting $$i=== " ; \
		mysql -B -e "select * from $$i" $(DB) > $$i.txt ;\
	done


backup-defs::
	mysqldump \
		--add-drop-table \
		--all  \
		--database $(DB) \
		--lock-tables \
		--no-data \
		-h $(DBHOST) -P $(DBPORT) -u $(DBUSER) -p > $(DEFFILE)
	bzip2 -f $(DEFFILE)

backup-data:
	mysqldump \
		--add-locks \
		--all  \
		--database $(DB) \
		--extended-insert \
		--lock-tables \
		--no-create-db \
		--no-create-info \
		--quick \
		-h $(DBHOST) -P $(DBPORT) -u $(DBUSER) -p > $(DATAFILE)
	bzip2 -f $(DATAFILE)

backup: backup-defs backup-data
	-mkdir backups/
	-mv *.bz2 backups/
		
#EOF

