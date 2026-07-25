<?php 
  require('inc/essentials.php');
  require('inc/db_config.php');
  adminLogin();

  // Stats
  $total_bookings   = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `bookings`"))['total'];
  $pending_bookings = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `bookings` WHERE `status`='pending'"))['total'];
  $confirmed_bookings = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `bookings` WHERE `status`='confirmed'"))['total'];
  $cancelled_bookings = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `bookings` WHERE `status`='cancelled'"))['total'];
  $total_revenue    = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(`total_price`) as total FROM `bookings` WHERE `status`='confirmed'"))['total'];
  $total_users      = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `user_cred`"))['total'];
  $total_rooms      = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `rooms` WHERE `status`=1 AND `removed`=0"))['total'];
  $total_reviews    = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `reviews`"))['total'];

  if(!$total_revenue) $total_revenue = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Dashboard</title>
    <?php require('inc/links.php'); ?>
</head>
<body class="bg-light">

<?php require('inc/header.php'); ?>

<div class="container-fluid" id="main-content">
  <div class="row">
    <div class="col-lg-10 ms-auto p-4 overflow-hidden">
      <h3 class="mb-4">DASHBOARD</h3>

      <!-- Stats Cards -->
      <div class="row g-3 mb-4">

        <div class="col-lg-3 col-md-6">
          <div class="card border-0 shadow-sm rounded-3 p-3">
            <div class="d-flex align-items-center">
              <div class="bg-dark text-white rounded-3 p-3 me-3">
                <i class="bi bi-calendar-check fs-4"></i>
              </div>
              <div>
                <h6 class="mb-0 text-muted">Total Bookings</h6>
                <h3 class="mb-0 fw-bold"><?= $total_bookings ?></h3>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="card border-0 shadow-sm rounded-3 p-3">
            <div class="d-flex align-items-center">
              <div class="bg-warning text-white rounded-3 p-3 me-3">
                <i class="bi bi-hourglass-split fs-4"></i>
              </div>
              <div>
                <h6 class="mb-0 text-muted">Pending</h6>
                <h3 class="mb-0 fw-bold"><?= $pending_bookings ?></h3>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="card border-0 shadow-sm rounded-3 p-3">
            <div class="d-flex align-items-center">
              <div class="bg-success text-white rounded-3 p-3 me-3">
                <i class="bi bi-check-circle fs-4"></i>
              </div>
              <div>
                <h6 class="mb-0 text-muted">Confirmed</h6>
                <h3 class="mb-0 fw-bold"><?= $confirmed_bookings ?></h3>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="card border-0 shadow-sm rounded-3 p-3">
            <div class="d-flex align-items-center">
              <div class="bg-danger text-white rounded-3 p-3 me-3">
                <i class="bi bi-x-circle fs-4"></i>
              </div>
              <div>
                <h6 class="mb-0 text-muted">Cancelled</h6>
                <h3 class="mb-0 fw-bold"><?= $cancelled_bookings ?></h3>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="card border-0 shadow-sm rounded-3 p-3">
            <div class="d-flex align-items-center">
              <div class="bg-primary text-white rounded-3 p-3 me-3">
                <i class="bi bi-currency-rupee fs-4"></i>
              </div>
              <div>
                <h6 class="mb-0 text-muted">Total Revenue</h6>
                <h3 class="mb-0 fw-bold">Rs.<?= number_format($total_revenue, 2) ?></h3>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="card border-0 shadow-sm rounded-3 p-3">
            <div class="d-flex align-items-center">
              <div class="bg-info text-white rounded-3 p-3 me-3">
                <i class="bi bi-people fs-4"></i>
              </div>
              <div>
                <h6 class="mb-0 text-muted">Total Users</h6>
                <h3 class="mb-0 fw-bold"><?= $total_users ?></h3>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="card border-0 shadow-sm rounded-3 p-3">
            <div class="d-flex align-items-center">
              <div class="bg-secondary text-white rounded-3 p-3 me-3">
                <i class="bi bi-door-open fs-4"></i>
              </div>
              <div>
                <h6 class="mb-0 text-muted">Active Rooms</h6>
                <h3 class="mb-0 fw-bold"><?= $total_rooms ?></h3>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="card border-0 shadow-sm rounded-3 p-3">
            <div class="d-flex align-items-center">
              <div class="bg-dark text-white rounded-3 p-3 me-3">
                <i class="bi bi-star fs-4"></i>
              </div>
              <div>
                <h6 class="mb-0 text-muted">Total Reviews</h6>
                <h3 class="mb-0 fw-bold"><?= $total_reviews ?></h3>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Recent Bookings -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <h5 class="mb-3">Recent Bookings</h5>
          <div class="table-responsive">
            <table class="table table-hover border text-center">
              <thead>
                <tr class="bg-dark text-light">
                  <th>Order ID</th>
                  <th>Name</th>
                  <th>Room</th>
                  <th>Check-in</th>
                  <th>Check-out</th>
                  <th>Total</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $recent = mysqli_query($con, "SELECT b.*, r.name as room_name FROM `bookings` b 
                    JOIN `rooms` r ON b.room_id = r.id 
                    ORDER BY b.id DESC LIMIT 5");

                  while($row = mysqli_fetch_assoc($recent)){
                    if($row['status'] == 'confirmed'){
                      $badge = 'success';
                    } else if($row['status'] == 'cancelled'){
                      $badge = 'danger';
                    } else {
                      $badge = 'warning';
                    }

                    echo "
                    <tr>
                      <td>#$row[id]</td>
                      <td>$row[name]</td>
                      <td>$row[room_name]</td>
                      <td>$row[check_in]</td>
                      <td>$row[check_out]</td>
                      <td>Rs.$row[total_price]</td>
                      <td><span class='badge bg-$badge'>$row[status]</span></td>
                    </tr>
                    ";
                  }
                ?>
              </tbody>
            </table>
          </div>
          <a href="bookings.php" class="btn btn-dark btn-sm shadow-none">View All Bookings</a>
        </div>
      </div>

    </div>
  </div>
</div>

<?php require('inc/scripts.php'); ?>
</body>
</html>