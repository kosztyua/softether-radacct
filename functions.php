<?php

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
 
function getsessiondata($sessid) {
  global $vpncmd, $softetherip, $hubname, $apipass;
  exec($vpncmd." ".$softetherip." /SERVER /HUB:".$hubname." /PASSWORD:".$apipass." /CSV /CMD SessionGet ".$sessid,$SessionGet);
  if(strpos($SessionGet[0],"rror occurred") != FALSE) { die("Error - SessionGet resulted in error"); }
  foreach ($SessionGet as $line){
    list($key,$val) = explode(",",$line,2);
    $result[$key] = $val;
  }
  return $result;
}

function getdhcpip($sessid) {
  global $vpncmd, $softetherip, $hubname, $apipass;
  $dhcpok = 0;
  for ($i=0;$i<5;$i++) {
  exec($vpncmd." ".$softetherip." /SERVER /HUB:".$hubname." /PASSWORD:".$apipass." /CSV /CMD IpTable", $IpTable);
    foreach ($IpTable as $line){
      if(strpos($line,$sessid)){
        if(strpos($line,"DHCP")){
          list(,$key,$val) = explode(",",$line);
          list($framedip) = explode(" ",$val);
          $dhcpok=1;
        }
      }
    }
    if ($dhcpok === 1) { return $framedip; }
    sleep(1);  
  }
  return FALSE;
}

function disconnectsession($sessid) {
  global $vpncmd, $softetherip, $hubname, $apipass;
  exec($vpncmd." ".$softetherip." /SERVER /HUB:".$hubname." /PASSWORD:".$apipass." /CMD SessionDisconnect ".$sessid, $output);
}

pcntl_signal(SIGCHLD, SIG_IGN);
