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

$hostname	= mysql_real_escape_string($_REQUEST['hn']);
$type		= mysql_real_escape_string($_REQUEST['t']);
$os_version     = mysql_real_escape_string($_REQUEST['os']);
$service_pack	= mysql_real_escape_string($_REQUEST['sp']);
$rows = array();

$types = array(
    "s" => "AND update_type = 'Security Update'",
    "u" => "AND update_type = 'Update'",
    "p" => "AND update_type = 'Service Pack'",
    "t" => ""
);

$pattern = '/(^[s|u|p|t]$)/';
preg_match($pattern, $type, $match);

if (count($match) > 0) {
    $AND = $types[$type];
} else {
    $AND = '';
}


$query = "SELECT DISTINCT(update_id) FROM updates 
          LEFT JOIN host_info AS h ON updates.hostname = h.hostname 
          WHERE (h.version = $os_version
          AND h.service_pack = '$service_pack'
          $AND) 
          AND update_id NOT IN (SELECT update_id FROM updates WHERE hostname = '$hostname')";

$result = mysql_query($query);

while ($row = mysql_fetch_assoc($result)) {
    $rows[] = $row;
}

$theJSON = json_encode($rows);
echo $theJSON;
?>
