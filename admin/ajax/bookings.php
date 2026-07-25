<?php
require('../inc/db_config.php');
require('../inc/essentials.php');

session_start();

if(isset($_POST['update_status'])){
  $data = filteration($_POST);
  
  $upd = mysqli_query($con, "UPDATE `bookings` SET `status`='$data[status]' WHERE `id`='$data[id]'");

  if($upd){
    echo json_encode(["status"=>"success"]);
  } else {
    echo json_encode(["status"=>"error"]);
  }
}
?>