<?php

header("Content-Type: text/event-stream\n\n");

$nctid = $_GET[nct_id];
#$nctid = "NCT02032823";

$servername = "xx";
$username = "xx";
$password = "xx";
$db_name = "xx";
$port = 3500;


#$db_link = mysqli_connect($servername, $username, $password, $db_name, $port);

#$db = new SQLite3("./rctportal.db");
$sql = "select id, trial_abstract, trial_target, status, disease_name, objective, phase, age, gender from jpc where textall like '%".$nctid."%';";


#mysqli_set_charset($db_link, "utf8");
#$results = mysqli_query($db_link, $sql);
$results = $db->query($sql);


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

exit;



?>


