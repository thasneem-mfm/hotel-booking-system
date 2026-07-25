<?php

require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');
require('../inc/sendgrid/sendgrid-php.php');

session_start();
date_default_timezone_set("Asia/Colombo");

function send_booking_mail($uemail, $uname, $room_name, $check_in, $check_out, $total)
{
  $email = new \SendGrid\Mail\Mail();
  $email->setFrom(SENDGRID_EMAIL, SENDGRID_NAME);
  $email->setSubject("Booking Received - " . SENDGRID_NAME);
  $email->addTo($uemail);
  $email->addContent("text/html", "
    <h2>Booking Received! 🙏</h2>
    <p>Dear <strong>$uname</strong>,</p>
    <p>Your booking has been received successfully. We will confirm it shortly.</p>
    <table border='1' cellpadding='8' cellspacing='0'>
      <tr><td><strong>Room</strong></td><td>$room_name</td></tr>
      <tr><td><strong>Check-in</strong></td><td>$check_in</td></tr>
      <tr><td><strong>Check-out</strong></td><td>$check_out</td></tr>
      <tr><td><strong>Total Amount</strong></td><td>Rs.$total</td></tr>
      <tr><td><strong>Payment</strong></td><td>Cash on Arrival</td></tr>
    </table>
    <br>
    <p>Thank you for choosing " . SENDGRID_NAME . "!</p>
  ");

  $sendgrid = new \SendGrid(SENDGRID_API_KEY);

  if($sendgrid->send($email)){
    return 1;
  } else {
    return 0;
  }
}

if(isset($_POST['check_availability']))
{
  $frm_data = filteration($_POST);
  $status = "";
  $result = "";

  $today_date   = new DateTime(date("Y-m-d"));
  $checkin_date  = new DateTime($frm_data['check_in']);
  $checkout_date = new DateTime($frm_data['check_out']);

  if($checkin_date == $checkout_date){
    $status = 'check_in_out_equal';
    $result = json_encode(["status"=>$status]);
  }
  else if($checkout_date < $checkin_date){
    $status = 'check_out_earlier';
    $result = json_encode(["status"=>$status]);
  }
  else if($checkin_date < $today_date){
    $status = 'check_in_earlier';
    $result = json_encode(["status"=>$status]);
  }

  if($status != ''){
    echo $result;
  }
  else{
    $room_id_check = $_SESSION['room']['id'];

    $room_q = select("SELECT `quantity` FROM `rooms` WHERE `id`=? LIMIT 1", [$room_id_check], "i");
    $room_qty = mysqli_fetch_assoc($room_q)['quantity'];

    $booked_count = select(
      "SELECT COUNT(*) as total FROM `bookings` 
        WHERE `room_id` = ? 
        AND `status` != 'cancelled'
        AND (
         (check_in <= ? AND check_out > ?) OR
         (check_in < ? AND check_out >= ?) OR
         (check_in >= ? AND check_out <= ?)
        )",
        [$room_id_check, $frm_data['check_in'], $frm_data['check_in'],
          $frm_data['check_out'], $frm_data['check_out'],
          $frm_data['check_in'], $frm_data['check_out']],
          "issssss"
    );

      $booked_num = mysqli_fetch_assoc($booked_count)['total'];

      if($booked_num >= $room_qty){
       echo json_encode(["status"=>"unavailable"]);
       exit;
      }

    $count_days = date_diff($checkin_date, $checkout_date)->days;
    $payment    = $_SESSION['room']['price'] * $count_days;

    $_SESSION['room']['payment']   = $payment;
    $_SESSION['room']['available'] = true;

    echo json_encode(["status"=>"available", "days"=>$count_days, "payment"=>$payment]);
  }
}

if(isset($_POST['confirm_booking']))
{
  if(!isset($_SESSION['room']) || $_SESSION['room']['available'] != true){
    echo json_encode(["status"=>"error", "msg"=>"Session expired. Please recheck dates."]);
    exit;
  }

  $frm_data = filteration($_POST);

  $room_id  = $_SESSION['room']['id'];
  $price    = $_SESSION['room']['payment'];
  $user_id  = $_SESSION['uId'];
  $name     = $frm_data['name'];
  $phone    = $frm_data['phonenum'];
  $address  = $frm_data['address'];
  $checkin  = $frm_data['check_in'];
  $checkout = $frm_data['check_out'];

  $ins = insert(
    "INSERT INTO `bookings` (`room_id`,`user_id`,`name`,`phone`,`address`,`check_in`,`check_out`,`total_price`,`status`) VALUES (?,?,?,?,?,?,?,?,'pending')",
    [$room_id, $user_id, $name, $phone, $address, $checkin, $checkout, $price],
    "iisssssd"
  );

  if($ins){
    $_SESSION['room']['available'] = false;

    $user_res  = select("SELECT * FROM `user_cred` WHERE `id`=? LIMIT 1", [$user_id], "i");
    $user_data = mysqli_fetch_assoc($user_res);

    $room_res  = select("SELECT * FROM `rooms` WHERE `id`=? LIMIT 1", [$room_id], "i");
    $room_data = mysqli_fetch_assoc($room_res);

    send_booking_mail(
      $user_data['email'],
      $name,
      $room_data['name'],
      $checkin,
      $checkout,
      $price
    );

    echo json_encode(["status"=>"success"]);
  } else {
    echo json_encode(["status"=>"error", "msg"=>"Booking failed. Try again."]);
  }
}

?>