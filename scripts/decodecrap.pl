#!/usr/bin/perl
#$Id$

use HTML::Entities;
use DBI;
use Getopt::Std;
use strict;

our($opt_t, $opt_v, $opt_h, $opt_p, $opt_d, $opt_u, $opt_s); #loathe perl
getopts('vrth:p:d:u:s:') or &usage();
our($dbh);

&main();

##END OF MAIN CODE
###############


sub
main()
{

	my $host = $opt_h ? $opt_h : "bc";
	my $user = $opt_u ? $opt_u : "root";
	my $pw = $opt_s ? $opt_s : "secret";
	my $port = $opt_p ? ":$opt_p" : "";
	my $dbname = $opt_d ? $opt_d : "coop_dev";

	#basic login and housekeeping stuff
	$dbh = DBI->connect("DBI:mysql:$dbname:$host$port", $user, $pw )
		or die "can't connect to database $!\n";

	&tester();

	$dbh->disconnect or die "couldnt' disconnect from database $!\n";

}

########################
#	EACHTABLE
#	surf each table.
########################
sub 
eachTable()
{
	my $addref = shift;
	my $rqueryobj ;
	my %ritem ;
	my $ritemref ;
	my $key ;

	$rqueryobj = $dbh->table_info('%', '%', '%');
	#my $rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
	$rqueryobj->execute() or die "couldn't execute $!\n";

	while ($ritemref = $rqueryobj->fetchrow_hashref){
		%ritem = %$ritemref;
		if($opt_v){
			foreach $key (keys %ritem) {
				printf("%s -> %s\n", $key, $ritem{$key} ? $ritem{$key} : "");
			}
			print("\n");
		}
		if($ritem{'TABLE_TYPE'} ne 'TABLE'){
			printf("%s is not a table\n", $ritem{'TABLE_NAME'});
			next;
		}
		&eachField($ritem{'TABLE_NAME'});
	} # end while
} #END EACHTABLE


########################
#	EACHFIELD
#	surf each field. tedious and ugly.
########################
sub
eachField()
{
	my $tablename = shift;
	my $rquery;
	my %ritem;
	my $ritemref;
	my $rqueryobj;

	$rquery = "explain $tablename";
	if($opt_v){
		print "doing <$rquery>\n"; #XXX debug only
	}
	$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
	$rqueryobj->execute() or die "couldn't execute $!\n";

	while ($ritemref = $rqueryobj->fetchrow_hashref){
		%ritem = %$ritemref;
		printf ("checking <%s>\n", $ritem{'Field'});
	} # end while

} #END EACHFIELD


########################
#	TESTER
#	a silly thign just to test my logic
########################
sub
tester()
{
	my @af = (  "Mr &amp; Mrs" ,  
			'PartyLite Candles, Snuffer, and Holder \&quot;inspired by memories of ice cream sundaes on warm summer evenings.\&quot;  Includes a Sundae Pillar Holder, which can be used for candles or ice cream, an Ice Cream Scoop Snuffer, a pair of 3\&quot; Ball Candles, and a dozen Tealight Candles.', 
			"there's nothing wrong with this one", 
			'this "should" be ok, if ungrammatical',
			'Memory game with the children\\\\\\\'s pictures.  One set for the AM and one set for the PM.  Can sell each game for $5.00 - $10.00 each so that each f amily can purchase one.'

	);
	my $a;

	foreach $a (@af){
		$a =~ s/\\//g; # get rid of the OLD, ugly, unneeded ones
		$a = decode_entities($a);
		$a = $dbh->quote($a);	# and now PUT IT BACK! you'll need this b4 saving
		printf ("\nnow <%s>\n", $a );
	}
} #END TESTER

##EOF
