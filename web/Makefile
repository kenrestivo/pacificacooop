#$Id$
#makefiel for web stuff. mostly syncing things.

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
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


#NFSNRSYNC="--rsync-path=/nfsn/bin/rsync"
NFSNUSR=ken
NFSNHOST=c1.phx.nearlyfreespeech.net
NFSNDOCROOT=/nfsn/content/paccoop/htdocs
NFSNLIVEPATH=$(NFSNDOCROOT)/sf/
LIVEHERE=../livesite/
NFSNDEVPATH=$(NFSNDOCROOT)/sf-dev/
DEVHERE=../web-online/
PLAYGROUND=../web/
XCLUDE='*dbhost*' 



all: help

help::
	@echo "Please choose a target"
	@egrep "^[a-z-_]*:" Makefile | awk '{print $$1}'

upload-live::
	-rm $(LIVEHERE)/.\#*
	rsync -e ssh \
		$(NFSNRSYNC) \
		-avxWcC  \
		--exclude $(XCLUDE) \
		--exclude "*.#*" \
		$(LIVEHERE) $(NFSNUSR)@$(NFSNHOST):$(NFSNLIVEPATH)

download-live::
	rsync -e ssh \
		$(NFSNRSYNC) -avxWcC  \
		--exclude $(XCLUDE) \
		--exclude "*.#*" \
		--exclude '*.sql' \
		$(NFSNUSR)@$(NFSNHOST):$(NFSNLIVEPATH)/ $(LIVEHERE)

upload-dev:: 
	-rm $(DEVHERE)/.\#*
	rsync -e ssh \
		$(NFSNRSYNC) -avxWcC  \
		--exclude $(XCLUDE) \
		--exclude "*.#*" \
		$(DEVHERE) $(NFSNUSR)@$(NFSNHOST):$(NFSNDEVPATH)

download-dev::
	rsync -e ssh \
		$(NFSNRSYNC) -avxWcC  \
		--exclude $(XCLUDE) \
		--exclude "*.#*" \
		--exclude '*.sql' \
		$(NFSNUSR)@$(NFSNHOST):$(NFSNDEVPATH)/ $(DEVHERE)

upload-head:: 
	-rm $(DEVHERE)/.\#*
	rsync -e ssh \
		$(NFSNRSYNC) -avxWcC  \
		--exclude $(XCLUDE) \
		--exclude "*.#*" \
		$(PLAYGROUND) $(NFSNUSR)@$(NFSNHOST):$(NFSNDEVPATH)

download-head::
	rsync -e ssh \
		$(NFSNRSYNC) -avxWcC  \
		--exclude $(XCLUDE) \
		--exclude "*.#*" \
		--exclude '*.sql' \
		$(NFSNUSR)@$(NFSNHOST):$(NFSNDEVPATH)/ $(PLAYGROUND)

commit-live:
	(cd $(LIVEHERE); cvs commit -m "syncing")

update-dev:
	(cd $(DEVHERE); cvs update )

sync-live: commit-live download-live

#TODO do a cvs update after downloading?
sync-dev: download-dev

diff-live:
	(cd $(PLAYGROUND); cvs diff -r livesite -r HEAD > $(LIVEHERE).temp.diff )

diff-dev:
	(cd $(PLAYGROUND); cvs diff -r dev_online -r HEAD > $(DEVHERE).temp.diff )

tags:
	etags *

update-dbobject::
	 php /usr/share/php/DB/DataObject/createTables.php coop-dbobj.ini

guarantee-pear::
	@for i in Archive_Tar Console_Getopt DB DB_DataObject  DB_DataObject_FormBuilder-beta Date HTML_Common HTML_Menu HTML_QuickForm HTML_Table HTML_Template_Sigma HTTP Mail Net_SMTP Net_Socket PEAR Pager Validate-alpha XML_Parser XML_RPC  ; do sudo pear install $i ; done


#EOF

