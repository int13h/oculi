function mkChart(type,site) {

    if(type == 2) {exit};

    Array.prototype.unique = function() {
        var o = {};
        var l = this.length;
        var r = [];

        for(i=0; i<l;i+=1) {
            o[this[i]] = this[i];
        }
        for(i in o) {
            r.push(o[i]);
        }

        return r;
    }

    var c_ak="#b88a00";
    var c_am="#444444";
    var c_an="#8a00b8";
    var c_bu="#2e00b8";
    var c_cl="#ffe93b";
    var c_co="#b82e00";
    var c_cu="#f5b800";
    var c_dg="#000000";
    var c_di="#cfc7b0";
    var c_in="#33ccff";
    var c_ki="#525252";
    var c_lu="#ffff7a";
    var c_ma="#929292";
    var c_ns="#cc0000";
    var c_pi="#339933";
    var c_se="#cc0000";
    var c_sh="#bdff7a";
    var c_st="#75a3d1";
    var c_tr="#ff7abd";
    var c_dw="#f53d00";

    var theSites=['Akerley','Amherst','Annapolis','Burridge',
                  'Chain Lake','Cogs','Cumberland','D. Gate',
                  'Digby','Institute','Kingstec','Lunenburg',
                  'Marconi','Pictou','Servers','Shelburne',
                  'Strait Area','Truro','Waterfront'];

    var urArgs = "type=" + type + "&site=" + site;

    $(function(){
        //Get the JSON string and pass it to the callback function
        $.get(".inc/compliance/history.php?" + urArgs, function(data){cb(data)});
    })

    function cb(data){

        var timestamp   = [];
        var s_ak = [];
        var s_am = [];
        var s_an = [];
        var s_bu = [];
        var s_cl = [];
        var s_co = [];
        var s_cu = [];
        var s_dg = [];
        var s_di = []; 
        var s_dw = []; 
        var s_in = [];
        var s_ki = [];
        var s_lu = [];
        var s_ma = [];
        var s_pi = [];
        var s_se = [];
        var s_sh = [];
        var s_st = [];
        var s_tr = [];
        var s_er = [];

        //converts the JSON string to a JavaScript object
        eval("theData=" + data);

        for (var i=0; i<theData.length; i++) {

            switch(theData[i].sn) {

                case 'Akerley':		s_ak.push(theData[i].cnt); break;
                case 'Amherst':         s_am.push(theData[i].cnt); break;
                case 'AVCM':            s_an.push(theData[i].cnt); break;
                case 'Burridge':        s_bu.push(theData[i].cnt); break;
                case 'Chain Lake':      s_cl.push(theData[i].cnt); break;
                case 'AVCL':            s_co.push(theData[i].cnt); break;
                case 'Cumberland':      s_cu.push(theData[i].cnt); break;
                case 'Dartmouth Gate':  s_dg.push(theData[i].cnt); break;
                case 'Digby':           s_di.push(theData[i].cnt); break;
                case 'Waterfront':      s_dw.push(theData[i].cnt); break;
                case 'Institute':       s_in.push(theData[i].cnt); break;
                case 'Kingstec':        s_ki.push(theData[i].cnt); break;
                case 'Lunenburg':       s_lu.push(theData[i].cnt); break;
                case 'Marconi':         s_ma.push(theData[i].cnt); break;
                case 'Pictou':          s_pi.push(theData[i].cnt); break;
                case 'Servers':         s_se.push(theData[i].cnt); break;
                case 'Shelburne':       s_sh.push(theData[i].cnt); break;
                case 'Strait Area':     s_st.push(theData[i].cnt); break;
                case 'Truro':           s_tr.push(theData[i].cnt); break;
            }  

            timestamp.push(theData[i].ts);
        }

        // Draw the graph
        var line = new RGraph.Line("siteLines", s_ak,s_am,s_an,s_bu,s_cl,
                                                s_co,s_cu,s_dg,s_di,s_in,
                                                s_ki,s_lu,s_ma,s_pi,s_se,
                                                s_sh,s_st,s_tr,s_dw);
        line.Set('chart.background.barcolor1', '#e9e9e9');
        line.Set('chart.background.barcolor2', '#e9e9e9');
        line.Set('chart.background.grid.color', '#d4d4d4');
        line.Set('chart.background.grid.hlines', true);
        line.Set('chart.background.grid.autofit.align', true);
        line.Set('chart.colors', [c_ak,c_am,c_an,c_bu,c_cl,
                                  c_co,c_cu,c_dg,c_di,c_in,
                                  c_ki,c_lu,c_ma,c_pi,c_se,
                                  c_sh,c_st,c_tr,c_dw]);
        line.Set('chart.linewidth', 2);
        line.Set('chart.filled', false);
        line.Set('chart.hmargin', 5);
        line.Set('chart.labels', timestamp.unique());
        line.Set('chart.text.font', 'verdana');
        line.Set('chart.title', 'Number of High Severity Hosts'); 
        line.Set('chart.text.angle', 90);
        line.Set('chart.gutter.left', 40);
        line.Set('chart.gutter.bottom', 55);
        line.Set('chart.key', theSites);
        line.Set('chart.key.position', 'graph');
        line.Set('chart.key.shadow', true);
        line.Set('chart.key.shadow.offsetx', 0);
        line.Set('chart.key.shadow.offsety', 0);
        line.Set('chart.key.shadow.blur', 15);
        line.Set('chart.key.shadow.color', '#ccc');
        line.Set('chart.key.interactive', true);
        line.Set('chart.key.position.x', 50);
        line.Set('chart.key.rounded', true);
        
        //RGraph.Clear(siteLines);
        line.Draw();
        
    }
}
