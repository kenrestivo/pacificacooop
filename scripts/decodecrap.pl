#!/usr/bin/perl
#$Id$

use HTML::Entities;
use DBI;

@af = (  "Mr &amp; Mrs" ,  
		'PartyLite Candles, Snuffer, and Holder \&quot;inspired by memories of ice cream sundaes on warm summer evenings.\&quot;  Includes a Sundae Pillar Holder, which can be used for candles or ice cream, an Ice Cream Scoop Snuffer, a pair of 3\&quot; Ball Candles, and a dozen Tealight Candles.', 
		"there's nothing wrong with this one", 
		'this "should" be ok, if ungrammatical',
		'Memory game with the children\\\\\\\'s pictures.  One set for the AM anJ d one set for the PM.  Can sell each game for $5.00 - $10.00 each so that each f amily can purchase one.'

);

foreach $a (@af){
	$a =~ s/\\//g; # get rid of the OLD, ugly, unneeded ones
	$a = decode_entities($a);
	$a = $dbh->quote($a);	# and now PUT IT BACK! you'll need this b4 saving
	printf ("\nnow <%s>\n", $a );
}


##EOF
