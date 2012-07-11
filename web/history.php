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

$base = dirname(__FILE__);
include "$base/config.php";
include "$base/functions.php";

// Check DB connection
cCheck();

$type = mysql_real_escape_string($_REQUEST['type']);
$site = mysql_real_escape_string($_REQUEST['site']);

$qTypes = array(
                 0 => "av",
                 1 => "os");

$type = $qTypes[$type];

if ($site) {
    if ($site == "All") {
        $AND = '';
    } else {
        $AND = "AND t.name = '$site'";
    }
} else {
   $AND = '';
}

$rows = array();

$end   = date("Y-m-d");
$start = date("Y-m-d",strtotime($end . "-30 days"));

$query = "SELECT SUBSTRING(timestamp,6,5) as ts, t.name as sn, h_sev as cnt FROM history 
          INNER JOIN sites AS t ON history.site = t.site 
          WHERE timestamp BETWEEN '$start' AND '$end' AND type='$type'
          ORDER BY t.name,timestamp ASC";

$result = mysql_query($query);

while ($row = mysql_fetch_assoc($result)) {
    $rows[] = $row;
}

$theJSON = json_encode($rows);
echo $theJSON;

?>
