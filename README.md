SoftEther RADIUS accounting PHP script
Copyright (C) 2015 Andras Kosztyu (kosztyua@vipcomputer.hu)

So I thought I'm not going to wait for others to finally create an accounting mod for SoftEther, and dnobori have no reason to further extend his work. This script is something I wrote in a weekend so I could integrate SoftEther into other parts of my system that already fully use RADIUS.

How does this work? The SoftEther server sends HUB security logs to a remote Syslog-ng server, which execute the scripts when the filter matches. These scripts do accounting start and stop, while interim update is done through cron. The parsing of the syslog is adjusted to english logs, with the softether using DHCP. 

The scripts are created for my needs, it is not guaranteed to work with your settings. They are not optimized and have not been tested in production environment.

1. Install a syslog-ng server and create new listener based on the example (syslog-ng_softether.conf).

2. Install PHP and place the PHP files somewhere. 

3. Modify the settings.php according to need. Add the new client to the RADIUS server.

4. Restart syslog-ng server.

5. Modify the SoftEther to send syslog to the syslog-ng server.

6. Profit. 
