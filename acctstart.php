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
  
require_once("settings.php");
require_once("functions.php");

while( $input = readline() ) {
    
  $pid = pcntl_fork();
  if ($pid === -1) { die(); }
  elseif ($pid === 0) {
    $re1 = '\d (\S+) \[.*The new session \"(.+)\" has been created.';
    if ($c=preg_match_all ("/".$re1."/is", $input, $matches))
    {
      	$softetherip=$matches[1][0];
	$sessid=$matches[2][0];
    }
    
    if (empty($sessid)) { exit; }
    
    $result = getsessiondata($sessid); // get session details from HUB
    $framedip = getdhcpip($sessid); // get DHCP assigned IP from HUB 
    
    if ($framedip === FALSE) { // if user could not get ip with dhcp, disconnect it 
      disconnectsession($sessid);
      exit; 
    }
    
    $db = new SQLite3($database);
    $db->busyTimeout(5000);
    $db->exec('CREATE TABLE IF NOT EXISTS sessions (sessionid varchar(255), username varchar (255), clientip varchar (255), inputoctets varchar (255), ' .
              'outputoctets varchar (255), framedip varchar (255), nasip varchar (255), nasport varchar (255), acctstarttime varchar (255), '.
              'acctsessiontime varchar (255), PRIMARY KEY(sessionid))');
    $query = $db->escapeString('INSERT OR REPLACE INTO sessions (sessionid, username, clientip, inputoctets, outputoctets, framedip, nasip, nasport, acctstarttime, acctsessiontime) VALUES ("'.$sessid.'","'.$result["User Name (Authentication)"].'","'.$result["Client IP Address"].'",NULL,NULL,"'.$framedip.'","'.$result["Server IP Address (Reported)"].'","'.$result["Server Port (Reported)"].'","'.$result["Connection Started at"].'",NULL)');
    $db->exec($query);
  
    $sessid = $db->escapeString($sessid);
    $results = $db->querySingle("SELECT * FROM sessions WHERE sessionid = '".$sessid."'", true);
  
    $acctsessionid = md5($sessid.$results['acctstarttime']);
    $tmpfname = tempnam($tmpdir, "acctstarttmp_");
    $handle = fopen($tmpfname, "w");
  
    $packet = "Service-Type = Framed-User"."\n".
              "Framed-Protocol = PPP"."\n".
              "NAS-Port = ".$results['nasport']."\n".
              "NAS-Port-Type = Async"."\n".
              "User-Name = '".$results['username']."'"."\n".
              "Calling-Station-Id = '".$results['clientip']."'"."\n".
              "Called-Station-Id = '".$results['nasip']."'"."\n".
              "Acct-Session-Id = '".$acctsessionid."'"."\n".
              "Framed-IP-Address = ".$results['framedip']."\n".
              "Acct-Authentic = RADIUS"."\n".
              "Event-Timestamp = ".time()."\n".
              "Acct-Status-Type = Start"."\n".
              "NAS-Identifier = '".$results['nasip']."'"."\n".
              "Acct-Delay-Time = 0"."\n". // handle?
              "NAS-IP-Address = ".$results['nasip']."\n";
    fwrite($handle, $packet);
    fclose($handle);

    exec("radclient ".$radiussrv.":".$radiusport." acct ".$radiuspass." -f ".$tmpfname);
    unlink($tmpfname);
  
    $db->close();
    exit(0);
  }
}

?>
