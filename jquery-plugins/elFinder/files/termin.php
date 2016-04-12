<!DOCTYPE html>
<html>
<head>
<meta charset='utf-8' />
<?php
    require_once("inc/stdLib.php");
    //include("inc/crmLib.php");
    $menu = $_SESSION['menu'];
    $head = mkHeader();
    echo $menu['stylesheets'];
    echo $menu['javascripts'];
    echo $head['FULLCALCSS'];
    echo $head['BOXCSS'];
    echo $head['COLORPICKERCSS'];
    echo $head['JQTIMECSS'];
    echo $head['THEME'];
    echo $head['FULLCALJS'];
    echo $head['JQBOX'];
    echo $head['JQTIME'];
    echo $head['COLORPICKERJS'];
    echo $head['JQCOOKIE'];
    echo $head['TRANSLATION'];
  //print_r( $_SESSION );
/*************************************************** +++ ToDo ++++*******************************************************
Kategorien und User + All in Tabs darstellen!!!!
Visibility für Gruppen
************************************************************************************************************************/
?>
<script>
    $(document).ready(function() {
        var cust_vend_pers;
        var language = kivi.myconfig.countrycode;
        //alert( myconfig.kivi.countrycode );
        $( ".lang" ).each( function(){

            var key = $( this ).attr( "data-lang" );
            if( $( this ).is( ":input" ) ) $( this ).attr( 'title',  typeof( langData[language][key] ) != 'undefined' ? langData[language][key] : 'LNG ERR'  );
            else $( this ).text( typeof( langData[language][key] ) != 'undefined' ? langData[language][key] : 'LNG ERR'  );
        });
         $( "#dialog" ).dialog({
            autoOpen: false,
            height: 540,
            width: 680,
            modal: true,
            buttons: {
                "Save": function() {
                    var start = moment($( "#startDate" ).val() + ' ' + $( "#startTime" ).val(),'L LT');
                    var end = moment($( "#endDate" ).val() + ' ' + $( "#endTime" ).val(),'L LT');
                    var start_cur = moment($( "#startDate" ).val() + ' ' + $( "#startTime" ).val(),'L LT').add('h', 2);
                    var end_cur = moment($( "#endDate" ).val() + ' ' + $( "#endTime" ).val(),'L LT').add('h', 2);
                    var title = $( "#title" ).val();
                    var description = $( "#description" ).val();
                    var id = $( "#id" ).val();
                    var allDay = $( "#allDay" ).is( ":checked" );
                    var uid = $( "#user option:selected" ).val();
                    var visibility = $( "#visibility option:selected" ).val();
                    var category = $( "#category option:selected" ).val();
                    var prio = $( "input:radio:checked[name='prioRadio']" ).val();
                    var job  = $( "input:radio:checked[name='jobRadio']" ).val();
                    var color = $( "#color" ).val();
                    var done = $( "#done" ).is( ":checked" );
                    var location = $( "#location" ).val();
                    var cust_vend_pers = $( "#tmp" ).data( "cust_vend_pers" );
                    var repeat = $( "#repeat" ).val();
                    var repeat_factor = $( "#repeat_factor" ).val();
                    var repeat_quantity = $( "#repeat_quantity" ).val();
                    var repeat_end =  moment($( "#repeat_end" ).val() + ' 23:59:59' ,'L LT');


                    if ( title ) {
                        event = { //wird benötigt???
                            title: title,
                            start: start_cur,
                            end: end_cur,
                            id : id,
                            description: description,
                            allDay: allDay,
                            uid : uid,
                            //category : category,
                            //prio: prio
                            color: color,
                            done:  done,
                            location:location
                        }
                        start = moment(start).format( "YYYY-MM-DD HH:mm:ss");
                        end = moment(end).format( "YYYY-MM-DD HH:mm:ss");
                        repeat_end = moment(repeat_end).format( "YYYY-MM-DD HH:mm:ss");
                        $.ajax({
                            url: 'jqhelp/calendar.php',
                            data: {
                                task:  $("#id").val() ? 'updateEvent' : 'newEvent',
                                title: $("#title").val(),
                                description: $("#description").val(),
                                id:   $("#id").val(),
                                allDay: $( "#allDay" ).is( ":checked" ),
                                uid : 0, //$("#user option:selected").val(),
                                visibility : visibility,
                                category : $( "#category option:selected" ).val(),
                                start: start,
                                end: end,
                                prio: prio,
                                job : job,
                                color: color,
                                done: done,
                                location:location,
                                cust_vend_pers:cust_vend_pers,
                                repeat:repeat,
                                repeat_factor:repeat_factor,
                                repeat_quantity:repeat_quantity,
                                repeat_end:repeat_end,


                            },
                            type: "POST",
                            error: function () {
                                alert('Ajax Error');
                            }
                        })
                        //Aktiven Tab suchen und Calendar aktualisieren
                        var activeTab = $("#tabs .ui-tabs-panel:visible").attr("id");
                        $( "#" + activeTab + " div:first-child" ).fullCalendar( 'refetchEvents' );
                        //Tabs vom User und der Category aktualisieren wenn diese sich ändern
                        if( uid != $( "#tmp" ).data( "uid" ) ) $( "#calendargetUsers" + uid ).fullCalendar( 'refetchEvents' );
                        if( category != $( "#tmp" ).data( "category" ) ) $( "#calendargetCategory" + category ).fullCalendar( 'refetchEvents' );
                    }
                    else alert('Title is empty'); //ToDo
                    $( this ).dialog( "close" );
                },
                Delete: function() {
                    var id = $( "#id" ).val();
                    var activeTab = $("#tabs .ui-tabs-panel:visible").attr("id");
                    if( id ) $( "#" + activeTab + " div:first-child" ).fullCalendar( 'removeEvents', id );
                    $.ajax({
                        url: 'jqhelp/calendar.php',
                        data: {
                            task:  'deleteEvent',
                            id: $("#id").val()
                        },
                        type: "POST",
                        success: function() {
                            //alert( "Event gelöscht"  );
                        },
                        error: function () {
                            alert('Ajax Error');
                        }
                    })

                    $( this ).dialog( "close" );
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            },
            close: function() {
                $( this ).dialog( "close" );
            }
        });

        var calculate_end = function(){
            $( "#repeat_end" ).val( moment( $( "#endDate" ).val(), "L" ).add( $( "#repeat" ).val(), $( "#repeat_factor" ).val() * $( "#repeat_quantity" ).val() ).format( "L" ) );
        };

        var calculate_repeat_quantity = function() {
            var a = moment( $( "#endDate" ).val(), 'L' );
            var b = moment( $( "#repeat_end" ).val(), 'L' );
            var erg = Math.floor( ( b.diff( a, $( "#repeat" ).val() ) ) / $( "#repeat_factor" ).val() );
            $( "#repeat_quantity" ).val( erg < 0 ? 0 : erg );
        };

        /*** SelectBox User, Kategorie **********************************************************************************************/
        $("#category").each(function(){
            var ajaxTask = this.id == 'user' ? 'getUsers': 'getCategory';
            $(this).selectBoxIt({
                theme:      "jqueryui",
                autoWidth:  true,
                height:     "12",
                populate: function() {
console.log('vor ajaxTask');
                    var deferred = $.Deferred()
                    $.ajax({
                        url: 'jqhelp/calendar.php',
                        data: { task: ajaxTask }
                    }).done(function(json) {
                        var obj = $.parseJSON( json.trim() ) ;
                        var objCalendar = $.parseJSON( json.trim() ) ;
                        if( ajaxTask == 'getCategory' ){
                            objCalendar.unshift( {value: 0, text: "ALLE"} );
                            obj.unshift( {value: 0, text: ""} );
                        }

                        console.log( obj );

                        $.each( objCalendar, function( i, val ){
                            //Neuen Tab erzeugen
                            if( val.value != 0 ){
                                $("div#tabs ul").append("<li><a href='#tab" + ajaxTask+ val.value + "'>" + val.text + "</a></li>"); //Ok
                                $("div#tabs").append("<div id='tab" + ajaxTask + val.value + "'><div id='calendar" + ajaxTask + val.value + "'></div></div>"); //Ok
                            }
                            //alert( '#calendar' + ajaxTask + val.value );
                            //Calendar für Tabs erzeugen
                            /*** Begin Calendar *********************************************************************************************************************/
                            $('#calendar' + ajaxTask + val.value  ).fullCalendar({
                                lang: language,
                                theme: true,
                                header: {
                                    left: 'prev,next today',
                                    center: 'title',
                                    right: 'month, agendaWeek, agendaDay'
                                },
                                minTime: '<?php echo $_SESSION['termbegin'] ? $_SESSION['termbegin'] : 7;echo ":00"; ?>',//ToDo via Ajax holen
                                maxTime: '<?php echo $_SESSION['termend'] ? $_SESSION['termend'] : 19; echo ":00"; ?>',
                                slotDuration: '<?php echo "00:";echo $_SESSION['termseq'] ? $_SESSION['termseq'] : 30; echo ":00"; ?>', //termseq
                                weekNumbers: true,
                                editable: true,
                                defaultView: 'agendaWeek',
                                dragble: true,
                                selectable: true,
                                selectHelper: true,
                                events: {
                                    url: 'jqhelp/calendar.php',
                                    data: { myuid: val.value != 0 ? "<?php echo $_SESSION['loginCRM']; ?>" : '0', where: val.value == 0 ? '' : (ajaxTask == "getUsers" ? "uid = " + val.value + " AND" : "category = " + val.value + " AND") }

                                },

                                /*** New Event ************************************************************************************/
                                select: function( start, end ){
                                    $( "#title" ).val('');
                                    $( "#description" ).val('');
                                    $( "#id" ).val('');
                                    $( "#allDay ").prop( 'checked', false );//ToDo abfragen ob es allDay ist
                                    $( "#startDate" ).val( moment( start ).format( "L") );
                                    $( "#startTime" ).val( moment( start ).format( "LT") );
                                    $( "#endDate" ).val( moment( end ).format( "L") );
                                    $( "#endTime" ).val( moment( end ).format( "LT") );
                                    //$( "#user" ).data("selectBox-selectBoxIt").selectOption( ajaxTask == 'getUsers' && $( "#tabs" ).tabs( "option", "active" ) != 0  ?  val.value.toString() : "<?php echo $_SESSION['loginCRM']; ?>" );
                                    $( "#category" ).data("selectBox-selectBoxIt").selectOption( ajaxTask == 'getCategory' && $( "#tabs" ).tabs( "option", "active" ) != 0 ? val.value.toString() : '0' );
                                    $( "#visibility" ).data( "selectBox-selectBoxIt" ).selectOption( '-1' );
                                    $( "#repeat" ).data( "selectBox-selectBoxIt" ).selectOption( 'day' );
                                    $( "#prio_1,#job_false" ).prop( 'checked', true );
                                    $( "div#jobRadio,div#prioRadio" ).buttonset( "refresh" );
                                    $( "#color, #repeat_quantity" ).val( "" );
                                    $( "#repeat_factor,#repeat_quantity" ).val( '0' );
                                    $( "#colorPick" ).hide();
                                    $( "#done" ).prop( 'checked', false );
                                    $( "#location,#repeat_end" ).val('');
                                    $( "#tmp" ).data( "srcId", '' );
                                    $( "#repeat,#repeat_factor,#repeat_quantity" ).change( calculate_end );
                                    $( "#repeat_end" ).change( calculate_repeat_quantity );
                                    $( ":input[type!='button'][id!='done']" ).attr( "disabled", false ).css( "background","#FFF" );
                                    $( "div#jobRadio,div#prioRadio" ).buttonset("refresh");
                                    $( "#dialog" ).dialog( "open" );
                                },
                                /*** Edit Event *************************************************************************************/
                                eventClick: function( event ){
                                    $( "#title" ).val( event.title );
                                    $( "#description" ).val( event.description );
                                    $( "#id" ).val( event.id );
                                    $( "#allDay" ).prop( 'checked', event.allDay );
                                    /*$("#allDay").button({ text: false}).click(function(e) {
                                        $(this).button("option", {
                                            icons: { primary: $(this)[0].checked ? "ui-icon-check" : "" }
                                        })
                                    })*/
                                    $( "#startDate" ).val( moment( event.start ).format( "L") );
                                    $( "#startTime" ).val( moment( event.start ).format( "LT") );
                                    $( "#endDate" ).val( moment( event.end ? event.end : event.start ).format( "L") );
                                    $( "#endTime" ).val( moment( event.end ? event.end : event.start ).format( "LT") );
                                    //$( "#user" ).data( "selectBox-selectBoxIt" ).selectOption( String( event.uid ) );
                                    $( "#category" ).data("selectBox-selectBoxIt").selectOption( String( event.category ) );
                                    $( "#visibility" ).data("selectBox-selectBoxIt").selectOption( String( event.visibility ) );
                                    $( "#prio_" + event.prio ).prop( 'checked', true );
                                    $( "#job_" + event.job ).prop( 'checked',true );
                                    $( "div#jobRadio,div#prioRadio" ).buttonset("refresh");
                                    $( "#colorPick" ).hide();
                                    $( "#color" ).val( event.color );
                                    $( "#tmp" ).data( "cust_vend_pers", event.cust_vend_pers );
                                    $( "#tmp" ).data( "uid", event.uid );
                                    $( "#tmp" ).data( "category", event.category );
                                    $( "#location" ).val( event.location );
                                    $( "#done" ).prop( 'checked', event.done );
                                    $( "#repeat" ).data("selectBox-selectBoxIt").selectOption( event.repeat.trim() );
                                    $( "#repeat,#repeat_factor,#repeat_quantity" ).change( calculate_end );
                                    $( "#repeat_end").change( calculate_repeat_quantity );
                                    $( "#repeat_factor" ).val( event.repeat_factor );
                                    $( "#repeat_quantity" ).val( event.repeat_quantity );
                                    $( "#repeat_end" ).val( moment( event.repeat_end ).format( "L") == 'Invalid date' ? '' : moment( event.repeat_end ).format( "L")  );
                                    $( "#chkboxDone" )[event.job ? 'show' : 'hide']();
                                    $( ":input[type!='button'][id!='done']" ).attr( "disabled", event.done ).css( "background", event.done ? "#F8F8F3" : "#FFF" );
                                    $( "div#jobRadio,div#prioRadio" ).buttonset("refresh");
                                    $( "#dialog" ).dialog( "open" );
                                },
                                /*** Move Event *************************************************************************************/
                                eventDrop: function(event) {
                                    var start = moment(event.start).format( "YYYY-MM-DD HH:mm:ss");
                                    var end  = event.end ? moment(event.end).format( "YYYY-MM-DD HH:mm:ss") : moment(event.start).add('h', 1).format( "YYYY-MM-DD HH:mm:ss"); //Warum eine Stunde addieren??
                                    $.ajax({
                                        url: 'jqhelp/calendar.php',
                                        data: {
                                            task:  'updateTimestamp',
                                            start:  start,
                                            end:    end,
                                            id:     event.id ? event.id : $( "#id" ).val(),
                                            allDay: event.allDay
                                        },
                                        type: "POST",
                                        //success: function(json) {
                                        //alert('Event verschoben');
                                        //},
                                        error: function () {
                                            alert('Ajax Error');
                                        }
                                    });
                                },
                                /*** Rezize Time ********************************************************************************************/
                                eventResize: function(event) {
                                    var start = moment(event.start).format( "YYYY-MM-DD HH:mm:ss");
                                    var end   = moment(event.end).format( "YYYY-MM-DD HH:mm:ss");
                                    $.ajax({
                                        url: 'jqhelp/calendar.php',
                                        data: {
                                            task:  'updateTimestamp',
                                            start:  start,
                                            end:    end,
                                            id:     event.id,
                                            allDay: event.allDay
                                        },
                                        type: "POST",
                                        //success: function(json) {
                                            //alert('Zeit geändert');
                                        //},
                                        error: function () {
                                            alert('Ajax Error');
                                        }
                                    });
                                },
                            });
                            /*** End Calendar *****************************************************************************************************/
                        });//$.each()
                        $( "div#tabs" ).tabs( "refresh" );
                        $( "#tabs" ).tabs({ active: $.cookie( 'active_tab' ) });
                        deferred.resolve( obj );
                    });
                    return deferred;
                }
            })
        })

        var tabs = $( "#tabs" ).tabs({
            activate: function( event, ui ){
                //den angeklickten Calendar rendern
                $( "#" + ui.newPanel.attr( 'id' ) + " div:first-child" ).fullCalendar( 'render' );
                //Cookie setzen
                $.cookie( 'active_tab', $( "#tabs" ).tabs( "option", "active" ) );
             }
        });

        $( "#visibility" ).selectBoxIt({
            theme:      "jqueryui",
            autoWidth:  true,
            height: "12",
            populate:   <?php  //ToDo via Ajax aus  DB holen
                            $grps = getAllERPgroups(1);
                            array_unshift( $grps, array( 'value' => '0', 'text' => 'Benutzer' ) );
                            array_unshift( $grps, array( 'value' => '-1', 'text' => 'Alle' ) );
                            echo json_encode( $grps );
                        ?>
        });
        $( "#repeat" ).selectBoxIt({
            theme:      "jqueryui",
            autoWidth:  true,
            height: "12",
            populate: [
                { value: "day",  text: langData[language]['DAY'] },
                { value: "week", text: langData[language]['WEEK'] },
                { value: "month",text: langData[language]['MONTH'] },
                { value: "year", text: langData[language]['YEAR'] }
            ]
        });

        $( "#jobRadio,#prioRadio" ).buttonset();//.find('label').height(28);;

        $( "#job_true" ).click( function() {
            $( "#endDate" ).val( moment( $( "#endDate" ).val(), 'DD.MM.YYYY' ).add( 'years', 1 ).format( "L") );
            $( "#allDay" ).prop( 'checked', true );
            $( "#chkboxDone" ).show( "fast" );
        });

        $( "#job_false" ).click( function() {
            $( "#endDate" ).val( moment().format( "L" ) );
            $( "#allDay" ).prop( 'checked', false ).change();
            $( "#chkboxDone" ).hide( "fast" );
        });

        $( "#prio_0" ).click( function(){
            $( "#color" ).val( 'green' );
        });

        $( "#prio_1" ).click( function(){
            $( "#color" ).val( '' );
        });

        $( "#prio_2" ).click( function(){
            $( "#color" ).val( 'red' );
        });

        $( "#color" ).click( function(){
            $( "#colorPick" ).toggle();
        });

        $( "#done" ).click( function(){//

            var color = $( "#prio_0" ).is( ':checked' ) ? 'green' : '';
                color = $( "#prio_2" ).is( ':checked' ) ? 'red' : '';
            if( $( "#done" ).is( ':checked' ) ) {
                $( "#tmp" ).data( "endDate", $( "#endDate" ).val() );
                $( "#tmp" ).data( "color", $( "#color" ).val() )
                //color = $( "#color" ).val();
                $( "#endDate" ).val( moment().format( "L") );
                $( "#color" ).val( 'green' );
                $(":input[type!='button'][id!='done']").attr("disabled", true).css("background","#F8F8F3");
                $( "div#jobRadio,div#prioRadio" ).buttonset("refresh");
            }
            else{
                $( "#endDate" ).val( $( "#tmp" ).data( "endDate" ) ? $( "#tmp" ).data( "endDate" ) : moment( $( "#endDate" ) ).add( 'years', 1 ).format( "L") );
                $( "#color" ).val( $( "#tmp" ).data( "color" ) ? $( "#tmp" ).data( "color" ) : color );
                $( ":input[type!='button'][id!='done']" ).attr( "disabled", false ).css( "background","#FFF" );
                $( "div#jobRadio,div#prioRadio" ).buttonset("refresh");
            }

        });

        $('#colorPick').colorPicker({
            //defaultColor: 1,
            columns: 13,     // number of columns (optional)
            color: ['#FF7400', '#CDEB8B','#6BBA70','#006E2E','#C3D9FF','#0101DF','#4096EE','#356AA0','#FF0096','#DF0101','#B02B2C','#112211','#000000'], // list of colors (optional)
            click: function(color){
                $('#color').val(color);
                $( "#colorPick" ).toggle();
            },
        });

        $.widget("custom.catcomplete", $.ui.autocomplete, {
            _renderMenu: function(ul,items) {
                var that = this,
                currentCategory = "";
                $.each( items, function( index, item ) {
                    if ( item.category != currentCategory ) {
                        ul.append( "<li class=\'ui-autocomplete-category\'>" + item.category + "</li>" );
                        currentCategory = item.category;
                    }
                    that._renderItemData(ul,item);
                });
            }
        });

        $( "#location" ).change(function(){
            //$( "#tmp" ).data( "cust_vend_pers", false );
            }).catcomplete({
            source: "jqhelp/autocompletion.php?case=name",
            minLength: '<?php echo $_SESSION['feature_ac_minlength']; ?>',
            delay: '<?php echo $_SESSION['feature_ac_delay']; ?>',
            disabled: false,//<?php echo ($_SESSION['feature_ac']?'true':'false'); ?>,ToDo BESSER: Einstellungen via Ajax laden
            select: function( e, ui ) {
                cust_vend_pers = ui.item.src+ui.item.id;
                $( "#tmp" ).data( "cust_vend_pers" , cust_vend_pers);
            }
        }).dblclick(function() {
            var cust_vend_pers = $( "#tmp" ).data( "cust_vend_pers" );
            var Q  = cust_vend_pers.charAt( 0 );
            if( cust_vend_pers != 'false' ) window.open( "firma" + (Q=='K' ? '2' : '1') + ".php?Q=" + Q + "&id=" + cust_vend_pers.slice(1) );
           // else window.open( "http://maps.google.de/maps/place/" + $( "#location" ).val() );
        }).tooltip( {
            show: {
            effect: 'slideDown'
        },
        open: function (event, ui) {
            setTimeout(function () {
                $(ui.tooltip).hide('slow');
            }, 2500);
        },
        position: { my: "center bottom-10", at: "center top" } });

        $( "#allDay" ).change( function(){
            if( !$(this).is( ":checked" ) ){
                $( "#startTime" ).val( moment( "<?php echo $_SESSION['termbegin'] ? $_SESSION['termbegin'] : 7;echo ":00"; ?>", "HH:mm" ).format( "LT") );
                $( "#endTime" ).val( moment( "<?php echo $_SESSION['termbegin'] ? $_SESSION['termbegin'] : 7;echo ":00"; ?>", "HH:mm" ).add( "minutes", '<?php echo $_SESSION['termseq'] ? $_SESSION['termseq'] : 30; ?>' ).format( "LT") );
            }
        });
        $( "#startDate,#endDate" ).datepicker();
        $( "#startTime,#endTime" ).timepicker({
            showPeriodLabels: false,
            hours: { starts: <?php echo $_SESSION['termbegin'] ? $_SESSION['termbegin'] : 7;?>, ends: <?php echo $_SESSION['termend'] ? $_SESSION['termend'] : 19; ?> },
            minutes: { interval: 15 },
            rows: 2,
            showNowButton: true,
            showCloseButton: true,
        });
    });//ready

</script>
<style>
    body {
        margin: 0;
        padding: 0;
        font-family: "Lucida Grande",Helvetica,Arial,Verdana,sans-serif;
        font-size: 14px;
    }


    .ui-autocomplete-category {
        font-weight: bold;
        padding: .2em .4em;
        margin: .8em 0 .2em;
        line-height: 1.5;
    }



    #titleLabel         { position:absolute; top:20px; left:32px; }
    #titleInp           { position:absolute; top:20px; left:95px;}
    #jobRadio           { position:absolute; top:10px; left:465px;}
    #start              { position:absolute; top:50px; left:32px; }
    #startDateInp       { position:absolute; top:50px; left:95px;}
    #startTimeInp       { position:absolute; top:50px; left:195px; }
    #chkboxAllDay       {  position:absolute; top:48px; left:295px; }
    #chkboxDone         { position:absolute; top:78px; left:295px; }
    #colorLabel         { position:absolute; top:50px; left:475px; }

    #colorInp           { position:absolute; top:50px; left:550px; }
    #colorPick          { position:absolute; top:80px; left:450px; z-index: 1; }
    #end                { position:absolute; top:80px; left:32px; }
    #endDateInp         { position:absolute; top:80px; left:95px; }
    #endTimeInp         { position:absolute; top:80px; left:195px;}
    #categoryLabel      { position:absolute; top:120px; left:34px; }
    #categoryDrop       { position:absolute; top:140px; left:32px;}
    #userLabel          { position:absolute; top:120px; left:220px; }
    #userDrop           { position:absolute; top:140px; left:218px;}
    #visibilityLabel    { position:absolute; top:120px; left:400px; }
    #visibilityDrop     { position:absolute; top:140px; left:400px;}
    #prioLabel          { position:absolute; top:90px; left:350px; }
    #prioRadio          { position:absolute; top:80px; left:412px;}
    #locationLabel      { position:absolute; top:173px; left:32px;}
    #locationInput      { position:absolute; top:190px; left:30px;}
    #locationInfo       { position:absolute; top:200px; left:500px;}
    #repeatLabel        { position:absolute; top:173px; left:220px;}
    #repeatFactorInput  { position:absolute; top:190px; left:220px;}
    #repeatSelect       { position:absolute; top:190px; left:270px;}
    #repeatQuantityInput{ position:absolute; top:190px; left:400px;}
    #repeatEndInput     { position:absolute; top:190px; left:500px;}
    #descTitle          { position:absolute; top:220px; left:30px;}
    #description              { position:absolute; top:220px; left:30px;}
    .title{
         width: 350px;
    }
    .date{
         width: 90px;
    }
    .time{
         width: 80px;
    }
    .location{
         width: 155px;
    }
    .repeatfactor{
         width: 30px;
         text-align: right;
    }
    .inp-checkbox+label {
        margin: .5em;
        width:16px;
        height:16px;
        vertical-align:middle;
    }
    .selectboxit-container .selectboxit-options {width: 125px;}
    .selectboxit-container span, .selectboxit-container .selectboxit-options a {height: 25px; line-height: 25px;}
    #repeatSelectBoxItContainer.selectboxit-container .selectboxit-options {width: 70px;}
    .radio+label{
       /* height: 25px;/*ToDo Buttonhöhe verringern*/
    }


</style>


</head>
<body>
<?php
    echo $menu['pre_content'];
    echo $menu['start_content_ui'];
    echo '<div class="ui-widget-content">';
?>

    <div id="tmp"></div>
    <div id="dialog" class="event-dialog" title="Event">
        <div id="dialog-inner">
            <div id="titleLabel">
              <b data-lang='TITLE' class='lang'></b>
            </div>

            <div id="titleInp">
                <input type="text"  id="title" class=" ui-widget-content ui-corner-all title">
                <input type="text"  name="id" id="id"  style="visibility: hidden" class="ui-widget-content ui-corner-all">
            </div>
            <div id="jobRadio">
                <form>
                    <input type="radio" id="job_false" class="radio" name="jobRadio" value='0'><label for="job_false" data-lang='TERM' class='lang'></label>
                    <input type="radio" id="job_true" class="radio" name="jobRadio" value='1' ><label for="job_true" data-lang='JOB' class='lang'></label>
                </form>
            </div>
            <div id="start">
              <b>Start: </b>
            </div>
            <div id="startDateInp">
                <input type="text"   id="startDate" class="text ui-widget-content ui-corner-all date">
            </div>
            <div id="chkboxAllDay"><input class="inp-checkbox" id="allDay"  type="checkbox"><label for="allDay" data-lang='ALLDAY' class='lang' ></label></div>
            <div id="chkboxDone" style="display: none"><input class="inp-checkbox" id="done"  type="checkbox"><label for="done" data-lang='DONE' class='lang'></label></div>
                <div id="colorLabel" data-lang='COLOR' class='lang' ></div>
            <div id="colorInp">
                <input type="text"  id="color" class="text ui-widget-content ui-corner-all date">
            </div>
            <div id="colorPick" style="display: none"></div>
            <div id="startTimeInp">
                <input type="text"   id="startTime" class="text ui-widget-content ui-corner-all time">
            </div>
            <div id="end">
              <b data-lang='END' class='lang'></b>
            </div>
            <div id="endDateInp">
                <input type="text"   id="endDate" class="text ui-widget-content ui-corner-all date">
            </div>
            <div id="endTimeInp">
                <input type="text"   id="endTime" class="text ui-widget-content ui-corner-all time">
            </div>
            <div id="categoryLabel" data-lang='CATEGORY' class='lang'>
            </div>
            <div id="categoryDrop">
                <select  id="category">
                </select>
            </div>
            <div id="userLabel" data-lang='USER' class='lang'>
            </div>
            <div id="userDrop">
                <select id="user" >

                </select>
            </div>
            <div id="visibilityLabel" data-lang='VISIBILITY' class='lang'>
            </div>
            <div id="visibilityDrop">
                <select id="visibility">

                </select>
            </div>
            <div id="prioLabel" data-lang='PRIORITY' class='lang' >
            </div>
            <div id="prioRadio">
                <form>
                    <input type="radio" id="prio_0" name="prioRadio" value='0' ><label for="prio_0" data-lang='MINOR'  class='lang'></label>
                    <input type="radio" id="prio_1" name="prioRadio" value='1' ><label for="prio_1" data-lang='NORMAL' class='lang'></label>
                    <input type="radio" id="prio_2" name="prioRadio" value='2' ><label for="prio_2" data-lang='MAJOR'  class='lang'></label>
                </form>
            </div>
            <div id="locationLabel" data-lang='CUSTOMER_LABEL' class='lang'></div>
            <div id="locationInput">
                <form>
                    <input type="text"  id="location" class="ui-widget-content ui-corner-all location lang" autocomplete="off" data-lang='DOUBLE_KLICK'>
                </form>
            </div>
            <div id="repeatLabel" data-lang='REPS' class='lang'></div>
            <div id="repeatFactorInput">
                <form>
                    <input type="text"  id="repeat_factor" class=" ui-widget-content ui-corner-all repeatfactor " > -
                </form>
            </div>
            <div id="repeatSelect">
                <select id="repeat" ></select>
            </div>
            <div id="repeatQuantityInput">
                <form>
                    <input type="text"  id="repeat_quantity" class=" ui-widget-content ui-corner-all repeatfactor lang"><span data-lang="BY_UNTIL" class="lang"></span>
                </form>
            </div>
            <div id="repeatEndInput">
                <form>
                    <input type="text"  id="repeat_end" class=" ui-widget-content ui-corner-all date" >
                </form>
            </div>
            <textarea name="description" id="description" class="text ui-widget-content ui-corner-all" rows="10" cols="64"></textarea>
        </div>


    </div>
    <p class="ui-state-highlight ui-corner-all tools lang" style="margin-top: 20px; padding: 0.6em;" data-lang="CALENDAR"></p>
    <div id="tabs">
        <ul>
            <li><a href="#tabs-1" data-lang='ALL'  class='lang'></a></li>

        </ul>
        <div id="tabs-1">
            <div id="calendargetCategory0"></div>
        </div>

    </div>

<?php
    echo $menu['end_content'];
    echo $head['TOOLS'];
    echo "</div>"
?>
</body>
</html>
