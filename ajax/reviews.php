<?php
require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');

session_start();

if(isset($_POST['submit_review'])){
  
  if(!(isset($_SESSION['login']) && $_SESSION['login']==true)){
    echo json_encode(["status"=>"error", "msg"=>"Please login!"]);
    exit;
  }

  $data       = filteration($_POST);
  $user_id    = $_SESSION['uId'];
  $booking_id = $data['booking_id'];
  $room_id    = $data['room_id'];
  $rating     = $data['rating'];
  $review     = $data['review'];

  // Already reviewed check
  $check = select("SELECT * FROM `reviews` WHERE `booking_id`=? AND `user_id`=? LIMIT 1",
    [$booking_id, $user_id], "ii");

  if(mysqli_num_rows($check) > 0){
    echo json_encode(["status"=>"error", "msg"=>"Already reviewed!"]);
    exit;
  }

  // Booking user-check
  $booking_check = select("SELECT * FROM `bookings` WHERE `id`=? AND `user_id`=? LIMIT 1",
    [$booking_id, $user_id], "ii");

  if(mysqli_num_rows($booking_check) == 0){
    echo json_encode(["status"=>"error", "msg"=>"Invalid booking!"]);
    exit;
  }

  $ins = insert(
    "INSERT INTO `reviews` (`booking_id`,`user_id`,`room_id`,`rating`,`review`) VALUES (?,?,?,?,?)",
    [$booking_id, $user_id, $room_id, $rating, $review],
    "iiiis"
  );

  if($ins){
    echo json_encode(["status"=>"success"]);
  } else {
    echo json_encode(["status"=>"error", "msg"=>"Failed to submit review!"]);
  }
}
?>