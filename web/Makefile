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


NFSNRSYNC=/nfsn/bin/rsync 
NFSNUSR=ken
NFSNHOST=phx3.nearlyfreespeech.net
NFSNPATH=/nfsn/content/paccoop/htdocs/sf
HERE=./
XCLUDE='*dbhost*' 



#XXX this is so borken
# the excludes do NOT work
# it utterly fucks up the permissions
# don't use it.

all::


upload::
	rsync -e ssh \
		--rsync-path=$(NFSNRSYNC) -avxWcC  \
		--exclude $(XCLUDE) \
		$(HERE) $(NFSNUSR)@$(NFSNHOST):$(NFSNPATH)

download::
	rsync -e ssh \
		--rsync-path=$(NFSNRSYNC) -avxWcC  \
		--exclude $(XCLUDE) \
		$(NFSNUSR)@$(NFSNHOST):$(NFSNPATH)/ $(HERE)

commit:
	cvs commit

sync: commit download
	


#EOF

