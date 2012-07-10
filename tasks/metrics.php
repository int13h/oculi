#!/usr/local/bin/php
<?php

$base = dirname(__FILE__);
include "$base/config.php";
include "$base/functions.php";
$entryDate = date('Y-m-d');

// Check DB connection
cCheck();

// Locations
$siteList = array(
                 'AK'	=> "Akerley||10.9.%.%",
                 'AM'	=> "Amherst||10.30.%.%",
                 'AN'	=> "AVCM||10.25.%.%",
                 'CO'	=> "AVCL||10.41.%.%",
                 'BU'	=> "Burridge||10.39.%.%",
                 'CU'	=> "Cumberland||10.36.%.%",
                 'DI'	=> "Digby||10.20.%.%",
                 'DW'	=> "Dartmouth||10.69.%.%",
                 'IN'	=> "Institute||10.13.%.%",
                 'KI'	=> "Kingstec||10.17.%.%",
                 'LU'	=> "Lunenburg||10.15.%.%",
                 'MA'	=> "Marconi||10.37.%.%",
                 'PI'	=> "Pictou||10.19.%.%",
                 'SH'   => "Shelburne||10.11.%.%",
                 'ST'	=> "Strait Area||10.21.%.%",
                 'TR'	=> "Truro||10.43.%.%",
                 'NSCC'	=> "Servers||10.%.1.%",
                 'XX'	=> "All NSCC||Null"
);

// OS Versions
$osVersions = array(
                   "4,5"  => "Windows 7",
                   "6,7"  => "Server 2008",
                   "8,9"  => "Server 2008 R2",
                   "1"    => "Windows XP",
                   "2,3"  => "Server 2003",
                   "10"   => "Windows Embedded",
                   "0"    => "NA",
                   "42"	  => "All"
);

function shortDate($date) {

    $answer = date("Y-m-d", strtotime($date));
    return $answer;

}

function avSev($days) {

    global  $hSev, $mSev, $lSev;

    $m = max($days);

    if ($m === 0) {
        return "n_sev";
    } else {
        switch ($m) {
            case ($m <= 2): return "n_sev";
            case ($m > 10): $hSev++; return "h_sev";
            case ($m > 5): $mSev++; return "m_sev";
            case ($m > 2): $lSev++; return "l_sev";
        }
    }
}

function osSev($days) {

    global  $hSev, $mSev, $lSev;

    if ($days < 0) {
        $hSev++;
        return "h_sev";
    }

    if ($days == 0) {
        return "n_sev";
    }

    switch ($days) {
        case ($days <= 2)       : return "n_sev"; break;
        case ($days > 10)       : $hSev++; return "h_sev"; break;
        case ($days > 5)        : $mSev++ ;return "m_sev"; break;
        case ($days > 2)        : $lSev++; return "l_sev"; break;
    }
}

function getTuesday($baseMonth,$thisDate,$type) {

    $dMap = array(1 => 8,
                  2 => 7,
                  3 => 13,
                  4 => 12,
                  5 => 11,
                  6 => 10,
                  7 => 9
    );

    // Today
    $today = strtotime(date("Y-m-d"));

    // This Tuesday
    $baseDay = date("N", strtotime("$baseMonth"));
    $offset = $dMap[$baseDay];
    $thisTues = strtotime("$baseMonth +$offset days");

    // Last Tuesday
    $baseDay = date("N", strtotime("$baseMonth -1month"));
    $offset = $dMap[$baseDay];
    $lastTues = strtotime("$baseMonth +$offset days -1month");

    if ($thisTues > $today) {
        $tptDays = round(($today - $lastTues) / 86400);
    } elseif ($today == $thisTues) {
        $tptDays = 0;
    } else {
        $tptDays = round(($today - $thisTues) / 86400);
    }

    if ($type == 0) {
       return $tptDays;
    }

    // Last client update
    $lastUpdate = strtotime($thisDate);

    // If this is greater than this update, we need to check if it
    // updated during the last window. If it isn't, then we have updated.

    $clientDays = round(($today - $lastUpdate) / 86400);

    if ($clientDays > $tptDays) {
        // How many days ago?
        $lptDays = round(($today - $lastTues) / 86400);

        // If this is true, then we didn't get the last update
        if ($clientDays > $lptDays) {
            $answer = -1;
            //echo "Bad: $clientDays -- $lptDays -- $tptDays<br>";
        } else {
            $answer = $tptDays;
            //echo "Good: $clientDays -- $lptDays -- $tptDays<br>";
        }
    } else {
        $answer = 1;
    }

    return $answer;
}

function timeDiff($stamp) {

    $now = strtotime(date('Y-m-d'));
    $stamp = strtotime($stamp);

    if ($stamp < $now) {
        $diff = round(($now - $stamp) / 86400);
        if ($diff < 1) {
            return 1;
        } else { 
            return $diff;
        }
    } else {
        return 0;
    }            
}

function getOS($n) {

    global $osVersions;

    foreach ($osVersions as $hs => $os) {

        if (preg_match("/(^|,)($n)/", $hs)) {
            $answer = $os;
            return $answer;
        }
        
    }

}

function doInserts($type,$data) {

    global $entryDate;

    $fields = $values = $last ='';
    $inserts = array();

    foreach ($data as $parts => $value) {

        list($site,$sev) = explode("||", $parts);

        if ($last != $site) {
            if ($fields) {
                $inserts[] = $fields . $values . ")";
            }

            $fields = "INSERT INTO history (timestamp,site,type";
            $values = ") VALUES (\"$entryDate\",\"$site\",\"$type\"";

            $fields .= ",$sev";
            $values .= ",\"$value\"";

        } else {

            $fields .= ",$sev";
            $values .= ",\"$value\"";

        }

        $last = $site;

    }

    $inserts[] = $fields . $values . ")";
    //print_r($insert);
    
    foreach ($inserts as $insert) {
        $theQuery = $insert;
        mysql_query($theQuery);
    }
}

function processQuery($theQuery, $qType) {

    global $siteList, $osVersions, $hSev, $mSev, $lSev;

    $rC = $hostCount = $hSev = $mSev = $lSev = $cinToday = 0;

    // Antivirus
    if ($qType == "av") {

        while ($row = mysql_fetch_row($theQuery)) {
 
            $rC++;
            $timestamp		= explode(" ", $row[0]);
            $mac		= $row[1];
            $ip_long		= $row[2];
            $ip			= long2ip($ip_long);
            $hostname		= $row[3];
            $engine_version 	= $row[4];
            $engine_index	= str_replace(".","",$row[4]);
            $assig_version	= $row[5];
            $assig_index	= str_replace(".","",$row[5]);
            $assig_applied	= explode(" ", $row[6]);
            $avsig_version	= $row[7];
            $avsig_index	= str_replace(".","",$row[7]);
            $avsig_applied	= explode(" ", $row[8]);
            $last_scan		= explode(" ", $row[9]);
            $assig_timediff	= timeDiff($assig_applied[0]);
            $asst		= shortDate($assig_applied[0]);
            $avsig_timediff	= timeDiff($avsig_applied[0]);
            $avst		= shortDate($avsig_applied[0]);
            $ls_timediff	= timeDiff($last_scan[0]);
            $lst		= shortDate($last_scan[0]);
            $avlc_timediff	= timeDiff($timestamp[0]);

            // We want to keep track of how many hosts checked in today
            if ($avlc_timediff == 0) {
                $cinToday++;
            }

            // This is passed to avSev to establish a row severity
            $dayDiffs = array($assig_timediff,$avsig_timediff,$ls_timediff,$avlc_timediff);
            $rowSev = avSev($dayDiffs);

            $avMetrics[] = substr($hostname, 0,2) . "||" . $rowSev;     
        }

        $avMetrics = array_count_values($avMetrics);
        doInserts($qType,$avMetrics);

    }

    // Updates
    if ($qType == "os") {

        while ($row = mysql_fetch_row($theQuery)) {
            $rC++;
            $timestamp[]	= $row[0];
            $mac[]            	= $row[1];
            $ip_long[]        	= $row[2];
            $ip[]             	= long2ip($row[2]);
            $hostname[]       	= $row[3];
            $version[]          = $row[4];
            $update_type[]	= $row[5];
            $update_id[]	= $row[6];
            $installed_by[]	= $row[7];
            $_installed_on	= explode(" ", $row[8]);
            $installed_on[]     = $_installed_on[0];

        }

        // Distinct host entries. This is used to feed the loop below
        $hL = array_count_values($hostname);
        $tC = $x = $uD = $sU = $sP = 0;
        $i = 0;

        foreach ($hL as $host => $count) { 
            $tC += $count;
            $i = $tC - 1;            
            $uC = 0;
            $mR = "0000-00-00";

            $n_host[]	= $host;
            $n_long[]	= $ip_long[$i];
            $n_ip[]	= $ip[$i];
            $n_ver[]	= getOS($version[$i]);
            $n_stamp[]	= $timestamp[$i]; 
           
            while ($x < $tC) {

                // Sum the update type                
                switch ($update_type[$x]) {
                    case "Update"		: 
                        $uD++;
                        break;

                    case "Security Update"	: 
                        $sU++; 
                        // Figure out the most recent security update
                        if ($installed_on[$x] > $mR) {
                            $mR = $installed_on[$x];
                        }
                        break;

                    case "Service Pack"		: 
                        $sP++;
                        break;
                }

                $x++;
                $uC++;
            }

            $x = $tC;
            $n_ud[] = $uD; // Update (all)
            $n_su[] = $sU; // Security Update (all)
            $n_sp[] = $sP; // Service Pack (all)
            $n_uc[] = $uC; // Sum all update types
            $n_mr[] = $mR; // Most recent update time

            $uD = $sU = $sP = 0;

        }

        // The difference between today and patch tuesday 
        $baseDate = date("Y-m-01");
        $thisToday = date("Y-m-d");
        $ptDiff = getTuesday($baseDate,$thisToday,0);

        // Now output the results     
        for ($r = 0; $r <= count($n_host) - 1; $r++) {
            $rC++;

            $ci_timediff = timeDiff($n_stamp[$r]);
            $lastCheckinTimes[] = $ci_timediff;

            // We want to keep track of how many hosts checked in today
            if ($ci_timediff == 0) {
                $cinToday++;
            }

            // How close to or far from are we to the last patch tuesday
            $thisDate = $n_mr[$r];
            $tuesDiff = getTuesday($baseDate,$thisDate,1);
            $rowSev = osSev($tuesDiff);
            $tsKey = date("U", strtotime($n_stamp[$r]));
            $checkIn = date("m-d H:i", strtotime($n_stamp[$r]));
            
            $osMetrics[] = substr($n_host[$r], 0,2) . "||" . $rowSev;
            
        }

        $osMetrics = array_count_values($osMetrics);
        doInserts($qType,$osMetrics);
    }

}


function doQuery($qType) {
   
    $theQueries = array(
        "av" => "SELECT h.timestamp, h.mac, h.ip, h.hostname, engine_version, assig_version, assig_applied, avsig_version, avsig_applied, last_scan
                 FROM av 
                 LEFT JOIN host_info AS h ON av.hostname = h.hostname
                 LEFT JOIN ad AS a ON av.hostname = a.hostname
                 WHERE h.ip != '0' AND h.hostname != '' AND a.hostname IS NOT NULL",
        "os" => "SELECT h.timestamp, h.mac, h.ip, h.hostname, h.version, update_type, update_id, update_installed_by, update_installed_on, h.service_pack
                 FROM updates
                 LEFT JOIN host_info AS h ON updates.hostname = h.hostname
                 LEFT JOIN ad AS a ON updates.hostname = a.hostname
                 WHERE h.ip != '0' AND h.hostname != '' AND a.hostname IS NOT NULL"
    );

    $theQuery = mysql_query($theQueries[$qType]);

    // Show Query
    //echo $theQueries[$qType];

    $numRows = mysql_num_rows($theQuery);
    if ($numRows <= 0) {
        echo "\nNo Results!\n";
        exit(0);
    } else {
        processQuery($theQuery,$qType);
    }
}

doQuery("os");
doQuery("av");

?>
