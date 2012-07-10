$(document).ready(function(){

    $("#box").click(function(){
        $("tr.n_sev").show();
        $("tr.l_sev").show();
        $("tr.m_sev").show();
        $("tr.h_sev").show();
        $("#box").fadeTo(400,1);
        $("#box_low").fadeTo(400,1);
        $("#box_med").fadeTo(400,1);
        $("#box_high").fadeTo(400,1);
    });

    $("#box_high").click(function(){
        $("tr.n_sev").hide();
        $("tr.l_sev").hide();
        $("tr.m_sev").hide();
        $("tr.h_sev").show();
        $("#box").fadeTo('fast',.2);
        $("#box_low").fadeTo(400,.2);
        $("#box_med").fadeTo(400,.2);
        $("#box_high").fadeTo(400,1);
    });

    $("#box_med").click(function(){
        $("tr.n_sev").hide();
        $("tr.l_sev").hide();
        $("tr.m_sev").show();
        $("tr.h_sev").hide();
        $("#box").fadeTo(400,.2);
        $("#box_low").fadeTo(400,.2);
        $("#box_med").fadeTo(400,1);
        $("#box_high").fadeTo(400,.2);
    });

    $("#box_low").click(function(){
        $("tr.n_sev").hide();
        $("tr.l_sev").show();
        $("tr.m_sev").hide();
        $("tr.h_sev").hide();
        $("#box").fadeTo(400,.2);
        $("#box_low").fadeTo(400,1);
        $("#box_med").fadeTo(400,.2);
        $("#box_high").fadeTo(400,.2);
    });

    //
    // Simple signature show/hide via the search input box
    //

    // Case insensitive contains
    jQuery.expr[':'].Contains = function(a,i,m){
        return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase())>=0;
    };

    $('#search').keyup(function(){
        $('tr[id^=row]').hide();
        searchString = $('#search').val();
        $("'tr[id^=row]':Contains('" + searchString + "')").show();

        // Change box values

        var nRows = $('tr[id^=row]:visible').length
        $("#c_all").text(nRows);

        var hRows = $('tr[class^=h_sev]:visible').length
        $("#c_hsev").text(hRows);

        var mRows = $('tr[class^=m_sev]:visible').length
        $("#c_msev").text(mRows);

        var lRows = $('tr[class^=l_sev]:visible').length
        $("#c_lsev").text(lRows);
    });

    // I don't love this, need to fix
    $('#clear_search').click(function() {
        if ($('#search').val() != '') {
            $('#search').val('');
            $('tr[id^=row]').show();
        }
    });
        
    $('#togglechart').click(function() {
        current = $('#togglechart').text();

        if (current == 'show history') {
            $('#chart_site').fadeIn('slow');
            $('#togglechart').text("hide history");
        } else {
            $('#chart_site').fadeOut('fast');
            $('#togglechart').text("show history");
        }
    });
});


function closeData() {

   $("#bye").remove();

}


function getUpdates(hostname,type,os) {

    $("#bye").remove();

    var urArgs = "hostname=" + hostname + "&type=" + type;

    $(function(){
        //Get the JSON string and pass it to the callback function
        $.get(".inc/compliance/updates.php?" + urArgs, function(data){cb(data)});
    })


    function cb(data){
        //converts the JSON string to a JavaScript object
        eval("theData=" + data);

        var tbl = '';
        var head = '';
        var row = '';
        var sp = 0;
        
        head += "<thead><tr><th class=sort_sub align=left><b>Hostname</b></th>";
        head += "<th class=sort_sub align=left>Type</th>";
        head += "<th class=sort_sub align=left>ID</th>";
        head += "<th class=sort_sub align=left>Installed By</th>";
        head += "<th class=sort_sub align=left>Installed On</th></tr></thead>";

        for (var i=0; i<theData.length; i++) {

            if (theData[i].update_type == 'Service Pack' && type != 'p') {
                var sp = theData[i].update_id;
            } else {
                row += "<tr><td class=sort_sub>" + theData[i].hostname + "</td>";
                row += "<td class=sort_sub>" + theData[i].update_type + "</td>";
                row += "<td class=sort_sub><a href=\"http://support.microsoft.com/kb/" + theData[i].update_id.replace("KB","") + "\" target=_new>" + theData[i].update_id + "</a></td>";
                row += "<td class=sort_sub>" + theData[i].update_installed_by + "</td>";
                row += "<td class=sort_sub>" + theData[i].update_installed_on.replace("00:00:00","") + "</td></tr>";
            }              

        }

        tbl += "<tr id=bye><td colspan=16><div id=pdata class=pdata>";
        tbl += "<div id=close_pdata class=close_pdata onClick=closeData();>CLOSE</div>";
        tbl += "<div id=miss_pdata class=miss_pdata onClick=getMissed('" + hostname + "','" + type + "','" + os + "','" + sp + "');><span id=mtext>Missing Updates</span><img id=mwork style=\"display: none;\" src=\".inc/compliance/work.gif\"></div>";
        tbl += "<table id=update_table width=100% class=sortable cellpadding=0 cellspacing=0>";
        tbl += head;
        tbl += row;
        tbl += "</table></td></div></tr>";

        $("#row-" + hostname).after(tbl);
        $('html,body').animate({scrollTop: $("#row-"+ hostname).offset().top},'fast');
        $("#pdata").fadeIn('slow');
    }
}

function getMissed(hostname,type,os,sp) {

     $("#mwork").css('display','');
     $("#mtext").css('display','none');

    if ($("#missing_updates").length == 0) {

        var urArgs = "hn=" + hostname + "&t=" + type + "&os=" + os + "&sp=" + sp;

        $(function(){
        //Get the JSON string and pass it to the cb function
            $.get(".inc/compliance/missing.php?" + urArgs, function(data){cb(data)});
        })

        function cb(data){
            //converts the JSON string to a JavaScript object
            eval("theData=" + data);

            var tbl = '';
            var head = '';
            var row = '';

            head += "<thead><tr><th colspan=5 class=sort_sub align=left><b>ID</b></th></tr></thead>";

            for (var i=0; i<theData.length; i++) {
                row += "<tr><td class=sort_sub><a href=\"http://support.microsoft.com/kb/" + theData[i].update_id.replace("KB","") + "\" target=_new>" + theData[i].update_id + "</a></td></tr>"; 
            }
            tbl += "<table id=missing_updates width=100% class=sortable cellpadding=0 cellspacing=0>";
            tbl += head;
            tbl += row;
            tbl += "</table>";

            $("#mwork").css('display','none');
            $("#mtext").css('display','');
            $("#miss_pdata").attr('onclick','');
            $("#update_table").before(tbl);
        }
    }
}


