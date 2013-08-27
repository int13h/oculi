#!/usr/local/bin/php
<?php

//
//
// This file is part of oculi
//
// Copyright (C) 2011-2012, Paul Halliday <paul.halliday@gmail.com>
//                          Sacha Evans <sacha.evans@nscc.ca>
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.
//
//

$debug = 0;
$timestamp = date('Y-m-d H:i:s');
$base = dirname(__FILE__);
$patch_dir = '/data/PATCHES';
include_once "$base/config.php";

// DB Connect
$db = mysql_connect($dbHost,$dbUser,$dbPass) or die();
mysql_select_db($dbName) or die();

function formatStamp($dateTime,$type) {
    
    $pattern = '/^(\d{4}-\d{1,2}-\d{1,2}|\d{1,2}\/\d{1,2}\/\d{4}) \d{2}:\d{2}$|\d{2}:\d{2}:\d{0,2}$/';

    preg_match($pattern, $dateTime, $match);

    if (count($match) > 0) {

        $offset = date("Z");
        switch ($type) {
            case 0: $format = 'y-m-d H:i:s'; break;
            case 1: $format = 'd H:i'; break;
            case 2: $format = 'G'; break;
            case 3: $format = 'm-d'; break;
            case 4: $format = 'H:i:s'; break;

        }

        if ($offset === 0) {

           $answer = date($format,strtotime($dateTime));

        } else {

           $answer = date($format,strtotime($dateTime . "$offset seconds"));

        }

    } else {

        $answer = '';

    }

    return $answer;

}

function processUpdates($line) {
    //Node,Description,FixComments,HotFixID,InstallDate,InstalledBy,InstalledOn,Name,ServicePackInEffect,Status
    $x = explode(",", $line);

    // For XP, we get variable results so just make everything an update
    if ($x[2] == "Update") {
        $update_type            = "Security Update";
    } else {
        $update_type		= $x[1];
    }
    $update_id			= $x[3];
    $update_installed_by	= str_replace("\\", "\\\\", $x[5]);
    $update_installed_on	= date('Y-m-d', strtotime($x[6]));
    $answer	   		= "$update_type,$update_id,$update_installed_by,$update_installed_on";
    
    return $answer;
}

function processIP($line) {

    $x = explode(",", $line);

    // Check and verify IP address
    $toStrip            = array("{","}");
    $ip			= str_replace($toStrip, "", $x[1]);
    $pattern		= '/(^\d+\.\d+\.\d+\.\d+)/';
    preg_match($pattern, $ip, $match);

    if (count($match) > 0) {
        $ip = $match[0];
    } else {
        $ip = "0.0.0.0";
    }
    // Check and verify MAC address
    $mac		= $x[3];
    $pattern		= '/(^[0-9A-f]{2}:[0-9A-f]{2}:[0-9A-f]{2}:[0-9A-f]{2}:[0-9A-f]{2}:[0-9A-f]{2})/';
    preg_match($pattern, $mac, $match);

    if (count($match) > 0) {
        $mac = $match[0];
    } else {
        $mac = "FF:FF:FF:FF:FF:FF";
    }
    
    // Convert to integer
    $ip = ip2long($ip);
    $answer = "$ip,$mac";
    
    return $answer;
}

function processAV($line) {
    
    // AV Result,1.1.7801.0,1.115.1028.0,11/1/2011 2:18:59 PM,1.115.1028.0,11/1/2011 2:18:59 PM,11/2/2011 3:10:28 AM
    $x			= explode(",", $line);
    $engine_version	= $x[1];
    $assig_version	= $x[2];
    $assig_applied	= formatStamp($x[3],0);
    $avsig_version	= $x[4];
    $avsig_applied	= formatStamp($x[5],0);
    $last_scan		= formatStamp(rtrim($x[6]),0);
    $answer		= "$engine_version,$assig_version,$assig_applied,$avsig_version,$avsig_applied,$last_scan";

    return $answer;
}

function processINV($line) {

    // Inventory,Dell Inc.,OptiPlex 745,GSQHPC1,,Intel(R) Core(TM)2 CPU 6300 @ 1.86GHz,2,75
    $line               = preg_replace('!\s+!', ' ', $line);
    $x			= explode(",", $line);
    $manufacturer	= rtrim($x[1]);
    $model		= rtrim($x[2]);
    $serial_number	= rtrim($x[3]);
    $asset_tag		= rtrim($x[4]);
    $processor          = rtrim($x[5]);

    $pattern = '/(\d+\.\d{2}[G|M]Hz$)/';
    preg_match($pattern, $processor, $match);

    if (count($match) > 0) {
        $remove = array('GHz','MHz');
        $frequency = str_replace($remove, '', $match[0]);
    } else {
        $frequency = "0.00";
    }
    
    $memory		= $x[6];
    $storage		= $x[7];
    $answer		= "$manufacturer,$model,$serial_number,$asset_tag,$processor,$frequency,$memory,$storage";
    
    return $answer;
}

function processFlash($line) {
    $x			= explode("||", $line);
    $flashplayer	= str_replace("FlashPlayer: ", "", $x[1]); 
    $flashplayeractivex = str_replace("FlashPlayerActiveX: ", "", $x[2]);
    $flashplayerplugin	= str_replace("FlashPlayerPlugin: ", "", $x[3]);
    $answer		= "$flashplayer||$flashplayeractivex||$flashplayerplugin";
    
    return $answer;  
}

function processJava($line) {
    $x			= explode("||", $line);
    $browserjava	= str_replace("BrowserJavaVersion: ", "", $x[1]);
    
    return $browserjava;
}

function processWinver($matches) {

    // Versions
    $osVersions = array(
                   1	=> "Microsoft Windows XP Professional",
                   2	=> "Microsoft Windows Server 2003 Standard Edition",
                   3	=> "Microsoft Windows Server 2003 Enterprise Edition",
                   4	=> "Microsoft Windows 7 Enterprise",
                   5	=> "Microsoft Windows 7 Professional",
                   6	=> "Microsoft Windows Server 2008 Enterprise",
                   7	=> "Microsoft Windows Server 2008 Standard",
                   8	=> "Microsoft Windows Server 2008 R2 Enterprise",
                   9	=> "Microsoft Windows Server 2008 R2 Standard",
                   10	=> "Microsoft Windows Embedded Standard"
    );
     
    list($_osVersion, $osStatus) = explode("|", $matches);
    $_osVersion = rtrim($_osVersion);

    $needle = array_search($_osVersion, $osVersions);
    if (!$needle) {
        $needle = 0;
    }
    
    return $needle;

}

function processFile($file,$time,$host,$timestamp) {

    global $db, $counter, $debug;

    // Defaults
    $updateResult   = array();
    $ipResult       = "0.0.0.0,FF:FF:FF:FF:FF:FF";
    $avResult       = "0,0,0000-00-00,0,0000-00-00,0000-00-00";
    $flashResult    = "Unknown||Unknown||Unknown";
    $javaResult     = "Unknown";
    $osVersion      = 0;
    $ipHit          = 0;
    $service_pack   = 0;
    $hostState      = 0;
    $location	    = 0;
    $user_id	    = 'Unknown';
    $admin_group    = 'Unknown';

    // Check sanity of file (this needs to be more robust) 
    if(filesize($file) < 1000) {
        $hostState = 1;
    }

    // If the file is suffixed with "_r" then we do a few more checks
    if (substr($file, -1) == 'r') {
        $location = 1;
    }
    
    if (($handle = fopen("$file", "r")) !== FALSE) {
        
        while (($line = fgets($handle, 5000)) !== FALSE) {

            // Remove bad chars
            $toRm = array("\0","\256");
            $line = str_replace($toRm, "", $line);
            $fields = explode(',', $line);

            // What type of info are we reading?
            switch ($fields[0]) {
                case $host:

                    // Match KB lines
                    $kbCheck = explode(" ", $fields[1]);
                    if ($kbCheck[0] == "Service" || $kbCheck[0] == "Security" || $kbCheck[0] == "Update") {
                        if ($fields[1] == "Service Pack") {
                            $service_pack = $fields[3];
                        }

                        $updateResult[] = processUpdates($line);
                        break;
                    }

                    // Match IP lines. Can be multiple hits but we just want 1: 10.x and enabled.
                    if (preg_match("/{/i", $fields[1]) && $ipHit == 0) {

                        // See if it is enabled
                        $isEnabled = explode(",", $line);
                        if ($isEnabled[2] == "TRUE") {
                            if (!preg_match("/^00:(0|1|5)(0|5|(c|C)):(1|2|5|6)(4|6|9)/", $isEnabled[3])) {
                                $ipResult = processIP($line);
                                $ipHit = 1;
                                break;
                            }
                        } else {
                            break;
                        }
                    }

                    // Match OS Version line
                    if (preg_match("/(^\S+,)(Microsoft.*\|.*$)/", $line, $matches)) {

                        $osVersion = processWinver($matches[2]);
                        break;

                    }
                                             
                    break;
        
                case "AV Result":
                    $avResult = processAV($line);
                    break;

                case "Inventory":
                    $invResult = processINV($line);
                    break;

                case "LastLoggedOnUser":
                    $user_id = addslashes(str_replace("LastLoggedOnUser,", "", $line));
                    break;

                case "InAdminGroup":
                    $admin_group = str_replace("InAdminGroup,", "", $line);
                    break;
              
                case "Flash":
                    $flashResult = processFlash($line);
                    break;

                case "Java":
                    $javaResult = processJava($line);
                    break;

                default:
                    break;
            }
        }

        // Close and delete
        fclose($handle);
        unlink($file);

    }

    // Do our inserts. These are conditional on whether or not a result was returned.
    if ($debug == 0) {
        // Hostinfo insert
        list ($ip,$mac) = explode(",", $ipResult);
        $fullTime = $timestamp . " " . str_replace("-", ":", $time);
        mysql_query("DELETE FROM updates WHERE hostname = \"$host\"");
        mysql_query("INSERT INTO host_info (timestamp,hostname,mac,ip,version,service_pack,age,status,location)
                                                  
                         VALUES (\"$fullTime\",\"$host\",\"$mac\",\"$ip\",\"$osVersion\",\"$service_pack\",\"$timestamp\",\"$hostState\",\"$location\")

                         ON DUPLICATE KEY UPDATE timestamp=\"$fullTime\",
                                                 hostname=\"$host\",
                                                 mac=\"$mac\",
                                                 ip=\"$ip\",
                                                 version=\"$osVersion\",
                                                 service_pack=\"$service_pack\",
                                                 status=\"$hostState\",
                                                 location=\"$location\"");

        // Updates insert(s)
        foreach ($updateResult as $result) {

            list ($update_type,$update_id,$update_installed_by,$update_installed_on) = explode(",",$result);

            mysql_query("INSERT INTO updates (timestamp,hostname,update_type,update_id,
                                                  update_installed_by,update_installed_on)

                         VALUES (\"$timestamp\",\"$host\",\"$update_type\",\"$update_id\",
                                 \"$update_installed_by\",\"$update_installed_on\")

                         ON DUPLICATE KEY UPDATE timestamp=\"$timestamp\",update_type=\"$update_type\",
                                                 update_id=\"$update_id\",
                                                 update_installed_by=\"$update_installed_by\",
                                                 update_installed_on=\"$update_installed_on\"");
        }
 
        // AV insert
        list ($engine_version,$assig_version,$assig_applied,$avsig_version,$avsig_applied,$last_scan) = explode(",", $avResult);
         
        mysql_query("INSERT INTO av (timestamp,hostname,engine_version,assig_version,
                                     assig_applied,avsig_version,avsig_applied,last_scan)

                     VALUES (\"$timestamp\",\"$host\",\"$engine_version\",\"$assig_version\",
                             \"$assig_applied\",\"$avsig_version\",\"$avsig_applied\",\"$last_scan\")

                     ON DUPLICATE KEY UPDATE timestamp=\"$timestamp\",engine_version=\"$engine_version\",
                                             assig_version=\"$assig_version\",assig_applied=\"$assig_applied\",
                                             avsig_version=\"$avsig_version\",avsig_applied=\"$avsig_applied\",
                                             last_scan=\"$last_scan\"");

        // Software versions
        list ($flashplayer,$flashplayeractivex,$flashplayerplugin) = explode("||", $flashResult);
        $browserjava = $javaResult;

        mysql_query("INSERT INTO software (timestamp,hostname,flashplayer,flashplayeractivex,flashplayerplugin,browserjava)

                     VALUES (\"$timestamp\",\"$host\",\"$flashplayer\",\"$flashplayeractivex\",\"$flashplayerplugin\",\"$browserjava\")

                     ON DUPLICATE KEY UPDATE timestamp=\"$timestamp\",flashplayer=\"$flashplayer\",
                                             flashplayeractivex=\"$flashplayeractivex\",
                                             flashplayerplugin=\"$flashplayerplugin\",browserjava=\"$browserjava\"");

        // Asset Info insert

        if ($invResult) {
            list ($manufacturer,$model,$serial_number,$asset_tag,$processor,$frequency,$memory,$storage) = explode(',', $invResult);

            mysql_query("INSERT INTO asset (timestamp,hostname,manufacturer,model,serial_number,
                                            asset_tag,processor,frequency,memory,storage,user_id,admin_group)

                         VALUES (\"$timestamp\",\"$host\",\"$manufacturer\",\"$model\",\"$serial_number\",
                                 \"$asset_tag\",\"$processor\",\"$frequency\",\"$memory\",\"$storage\",\"$user_id\",\"$admin_group\")

                         ON DUPLICATE KEY UPDATE timestamp=\"$timestamp\",manufacturer=\"$manufacturer\",model=\"$model\",
                                                 serial_number=\"$serial_number\",asset_tag=\"$asset_tag\",
                                                 processor=\"$processor\",frequency=\"$frequency\",memory=\"$memory\",
                                                 storage=\"$storage\",user_id=\"$user_id\",admin_group=\"$admin_group\"");
        }

    } else {
        // Put debug stuff here
        echo "$ipResult\n";
        echo "$ip $mac\n";
    }

}

// Files are prefixed with a date in the format "yyyy-mm-dd" 
// If no date is provided the current day will be used.
// 2012-01-13_14-34_TRLTM21301_KB.txt

if (isset($argc)) {
    if ($argc == 1 || $argc > 3 || !preg_match("(^\d{4}-\d{2}-\d{2}$)", $argv[1])) {
        echo "\nUsage: $argv[0] <yyyy-mm-d> <site prefix>\n\n";
        exit;        
    } else {
        $base_date = $argv[1];
    }
} else {
    $base_date = date('Y-m-d');
}

// If we get a campus prefix (or hostname), use it. Otherwise, we do everything.
if ($argc == 3) {
    $site = $argv[2];
} else {
    $site = '';
}

// Get our file list

if (file_exists($patch_dir)) {

    $files = scandir("$patch_dir");
    $counter = 0;

    if ($files != false && count($files) > 2) {
        for ($i = 0, $fc = count($files) - 1; $i <= $fc; $i++) {
            if (($files[$i] != ".") && ($files[$i] != "..")) {
                if (preg_match("/${base_date}_\d{2}-\d{2}_${site}/i", $files[$i])) {
                    $fileParts = explode("_", $files[$i]);
                    processFile("$patch_dir/$files[$i]",$fileParts[1],$fileParts[2],$base_date);
                    $counter++;
                }
            }
        }
        echo "\nProcessed: $counter\n";
    }
} else {
    echo "\nError: The directory \"${patch_dir}\" does not exist.\n";
}

?>

