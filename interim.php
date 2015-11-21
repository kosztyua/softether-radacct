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
require_once("settings.php");
require_once("functions.php");

exec("vpncmd ".$softetherip." /SERVER /HUB:".$hubname." /PASSWORD:".$apipass." /CSV /CMD SessionList", $SessionList);
$sessids = array();
foreach ($SessionList as $index=>$line){
  if(!strpos($line,"Local Bridge") && !strpos($line,"SecureNAT Session") && !strpos($line,"User Name")){
    list($sessids[$index]) = explode(",",$line);
  }
}
if(count($sessids)==0){die("No sessions open");}

$db = new SQLite3($database);
$db->busyTimeout(5000);
foreach ($sessids as $sessid){
  exec("vpncmd ".$softetherip." /SERVER /HUB:".$hubname." /PASSWORD:".$apipass." /CSV /CMD SessionGet ".$sessid, $SessionGet);
  if(strpos($SessionGet[0],"rror occurred") != FALSE) { continue; } // hmm 
  foreach ($SessionGet as $line){
    list($key,$val) = explode(",",$line,2);
    $sessiondata[$sessid][$key] = $val;
  }

  $sessid = $db->escapeString($sessid);
  $results = $db->querySingle("SELECT * FROM sessions WHERE sessionid = '".$sessid."'", true);
  if($results == FALSE) // if local accounting does not have session, should be disconnected immediately 
  { 
    disconnectsession($sessid);
    break; // jump to next session
  }

  list($time1,,$time2) = explode(" ",$results['acctstarttime']);
  $sessiontime = time() - strtotime($time1." ".$time2);

  $replace1 = array(",","bytes"," ","\"");
  $indata = str_replace($replace1,"",$sessiondata[$sessid]['Incoming Data Size']);
  $outdata = str_replace($replace1,"",$sessiondata[$sessid]['Outgoing Data Size']);

  $acctsessionid = md5($sessid.$results['acctstarttime']);

  $tmpfname = tempnam($tmpdir, "interimtmp_");
  $handle = fopen($tmpfname, "w");

  $query = "UPDATE sessions SET inputoctets = '" . $indata . "', outputoctets = '" . $outdata . "', acctsessiontime = '" . $sessiontime . "' WHERE sessionid = '" . $sessid . "'";
  $db->exec($query);

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
            "Acct-Status-Type = Interim-Update"."\n".
            "NAS-Identifier = '".$results['nasip']."'"."\n".
            "Acct-Delay-Time = 0"."\n".
            "NAS-IP-Address = ".$results['nasip']."\n";
  fwrite($handle, $packet);
  fclose($handle);
  exec("radclient ".$radiussrv.":".$radiusport." acct ".$radiuspass." -f ".$tmpfname);
  unlink($tmpfname);
}
$db->close();

?>

