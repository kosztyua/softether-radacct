# Archive old logs
/usr/bin/find /var/log/remote ! -name "*.gz" -type f ! -path "*`/bin/date +%Y/%m/%d`*" -exec /bin/gzip {} \;
# Delete old archives
find /var/log/remote/ -daystart -mtime +365 -type f -exec rm {} \;
# Delete empty directories
find /var/log/remote/ -depth -type d -empty -exec rmdir {} \;
