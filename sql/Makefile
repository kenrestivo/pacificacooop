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
DB=coop
TEMPFILE=/tmp/export.sql
DATE=${shell date +%Y.%m.%d.%H.%M.%S}
DATAFILE=backups/$(DB)-$(DBHOST)-data.$(DATE)
DEFFILE=backups/$(DB)-$(DBHOST)-defs.$(DATE)
TABS=cooptabs$(DATE)
DBPORT=3306
DBUSER=ken
REALDB=pacsql.db
FARPORT=2299
BKPW=test
PW=test
BKUSER=input
NFSNHOST=dev.phx.nearlyfreespeech.net

all::
	@echo "not so fast... get specific"
	@egrep "^[a-z\-]*:" Makefile | awk '{print $$1}' 

create::
	mysql -u $(DBUSER) -p -h $(DBHOST) -P $(DBPORT) mysql < definition.sql

dummy::
	mysql -u $(DBUSER) -p -h $(DBHOST) -P $(DBPORT) $(DB) < dummydata.sql

reports::
	mysql -u $(DBUSER) -p -h $(DBHOST) -P $(DBPORT) $(DB) < queries.sql


backup-defs::
	mysqldump \
		--add-drop-table \
		--all  \
		--compress \
		--database $(DB) \
		--lock-tables \
		--no-data \
		-h $(DBHOST) -P $(DBPORT) -u $(BKUSER) --password=$(BKPW) > $(DEFFILE)
	bzip2 -f $(DEFFILE)

backup-data:
	mysqldump \
		--add-locks \
		--all  \
		--compress \
		--database $(DB) \
		--extended-insert \
		--lock-tables \
		--no-create-db \
		--no-create-info \
		--quick \
		-h $(DBHOST) -P $(DBPORT) -u $(BKUSER) --password=$(BKPW) > $(DATAFILE)
	bzip2 -f $(DATAFILE)

backup: backup-defs backup-data
	-mkdir backups/
	-mv *.bz2 backups/

backup-local: backup

backup-live::
	$(MAKE) backup DBUSER=$(BKUSER) DBHOST=127.0.0.1 DBPORT=$(FARPORT) PW=$(BKPW)

ssh-tunnel-broken::
	ssh -n -f -N -L6666:$(NFSNHOST):22 portaserver.restivo.org
	ssh -n -f -N -L$(FARPORT):$(REALDB):3306 -p 6666 ken@localnfsn

ssh-tunnel::
	ssh -n -f -N -L$(FARPORT):$(REALDB):3306  ken@$(NFSNHOST)

diagram:
	autodia.pl -l DBI -i "mysql:$(DB):$(DBHOST)" -U$(BKUSER) -P$(BKPW) -o schema.dia

ksql-live:
	ksql $(DB) 127.0.0.1 $(BKUSER) $(FARPORT)

mysql-live:
	mysql -h  127.0.0.1 -u $(BKUSER) -P $(FARPORT) $(DB)

restore:
	../scripts/reset.py


backup-drupal::
	$(MAKE) backup  DB="coop_drupal" BKUSER"=backup" BKPW="2a5e8deecfd"


backup-drupal-live::
	$(MAKE) backup DB="coop_drupal"  BKUSER"=backup" DBHOST=127.0.0.1  DBPORT=$(FARPORT) BKPW="2a5e8deecfd" 


#EOF

