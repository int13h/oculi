<?php

// AV
$q1 = "SELECT s.name, h_sev FROM history INNER JOIN sites AS s ON history.site = s.site WHERE $when[0]  AND type = 'av'";
$q2 = "SELECT s.name, h_sev FROM history INNER JOIN sites AS s ON history.site = s.site WHERE $when[0]  AND type = 'os'";
$r1 = mysql_query($q1);
$r2 = mysql_query($q2);

list($chartName1,$chartQuery1,$chartCol1) = explode('||', $chartArgs[0]);
list($chartName2,$chartQuery2,$chartCol2) = explode('||', $chartArgs[1]);


$i = 0;

$av = $os = array();

while ($row = mysql_fetch_row($r1)) {    
    $av[$row[0]] = $row[1];
    $lbl[]	 = $row[0];
}

while ($row = mysql_fetch_row($r2)) {
    $os[$row[0]] = $row[1];
    $lbl[]       = $row[0];
}

$lbl = array_unique($lbl);
asort($lbl);

$bar		= '[';
$xlabel		= '';
$tooltips	= '[';

foreach($lbl as $needle) {
   
    $avr = $av[$needle];
    $osr = $os[$needle];
    if ($avr === NULL) {
        $avr = 0;
    }
    if ($osr === NULL) {
        $osr = 0;
    }

    $bar	.= '[' . $avr . ',' . $osr . '],';
    $tooltips	.= "\"$avr\",\"$osr\",";
    $xlabel	.= '"' . $needle . '"' . ',';
}

$bar = rtrim($bar,',');
$bar .= ']';
$tooltips = rtrim($tooltips,',');
$tooltips .= ']';
$xlabel = rtrim($xlabel,',');

// Chart Logic

echo "<br><canvas id=daily width=\"950\" height=\"250\">[No canvas support]</canvas>";

echo "\r<script>";

echo "
    function createDaily () {
    var bar1 = new RGraph.Bar('daily', $bar);
    bar1.Set('chart.title', 'Number of Critical Devices');
    bar1.Set('chart.yaxispos', 'left');
    bar1.Set('chart.background.grid', true);
    bar1.Set('chart.background.grid.autofit', true);
    bar1.Set('chart.background.grid.vlines', true);
    bar1.Set('chart.background.grid.width', .5);
    bar1.Set('chart.labels', [$xlabel]);
    bar1.Set('chart.text.angle', 45);
    bar1.Set('chart.colors', ['$chartCol1', '$chartCol2']);
    bar1.Set('chart.gutter.top', 30);
    bar1.Set('chart.gutter.left', 60);
    bar1.Set('chart.gutter.right', 40);
    bar1.Set('chart.gutter.bottom', 70);
    bar1.Set('chart.strokecolor', 'black');
    bar1.Set('chart.text.size', 8);
    bar1.Set('chart.text.font', 'verdana');
    bar1.Set('chart.ylabels.count', 10);
    bar1.Set('chart.background.grid.autofit.align', true);
    bar1.Set('chart.variant', '3d');
    bar1.Set('chart.tooltips', $tooltips);
    bar1.Set('chart.key', ['$chartName1', '$chartName2']);
    bar1.Set('chart.key.position', 'gutter');
    bar1.Set('chart.key.position.y', bar1.canvas.height - 8);
    bar1.Set('chart.key.text.size', 8);
    bar1.Set('chart.key.position.gutter.boxed', false);
    bar1.Draw();
    }
    createDaily();";
echo "</script>";
?>
