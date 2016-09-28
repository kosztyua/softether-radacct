/*
 * SoftEther RADIUS accounting PHP script
 * Copyright (C) 2015 Andras Kosztyu (kosztyua@vipcomputer.hu)
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
 
<?php

// SE specific settings
$apipass = "TotallySecretPassword1"; // softether hub password
$hubname = "HUB"; // softether hub name
$softetherip = "192.168.1.122"; // softether hub address
$vpncmd = "/usr/local/vpnserver/vpncmd";

// radius specific settings
$radiussrv = array("192.168.1.123","192.168.1.123"); // radius server addresses, 1 or more for backup
$radiuspass = "AnotherTotallySecret1"; // radius secret
$radiusport = "1813"; // radius server accounting port
$radtimeout = "5"; // radius query timeout in seconds, can be floating point number - normally should be 3, and up to 10 on slow networks
$radretry = "2"; // radius query retries in integer, if query timeouts
 
// other settings
$database = "/var/radius/sessions.db"; // temporary database location
$tmpdir = "/tmp"; // temporary directory

?>
