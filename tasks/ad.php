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

function processFile($file) {

    global $db, $debug;

    if (($handle = fopen("$file", "r")) !== FALSE) {         
        
        while (($line = fgets($handle, 5000)) !== FALSE) {
            // Remove bad chars
            $toRm = array("\0","\256");
            $line = str_replace($toRm, "", $line);

            $pattern = '/^(.+#)/';
            
            if (!preg_match($pattern, $line) && strlen($line) > 0) {
                $hosts[] = rtrim($line);
            }

        }
    }
    fclose($handle);
    unlink($file);

    // Inserts
    if ($debug == 0) {

        // Remove old results
           mysql_query("DELETE FROM ad WHERE hostname IS NOT NULL");

        foreach ($hosts as $hostname) { 

            mysql_query("INSERT INTO ad (hostname) VALUES (\"$hostname\") ON DUPLICATE KEY UPDATE hostname=\"$hostname\"");

        }

    } else {

        // Put debug stuff here
        print_r($hosts);

    }

}

// Files look like: "<yyyy-mm-d>_hostlist-0042.txt" 

if (isset($argc)) {

    if ($argc == 1 || $argc > 2 || !preg_match("(^\d{4}-\d{2}-\d{2}$)", $argv[1])) {

        echo "\nUsage: $argv[0] <yyyy-mm-d>\n\n";
        exit;        

    } else {

        $base_date = $argv[1];

    }

} else {

    $base_date = date('Y-m-d');

}

$inFile = "$patch_dir/${base_date}_hostlist-0042.txt";


if (file_exists($inFile)) {

    processFile($inFile);

} else {

    echo "\nError: \"$inFile\" does not exist.\n";

}

?>

