#!/usr/bin/perl -w


#$Id$
#move the email addresses from kids db to parents db
#move the phone from the kids to the family db
#shouldn't need this anymore, but it may come in handy later if i ever
#have to split up tables again

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


use DBI;

#basic login and housekeeping stuff
$dbh = DBI->connect("DBI:mysql:coop:bc", "input", "test" )
    or die "can't connect to database $!\n";

&movethem('email_address', 'kids', 'parents');
&movethem('phone', 'kids', 'families');

$dbh->disconnect or die "couldnt' disconnect from dtatbase $!\n";

##END OF MAIN CODE
###############

sub movethem {
	$col = shift;
	$from = shift;
	$to = shift;
	#approximate list of families
	$rquery = "select * from $from where $col is not null";
	#print "doing <$rquery>\n"; #XXX debug only
	$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
	$rqueryobj->execute() or die "couldn't execute $!\n";

	while ($ritemref = $rqueryobj->fetchrow_hashref){
		%ritem = %$ritemref;
		$changer = $ritem{$col};
		$id = $ritem{'family_id'};

		#ok, fix em!
		$query = "update $to set $col = \"$changer\" where family_id = $id";
		print "doing <$query>\n";
		print STDERR $dbh->do($query) . "\n";
	} # end while
}
