#!/usr/bin/perl -w


#$Id$
# add the update stuff

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

getopts('vth:p:d:') or &usage();


$host = $opt_h ? $opt_h : "bc";
$port = $opt_p ? ":$opt_p" : "";
$dbname = $opt_d ? $opt_d : "coop_dev";
#basic login and housekeeping stuff
$dbh = DBI->connect("DBI:mysql:$dbname:$host$port", "input", "test" )
    or die "can't connect to database $!\n";

@adds = ("audituserid int(32)", "entered datetime", "updated timestamp");

&addall(\@adds);

$dbh->disconnect or die "couldnt' disconnect from dtatbase $!\n";

##END OF MAIN CODE
###############

sub addall {
	$addref = shift;
	my $rqueryobj ;
	my %ritem ;
	my $ritemref ;
	my %key ;

	$rqueryobj = $dbh->table_info('%', '%', '%');
	#my $rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
	$rqueryobj->execute() or die "couldn't execute $!\n";

	while ($ritemref = $rqueryobj->fetchrow_hashref){
		%ritem = %$ritemref;
		if($opt_v){
			foreach $key (keys %ritem) {
				printf("%s -> %s\n", $key, $ritem{$key});
			}
			print("\n");
		}
		if($ritem{'TABLE_TYPE'} ne 'TABLE'){
			next;
		}
		unless($opt_t){
			&addthem($ritem{'TABLE_NAME'}, \@adds);
		}
	} # end while
} #END ADDALL

sub addthem {
	my $tablename = shift;
	my $addref = shift;
	my $query;
	#approximate list of families
	#ok, fix em!
	$query = "this is the $tablename query";
	if($opt_v){
		print "doing <$query>\n";
	}
	unless($opt_t){
		print STDERR $dbh->do($query) . "\n";
	}
} #END ADDTHEM


sub usage()
{
    print STDERR "usage: $0 -v -t\n";
    print STDERR "\t-v verbose \n";
    print STDERR "\t-t test (don't actually update db) \n";
    print STDERR "\t-h hostname of db\n";
    print STDERR "\t-p port db is running on\n";
    print STDERR "\t-d database name\n";
	exit 1;
}

#EOF
