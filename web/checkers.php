// Is the client outside of our network?
functions chkLIP($x) {



}

// Validate host naming
function chkHostname($x) {
    $result = 0;

    $chk00 = array('AK','AM','AN','DG','BU','CE','CO','CU','DW','DI','IN','KI',
                'LU','MA','PI','SF','SH','ST','DW');

    $chk01 = array('LT','MD','ML','PR','PL','SC','WS','WST');


    $result += count($chk00[substr($x, 0, 2)]);
    $result += count($chk01[substr($x, 2, 4)]);    

    echo $result;


}
