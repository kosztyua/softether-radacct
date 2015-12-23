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
    $re1 = '\d (\S+) \[.*Session \"(.+)\": The session has been terminated.';
    if ($c=preg_match_all ("/".$re1."/is", $input, $matches))
    {
      $softetherip=$matches[1][0];
      $sessid=$matches[2][0];
    }
  
    $delimiter1 = "outgoing data size:";
    $delimiter2 = "bytes,";
    $pos1 = strpos($input, $delimiter1) + strlen($delimiter1) + 1;
    $pos2 = strpos($input, $delimiter2) - 1;
    $sstrlen = $pos2 - $pos1;
    $outdata = substr($input, $pos1, $sstrlen);
  
    $delimiter1 = "incoming data size:";
    $delimiter2 = "bytes.";
    $pos1 = strpos($input, $delimiter1) + strlen($delimiter1) + 1;
    $pos2 = strpos($input, $delimiter2) - 1;
    $sstrlen = $pos2 - $pos1;
    $indata = substr($input, $pos1, $sstrlen);
  
    $db = new SQLite3($database);
    $db->busyTimeout(5000);
    $sessid = $db->escapeString($sessid);
    $results = $db->querySingle("SELECT * FROM sessions WHERE sessionid = '".$sessid."'", true);
    if($results == FALSE) { die("Error - could not find sessionid");} // not good, session may remain zombie in RADIUS, but cannot properly identify to close so meh - maybe close all sessions for user and wait for next interim-update to fix?
  
    list($time1,,$time2) = explode(" ",$results['acctstarttime']);
    $sessiontime = time() - strtotime($time1." ".$time2);
  
    $acctsessionid = md5($sessid.$results['acctstarttime']);
  
    $tmpfname = tempnam($tmpdir, "acctstoptmp_");
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
              "Acct-Session-Time = ".$sessiontime."\n".
              "Acct-Input-Octets = ".$indata."\n".
              "Acct-Output-Octets = ".$outdata."\n".
              "Acct-Status-Type = Stop"."\n".
              "NAS-Identifier = '".$results['nasip']."'"."\n".
              "Acct-Delay-Time = 0"."\n".
              "NAS-IP-Address = ".$results['nasip']."\n";
    fwrite($handle, $packet);
    fclose($handle);
    exec("radclient ".$radiussrv.":".$radiusport." acct ".$radiuspass." -f ".$tmpfname);
    unlink($tmpfname);

    $db->exec("DELETE FROM sessions WHERE sessionid = (SELECT sessionid FROM sessions WHERE sessionid = '".$sessid."' LIMIT 1)");
    $db->close();
    exit(0);
  }
}

?>
