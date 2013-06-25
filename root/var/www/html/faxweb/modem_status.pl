#!/usr/bin/perl  
#

# Clean up our environment (we're running SUID!)
delete @ENV{qw(IFS CDPATH ENV BASH_ENV PATH)};
$< = $>;

system("/usr/bin/faxstat","-sl");
exit 0;

