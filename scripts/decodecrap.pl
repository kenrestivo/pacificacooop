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

	#&tester();
	&eachTable();

	$dbh->disconnect or die "couldnt' disconnect from database $!\n";

}

########################
#	EACHTABLE
#	surf each table.
########################
sub 
eachTable()
{
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
		printf("checking table <%s>\n",  $ritem{'TABLE_NAME'} );
		&eachField($ritem{'TABLE_NAME'}, &findKey($ritem{'TABLE_NAME'}));
	} # end while
} #END EACHTABLE


########################
#	FINDKEY
#	find the primary key field
########################
sub
findKey()
{
	my $tablename = shift;
	my $keyname ;
	my $rquery;
	my %ritem;
	my $ritemref;
	my $rqueryobj;

	$rquery = "explain $tablename";
	if($opt_v){
		print "findKey(): doing <$rquery>\n"; 
	}
	$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
	$rqueryobj->execute() or die "couldn't execute $!\n";

	while ($ritemref = $rqueryobj->fetchrow_hashref){
		%ritem = %$ritemref;
		if($ritem{'Key'} eq "PRI"){
			$keyname = $ritem{'Field'};
			printf ("\tgot key <%s> for %s\n", $keyname, $tablename);
		}
	} # end while

	return $keyname

} #END FINDKEY

########################
#	EACHFIELD
#	surf each field. tedious and ugly.
########################
sub
eachField()
{
	my $tablename = shift;
	my $keyname = shift;
	my $rquery;
	my %ritem;
	my $ritemref;
	my $rqueryobj;

	$rquery = "explain $tablename";
	if($opt_v){
		print "eachField(): doing <$rquery>\n"; 
	}
	$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
	$rqueryobj->execute() or die "couldn't execute $!\n";

	while ($ritemref = $rqueryobj->fetchrow_hashref){
		%ritem = %$ritemref;
		printf ("\tchecking field <%s>\n", $ritem{'Field'});
		&findUglies($tablename, $ritem{'Field'}, $keyname);
	} # end while

} #END EACHFIELD


########################
#	FINDUGLIES
#	seatch each field lookign for uglies
########################
sub
findUglies()
{
	my $tablename = shift;
	my $fieldname = shift;
	my $keyname = shift;
	my $rquery;
	my %ritem;
	my $ritemref;
	my $rqueryobj;

	$rquery = "select * from $tablename
				where $fieldname like \"%&%;%\" or
					$fieldname like \"%\\\\\\\\%\"
		";
	if($opt_v){
		print "findUglies(): doing <$rquery>\n"; 
	}
	$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
	$rqueryobj->execute() or die "couldn't execute $!\n";

	while ($ritemref = $rqueryobj->fetchrow_hashref){
		%ritem = %$ritemref;
		printf ("found problem with <%s> %s %d: <%s>\n", 
			$fieldname, $keyname, $ritem{$keyname}, $ritem{$fieldname});
		&repairUglies($tablename, $fieldname, $keyname, 
				$ritem{$keyname}, $ritem{$fieldname});
	} # end while

} #END FINDUGLIES


########################
#	REPAIRUGLIES
#	go ahead and replace the field with a now-workign one
########################
sub
repairUglies()
{
	my $tablename = shift;
	my $fieldname = shift;
	my $keyname = shift;
	my $id = shift;
	my $text = shift;
	my $fixed ;
	my $rquery;
	my %ritem;
	my $ritemref;
	my $rqueryobj;

	#put on the condom! a missing indiex could screw me up, bad!
	$tablename && $fieldname && $text && $keyname && $id 
		or die "HEY!! bad args to repairUglies()!\n";

	$fixed = &fixit($text);

	#NOTE! i don't need to quote the $fixed: dbh->quote() does it 4 me
	$rquery = "update $tablename set $fieldname = $fixed 
				where $keyname = $id LIMIT 1
		";
	if($opt_v){
		print "repairUglies(): doing <$rquery>\n"; 
	}
	#FINALLY! this actually DOES something, and fixes the thing
	unless ($opt_t){
		$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
		$rqueryobj->execute() or die "couldn't execute $!\n";
	}

} #END REPAIRUGLIES
########################
#	FIXIT
#	ok, well, fix the damn thing and return the repaired string.
########################
sub
fixit()
{
	my $a = shift;

	$a =~ s/\\//g; # get rid of the OLD, ugly, unneeded ones
	$a = decode_entities($a);
	$a = $dbh->quote($a);	# and now PUT IT BACK! you'll need this b4 saving

	if($opt_v){
		printf ("\nfixit(): now <%s>\n", $a );
	}

	return $a;

} #END FIXIT


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
		printf ("\nnow <%s>\n", &fixit($a) );
	}
} #END TESTER

##EOF
