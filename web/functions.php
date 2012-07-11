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

// Check and connect to DB
function cCheck() {
    global $base;
    if (file_exists("$base/config.php")) {
        global $dbHost,$dbName,$dbUser,$dbPass;
        $link = mysql_connect($dbHost,$dbUser,$dbPass);

        if (!$link) {
            die('Connection failed: ' . mysql_error());
        }

        $db = mysql_select_db($dbName,$link);

        if (!$db) {
            die('Database selection failed: ' . mysql_error());
        }

        mysql_select_db($dbName) or die();

    } else {
        echo "<center>
              <b>Configuration file not found</b><br>
              Edit 'config.php.sample' to taste and then rename it to 'config.php'
              </center>";
        die();
    }

}

// String to Hex to String
function hextostr($x) {
  $s='';
  foreach(explode("\n",trim(chunk_split($x,2))) as $h) $s.=chr(hexdec($h));
  return($s);
}

function strtohex($x) {
  $s='';
  foreach(str_split($x) as $c) $s.=sprintf("%02X",ord($c));
  return($s);
}

function retAv($x) {
    $y = array_sum($x);
    if ($y > 0) {
        $answer = $y / count($x);
        return $answer;
    }
    return 0;
}

// Calculate normal distribution
function retND($x,$avg,$std) {

    if ($std > 0) {
        $answer = round(($x - $avg) / $std,1);
    } else {
        $answer = 0;
    }

    return $answer;
}
?>
