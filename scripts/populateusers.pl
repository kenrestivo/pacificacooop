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
use Getopt::Std;


getopts('vth:p:') or &usage();


#the access hash
# in perl, the hash keys should NOT be quoted, or all hell will break loose!!
%access = (
	ACCESS_NONE => 0,
	ACCESS_SUMMARY => 100,
	ACCESS_VIEW => 200,
	ACCESS_VIEWALL => 300,
	ACCESS_EDIT => 500,
	ACCESS_ADD => 600,
	ACCESS_DELETE => 700,
	ACCESS_ADMIN => 800
);

#default privs for all families
@familydefaults =  (
	[ $access{'ACCESS_DELETE'}, "invitations" ],
	[ $access{'ACCESS_DELETE'}, "auction" ],
	[ $access{'ACCESS_EDIT'}, "roster" ]
);

@teacherdefaults =  (
	[ $access{'ACCESS_VIEW'}, "roster" ],
	[ $access{'ACCESS_DELETE'}, "insurance" ]
);

### main code starts here

$host = $opt_h ? $opt_h : "bc";
$port = $opt_p ? ":$opt_p" : "";
#basic login and housekeeping stuff
$dbh = DBI->connect("DBI:mysql:coop:$host$port", "input", "test" )
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
if($opt_v){
	print "doing <$rquery>\n"; 
}
$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
$rqueryobj->execute() or die "couldn't execute $!\n";

while ($ritemref = $rqueryobj->fetchrow_hashref){
	%ritem = %$ritemref;

	$uid = &addUser($ritem{'name'}. " Family", $ritem{'familyid'});
	&addDefaultPrivs($uid, \@familydefaults);

} # end while


#add users for teacher sandy, teacher pat, teacher catherine
foreach $teacher ("Teacher Sandy", "Teacher Catherine", "Teacher Pat"){
	$uid = &addUser($teacher);
	&addDefaultPrivs($uid, \@teacherdefaults);
}

$dbh->disconnect or die "couldnt' disconnect from dtatbase $!\n";

exit 0;
#END MAIN
########################


sub
addDefaultPrivs()
{
	my $uid = shift;
	my $defref = shift;
	my $arref;
	my $query;

	#NOW, add the privs
	foreach $arref (@$defref){
		$query = sprintf("insert into privs 
				set userid = %d, authlevel = %d, realm = '%s' ", 
			$uid, $$arref[0], $$arref[1]);
		if($opt_v){
			print "doing <$query>\n";
		}
		unless($opt_t){
			print STDERR $dbh->do($query) . "\n"; #THIS DOES IT!
		}
	}

}

sub
addUser()
{
	my $name = shift;
	my $familyid = shift;
	my $query;

	print "adding <$name> into users\n";

	#ok, add the users
	$query = sprintf("insert into users  set
			name = \'%s\' ,
			familyid = %d
			",
			$name, $familyid);
	if($opt_v){
		print "doing <$query>\n";
	}
	unless($opt_t){
		print STDERR $dbh->do($query) . "\n"; #THIS DOES IT!
	}
	
	#find out what we got for a uid
	return $dbh->{'mysql_insertid'};

}

sub usage()
{
    print STDERR "usage: $0 -v -t\n";
    print STDERR "\t-v verbose \n";
    print STDERR "\t-t test (don't actually update db) \n";
    print STDERR "\t-h hostname of db\n";
    print STDERR "\t-p port db is running on\n";
	exit 1;
}

#EOF
