<?php require('inc/links.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $settings_r['site_title'] ?> - Profile</title>
</head>
<body class="bg-light">

<?php require('inc/header.php'); ?>

<?php
if(!(isset($_SESSION['login']) && $_SESSION['login']==true)){
  redirect('index.php');
}

$user_id  = $_SESSION['uId'];
$user_res = select("SELECT * FROM `user_cred` WHERE `id`=? LIMIT 1", [$user_id], "i");
$user     = mysqli_fetch_assoc($user_res);
?>

<div class="container my-5">
  <div class="row">
    <div class="col-12 mb-4 px-4">
      <h2 class="fw-bold">MY PROFILE</h2>
      <div style="font-size: 14px;">
        <a href="index.php" class="text-secondary text-decoration-none">HOME</a>
        <span class="text-secondary"> > </span>
        <a href="#" class="text-secondary text-decoration-none">PROFILE</a>
      </div>
    </div>

    <!-- Basic Information -->
    <div class="col-12 px-4 mb-4">
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
          <h5 class="fw-bold mb-4">Basic Information</h5>
          <form id="profile_form">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" value="<?= $user['name'] ?>" class="form-control shadow-none" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Phone Number</label>
                <input type="number" name="phonenum" value="<?= $user['phonenum'] ?>" class="form-control shadow-none" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="dob" value="<?= $user['dob'] ?>" class="form-control shadow-none" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Pincode</label>
                <input type="number" name="pincode" value="<?= $user['pincode'] ?>" class="form-control shadow-none" required>
              </div>
              <div class="col-md-12 mb-3">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control shadow-none" rows="2" required><?= $user['address'] ?></textarea>
              </div>
              <div class="col-12">
                <button type="submit" class="btn text-white custom-bg shadow-none">Save Changes</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Picture -->
    <div class="col-lg-6 px-4 mb-4">
      <div class="card border-0 shadow-sm rounded-3 h-100">
        <div class="card-body p-4">
          <h5 class="fw-bold mb-4">Picture</h5>
          <div class="text-center mb-3">
            <img src="<?= USERS_IMG_PATH.$user['profile'] ?>" id="preview_img"
              style="width:150px; height:150px; object-fit:cover;" class="rounded-circle border shadow">
          </div>
          <form id="picture_form">
            <input type="file" name="profile" id="profile_input" accept=".jpg,.jpeg,.png,.webp" class="form-control shadow-none mb-3">
            <button type="submit" class="btn text-white custom-bg shadow-none w-100">Save Changes</button>
          </form>
        </div>
      </div>
    </div>

  <!-- Change Password -->
   <div class="col-lg-6 px-4 mb-4">
     <div class="card border-0 shadow-sm rounded-3 h-100">
       <div class="card-body p-4">
         <h5 class="fw-bold mb-4">Change Password</h5>
         <form id="password_form">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">New Password</label>
              <input type="password" name="new_pass" class="form-control shadow-none" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Confirm Password</label>
              <input type="password" name="confirm_pass" class="form-control shadow-none" required>
            </div>
          </div>
          <button type="submit" class="btn text-white custom-bg shadow-none">Save Changes</button>
         </form>
       </div>
     </div>
   </div>
  </div>
</div>

<?php require('inc/footer.php'); ?>

<script>
  // Picture preview
  document.getElementById('profile_input').addEventListener('change', function(){
    let file = this.files[0];
    if(file){
      let reader = new FileReader();
      reader.onload = function(e){
        document.getElementById('preview_img').src = e.target.result;
      }
      reader.readAsDataURL(file);
    }
  });

  // Basic info form
  let profile_form = document.getElementById('profile_form');
  profile_form.addEventListener('submit', function(e){
    e.preventDefault();
    let data = new FormData(profile_form);
    data.append('update_profile', '');

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/login_register.php", true);
    xhr.onload = function(){
      if(this.responseText == 'success'){
        alert('success', 'Profile updated successfully!');
        setTimeout(() => location.reload(), 1500);
      } else if(this.responseText == 'phone_already'){
        alert('error', 'Phone number already registered!');
      } else {
        alert('error', 'Update failed! Try again.');
      }
    };
    xhr.send(data);
  });

  // Picture form
  let picture_form = document.getElementById('picture_form');
  picture_form.addEventListener('submit', function(e){
    e.preventDefault();
    let data = new FormData(picture_form);
    data.append('update_profile', '');
    data.append('name', '<?= $user['name'] ?>');
    data.append('phonenum', '<?= $user['phonenum'] ?>');
    data.append('dob', '<?= $user['dob'] ?>');
    data.append('pincode', '<?= $user['pincode'] ?>');
    data.append('address', '<?= addslashes($user['address']) ?>');

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/login_register.php", true);
    xhr.onload = function(){
      if(this.responseText == 'success'){
        alert('success', 'Picture updated successfully!');
        setTimeout(() => location.reload(), 1500);
      } else if(this.responseText == 'inv_img'){
        alert('error', 'Invalid image format!');
      } else if(this.responseText == 'upd_failed'){
        alert('error', 'Image upload failed!');
      } else {
        alert('error', 'Update failed! Try again.');
      }
    };
    xhr.send(data);
  });

  // Password form
  let password_form = document.getElementById('password_form');
  password_form.addEventListener('submit', function(e){
    e.preventDefault();
    let new_pass     = password_form.elements['new_pass'].value;
    let confirm_pass = password_form.elements['confirm_pass'].value;

    if(new_pass != confirm_pass){
      alert('error', 'Passwords do not match!');
      return;
    }

    let data = new FormData();
    data.append('update_password', '');
    data.append('new_pass', new_pass);

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/login_register.php", true);
    xhr.onload = function(){
      if(this.responseText == 'success'){
        alert('success', 'Password updated successfully!');
        password_form.reset();
      } else {
        alert('error', 'Update failed! Try again.');
      }
    };
    xhr.send(data);
  });
</script>

</body>
</html>