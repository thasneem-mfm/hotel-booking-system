<?php 
  require('inc/essentials.php');
  require('inc/db_config.php');
  adminLogin();

  $limit = 5;
  $page  = isset($_GET['page']) ? intval($_GET['page']) : 1;
  $start = ($page - 1) * $limit;

  $total_res   = mysqli_query($con, "SELECT COUNT(*) as total FROM `bookings`");
  $total_count = mysqli_fetch_assoc($total_res)['total'];
  $total_pages = ceil($total_count / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php require('inc/links.php'); ?>
<title>Admin - Bookings</title>
</head>
<body class="bg-light">

<?php require('inc/header.php'); ?>

<div class="container-fluid" id="main-content">
  <div class="row">
    <div class="col-lg-10 ms-auto p-4 overflow-hidden">

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-2">BOOKINGS</h3>
      </div>

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover border text-center">
              <thead>
                <tr class="bg-dark text-light">
                  <th>#</th>
                  <th>Order ID</th>
                  <th>Name</th>
                  <th>Phone</th>
                  <th>Room</th>
                  <th>Check-in</th>
                  <th>Check-out</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $res = mysqli_query($con, "SELECT b.*, r.name as room_name FROM `bookings` b JOIN `rooms` r ON b.room_id = r.id ORDER BY b.id ASC LIMIT $start, $limit");

                  $count = $start + 1;

                  while($row = mysqli_fetch_assoc($res)){

                    if($row['status'] == 'confirmed'){
                      $badge = 'success';
                    } else if($row['status'] == 'cancelled'){
                      $badge = 'danger';
                    } else {
                      $badge = 'warning';
                    }

                    $id        = $row['id'];
                    $name      = $row['name'];
                    $phone     = $row['phone'];
                    $room_name = $row['room_name'];
                    $check_in  = $row['check_in'];
                    $check_out = $row['check_out'];
                    $price     = $row['total_price'];
                    $status    = $row['status'];

                    $p_sel = $status == 'pending'   ? 'selected' : '';
                    $c_sel = $status == 'confirmed' ? 'selected' : '';
                    $x_sel = $status == 'cancelled' ? 'selected' : '';

                    echo "
                    <tr>
                      <td>$count</td>
                      <td>#$id</td>
                      <td>$name</td>
                      <td>$phone</td>
                      <td>$room_name</td>
                      <td>$check_in</td>
                      <td>$check_out</td>
                      <td>Rs.$price</td>
                      <td><span class='badge bg-$badge'>$status</span></td>
                      <td>
                        <div class='d-flex gap-2 justify-content-center'>
                         <select class='form-select form-select-sm shadow-none' style='width:130px;' onchange='updateStatus($id, this.value)'>
                          <option value='pending' $p_sel>Pending</option>
                          <option value='confirmed' $c_sel>Confirmed</option>
                          <option value='cancelled' $x_sel>Cancelled</option>
                         </select>
                         <a href='download_booking_pdf.php?id=$id' class='btn btn-sm btn-dark shadow-none'>
                          <i class='bi bi-file-earmark-pdf'></i>
                         </a>
                        </div>
                      </td>
                    </tr>
                    ";

                    $count++;
                  }
                ?>
              </tbody>
            </table>
          </div>

          <?php if($total_pages > 1): ?>
          <nav class="mt-3">
            <ul class="pagination justify-content-center">
              <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
              </li>
              <?php for($i = 1; $i <= $total_pages; $i++): ?>
              <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
              </li>
              <?php endfor; ?>
              <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
              </li>
            </ul>
          </nav>
          <?php endif; ?>

        </div>
      </div>

    </div>
  </div>
</div>

<?php require('inc/scripts.php'); ?>

<script>
function updateStatus(id, status){
  let data = new FormData();
  data.append('update_status', '');
  data.append('id', id);
  data.append('status', status);

  let xhr = new XMLHttpRequest();
  xhr.open("POST", "ajax/bookings.php", true);
  xhr.onload = function(){
    let res = JSON.parse(this.responseText);
    if(res.status == 'success'){
      alert('success', 'Status updated!');
      setTimeout(() => location.reload(), 1000);
    } else {
      alert('error', 'Update failed!');
    }
  };
  xhr.send(data);
}
</script>

</body>
</html>