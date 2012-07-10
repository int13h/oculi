<?php

$base = dirname(__FILE__);
include "$base/config.php";
include "$base/functions.php";

// Check DB connection
cCheck();

// Report Types
$qTypes = array(
                 0 => "Antivirus||Null",
                 1 => "Updates||Null",
                 2 => "Inventory||Null",
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

    $answer = date("y-m-d", strtotime($date));
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
        case ($days <= 2)	: return "n_sev"; break;
        case ($days > 10)	: $hSev++; return "h_sev"; break;
        case ($days > 5)	: $mSev++ ;return "m_sev"; break;
        case ($days > 2)	: $lSev++; return "l_sev"; break;
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

function getChar($rowSev) {

    switch ($rowSev) {
        case 'n_sev': $char = '&#10003'; break;
        case 'l_sev': $char = 'I'; break;
        case 'm_sev': $char = 'W'; break;
        case 'h_sev': $char = 'C'; break;
             default: $char = '?'; break;
    }

    return $char;

}

function processQuery($theQuery,$theCols,$totalHosts) {

    global $base, $siteList, $osVersions, $qTypes, $siteName, $qType, $rfText, $hSev, $mSev, $lSev;

    $rC = $hostCount = $hSev = $mSev = $lSev = $cinToday = 0;
    $site = explode("||", $siteList[$siteName]);
    $type = explode("||", $qTypes[$qType]);
    $totalHosts = mysql_fetch_object($totalHosts);
    $totalHosts = $totalHosts->n;

    // Begin Table
    $html = "<table id=t_result class=sortable width=950 align=center cellpadding=0 cellspacing=0 style=\"border: none;\">\n";

    
    // Column Headings

    $html .= "<thead><tr>\n";

    $numCols = sizeof($theCols);

    for ($i = 0; $i < $numCols; ++$i) {

        list($name,$width) = explode(",",$theCols[$i]);
        if ($name == 'dark') {
            $html .= "<th class=dark width=$width align=left></th>\n";
        } else {
            $html .= "<th class=sort width=$width align=left>$name</th>\n";
        }
    }

    $html .= "</tr></thead>\n\n";

    //
    // Antivirus
    //

    if ($qType == 0) {

        while ($row = mysql_fetch_row($theQuery)) {

            $rC++;
            $timestamp		= explode(" ", $row[0]);
            $mac		= $row[1];
            $ip_long		= $row[2];
            $ip			= long2ip($ip_long);
            $hostname		= $row[3];
            $lst                = shortDate($last_scan[0]);
            $avlc_timediff      = timeDiff($timestamp[0]);
            $engine_version 	= $row[4];
            $engine_index	= str_replace(".","",$row[4]);
            $assig_version	= $row[5];
            $assig_index	= str_replace(".","",$row[5]);
            $assig_applied	= explode(" ", $row[6]);
            $avsig_version	= $row[7];
            $avsig_index	= str_replace(".","",$row[7]);
            $avsig_applied	= explode(" ", $row[8]);
            $last_scan		= explode(" ", $row[9]);
            $status		= $row[10];
            $location		= $row[11];

            $assig_timediff	= timeDiff($assig_applied[0]);
            $asst		= shortDate($assig_applied[0]);
            $avsig_timediff	= timeDiff($avsig_applied[0]);
            $avst		= shortDate($avsig_applied[0]);
            $ls_timediff	= timeDiff($last_scan[0]);
            $lst		= shortDate($last_scan[0]);
            $avlc_timediff	= timeDiff($timestamp[0]);

            // We want to keep track of how many host checked in today
            if ($avlc_timediff == 0) {
                $cinToday++;
            }

            // If the client returns a status other than 0, flag it
            if ($status != 0) {
                $dayDiffs = array(500);
            } else {
                $dayDiffs = array($assig_timediff,$avsig_timediff,$ls_timediff,$avlc_timediff);
            }

            $rowSev = avSev($dayDiffs);

            $tsKey = date("U", strtotime($row[0]));
            $checkIn = date("m-d H:i", strtotime($row[0]));

            $lclass = '<div class=local>L</div>';
            if ($location > 0) {
                $lclass = '<div class=remote>R</div>';
            }

            $char = getChar($rowSev);

            if ($status == 1) {
                $html .= "<tr class=$rowSev name=row-$rC id=row-$rC>\n
                          <td class=sort_l sorttable_customkey=\"$rowSev\"><div class=\"$rowSev\">$char</div>$lclass</td>\n
                          <td class=sort_l>$hostname</td>\n
                          <td class=sort_l sorttable_customkey=\"$ip_long\">$ip</td>
                          <td class=sort_err colspan=8>Error code 1: General failure processing client output. Run script manually on client and examine output.</td>\n
                          <td class=sort_r sorttable_customkey=\"$tsKey\">$checkIn</td>\n
                          <td class=sort_r>$avlc_timediff</td>\n
                          </tr>\n";
            } else {
                $html .= "<tr class=$rowSev name=row-$rC id=row-$rC>\n
                          <td class=sort_l sorttable_customkey=\"$rowSev\"><div class=\"$rowSev\">$char</div>$lclass</td>\n
                          <td class=sort_l>$hostname</td>\n
                          <td class=sort_l sorttable_customkey=\"$ip_long\">$ip</td>
                          <td class=sort_r sorttable_customkey=\"$assig_index\">$assig_version</td>\n
                          <td class=sort_r>$asst</td>\n
                          <td class=sort_r>$assig_timediff</td>\n
                          <td class=sort_r sorttable_customkey=\"$avsig_index\">$avsig_version</td>\n
                          <td class=sort_r>$avst</td>\n
                          <td class=sort_r>$avsig_timediff</td>\n
                          <td class=sort_r>$lst</td>\n
                          <td class=sort_r>$ls_timediff</td>\n
                          <td class=sort_r sorttable_customkey=\"$tsKey\">$checkIn</td>\n
                          <td class=sort_r>$avlc_timediff</td>\n
                          </tr>\n";
            }
        }

        $html .= "</table>\n";
        $hostCount = $rC;

    }

    //
    // Updates
    //

    if ($qType == 1) {

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
            $status[]           = $row[10];
            $location[]         = $row[11];
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

            $n_host[]		= $host;
            $n_long[]		= $ip_long[$i];
            $n_ip[]		= $ip[$i];
            $n_ver[]		= getOS($version[$i]);
            $n_stamp[]		= $timestamp[$i];
            $n_status[]		= $status[$i];
            $n_location[]	= $location[$i];
           
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

        // Standard Deviation
        $std_ud = stats_standard_deviation($n_ud);
        $std_su = stats_standard_deviation($n_su);
        $std_sp = stats_standard_deviation($n_sp);
        $std_uc = stats_standard_deviation($n_uc);

        // Average
        $avg_ud = retAv($n_ud);
        $avg_su = retAv($n_su);
        $avg_sp = retAv($n_sp);
        $avg_uc = retAv($n_uc);

        // The difference between today and patch tuesday 
        $baseDate = date("Y-m-01");
        $thisToday = date("Y-m-d");

        // Now output the results     
        for ($r = 0; $r <= count($n_host) - 1; $r++) {
            $rC++;

            // Normal Distribution and column colour
            $nd_ud = abs(retND($n_ud[$r],$avg_ud,$std_ud));
            $cl_ud = getSeverity($nd_ud,10);

            $nd_su = abs(retND($n_su[$r],$avg_su,$std_su));
            $cl_su = getSeverity($nd_su,10);

            $nd_sp = abs(retND($n_sp[$r],$avg_sp,$std_sp));
            $cl_sp = getSeverity($nd_sp,10);

            $nd_uc = abs(retND($n_uc[$r],$avg_uc,$std_uc));
            $cl_uc = getSeverity($nd_uc,10);

            $lu_timediff = timeDiff($n_mr[$r]);
            $lastUpdateTimes[] = $lu_timediff; 
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

            $lclass = '<div class=local>L</div>';
            if ($n_location[$r] > 0) {
                $lclass = '<div class=remote>R</div>';
            }
    
            $char = getChar($rowSev);

            $html .= "<tr class=$rowSev name=row-$rC id=\"row-$n_host[$r]\">\n
                      <td class=sort_l sorttable_customkey=\"$rowSev\"><div class=\"$rowSev\">$char</div>$lclass</td>\n
                      <td class=sort_l>$n_host[$r]</td>\n
                      <td class=sort_l sorttable_customkey=\"$n_long[$r]\">$n_ip[$r]</td>\n
                      <td class=sort_l>$n_ver[$r]</td>\n
                      <td class=sort_r_l onClick=\"getUpdates('$n_host[$r]','s','$version[$r]');\">$n_su[$r]</td>\n
                      <td sorttable_customkey=\"$nd_su\" class=inc style=\"background: $cl_su;\"></td>\n
                      <td class=sort_r_l onClick=\"getUpdates('$n_host[$r]','u','$version[$r]');\">$n_ud[$r]</td>\n
                      <td sorttable_customkey=\"$nd_ud\" class=inc style=\"background: $cl_ud;\"></td>\n
                      <td class=sort_r_l onClick=\"getUpdates('$n_host[$r]','p','$version[$r]')\">$n_sp[$r]</td>\n
                      <td sorttable_customkey=\"$nd_sp\" class=inc style=\"background: $cl_sp;\"></td>\n
                      <td class=sort_r_l onClick=\"getUpdates('$n_host[$r]','t','$version[$r]')\">$n_uc[$r]</td>\n
                      <td sorttable_customkey=\"$nd_uc\" class=inc style=\"background: $cl_uc;\"></td>\n
                      <td class=sort_r>$n_mr[$r]</td>\n
                      <td class=sort_r>$lu_timediff</td>\n
                      <td class=sort_r sorttable_customkey=\"$tsKey\">$checkIn</td>\n
                      <td class=sort_r>$ci_timediff</td>\n
                      </tr>";
        }

        // Get standard deviation and Average for time values
        sort($lastUpdateTimes);
        sort($lastCheckinTimes);
        $lastUpdateAvg = retAV($lastUpdateTimes);
        $lastUpdateStd = stats_standard_deviation($lastUpdateTimes);
        $lastUpdate = $lastUpdateTimes[round((50/100) * count($lastUpdateTimes) - .5)];
        $lastCheckAvg = retAV($lastCheckinTimes);
        $lastCheckStd = stats_standard_deviation($lastCheckinTimes);
        $lastCheck = $lastCheckinTimes[round((50/100) * count($lastCheckinTimes) - .5)];

        $html .= "</table>\n";
        $hostCount = count(array_unique($hostname));

    }

    //
    // Inventory
    //

    if ($qType == 2) {


        while ($row = mysql_fetch_row($theQuery)) {

            $rC++;
            $hostname           = $row[1];
            $manufacturer	= $row[3];
            $model		= $row[4];
            $serial_number      = $row[5];
            $asset_tag          = $row[6];
            $processor          = $row[7];
            $frequency          = $row[8];
            $memory             = $row[9];
            $storage            = $row[10];

            $html .= "<tr id=row-$rC>\n
                      <td class=sort_l>$hostname</td>\n
                      <td class=sort_l>$manufacturer</td>\n
                      <td class=sort_l>$model</td>\n
                      <td class=sort_l>$serial_number</td>\n
                      <td class=sort_l>$asset_tag</td>\n
                      <td class=sort_l>$frequency</td>\n
                      <td class=sort_l>$memory</td>\n
                      <td class=sort_l>$storage</td>\n
                      </tr>\n";
        }

        $html .= "</table>\n";
        $hostCount = $rC;

    }

    // Per values for severities
    $hsP = $msP = $lsP = 0; 
    if ($hostCount > 0) {
        $hsP = round($hSev / $hostCount * 100,1);
        $msP = round($mSev / $hostCount * 100,1);
        $lsP = round($lSev / $hostCount * 100,1);
        
        if ($totalHosts > 0) {
            $ofTotal = round($hostCount / $totalHosts  * 100,1);
        } else {
            $ofTotal = "0";
        }
    }             
   
    // Report Header
    echo "\r<table width=950 align=center cellpadding=0 cellspacing=0 style=\"border-collapse: collapse;\">\n
          \r<tr>\n
          \r<td width=130 class=black align=right style=\"padding-top: 10px;\"><b>Location:</b></td>\n
          \r<td class=black align=left style=\"padding-top: 10px;\">$site[0]</td>\n
          \r</tr>\n
          \r<tr>\n
          \r<td class=black align=right><b>Report Type:</b></td>\n
          \r<td class=black align=left>$type[0]</td>\n
          \r</tr>\n
          \r<tr>\n
          \r<td class=black align=right><b>Report Filter:</b></td>\n
          \r<td class=black align=left>$rfText</td>\n
          \r</tr>\n";

    if ($qType < 2) {

        $helper = '';

        if ($qType == 1) {
             $ptDiff = getTuesday($baseDate,$thisToday,0);
             $tuesAgo = date("l F jS", strtotime("$thisToday - $ptDiff days"));
        
            echo "\r<tr>\n
                  \r<td class=black align=right><b>Last Release:</b></td>\n
                  \r<td class=black align=left> $tuesAgo, <b>$ptDiff day(s) ago</b>. Last Update comparisons are performed using this date.</td>\n
                  \r</tr>\n";

            $helper = "\r<tr>\n
                       \r<td align=center colspan=4><img src=pt.png><br><br></td>\n
                       \r</tr>\n";

            echo "</table>\n";

        } else {

            echo "</table><br>\n";

        }

        // The boxes
        echo "<br><table width=950 align=center>\n
          $helper
          \r<tr>\n
          \r<td align=center><div class=big>Total Hosts</div><div id=box class=box><span id=c_all>$hostCount</span><br><span class=small>(${ofTotal}% of all clients)</span><br><span class=xsmall>$cinToday checked in today</span></div></td>\n
          \r<td align=center><div class=big>Critical</span></div><div id=box_high class=box_high><span id=c_hsev>$hSev</span><br><span class=small>($hsP%)</span><br><span class=xsmall>more than 10 days</span></div></td>\n
          \r<td align=center><div class=big>Warning</div><div id=box_med class=box_med><span id=c_msev>$mSev</span><br><span class=small>($msP%)</span><br><span class=xsmall>between 6 and 10 days</span></div></td>\n
          \r<td align=center><div class=big>Information</div><div id=box_low class=box_low><span id=c_lsev>$lSev</span><br><span class=small>($lsP%)</span><br><span class=xsmall>between 3 and 5 days</span></div></td>\n
          \r</tr>\n";

    }

    echo "\r<tr>\n
          \r<td colspan=4 class=black align=center style=\"padding-bottom: 10px; font-size: 9px;\">\n
          \r<br>[ click the boxes above to filter results, click on column headings to sort ]\n
          \r</td>\n
          \r</tr>\n
          \r</table>";

    echo $html;

}


function doQuery() {

    global $rfText;
    $rfText = "No filter applied";

    $search	=  $_REQUEST["search"];
    $os     	=  htmlspecialchars($_REQUEST["os"]);
    $siteName   =  htmlspecialchars($_REQUEST["siteName"]);
    $qType      =  htmlspecialchars($_REQUEST["qType"]);

    // Search
    if ($search) {
        $rfText = '';
        $subjects = array(
                           'host'	=> 'h.hostname',
                           'ip'		=> 'INET_NTOA(h.ip)',
                           'kb'		=> 'update_id',
                           'ev'		=> 'engine_version',
                           'as'		=> 'ass_version',
                           'av'		=> 'avs_version'
                         );

        $toFilter = explode(";", $search);
        $search     =  htmlspecialchars($search);
        $c = count($toFilter);
        $xFilter = '';
        $problem = 0;

        for ($i = 0; $i < $c; ++$i) {

            @list($subj,$pred) = explode("=", $toFilter[$i]);

            $subj = mysql_real_escape_string($subj);
            $pred = mysql_real_escape_string($pred);

            // tight or loose query
            $prefix = "=";
            if ($subj[0] == "~") {
                $prefix = "LIKE";
                $subj = ltrim($subj, "~");
            }
            if (array_key_exists($subj, $subjects)) {
                $subject = $subjects[$subj];
                if ($i == 0) {
                    $xFilter .= "WHERE " . $subject . " $prefix " . "'$pred' ";
                } else {
                    $xFilter .= "AND " . $subject . " $prefix " . "'$pred' ";
                }
            } else {
                $problem = 1;
            }
        
            if ($problem == 1) {
                $xFilter = '';
                echo "<div class=empty>Syntax Error, no filter applied</div>";
                echo "</td>\n</tr>\n</table>\n</body>\n</html>\n";
                exit(0) ;
            } else {
                $tmpRf = htmlspecialchars("$subj -> $pred");
                $rfText .= "$tmpRf<br>";
            }
        }
    } else {
        $xFilter = '';
    }

    // OS Type
    if ((isset($os)) && ($os != 42)) {
        $oss = explode(",", $os);
        $c = count($oss);
        for ($i = 0; $i < $c; ++$i) {
            if ($i == 0) {
                $osVers = "AND (h.version = '$oss[$i]'";
            } else {
                $osVers .= " OR h.version = '$oss[$i]'";
            }
        }
        $osVers .= ")";
    } else {
        $osVers = '';
    }

    if (!$search) {
        // Site selection
        if ($siteName == 'All') {
    
            switch ($qType) {
                case 0: $WHERE = 'WHERE h.hostname != \'\''; break;             
                case 1: $WHERE = 'WHERE h.hostname != \'\''; break;
                case 2: $WHERE = 'WHERE h.hostname != \'\''; break;
            }

        } elseif ($siteName == '01') {
        
            $WHERE = 'WHERE h.location = 1';

        } else {

            switch ($qType) {
                case 0: $WHERE = "WHERE h.hostname LIKE '$siteName%'"; break;
                case 1: $WHERE = "WHERE h.hostname LIKE '$siteName%'"; break;
                case 2: $WHERE = "WHERE h.hostname LIKE '$siteName%'"; break;
            }
    
        }

    }

    $theQueries = array(
        "q0" => "SELECT h.timestamp, h.mac, h.ip, h.hostname, engine_version, assig_version, assig_applied, avsig_version, avsig_applied, last_scan, h.status, h.location
                 FROM av 
                 LEFT JOIN host_info AS h ON av.hostname = h.hostname
                 LEFT JOIN ad AS a ON av.hostname = a.hostname
                 $WHERE $xFilter $osVers AND a.hostname IS NOT NULL",
        "c0" => ",100||Host,''||IP,100||AS Version,100||Applied,100||Days,50||AV Version,100||Applied,100||Days,49||Last Scan,100||Days,49||Check-in,110||Days,49",
        "q1" => "SELECT h.timestamp, h.mac, h.ip, h.hostname, h.version, update_type, update_id, update_installed_by, update_installed_on, h.service_pack, h.status, h.location
                 FROM updates
                 LEFT JOIN host_info AS h ON updates.hostname = h.hostname
                 LEFT JOIN ad AS a ON updates.hostname = a.hostname
                 $WHERE $xFilter $osVers AND a.hostname IS NOT NULL",
        "c1" => ",60||Host,''||IP,100||OS,120||Security,70||dark,10||Update,70||dark,10||Svc. Pack,70||dark,10||Total,80||dark,10||Last Update,100||Days,50||Check-in,100||Days,50",
        "q2" => "SELECT h.ip, h.hostname, h.version, manufacturer, model, serial_number, asset_tag, processor, frequency, memory, storage
                 FROM asset
                 LEFT JOIN host_info AS h ON asset.hostname = h.hostname
                 LEFT JOIN ad AS a ON asset.hostname = a.hostname
                 $WHERE $xFilter $osVers AND a.hostname IS NOT NULL",
        "c2" => "Host,''||Manufacturer,'150'||Model,'150'||Serial #,''||Asset Tag,''||Freq.,'50'||Mem.,'50'||Disk,'50'",
    );

    //echo $theQueries["q".$qType];
    $theQuery = mysql_query($theQueries["q".$qType]);
    $theCols = explode("||",$theQueries["c".$qType]);
    $totalHosts = mysql_query("SELECT COUNT(DISTINCT(host_info.hostname)) AS n 
                               FROM host_info
                               LEFT JOIN ad AS a ON host_info.hostname = a.hostname
                               WHERE a.hostname IS NOT NULL");
    // Show Query
    //echo $theQueries[q2];

    $numRows = mysql_num_rows($theQuery);
    if ($numRows <= 0) {
        echo "<div class=empty>The query returned no results</div>";
        echo "</td>\n</tr>\n</table>\n</body>\n</html>\n";
        exit(0);
    } else {
        processQuery($theQuery,$theCols,$totalHosts);
    }
}
?>

<form id=comp method=post>
<table align=center width=950 cellpadding=0 cellspacing=0 style="background: #ffffff; border: 1pt solid gray;">
<tr>
<td class=charts colspan=4 align=center>
<?php

    if(!isset($_REQUEST['qType'])) { $qType = "0"; } else { $qType = $_REQUEST['qType']; }
    if(!isset($_REQUEST['siteName'])) { $siteName = ""; } else { $siteName = $_REQUEST['siteName']; }
    if(!isset($_REQUEST['search'])) { $search = ''; } else { $search = $_REQUEST['search']; }

    if ($qType < 2 && $search == '') {
        if ($siteName == '') {
            include_once '.charts/daily.php';
        } elseif ($siteName == 'All') {
            include_once '.charts/daily.php';
        } else {
            include_once '.charts/site.php';
        }
    }
 
    if ($qType == 2 && $search == '') {
        include_once '.charts/inventory.php';
    } 
?>
</td>
</tr>

<tr>
<td align=right class=controls colspan=4 style="padding-left: 20px; padding-bottom: none;">SEARCH:</b>
&nbsp;<input class=input type=text size=90 id=search name=search maxlength="256" style="padding-bottom: none;" value="<?php echo $search;?>">
&nbsp;<span id=clear_search class=clear>&#x21BA;</span>
<div id=help style="padding-right: 35px; padding-top: 10px; font-size: 10px;">
subjects: <span style="color: gray;">host | ip | kb | ev | as | av</span>
&nbsp;&nbsp;example: <span style="color: gray;">kb=kb2588516</span>
<br>like match: <span style="color: gray;">prefix subject with "~", "%" is wild&nbsp;&nbsp;</span>
example: <span style="color: gray;">~ip=10.%.239.%</span>
<br>chains: <span style="color: gray;">separator is ";"</span>
example: <span style="color: gray;">~ip=10.%.239.%;~host=CU%</span>
</div>
</td>
</tr>
<tr>
<td align=right class=controls>
OS TYPE:
<SELECT id=os name=os class=input>
<?php
    if(!isset($_REQUEST['os'])) { $os = 42; } else { $os = $_REQUEST['os']; }
    mkSelect($osVersions,$os);
?>
</SELECT>
</td>

<td align=right class=controls width=200>
LOCATION:
<SELECT id=siteName name=siteName class=input>
<?php
    if(!isset($_REQUEST['siteName'])) { $siteName = ''; } else { $siteName = $_REQUEST['siteName']; }
    mkSelect($siteList,$siteName);
?>
</SELECT>
</td>

<td align=right class=controls width=200>
REPORT TYPE:&nbsp;
<SELECT id=qType name=qType class=input>
<?php
    if(!isset($_REQUEST['qType'])) { $qType = ''; } else { $qType = $_REQUEST['qType']; }
    mkSelect($qTypes,$qType);
?>
</SELECT>
</td>
<td align=right class=controls>
<input id=base name=base type="submit" value=submit class=rb>
<input type="button" value="reset" onClick="location.href='p-comp.php<?php echo "?id=$id";?>';" class=rb>
</td></tr>
<tr><td colspan=4 style="background: #fafafa;">

<?php
if (isset($_POST['base'])) {
    switch ($_POST['base']) {
        case "submit":
            doQuery();
            break;
        default:
            doQuery();
            break;
   }
}
?>
</td></tr>
</table>
</form>
