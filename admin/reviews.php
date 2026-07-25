<?php 
  require('inc/essentials.php');
  require('inc/db_config.php');
  adminLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php require('inc/links.php'); ?>
<title>Admin - Reviews</title>
</head>
<body class="bg-light">

<?php require('inc/header.php'); ?>

<div class="container-fluid" id="main-content">
  <div class="row">
    <div class="col-lg-10 ms-auto p-4 overflow-hidden">
      <h3 class="mb-4">REVIEWS</h3>

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover border text-center">
              <thead>
                <tr class="bg-dark text-light">
                  <th>#</th>
                  <th>User</th>
                  <th>Room</th>
                  <th>Rating</th>
                  <th>Review</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $res = mysqli_query($con, "SELECT rv.*, uc.name as user_name, r.name as room_name 
                    FROM `reviews` rv 
                    JOIN `user_cred` uc ON rv.user_id = uc.id 
                    JOIN `rooms` r ON rv.room_id = r.id 
                    ORDER BY rv.id DESC");

                  $count = 1;
                  while($row = mysqli_fetch_assoc($res)){

                    $stars = '';
                    for($s = 1; $s <= 5; $s++){
                      if($s <= $row['rating']){
                        $stars .= '<i class="bi bi-star-fill text-warning"></i>';
                      } else {
                        $stars .= '<i class="bi bi-star text-warning"></i>';
                      }
                    }

                    $status_badge = $row['status'] == 1 ? 
                      "<span class='badge bg-success'>Active</span>" : 
                      "<span class='badge bg-danger'>Hidden</span>";

                    $toggle_btn = $row['status'] == 1 ?
                      "<button onclick='toggleReview({$row['id']}, 0)' class='btn btn-sm btn-warning shadow-none me-1'>Hide</button>" :
                      "<button onclick='toggleReview({$row['id']}, 1)' class='btn btn-sm btn-success shadow-none me-1'>Show</button>";

                    $id          = $row['id'];
                    $user_name   = $row['user_name'];
                    $room_name   = $row['room_name'];
                    $review_text = $row['review'];
                    $created_at  = date("d M Y", strtotime($row['created_at']));

                    echo "
                    <tr>
                      <td>$count</td>
                      <td>$user_name</td>
                      <td>$room_name</td>
                      <td>$stars</td>
                      <td style='max-width:200px; text-align:left;'>$review_text</td>
                      <td>$created_at</td>
                      <td>$status_badge</td>
                      <td>
                        $toggle_btn
                        <button onclick='deleteReview($id)' class='btn btn-sm btn-danger shadow-none'>
                          <i class='bi bi-trash'></i>
                        </button>
                      </td>
                    </tr>
                    ";
                    $count++;
                  }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<?php require('inc/scripts.php'); ?>

<script>
function toggleReview(id, status){
  let data = new FormData();
  data.append('toggle_review', '');
  data.append('id', id);
  data.append('status', status);

  let xhr = new XMLHttpRequest();
  xhr.open("POST", "ajax/reviews.php", true);
  xhr.onload = function(){
    let res = JSON.parse(this.responseText);
    if(res.status == 'success'){
      alert('success', 'Review status updated!');
      setTimeout(() => location.reload(), 1000);
    } else {
      alert('error', 'Update failed!');
    }
  };
  xhr.send(data);
}

function deleteReview(id){
  if(confirm("Are you sure you want to delete this review?")){
    let data = new FormData();
    data.append('delete_review', '');
    data.append('id', id);

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/reviews.php", true);
    xhr.onload = function(){
      let res = JSON.parse(this.responseText);
      if(res.status == 'success'){
        alert('success', 'Review deleted!');
        setTimeout(() => location.reload(), 1000);
      } else {
        alert('error', 'Delete failed!');
      }
    };
    xhr.send(data);
  }
}
</script>

</body>
</html>