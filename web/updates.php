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

$hostname = mysql_real_escape_string($_REQUEST['hostname']);
$type = $_REQUEST['type'];

$types = array(
    "s" => "AND (update_type = 'Security Update' OR  update_type = 'Service Pack')",
    "u" => "AND (update_type = 'Update' OR  update_type = 'Service Pack')",
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

$rows = array();

$query = "SELECT * FROM updates WHERE hostname = '$hostname' $AND ORDER BY update_installed_on DESC";
$result = mysql_query($query);

while ($row = mysql_fetch_assoc($result)) {
    $rows[] = $row;
}

$theJSON = json_encode($rows);
echo $theJSON;
?>
