SoftEther RADIUS accounting PHP script
Copyright (C) 2015 Andras Kosztyu (kosztyua@vipcomputer.hu)
#####
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#####
 
So I thought I'm not going to wait for others to finally create an accounting mod for SoftEther, and dnobori have no reason to further extend his work. This script is something I wrote in a weekend so I could integrate SoftEther into other parts of my system that already fully use RADIUS.

How does this work? The SoftEther server sends HUB security logs to a remote Syslog-ng server, which execute the scripts when the filter matches. These scripts do accounting start and stop, while interim update is done through cron. The parsing of the syslog is adjusted to english logs, with the softether using DHCP. 

The scripts are created for my needs, it is not guaranteed to work with your settings. They are not optimized and have not been tested in production environment.

Install a syslog-ng server and create new listener based on the example (syslog-ng_softether.conf).
Install PHP and place the PHP files somewhere. 
Modify the settings.php according to need. 
Restart syslog-ng server.
Modify the SoftEther to send syslog to the syslog-ng server.
Profit. 
