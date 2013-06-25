#!/usr/bin/perl  
#

# Clean up our environment (we're running SUID!)
delete @ENV{qw(IFS CDPATH ENV BASH_ENV PATH)};
$< = $>;
open( FH, "| /sbin/e-smith/pam-authenticate-pw" );
print FH $ARGV[0]."\n";
print FH $ARGV[1]."\n";
close( FH ) or exit 1;
exit 0;
