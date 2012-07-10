<?php

$site = substr($siteName,0,2);

list($chartName,$chartQuery,$chartCol) = explode('||', $chartArgs[$qType]);

$q1 = "SELECT SUBSTRING(timestamp,1,10), h_sev 
       FROM history 
       INNER JOIN sites AS s ON history.site = s.site 
       WHERE timestamp BETWEEN DATE_SUB(\"$s\", INTERVAL 30 DAY) AND \"$s\" 
       AND history.site = \"$site\" AND type = \"$chartQuery\"";

$r1 = mysql_query($q1);

$bar = $lbl = $tip = '';

while ($row = mysql_fetch_row($r1)) {    
    $bar .= "$row[1],";
    $lbl .= "\"$row[0]\",";
    $tip .= "\"$row[1]\",";
}

$bar = rtrim($bar,',');
$lbl = rtrim($lbl,',');
$tip = rtrim($tip,',');

// Chart Logic

echo "<div class=right id=togglechart>show history</div>";
echo "<br><canvas id=chart_site width=\"950\" height=\"250\">[No canvas support]</canvas>";

echo "\r<script>";

echo "
    function createDaily () {
    var bar1 = new RGraph.Bar('chart_site', [$bar]);
    bar1.Set('chart.title', '$chartName - Number of Critical Devices (30 Day History)');
    bar1.Set('chart.yaxispos', 'left');
    bar1.Set('chart.background.grid', true);
    bar1.Set('chart.background.grid.autofit', true);
    bar1.Set('chart.background.grid.vlines', true);
    bar1.Set('chart.background.grid.width', .5);
    bar1.Set('chart.labels', [$lbl]);
    bar1.Set('chart.text.angle', 45);
    bar1.Set('chart.colors', ['$chartCol']);
    bar1.Set('chart.gutter.top', 30);
    bar1.Set('chart.gutter.left', 75);
    bar1.Set('chart.gutter.right', 50);
    bar1.Set('chart.gutter.bottom', 60);
    bar1.Set('chart.strokecolor', 'black');
    bar1.Set('chart.text.size', 8);
    bar1.Set('chart.text.font', 'verdana');
    bar1.Set('chart.tooltips', [$tip]);    
    bar1.Set('chart.background.grid.autofit.align', true);
    bar1.Set('chart.variant', '3d');
    bar1.Draw();
    }
    createDaily();";
echo "</script>";
?>
