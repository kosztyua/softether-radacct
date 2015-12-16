SoftEther RADIUS accounting PHP script ( for Centos 7)

*  Install a syslog-ng server and create new listener based on the example (syslog-ng_softether.conf).
```
cd /root
wget http://dl.fedoraproject.org/pub/epel/7/x86_64/e/epel-release-7-5.noarch.rpm
rpm -Uvh /root/epel-release-7-5.noarch.rpm
yum -y install syslog-ng syslog-ng-libdbi
```
*  Install PHP and place the PHP files somewhere. 
```
yum install php -y
```
*  Install radius client utility.
```
yum install freeradius-utils -y
```
*  Modify the settings.php according to need. Add the new client to the RADIUS server.

*  Restart syslog-ng server.

*  Modify the SoftEther to send syslog to the syslog-ng server.

For interim RADIUS updates, setup a crontab with interim.php. For archiving old log files, setup a crontab with archive.php, it will gzip every logfile older than 1 day and remove older than 365 days.
