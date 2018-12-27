<?php

header("Content-Type: text/event-stream\n\n");


$ori_term = $_GET[term];
$ori_term2 = $_GET[term2];
$ori_term3 = $_GET[term3];
$status = $_GET[status];

$py_home = "/usr/local/package/python2.7/current/bin/python";
#$py_home = "/usr/bin/python";

$servername = "xx";
$username = "xx";
$password = "xx";
$db_name = "xx";
$port = 3500;


$cmd = $py_home.' ./translation.py ' . $ori_term;
$term = exec($cmd);

$cmd = $py_home.' ./translation.py ' . $ori_term2;
$term2 = exec($cmd);

$cmd = $py_home.' ./translation.py ' . $ori_term3;
$term3 = exec($cmd);

echo "data: trans|".$term."|".$term2."|".$term3."\n\n";

if ($term == "" and $term2 == "" and $term3 == ""){
    echo "data: end\n\n";   
}

$search_str = "";
if ($term2 != ""){
    $search_str .= " and (textall like '%$term2%'";
}
if ($ori_term2 != ""){
    $search_str .= " or textall like '%$ori_term2%')";
}

if ($term3 != ""){
    $search_str .= " and (textall like '%$term3%'";
}
if ($ori_term3 != ""){
    $search_str .= " or textall like '%$ori_term3%')";
}



$arr = explode("|", $status);
$status_str = "status = '' or ";
foreach ($arr as $a){
    $status_str = $status_str."status = '".$a."' or ";
}



$db = new SQLite3("./rctportal.db");
#$db_link = mysql_connect($servername, $username, $password, $db_name, $port);

#mysqli_set_charset($db_link, "utf8");


$sql = "select id, trial_abstract, status, disease_name, phase, age, gender, trial_target from jpc where (textall like '%$term%' or textall like '%$ori_term%')";
$sql = $sql.$search_str." and (".(substr($status_str, 0, -4).");");


$results = $db->query($sql);
#$results = mysqli_query($db_link, $sql);


#while ($row = $results->fetch_assoc()){
while ($row = $results->fetchArray(SQLITE3_ASSOC)){
    $id = $row["id"];
    $abst = $row["trial_abstract"];
    $status = $row["status"];
    $disease = $row["disease_name"];
    $phase = $row["phase"];
    $age = $row["age"];
    $gender = $row["gender"];
    $sponser = $row["trial_target"];

    $r_str = str_replace(array("\r\n","\n","\r"), "<br>", $id."|".$abst."|".$status."|".$disease."|".$phase."|".$age."|".$gender."|".$sponser);
    echo "data: ".$r_str, "\n\n";

    ob_flush();
    flush();
    usleep(0);
}

#mysqli_close($db_link);
$db->close();
echo "data: end\n\n";





?>


