<?php
    require_once("inc/stdLib.php");
    if ( $_SESSION['Admin'] != 1 ) header("location:no.html");
    include("inc/template.inc");
    include("inc/crmLib.php");
    include_once("inc/UserLib.php");
    if ($_POST["newgrp"]) {
        $msg=saveGruppe($_POST);
    } else if ($_POST["delgrp"] && $_POST["gruppe"]>0) {
        $msg=delGruppe($_POST["gruppe"]);
    } else if ($_POST["selgrp"]) {
         $mitglieder=getMitglieder($_POST["gruppe"]);
    } else if ($_POST["usrgrp"]) {
        if ($_POST["grpusr"]) {
            $mitgl=array_unique($_POST["grpusr"]);
        } else {
            $mitgl="";
        }
        saveMitglieder($mitgl,$_POST["gruppe"]);
        $gruppe=-1;
    }
    $grp=getGruppen();
    $mit=getAllUser(array(0=>true,1=>"%"));
    $t = new Template($base);
    doHeader($t);
    $t->set_file(array("usr2" => "user3.tpl"));
    $t->set_var(array(
            UID    => $_SESSION["loginCRM"],
            msg    => $msg
            ));
    $t->set_block("usr2","Selectbox","Block");
    if ($grp) {
        foreach($grp as $zeile) {
            if ($_POST["gruppe"]==$zeile["grpid"]) { $sel=" selected"; } else { $sel=""; };
            $t->set_var(array(
                SEL     =>    $sel,
                GRPID   =>    $zeile["grpid"],
                NAME    =>    $zeile["grpname"]
            ));
            $t->parse("Block","Selectbox",true);
        }
    }
    $t->set_block("usr2","Selectbox2","Block2");
    if ($mitglieder) {
        foreach($mitglieder as $zeile) {
            $t->set_var(array(
                USRID      =>    $zeile["usrid"],
                USRNAME    =>    $zeile["login"].", ".$zeile["name"]
            ));
            $t->parse("Block2","Selectbox2",true);
        }
    }
    $t->set_block("usr2","Selectbox3","Block3");
    if ($mit) {
        foreach($mit as $zeile) {
            $t->set_var(array(
                USRID      =>    $zeile["id"],
                USRNAME    =>    $zeile["login"].", ".$zeile["name"]
            ));
            $t->parse("Block3","Selectbox3",true);
        }
    }
    $t->pparse("out",array("usr2"));
?>
