<?php
require('../inc/db_config.php');
require('../inc/essentials.php');

session_start();

if(isset($_POST['toggle_review'])){
  $data = filteration($_POST);
  $upd = mysqli_query($con, "UPDATE `reviews` SET `status`='$data[status]' WHERE `id`='$data[id]'");
  if($upd){
    echo json_encode(["status"=>"success"]);
  } else {
    echo json_encode(["status"=>"error"]);
  }
}

if(isset($_POST['delete_review'])){
  $data = filteration($_POST);
  $del = mysqli_query($con, "DELETE FROM `reviews` WHERE `id`='$data[id]'");
  if($del){
    echo json_encode(["status"=>"success"]);
  } else {
    echo json_encode(["status"=>"error"]);
  }
}
?>