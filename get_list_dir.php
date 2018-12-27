<?php


header("Content-Type: text/event-stream\n\n");

$user = $_GET[user];
#$user = "testuser";


function read_file($type, $filelist){
    foreach ($filelist as $file){
        $handle = fopen($file, "r");
        $str = $type . "|||" . basename($file) . "|||";
        while ($line = fgets($handle)){
            $str .= trim($line) . "|||";
        }
        fclose($handle);
        echo "data: " . $str, "\n\n";
        //echo $str, "\n";
    }
}

$path = "../cgi-bin/wfg_api/output/".$user."/tmp/";
$filelist = glob($path . '*',GLOB_BRACE);
read_file("waiting", $filelist);


$path = "../cgi-bin/wfg_api/output/".$user."/analyzing/";
$filelist = glob($path . '*',GLOB_BRACE);
read_file("analyzing", $filelist);


$path = "../cgi-bin/wfg_api/output/".$user."/complete/";
#$filelist = glob($path . '*', GLOB_NOSORT);
#array_multisort(array_map('filetime', $filelist),SORT_NUMERIC,SORT_DESC,$filelist);
$filelist = glob($path . '*',GLOB_BRACE);
read_file("complete", $filelist);


$path = "../cgi-bin/wfg_api/output/".$user."/failure/";
$filelist = glob($path . '*',GLOB_BRACE);
read_file("failure", $filelist);

ob_flush();
flush();
#echo "data: end\n\n";



?>


