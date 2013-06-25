#!/usr/bin/perl  
#

# Clean up our environment (we're running SUID!)
delete @ENV{qw(IFS CDPATH ENV BASH_ENV PATH)};
$< = $>;

if ($ARGV[0] =~ /([a-z0-9A-Z ]+)/) {
    $user = $1;
}
if ($ARGV[1] =~ /([0-9]+)/) {
    $id = $1;
}

system("/bin/su -s /bin/sh -c \"/usr/bin/faxrm $id\" $user");

exit 0;


