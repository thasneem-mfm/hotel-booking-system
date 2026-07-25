<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php require('inc/links.php'); ?>
<title><?php echo $settings_r['site_title'] ?> - ROOM DETAILS</title>
</head>
<body class="bg-light">

<?php require('inc/header.php'); ?>

<?php
if(!isset($_GET['id'])){
  redirect('rooms.php');
}

$data = filteration($_GET);

$room_res = select("SELECT * FROM `rooms` WHERE `id`=? AND `status`=? AND `removed`=?",[$data['id'],1,0],'iii');

if(mysqli_num_rows($room_res)==0){
  redirect('rooms.php');
}

$room_data = mysqli_fetch_assoc($room_res);
?>

<div class="container">
  <div class="row">

    <div class="col-12 my-5 mb-4 px-4">
      <h2 class="fw-bold"><?php echo $room_data['name'] ?></h2>
      <div style="font-size: 14px;">
        <a href="index.php" class="text-secondary text-decoration-none">HOME</a>
        <span class="text-secondary"> > </span>
        <a href="rooms.php" class="text-secondary text-decoration-none">ROOMS</a>
      </div>
    </div>

    <!-- Image (left) -->
    <div class="col-lg-7 col-md-12 px-4 mb-4">
      <div id="roomCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
          <?php
            $room_img = ROOMS_IMG_PATH."thumbnail.jpg";
            $img_q = mysqli_query($con,"SELECT * FROM `room_image` WHERE `room_id`='$room_data[id]'");

            if(mysqli_num_rows($img_q) > 0){
              $active_class = 'active';
              while($img_res = mysqli_fetch_assoc($img_q)){
                echo "
                <div class='carousel-item $active_class'>
                  <img src='".ROOMS_IMG_PATH.$img_res['image']."' class='d-block w-100 rounded'>
                </div>";
                $active_class = '';
              }
            } else {
              echo "<div class='carousel-item active'>
                <img src='$room_img' class='d-block w-100 rounded'>
              </div>";
            }
          ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#roomCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#roomCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon"></span>
        </button>
      </div>
    </div>

    <!-- Details (right) -->
    <div class="col-lg-5 col-md-12 px-4 mb-4">
      <div class="card mb-4 border-0 shadow-sm rounded-3">
        <div class="card-body">
          <?php
            echo "<h4>Rs.$room_data[price] per night</h4>";

            $avg_res = mysqli_query($con, "SELECT AVG(`rating`) as avg_rating, COUNT(*) as total FROM `reviews` WHERE `room_id`='$room_data[id]'");
            $avg_data = mysqli_fetch_assoc($avg_res);
            $avg = round($avg_data['avg_rating']);
            $total_reviews = $avg_data['total'];

            $stars = '';
            for($s = 1; $s <= 5; $s++){
              if($s <= $avg){
                $stars .= '<i class="bi bi-star-fill text-warning"></i>';
              } else {
                $stars .= '<i class="bi bi-star text-warning"></i>';
              }
            }

            echo "<div class='mb-3'>$stars <small class='text-muted ms-1'>($total_reviews reviews)</small></div>";

            $fea_q = mysqli_query($con,"SELECT f.name FROM `features` f
              INNER JOIN `room_features` rfea ON f.id = rfea.features_id
              WHERE rfea.room_id = '$room_data[id]'");

            $features_data = "";
            while($fea_row = mysqli_fetch_assoc($fea_q)){
              $features_data .= "<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>$fea_row[name]</span>";
            }

            echo "
            <div class='features mb-3'>
              <h6 class='mb-1'>Features</h6>
              $features_data
            </div>";

            $fac_q = mysqli_query($con,"SELECT f.name FROM `facilities` f
              INNER JOIN `room_facilities` rfac ON f.id = rfac.facilities_id
              WHERE rfac.room_id = '$room_data[id]'");

            $facilities_data = "";
            while($fac_row = mysqli_fetch_assoc($fac_q)){
              $facilities_data .= "<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>$fac_row[name]</span>";
            }

            echo "
            <div class='facilities mb-3'>
              <h6 class='mb-1'>Facilities</h6>
              $facilities_data
            </div>";

            echo "
            <div class='mb-3'>
              <h6 class='mb-1'>Guests</h6>
              <span class='badge rounded-pill bg-light text-dark text-wrap'>$room_data[adult] Adults</span>
              <span class='badge rounded-pill bg-light text-dark text-wrap'>$room_data[children] Children</span>
            </div>";

            echo "
            <div class='mb-3'>
              <h6 class='mb-1'>Area</h6>
              <span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>$room_data[area] sq. ft.</span>
            </div>";

            if(!$settings_r['shutdown']){
              $login = 0;
              if(isset($_SESSION['login']) && $_SESSION['login']==true){
                $login = 1;
              }
              echo "<button onclick='checkLoginToBook($login,$room_data[id])' class='btn w-100 text-white custom-bg shadow-none mb-1'>Book Now</button>";
            }
          ?>
        </div>
      </div>
    </div>

    <!-- Description -->
    <div class="col-12 mt-4 px-4">
      <div class="mb-5">
        <h5>Description</h5>
        <p><?php echo $room_data['description'] ?></p>
      </div>

      <!-- Reviews -->
      <div>
        <h5 class="mb-4">Reviews & Ratings</h5>

        <?php
          $review_res = mysqli_query($con, "SELECT rv.*, uc.name as user_name, uc.profile as user_pic 
            FROM `reviews` rv 
            JOIN `user_cred` uc ON rv.user_id = uc.id 
            WHERE rv.room_id = '$room_data[id]' AND rv.status = 1
            ORDER BY rv.id DESC");

          if(mysqli_num_rows($review_res) == 0){
            echo "<div class='alert alert-info'>No reviews yet!</div>";
          }

          while($rv = mysqli_fetch_assoc($review_res)){

            $rating_words = [
              1 => 'Poor',
              2 => 'Fair',
              3 => 'Good',
              4 => 'Very Good',
              5 => 'Excellent'
            ];
            $rating_word = $rating_words[$rv['rating']];

            $stars = '';
            for($s = 1; $s <= 5; $s++){
              if($s <= $rv['rating']){
                $stars .= '<i class="bi bi-star-fill text-warning"></i>';
              } else {
                $stars .= '<i class="bi bi-star text-warning"></i>';
              }
            }

            $user_pic    = USERS_IMG_PATH.$rv['user_pic'];
            $user_name   = $rv['user_name'];
            $review_text = $rv['review'];
            $created_at  = date("d M Y", strtotime($rv['created_at']));

            echo "
            <div class='card border-0 shadow-sm mb-3 p-3'>
              <div class='d-flex align-items-center mb-2'>
                <img src='$user_pic' width='50px' height='50px' style='object-fit:cover;' class='rounded-circle me-3'>
                <div>
                  <h6 class='mb-0'>$user_name</h6>
                  <small class='text-muted'>$created_at</small>
                </div>
              </div>
              <div class='mb-2'>
                $stars <span class='ms-2 fw-bold'>$rating_word</span>
              </div>
              <p class='mb-0'>$review_text</p>
            </div>
            ";
          }
        ?>
      </div>
    </div>

  </div>
</div>

<?php require('inc/footer.php'); ?>

</body>
</html>