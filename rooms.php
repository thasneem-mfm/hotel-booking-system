<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php require('inc/links.php'); ?>
<title><?php echo $settings_r['site_title'] ?> - ROOMS</title>
</head>
<body class="bg-light">

<?php require('inc/header.php'); ?>

<div class="my-5 px-4">
  <h2 class="fw-bold h-font text-center">OUR ROOMS</h2>
  <div class="h-line bg-dark"></div>
</div>

<?php
  $max_adult    = mysqli_fetch_assoc(mysqli_query($con, "SELECT MAX(`adult`) as max FROM `rooms` WHERE `status`=1 AND `removed`=0"))['max'];
  $max_children = mysqli_fetch_assoc(mysqli_query($con, "SELECT MAX(`children`) as max FROM `rooms` WHERE `status`=1 AND `removed`=0"))['max'];
?>

<div class="container-fluid">
  <div class="row">

    <!-- Sidebar Filter -->
    <div class="col-lg-3 col-md-12 mb-lg-0 mb-4 ps-4">
      <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow">
        <div class="container-fluid flex-lg-column align-items-stretch">
          <h4 class="mt-2">FILTERS</h4>
          <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterDropdown" aria-expanded="false">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse flex-column align-items-stretch mt-2" id="filterDropdown">
            <form action="rooms.php" method="GET" id="filter_form">

              <!-- Check Availability -->
              <div class="border bg-light p-3 rounded mb-3">
                <h5 class="mb-3" style="font-size:18px;">CHECK AVAILABILITY</h5>
                <label class="form-label">Check-in</label>
                <input type="date" name="check_in" id="check_in" value="<?= isset($_GET['check_in']) ? $_GET['check_in'] : '' ?>" class="form-control shadow-none mb-3">
                <label class="form-label">Check-out</label>
                <input type="date" name="check_out" id="check_out" value="<?= isset($_GET['check_out']) ? $_GET['check_out'] : '' ?>" class="form-control shadow-none">
              </div>

              <!-- Facilities -->
              <div class="border bg-light p-3 rounded mb-3">
                <h5 class="mb-3" style="font-size:18px;">FACILITIES</h5>
                <?php
                  $fac_res = mysqli_query($con, "SELECT * FROM `facilities` ORDER BY `id` ASC");
                  while($fac = mysqli_fetch_assoc($fac_res)){
                    $checked = '';
                    if(isset($_GET['facilities']) && in_array($fac['id'], $_GET['facilities'])){
                      $checked = 'checked';
                    }
                    echo "
                    <div class='mb-2'>
                      <input type='checkbox' name='facilities[]' value='{$fac['id']}' id='f{$fac['id']}' class='form-check-input shadow-none me-1 fac-check' $checked>
                      <label class='form-check-label' for='f{$fac['id']}'>{$fac['name']}</label>
                    </div>
                    ";
                  }
                ?>
              </div>

              <!-- Guests -->
              <div class="border bg-light p-3 rounded mb-3">
                <h5 class="mb-3" style="font-size:18px;">GUESTS</h5>
                <div class="d-flex">
                  <div class="me-3">
                    <label class="form-label">Adults</label>
                    <select name="adult" id="adult_select" class="form-select shadow-none">
                      <option value="">Any</option>
                      <?php for($i = 1; $i <= $max_adult; $i++):
                        $selected = (isset($_GET['adult']) && $_GET['adult'] == $i) ? 'selected' : '';
                      ?>
                      <option value="<?= $i ?>" <?= $selected ?>><?= $i ?></option>
                      <?php endfor; ?>
                    </select>
                  </div>
                  <div>
                    <label class="form-label">Children</label>
                    <select name="children" id="children_select" class="form-select shadow-none">
                      <option value="">Any</option>
                      <?php for($i = 1; $i <= $max_children; $i++):
                        $selected = (isset($_GET['children']) && $_GET['children'] == $i) ? 'selected' : '';
                      ?>
                      <option value="<?= $i ?>" <?= $selected ?>><?= $i ?></option>
                      <?php endfor; ?>
                    </select>
                  </div>
                </div>
              </div>

              <a href="rooms.php" class="btn btn-outline-dark shadow-none w-100 mb-2">Clear Filters</a>

            </form>
          </div>
        </div>
      </nav>
    </div>

    <!-- Rooms List -->
    <div class="col-lg-9 col-md-12 px-4">

      <?php
        $where  = "WHERE r.`status`=1 AND r.`removed`=0";
        $params = [];
        $types  = "";

        if(isset($_GET['check_in']) && isset($_GET['check_out']) && $_GET['check_in'] != '' && $_GET['check_out'] != ''){
          $check_in  = mysqli_real_escape_string($con, $_GET['check_in']);
          $check_out = mysqli_real_escape_string($con, $_GET['check_out']);
          $where .= " AND r.`id` NOT IN (
            SELECT `room_id` FROM `bookings`
            WHERE `status` != 'cancelled'
            AND (
              (check_in <= '$check_in' AND check_out > '$check_in') OR
              (check_in < '$check_out' AND check_out >= '$check_out') OR
              (check_in >= '$check_in' AND check_out <= '$check_out')
            )
          )";
        }

        if(isset($_GET['adult']) && $_GET['adult'] != ''){
          $where   .= " AND r.`adult` >= ?";
          $params[] = intval($_GET['adult']);
          $types   .= "i";
        }

        if(isset($_GET['children']) && $_GET['children'] != ''){
          $where   .= " AND r.`children` >= ?";
          $params[] = intval($_GET['children']);
          $types   .= "i";
        }

        if(isset($_GET['facilities']) && !empty($_GET['facilities'])){
          $fac_ids   = array_map('intval', $_GET['facilities']);
          $fac_in    = implode(',', $fac_ids);
          $fac_count = count($fac_ids);
          $where .= " AND r.`id` IN (
            SELECT `room_id` FROM `room_facilities`
            WHERE `facilities_id` IN ($fac_in)
            GROUP BY `room_id`
            HAVING COUNT(DISTINCT `facilities_id`) = $fac_count
          )";
        }

        if(!empty($params)){
          $room_res = select("SELECT r.* FROM `rooms` r $where", $params, $types);
        } else {
          $room_res = mysqli_query($con, "SELECT r.* FROM `rooms` r $where");
        }

        if(mysqli_num_rows($room_res) == 0){
          echo "<div class='alert alert-info'>No rooms found for the selected filters!</div>";
        }

        while($room_data = mysqli_fetch_assoc($room_res)){
          $fea_q = mysqli_query($con,"SELECT f.name FROM `features` f
            INNER JOIN `room_features` rfea ON f.id = rfea.features_id
            WHERE rfea.room_id = '$room_data[id]'");
          $features_data = "";
          while($fea_row = mysqli_fetch_assoc($fea_q)){
            $features_data .= "<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>$fea_row[name]</span>";
          }

          $fac_q = mysqli_query($con,"SELECT f.name FROM `facilities` f
            INNER JOIN `room_facilities` rfac ON f.id = rfac.facilities_id
            WHERE rfac.room_id = '$room_data[id]'");
          $facilities_data = "";
          while($fac_row = mysqli_fetch_assoc($fac_q)){
            $facilities_data .= "<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>$fac_row[name]</span>";
          }

          $room_thumb = ROOMS_IMG_PATH."thumbnail.jpg";
          $thumb_q = mysqli_query($con,"SELECT * FROM `room_image` WHERE `room_id`='$room_data[id]' AND `thumb`='1'");
          if(mysqli_num_rows($thumb_q) > 0){
            $thumb_res = mysqli_fetch_assoc($thumb_q);
            $room_thumb = ROOMS_IMG_PATH.$thumb_res['image'];
          }

          $book_btn = "";
          if(!$settings_r['shutdown']){
            $login = 0;
            if(isset($_SESSION['login']) && $_SESSION['login'] == true){
              $login = 1;
            }
            $book_btn = "<button onclick='checkLoginToBook($login,$room_data[id])' class='btn btn-sm w-100 text-white custom-bg shadow-none mb-2'>Book Now</button>";
          }

          echo<<<data
          <div class="card mb-4 border-0 shadow">
            <div class="row g-0 p-3 align-items-center">
              <div class="col-md-5 mb-lg-0 mb-md-0 mb-3">
                <img src="$room_thumb" class="img-fluid rounded">
              </div>
              <div class="col-md-5 px-lg-3 px-md-3 px-0">
                <h5 class="mb-3">$room_data[name]</h5>
                <div class="features mb-3">
                  <h6 class="mb-1">Features</h6>
                  $features_data
                </div>
                <div class="facilities mb-3">
                  <h6 class="mb-1">Facilities</h6>
                  $facilities_data
                </div>
                <div class="guests">
                  <h6 class="mb-1">Guests</h6>
                  <span class="badge rounded-pill bg-light text-dark text-wrap">$room_data[adult] Adults</span>
                  <span class="badge rounded-pill bg-light text-dark text-wrap">$room_data[children] Children</span>
                </div>
              </div>
              <div class="col-md-2 mt-lg-0 mt-md-0 mt-4 text-center">
                <h6 class="mb-4">Rs.$room_data[price] per night</h6>
                $book_btn
                <a href="room_details.php?id=$room_data[id]" class="btn btn-sm w-100 btn-outline-dark shadow-none">More details</a>
              </div>
            </div>
          </div>
          data;
        }
      ?>

    </div>
  </div>
</div>

<?php require('inc/footer.php'); ?>

<script>
  let filter_form = document.getElementById('filter_form');

  // Facilities checkbox — real-time filter
  document.querySelectorAll('.fac-check').forEach(function(checkbox){
    checkbox.addEventListener('change', function(){
      filter_form.submit();
    });
  });

  // Adult select — real-time filter
  document.getElementById('adult_select').addEventListener('change', function(){
    filter_form.submit();
  });

  // Children select — real-time filter
  document.getElementById('children_select').addEventListener('change', function(){
    filter_form.submit();
  });

  // Check-in & Check-out 
  document.getElementById('check_in').addEventListener('change', function(){
    if(document.getElementById('check_out').value != ''){
      filter_form.submit();
    }
  });

  document.getElementById('check_out').addEventListener('change', function(){
    if(document.getElementById('check_in').value != ''){
      filter_form.submit();
    }
  });
</script>

</body>
</html>