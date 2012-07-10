<?php
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
