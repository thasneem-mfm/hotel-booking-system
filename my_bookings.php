<?php require('inc/links.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $settings_r['site_title'] ?> - My Bookings</title>
</head>
<body class="bg-light">

<?php require('inc/header.php'); ?>

<?php
if(!(isset($_SESSION['login']) && $_SESSION['login']==true)){
  redirect('index.php');
}

$user_id = $_SESSION['uId'];

$res = mysqli_query($con, "SELECT b.*, r.name as room_name FROM `bookings` b 
  JOIN `rooms` r ON b.room_id = r.id 
  WHERE b.user_id = '$user_id' 
  ORDER BY b.id DESC");
?>

<div class="container my-5">
  <div class="row">
    <div class="col-12 mb-4 px-4">
      <h2 class="fw-bold">MY BOOKINGS</h2>
      <div style="font-size: 14px;">
        <a href="index.php" class="text-secondary text-decoration-none">HOME</a>
        <span class="text-secondary"> > </span>
        <a href="#" class="text-secondary text-decoration-none">MY BOOKINGS</a>
      </div>
    </div>

    <?php if(mysqli_num_rows($res) == 0): ?>
    <div class="col-12 px-4">
      <div class="alert alert-info">No bookings found!</div>
    </div>
    <?php else: ?>

    <?php while($row = mysqli_fetch_assoc($res)): ?>

    <?php
      if($row['status'] == 'confirmed'){
        $badge = 'success';
      } else if($row['status'] == 'cancelled'){
        $badge = 'danger';
      } else {
        $badge = 'warning';
      }

      $room_thumb = ROOMS_IMG_PATH."thumbnail.jpg";
      $thumb_q = mysqli_query($con,"SELECT * FROM `room_image` WHERE `room_id`='$row[room_id]' AND `thumb`='1'");
      if(mysqli_num_rows($thumb_q)>0){
        $thumb_res = mysqli_fetch_assoc($thumb_q);
        $room_thumb = ROOMS_IMG_PATH.$thumb_res['image'];
      }

      // Review already check
      $review_check = mysqli_query($con, "SELECT * FROM `reviews` WHERE `booking_id`='$row[id]' AND `user_id`='$user_id' LIMIT 1");
      $reviewed = mysqli_num_rows($review_check) > 0;
    ?>

    <div class="col-12 px-4 mb-4">
      <div class="card border-0 shadow-sm rounded-3">
        <div class="row g-0 p-3 align-items-center">

          <div class="col-md-3 mb-md-0 mb-3">
            <img src="<?= $room_thumb ?>" class="img-fluid rounded">
          </div>

          <div class="col-md-6 px-3">
            <h5><?= $row['room_name'] ?></h5>
            <p class="mb-1"><strong>Order ID:</strong> #<?= $row['id'] ?></p>
            <p class="mb-1"><strong>Check-in:</strong> <?= $row['check_in'] ?></p>
            <p class="mb-1"><strong>Check-out:</strong> <?= $row['check_out'] ?></p>
            <p class="mb-1"><strong>Total:</strong> Rs.<?= $row['total_price'] ?></p>
            <p class="mb-1"><strong>Payment:</strong> Cash on Arrival</p>
            <p class="mb-0"><strong>Booked At:</strong> <?= $row['booked_at'] ?></p>
          </div>

          <div class="col-md-3 text-center mt-md-0 mt-3">
            <span class="badge bg-<?= $badge ?> fs-6 mb-3 d-block"><?= $row['status'] ?></span>
            <a href="download_my_booking_pdf.php?id=<?= $row['id'] ?>" class="btn btn-dark btn-sm shadow-none w-100 mb-2">
              <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
            </a>
            <?php if($reviewed): ?>
            <button class="btn btn-secondary btn-sm shadow-none w-100" disabled>
              <i class="bi bi-star-fill me-1"></i>Reviewed
            </button>
            <?php else: ?>
            <button class="btn btn-outline-dark btn-sm shadow-none w-100" 
              onclick="openReviewModal(<?= $row['id'] ?>, <?= $row['room_id'] ?>)">
              <i class="bi bi-star me-1"></i>Review & Rating
            </button>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>

    <?php endwhile; ?>
    <?php endif; ?>

  </div>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" data-bs-backdrop="static" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-star-fill text-warning me-2"></i>Review & Rating</h5>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="review_form">
          <input type="hidden" name="booking_id" id="review_booking_id">
          <input type="hidden" name="room_id" id="review_room_id">

          <!-- Star Rating -->
          <div class="mb-4">
            <label class="form-label fw-bold">Rating</label>
            <div class="d-flex flex-column gap-2">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="rating" id="r5" value="5" required>
                <label class="form-check-label" for="r5">
                  ⭐⭐⭐⭐⭐ Excellent
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="rating" id="r4" value="4">
                <label class="form-check-label" for="r4">
                  ⭐⭐⭐⭐ Very Good
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="rating" id="r3" value="3">
                <label class="form-check-label" for="r3">
                  ⭐⭐⭐ Good
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="rating" id="r2" value="2">
                <label class="form-check-label" for="r2">
                  ⭐⭐ Fair
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="rating" id="r1" value="1">
                <label class="form-check-label" for="r1">
                  ⭐ Poor
                </label>
              </div>
            </div>
          </div>

          <!-- Review Text -->
          <div class="mb-4">
            <label class="form-label fw-bold">Your Review</label>
            <textarea name="review" class="form-control shadow-none" rows="4" placeholder="Share your experience..." required></textarea>
          </div>

          <button type="submit" class="btn text-white custom-bg shadow-none w-100">Submit Review</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require('inc/footer.php'); ?>

<script>
function openReviewModal(booking_id, room_id){
  document.getElementById('review_booking_id').value = booking_id;
  document.getElementById('review_room_id').value    = room_id;

  var modal = new bootstrap.Modal(document.getElementById('reviewModal'));
  modal.show();
}

document.getElementById('review_form').addEventListener('submit', function(e){
  e.preventDefault();

  let data = new FormData(this);
  data.append('submit_review', '');

  let xhr = new XMLHttpRequest();
  xhr.open("POST", "ajax/reviews.php", true);

  xhr.onload = function(){
    let res = JSON.parse(this.responseText);
    if(res.status == 'success'){
      alert('success', 'Review submitted successfully!');
      setTimeout(() => location.reload(), 1500);
    } else {
      alert('error', res.msg || 'Something went wrong!');
    }
  };

  xhr.send(data);
});
</script>

</body>
</html>