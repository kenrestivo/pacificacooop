#!/usr/bin/perl -w


#$Id$
# deduces who families are, by grouping the kids names, then deposits the parents in.
# pretty much a one-shot script. i shouldn't need it unless i re-import the roster again


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

use DBI;

#basic login and housekeeping stuff
$dbh = DBI->connect("DBI:mysql:coop:bc", "input", "test" )
    or die "can't connect to database $!\n";


#approximate list of families
$rquery = "select last_name from kids group by last_name order by last_name";
print "doing <$rquery>\n"; #XXX debug only
$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
$rqueryobj->execute() or die "couldn't execute $!\n";

while ($ritemref = $rqueryobj->fetchrow_hashref){
	%ritem = %$ritemref;
	push(@fams, $ritem{'last_name'});
} # end while

#create (and save) the family id's for them.
foreach $fam (@fams){
	$query = "insert into families set name = \'$fam\'";
	print "doing <$query>\n";
	print STDERR $dbh->do($query) . "\n";
	$id = $dbh->{'mysql_insertid'}; #weird shorthand, not always good

	#ok, do the updating
	foreach $tab ('parents', 'kids'){
		$squery = "update $tab set family_id = $id where last_name = \'$fam\'";
		print "doing <$squery>\n";
		print STDERR $dbh->do($squery) . "\n";
	}
}

$dbh->disconnect or die "couldnt' disconnect from dtatbase $!\n";

#EOF
