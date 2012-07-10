<?php

if ($siteName == 'All') {
    $WHERE = '';
} else {
    $WHERE = "WHERE asset.hostname LIKE '$siteName%'";
}

// Inventory
$q1 = "SELECT COUNT(manufacturer) AS n, manufacturer 
       FROM asset INNER JOIN ad AS a ON asset.hostname = a.hostname 
       $WHERE
       GROUP BY manufacturer 
       ORDER BY n DESC";

$q2 = "SELECT COUNT(model) AS n, model 
       FROM asset INNER JOIN ad AS a ON asset.hostname = a.hostname 
       $WHERE
       GROUP BY model
       ORDER BY n DESC";

$r1 = mysql_query($q1);
$r2 = mysql_query($q2);

list($chartName,$chartQuery,$chartCol) = explode('||', $chartArgs[$qType]);

$bar00 = $bar01 = $lbl00 = $lbl01 = $tip00 = $tip01 = '';

while ($row = mysql_fetch_row($r1)) {
    $bar00 .= "$row[0],";
    if (strlen($row[1]) > 20) {
        $tmp00 = substr($row[1], 0,15);
        $lbl00 .= "\"$tmp00..\",";
    } else {
        $lbl00 .= "\"$row[1]\",";
    }
    $tip00 .= "\"$row[0]\",";
}

$i = $other = 0;

while ($row = mysql_fetch_row($r2)) {

    if ($i < 10) {
        $bar01 .= "$row[0],";
        if (strlen($row[1]) > 20) {
            $tmp01 = substr($row[1], 0,15);
            $lbl01 .= "\"$tmp01..\",";
        } else {
            $lbl01 .= "\"$row[1]\",";
        }
        $tip01 .= "\"$row[0]\",";
    } else {
        $other += $row[0];
    }

    $i++;

}

$bar01 .= "$other,";
$lbl01 .= "\"Other\",";
$tip01 .= "\"$other\",";


$bar00 = rtrim($bar00,',');
$lbl00 = rtrim($lbl00,',');
$tip00 = rtrim($tip00,',');
$bar01 = rtrim($bar01,',');
$lbl01 = rtrim($lbl01,',');
$tip01 = rtrim($tip01,',');


// Chart Logic

echo "<br><canvas id=chart_manufacturer width=\"450\" height=\"250\">[No canvas support]</canvas>";
echo "<canvas id=chart_model width=\"450\" height=\"250\">[No canvas support]</canvas>";

echo "\r<script>";

echo "
    function createManufacturer() {
      var bar1 = new RGraph.Bar('chart_manufacturer', [$bar00]);
      bar1.Set('chart.title', 'Distribution by Manfacturer');
      bar1.Set('chart.yaxispos', 'left');
      bar1.Set('chart.background.grid', true);
      bar1.Set('chart.background.grid.autofit', true);
      bar1.Set('chart.background.grid.vlines', true);
      bar1.Set('chart.background.grid.width', .5);
      bar1.Set('chart.labels', [$lbl00]);
      bar1.Set('chart.text.angle', 45);
      bar1.Set('chart.colors', ['$chartCol']);
      bar1.Set('chart.gutter.top', 30);
      bar1.Set('chart.gutter.left', 75);
      bar1.Set('chart.gutter.right', 50);
      bar1.Set('chart.gutter.bottom', 100);
      bar1.Set('chart.strokecolor', 'black');
      bar1.Set('chart.text.size', 8);
      bar1.Set('chart.text.font', 'verdana');
      bar1.Set('chart.tooltips', [$tip00]);
      bar1.Set('chart.background.grid.autofit.align', true);
      bar1.Set('chart.variant', '3d');
      bar1.Draw();
    }

    function createModel() {
      var bar1 = new RGraph.Bar('chart_model', [$bar01]);
      bar1.Set('chart.title', 'Distribution by Model');
      bar1.Set('chart.yaxispos', 'left');
      bar1.Set('chart.background.grid', true);
      bar1.Set('chart.background.grid.autofit', true);
      bar1.Set('chart.background.grid.vlines', true);
      bar1.Set('chart.background.grid.width', .5);
      bar1.Set('chart.labels', [$lbl01]);
      bar1.Set('chart.text.angle', 45);
      bar1.Set('chart.colors', ['$chartCol']);
      bar1.Set('chart.gutter.top', 30);
      bar1.Set('chart.gutter.left', 75);
      bar1.Set('chart.gutter.right', 50);
      bar1.Set('chart.gutter.bottom', 100);
      bar1.Set('chart.strokecolor', 'black');
      bar1.Set('chart.text.size', 8);
      bar1.Set('chart.text.font', 'verdana');
      bar1.Set('chart.tooltips', [$tip01]);
      bar1.Set('chart.background.grid.autofit.align', true);
      bar1.Set('chart.variant', '3d');
      bar1.Draw();
    }

    createManufacturer();
    createModel();

";
    

echo "</script>";
?>
