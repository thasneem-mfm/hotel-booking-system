<?php
require('inc/links.php');

if(!(isset($_SESSION['login']) && $_SESSION['login']==true)){
  redirect('index.php');
}

require('vendor/autoload.php');

$id      = intval($_GET['id']);
$user_id = $_SESSION['uId'];

$res = mysqli_query($con, "SELECT b.*, r.name as room_name FROM `bookings` b 
  JOIN `rooms` r ON b.room_id = r.id 
  WHERE b.id = $id AND b.user_id = $user_id 
  LIMIT 1");

if(mysqli_num_rows($res) == 0){
  redirect('my_bookings.php');
}

$row = mysqli_fetch_assoc($res);

$mpdf = new \Mpdf\Mpdf();

$html = "
<h2 style='text-align:center;'>Booking Receipt</h2>
<h4 style='text-align:center;'>Order ID: #{$row['id']}</h4>
<br>
<table border='1' cellpadding='10' cellspacing='0' width='100%' style='border-collapse:collapse; font-size:13px;'>
  <tr><td><strong>Name</strong></td><td>{$row['name']}</td></tr>
  <tr><td><strong>Phone</strong></td><td>{$row['phone']}</td></tr>
  <tr><td><strong>Address</strong></td><td>{$row['address']}</td></tr>
  <tr><td><strong>Room</strong></td><td>{$row['room_name']}</td></tr>
  <tr><td><strong>Check-in</strong></td><td>{$row['check_in']}</td></tr>
  <tr><td><strong>Check-out</strong></td><td>{$row['check_out']}</td></tr>
  <tr><td><strong>Total Amount</strong></td><td>Rs.{$row['total_price']}</td></tr>
  <tr><td><strong>Payment</strong></td><td>Cash on Arrival</td></tr>
  <tr><td><strong>Status</strong></td><td>{$row['status']}</td></tr>
  <tr><td><strong>Booked At</strong></td><td>{$row['booked_at']}</td></tr>
</table>
";

$mpdf->WriteHTML($html);
$mpdf->Output('my_booking_'.$row['id'].'.pdf', 'D');
?>