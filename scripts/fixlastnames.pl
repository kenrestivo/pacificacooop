#!/usr/bin/perl -w


#$Id$
#deciphers hypenated last names
# another one-shot i shouldn't need again, now that the rastafarai is in there

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


#approximate list of families
$rquery = "select * from parents where first_name like \"% %\"";
#print "doing <$rquery>\n"; #XXX debug only
$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
$rqueryobj->execute() or die "couldn't execute $!\n";

while ($ritemref = $rqueryobj->fetchrow_hashref){
	%ritem = %$ritemref;
	$oldlast_name = $ritem{'last_name'};
	$first_name = $ritem{'first_name'};
	$id = $ritem{'parent_id'};

	($newfirst_name, $newlast_name) = split(/ +/, $first_name);
	print "shall i change <$oldlast_name, $first_name> into <$newlast_name, $newfirst_name>?\n";
	$reply = <STDIN>;

	if($reply !~ /[nN]/){
		#ok, fix em!
		$query = "update parents 
				set last_name = \'$newlast_name\', first_name = \'$newfirst_name\' 
				where parent_id = $id";
		print "doing <$query>\n";
		print STDERR $dbh->do($query) . "\n";
	}
} # end while


$dbh->disconnect or die "couldnt' disconnect from dtatbase $!\n";

#EOF
