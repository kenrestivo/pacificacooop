#!/usr/bin/perl -w


#$Id$
# deduces the user names from the family names
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


#the access hash
# TODO learn how to do hashes properly in perl
%access = (
	ACCESS_NONE => 0,
	ACCESS_SUMMARY => 100,
	ACCESS_VIEW => 200,
	ACCESS_EDIT => 500,
	ACCESS_ADD => 600,
	ACCESS_DELETE => 700,
	ACCESS_ADMIN => 800
);

	#default privs
@defaults =  (
	[ $access{'ACCESS_DELETE'}, "invitations" ],
	[ $access{'ACCESS_DELETE'}, "donations" ],
	[ $access{'ACCESS_ADD'}, "donations" ]
);

#basic login and housekeeping stuff
$dbh = DBI->connect("DBI:mysql:coop:bc", "input", "test" )
    or die "can't connect to database $!\n";


#approximate list of families
$rquery = "select families.name, families.familyid
		from families 
			left join leads on families.familyid = leads.familyid
		left join kids on kids.familyid = families.familyid
		left join attendance on attendance.kidsid = kids.kidsid
		left join enrol on enrol.enrolid = attendance.enrolid
		where attendance.dropout is NULL
	group by families.familyid\n";
#print "doing <$rquery>\n"; #XXX debug only
$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
$rqueryobj->execute() or die "couldn't execute $!\n";

while ($ritemref = $rqueryobj->fetchrow_hashref){
	%ritem = %$ritemref;
	$familyid = $ritem{'familyid'};
	$name = $ritem{'name'};

	print "adding <$name> into users\n";

	#ok, fix em!
	$query = "insert into users 
			set familyid = $familyid, name = \'$name Family\' 
		";
	print "doing <$query>\n";
#	print STDERR $dbh->do($query) . "\n"; #THIS DOES IT!

} # end while


$dbh->disconnect or die "couldnt' disconnect from dtatbase $!\n";

#EOF
