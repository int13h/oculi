<?php
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
