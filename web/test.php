#!/usr/local/bin/php

<?php

$hostname = "Z";
$ips = array('10.9.2.0','10.11.141.0','10.1.40.1','10.1.41.1','10.1.1.1','10.1.40.1','10.9.1.1');

foreach ($ips as $ip) {

    // Assign certain IP's to other groups
    $groupMappings = array('40' => "A",
                           '41' => "B",
                            '1' => "C");
 
    $ocTest = explode(".", $ip);
    $groupKeys = array_keys($groupMappings);
    $groupTest = array_search("$ocTest[2]", $groupKeys);

    if($groupTest !== FALSE) {
        $hostGroup = $groupMappings[$groupKeys[$groupTest]];
        echo "Hit! $ip : $hostname : $hostGroup\n";
    } else {
        $hostGroup = substr($hostname, 0,2);
        echo "Miss! $ip : $hostname : $hostGroup\n";
    }
}

?>
