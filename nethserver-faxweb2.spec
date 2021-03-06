Summary: NethServer web interface to Hylafax
Name: nethserver-faxweb2
Version: 1.1.1
Release: 1%{?dist}
License: GPL
Source: %{name}-%{version}.tar.gz
BuildArch: noarch
URL: https://dev.nethesis.it/projects/%{name}

BuildRequires: nethserver-devtools

AutoReq: no
Requires: ImageMagick, php-mysql
Requires: nethserver-hylafax, nethserver-httpd, nethserver-mysql

%description
NethServer web interface to Hylafax.

%prep
%setup

%post

%preun

%build
perl createlinks

%install
rm -rf $RPM_BUILD_ROOT
mkdir -p root/var/www/html/faxweb/elenco_inviati
mkdir -p root/var/www/html/faxweb/tmp
mkdir -p root/var/www/html/faxweb/uploads
mkdir -p root/var/lib/nethserver/fax/docs/thumb
mkdir -p root/var/lib/nethserver/fax/docs/sentm

(cd root   ; find . -depth -print | cpio -dump $RPM_BUILD_ROOT)

%{genfilelist} \
    --dir /var/www/html/faxweb/elenco_inviati 'attr(775, apache, apache)' \
    --dir /var/www/html/faxweb/uploads 'attr(775, apache, apache)' \
    --dir /var/www/html/faxweb/tmp 'attr(775, apache, apache)' \
    --file /var/www/html/faxweb/modem_status.pl 'attr(4755, root, apache)' \
    --file /var/www/html/faxweb/rmfax.pl 'attr(4755, root, apache)' \
    --file /var/www/html/faxweb/auth.pl 'attr(4755, root, apache)' \
    --dir /var/spool/hylafax 'attr(0755, uucp, uucp)' \
    --dir /var/spool/hylafax/etc 'attr(0755, uucp, uucp)' \
    --dir /var/lib/nethserver/fax/docs/thumb 'attr(775, apache, apache)' \
    --dir /var/lib/nethserver/fax/docs/sentm 'attr(775, apache, apache)' \
    $RPM_BUILD_ROOT > e-smith-%{version}-filelist
echo "%doc COPYING"          >> e-smith-%{version}-filelist

%clean 
rm -rf $RPM_BUILD_ROOT

%files -f e-smith-%{version}-filelist
%defattr(-,root,root)

%changelog
* Thu Mar 12 2015 Stefano Fancello <stefano.fancello@nethesis.it> - 1.1.1-1.ns6
- fix #3080 [NethServer]: if "filter by device" is enabled, no faxes are displayed on interface 
- spec: move from spec.in to spec. Refs #3009

* Thu Oct 02 2014 Davide Principi <davide.principi@nethesis.it> - 1.1.0-1.ns6
- faxweb: admins cannot see user list into "Inoltra a" window - Bug #2834 [NethServer]
- faxweb: unable to authenticate if password contains $ - Bug #2829 [NethServer]
- Faxweb: implement migration - Enhancement #2789 [NethServer]

* Mon Aug 04 2014 Davide Principi <davide.principi@nethesis.it> - 1.0.5-1.ns6
- faxweb: non-admin users unable to see their faxes - Bug #2830 [NethServer]

* Thu Jul 31 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.4-1.ns6
- Fix accounting and notify - Bug #2746

* Mon May 05 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.3-1.ns6
- Fix fax preview - Bug #2721 
- Add dashboard widget - Enhancement #2709

* Tue Mar 25 2014 Davide Principi <davide.principi@nethesis.it> - 1.0.2-1.ns6
- Fixed DB defaults: changed type to "configuration"

* Wed Feb 05 2014 Davide Principi <davide.principi@nethesis.it> - 1.0.1-1.ns6
- Rebuild for 6.5 beta3

* Tue Jun 25 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.0-1.ns6
- Port neth-faxweb2 to NethServer. Refs #1954

