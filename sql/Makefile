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
DATAFILE=backups/$(DBNAME)-$(DBHOST)-data.$(DATE)
DEFFILE=backups/$(DBNAME)-$(DBHOST)-defs.$(DATE)
TABS=cooptabs$(DATE)
DB=coop
DBPORT=3306
DBUSER=ken
REALDB=pacsql.db
FARPORT=2299
BKPW=test
PW=test
BKUSER=input

all::
	@echo "not so fast... get specific"
	@egrep "^[a-z]*:" Makefile | awk '{print $$1}' 

create::
	mysql -u $(DBUSER) -p -h $(DBHOST) -P $(DBPORT) mysql < definition.sql

dummy::
	mysql -u $(DBUSER) -p -h $(DBHOST) -P $(DBPORT) $(DBNAME) < dummydata.sql

reports::
	mysql -u $(DBUSER) -p -h $(DBHOST) -P $(DBPORT) $(DBNAME) < queries.sql


backup-defs::
	mysqldump \
		--add-drop-table \
		--all  \
		--database $(DB) \
		--lock-tables \
		--no-data \
		-h $(DBHOST) -P $(DBPORT) -u $(DBUSER) --password=$(PW) > $(DEFFILE)
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
		-h $(DBHOST) -P $(DBPORT) -u $(DBUSER) --password=$(PW) > $(DATAFILE)
	bzip2 -f $(DATAFILE)

backup: backup-defs backup-data
	-mkdir backups/
	-mv *.bz2 backups/

backup-local: backup

backup-live::
	$(MAKE) backup DBUSER=$(BKUSER) DBHOST=127.0.0.1 DBPORT=$(FARPORT) PW=$(BKPW)

ssh-tunnel::
	ssh -n -f -N -L$(FARPORT):$(REALDB):3306 ken@c1.phx.nearlyfreespeech.net

diagram:
	autodia.pl -l DBI -i "mysql:$(DB):$(DBHOST)" -U$(BKUSER) -P$(BKPW) -o schema.dia

ksql-live:
	ksql $(DBNAME) 127.0.0.1 $(BKUSER) $(FARPORT)

mysql-live:
	mysql -h  127.0.0.1 -u $(BKUSER) -P $(FARPORT) $(DBNAME)

#EOF

