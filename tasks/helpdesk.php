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

$base = dirname(__FILE__);
include_once "$base/config.php";
include_once "$base/functions.php";

// Check DB connection
cCheck();

function sendMail($office, $message, $problems) {

    global $recipient, $domain;

    $to         = $recipient;
    $subject    = 'Compliance: You have ' .  $problems . ' host(s) in a critical state $mapping';
    $headers    = 'From: compliance@' . $domain . "\r\n" .
                  'Reply-To: compliance@' . '$domain';

    $message   .= "\r\n\r\nPriority: High\r\n";
    $message   .= "Category: IT Admin\r\n";
    $message   .= "Issue Type: Compliance Updates\r\n";
    $message   .= "Office: $office\r\n";

    mail($to, $subject, $message, $headers);
}

// Get host groups

$groupQuery = mysql_query("SELECT site, name FROM sites WHERE site != 'NS'");

// Cycle through the groups and generate host lists

while ($a = mysql_fetch_row($groupQuery)) {

    $prefix	= $a[0];
    $office	= $a[1];

    unset($hostQuery);

    $_hostQuery = mysql_query("SELECT hostname, inet_ntoa(ip), alert_os, alert_av, alert_status, alert_history 
                              FROM host_info
                              WHERE alert_status > 0
                              AND (hostname LIKE \"${prefix}%\" OR hostname LIKE \"$prePrefix$prefix%\")");

    $hostQuery = mysql_query("SELECT hostname, inet_ntoa(ip), alert_os, alert_av, alert_status, alert_history
                              FROM host_info
                              WHERE alert_status > 0
                              AND hostname LIKE \"${prefix}%\"");


    $problems = mysql_num_rows($hostQuery);

    if ($problems > 0) {

        $message = "Host | IP Address | OS | AV | History\r\n\r\n";

        while ($b = mysql_fetch_row($hostQuery)) {

            $message .= $b[0] . "\t\t" . $b[1] . "\t\t" . $b[2] . "\t\t" . $b[3] . "\t\t" . $b[5] . "\r\n"; 
    
        }

        sendMail($office, $message, $problems);

    }

}

?>
