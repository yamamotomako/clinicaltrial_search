<?php

header("Content-Type: text/event-stream\n\n");

$term = $_GET[term];
$term2 = $_GET[term2];
$term3 = $_GET[term3];
$status = $_GET[status];
$lc = $_GET[location];

#$term="olaparib";
#$status="Not yet recruiting|Recruiting|Enrolling by Invitation|Active, not recruiting|Available";
#$lc="all";

$servername = "xx";
$username = "xx";
$password = "xx";
$db_name = "xx";
$port = 3500;

$nct_id_array = [];
$jpc_id_array = [];
$sec_hash = [];


$sql_head = "select nct_id,secondary_id,overall_status,brief_title,phase,cond,min_age,location_countries,gender,agency,intervention_name from ctg_";
#$sql_head = "select count(*) from ctg_";
$sql_tail = " where textall like '%$term%'";


$str = "";
if ($term2 != ""){
    $str .= " and textall like '%$term2%'";
}
if ($term3 != ""){
    $str .= " and textall like '%$term3%'";
}

#$sql_tail = $sql_tail.$str;
$sql_tail = $sql_tail.$str." and (";


$str = "";
$arr = explode("|", $status);
foreach ($arr as $a){
    $str = $str."overall_status = '".$a."' or ";
}
$sql_tail = $sql_tail.(substr($str, 0, -4).");");


$db_link = mysqli_connect($servername, $username, $password, $db_name, $port);


if ($lc == "all"){
    for ($num = 1; $num < 380; $num++){
        $num = sprintf('%04d', $num);

        $sql = $sql_head.$num.$sql_tail;

        #echo $num.":";

        $result = mysqli_query($db_link, $sql);

        #echo $result->num_rows."|||"."\n\n";

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $nct_id = $row["nct_id"];
                $second_id = $row["secondary_id"];
                $title = $row["brief_title"];
                $status = $row["overall_status"];
                $phase = $row["phase"];
                $condition = $row["cond"];
                $min_age = $row["min_age"];
                #$max_age = $row["max_age"];
                $gender = $row["gender"];
                $sponser = $row["agency"];
                $location = $row["location_countries"];
                $intervention_name = $row["intervention_name"];

                $r_str = $num."|".str_replace(array("\r\n","\n","\r"), "<br>", $nct_id."|".$second_id."|".$title."|".$status."|".$phase."|".$condition."|".$min_age."|".$gender."|".$sponser."|".$location."|".$intervention_name);
                echo "data: ".$r_str, "\n\n";

                $nct_id_array[] = $nct_id;
                $arr = explode("\n", $second_id);
                $arr = array_map("trim", $arr);
                $arr = array_filter($arr, "strlen");
                $arr = array_unique($arr);
                $sec_hash[$nct_id] = $arr;

                ob_flush();
                flush();
                usleep(0);
            }
        }
        
    }
}else{
        $sql = $sql_head."jp".$sql_tail;

        $result = mysqli_query($db_link, $sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $nct_id = $row["nct_id"];
                $second_id = $row["secondary_id"];
                $title = $row["brief_title"];
                $status = $row["overall_status"];
                $phase = $row["phase"];
                $condition = $row["cond"];
                $min_age = $row["min_age"];
                #$max_age = $row["max_age"];
                $gender = $row["gender"];
                $sponser = $row["agency"];
                $location = $row["location_countries"];
                $intervention_name = $row["intervention_name"];

                $r_str = $num."|".str_replace(array("\r\n","\n","\r"), "<br>", $nct_id."|".$second_id."|".$title."|".$status."|".$phase."|".$condition."|".$min_age."|".$gender."|".$sponser."|".$location."|".$intervention_name);
                echo "data: ".$r_str, "\n\n";

                $nct_id_array[] = $nct_id;
                $arr = explode("\n", $second_id);
                $arr = array_map("trim", $arr);
                $arr = array_filter($arr, "strlen");
                $arr = array_unique($arr);
                $sec_hash[$nct_id] = $arr;

                ob_flush();
                flush();
                usleep(0);
            }
        }
}



mysqli_close($db_link);

echo "data: end\n\n";
exit;

#---------------------------------------------------------
# sql query for jp database
#---------------------------------------------------------

if (count($nct_id_array) == 0){
    echo "data: end\n\n";
    $db->close();
    exit;
}




$db = new SQLite3("./rctportal.db");

$sql_jpc = "select id, trial_abstract, trial_target, status, disease_name, objective, phase, age, gender from jpc where textall like ";


foreach ($nct_id_array as $id){

    $results = $db->query($sql_jpc."'%".$id."%';");

    while ($row = $results->fetchArray(SQLITE3_ASSOC)){

        $jpc_id_array[] = $id;

        $jpc_id = $row["id"];
        $abst = $row["trial_abstract"];
        $status = $row["status"];
        $disease = $row["disease_name"];
        $phase = $row["phase"];
        $age = $row["age"];
        $gender = $row["gender"];
        $sponser = $row["trial_target"];

        $r_str = str_replace(array("\r\n","\r","\n"), "<br>", $id."|".$jpc_id."|".$abst."|".$status."|".$disease."|".$phase."|".$age."|".$gender."|".$sponser);

        echo "data: jpc-".$r_str, "\n\n";
        ob_flush();
        flush();
        usleep(0);

    }
}


foreach ($nct_id_array as $id){
    if (! in_array($id, $jpc_id_array)){
        $sec_id_array[] = $id;
    }
}

//echo print_r($nct_id_array);
//echo print_r($jpc_id_array);
//echo print_r($umin_id_array);


foreach ($sec_id_array as $id){

    $sql_umin = "select id, brief_title, status, condition, primary_outcome, secondary_outcome, age_lower_limit, gender, primary_sponser from umin where ";
    //echo $id . "\n";
    $sec_id = $sec_hash[$id];
    //echo print_r($sec_id) . "\n";

    if (count($sec_id) == 0){
        continue;
    }

    $str = "";
    $cnt = 1;
    foreach($sec_id as $buf){
        $str .= " textall like '%" . $buf . "%'";
        if ($cnt == count($sec_id)){
            $str .= ";";
        }else{
            $str .= " or";
        }
        $cnt = $cnt + 1;
    }

    $sql_umin = $sql_umin . $str;
    //echo $sql_umin . "\n";

    $results = $db->query($sql_umin);

    while ($row = $results->fetchArray(SQLITE3_ASSOC)){
        $umin_id = $row["id"];
        $abst = $row["brief_title"];
        $status = $row["status"];
        $cond = $row["condition"];
        $p_out = $row["primary_outcome"];
        $age_lower = $row["age_lower_limit"];
        $gender = $row["gender"];
        $sponser = $row["primary_sponser"];

        $r_str = str_replace(array("\r\n","\r","\n"), "<br>", $id."|".$umin_id."|".$abst."|".$status."|".$cond."|".""."|".$age_lower."|".$gender."|".$sponser);

        echo "data: umi-".$r_str, "\n\n";
        ob_flush();
        flush();
        usleep(0);

    }
}



echo "data: end\n\n";



$db->close();

?>


