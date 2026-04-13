<?php
session_start();
include("includes/db/db.php");
include("includes/temp/header.php");

$email = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

  $email = $_POST['email'];
  $pass  = $_POST['pass'];

  /* ========================= VALIDATION ========================= */

  if ($email == "" && $pass == "") {
    $_SESSION['message_login'] = "Please fill in all fields.";
  } else if ($email == "") {
    $_SESSION['message_login'] = "Please enter Email.";
  } else if ($pass == "") {
    $_SESSION['message_login'] = "Please enter Password.";
  } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['message_login'] = "Enter a valid email address.";
  } else if (strlen($pass) < 5) {
    $_SESSION['message_login'] = "Password must be at least 5 characters long.";
  }

  /* ========================= LOGIN ========================= */

  if (!isset($_SESSION['message_login'])) {

    $statement = $connect->prepare("SELECT * FROM researchers WHERE email=?");
    $statement->execute(array($email));

    if ($statement->rowCount() > 0) {

      $result = $statement->fetch();

      if ($pass != $result['password']) {
        $_SESSION['message_login'] = "Check Your Password";
      } else {

        if ($result['role'] == "admin") {
          $_SESSION['admin_login'] = $email;
          header("Location: Admin/dashboard.php");
          exit();
        } else {
          $_SESSION['user_login'] = $email;
          header("Location: index.php");
          exit();
        }
      }
    } else {
      $_SESSION['message_login'] = "Your Account Not in DB";
    }
  }
}
?>

<div class="container mt-5 pt-5">
  <div class="row">
    <div class="col-md-5 m-auto">
      <div class="card shadow p-4 ">

        <h3 class="auth-title">
          <i class="fa-solid fa-heart-pulse text-danger"></i> MediCare Login
        </h3>

        <!-- الرسائل -->
        <?php
        if (isset($_SESSION['message_login'])) {
          echo "<h4 class='alert alert-danger text-center mb-4' id='message_login'>" . $_SESSION['message_login'] . "</h4>";
          unset($_SESSION['message_login']);
        }

        if (isset($_SESSION['success'])) {
          echo "<h4 class='alert alert-success text-center mb-4' id='success'>" . $_SESSION['success'] . "</h4>";
          unset($_SESSION['success']);
        }
        ?>

        <form method="post">
          <input type="email" name="email"
            value="<?php echo $email; ?>"
            placeholder="E-mail"
            class="form-control mb-4">

          <input type="password" name="pass"
            placeholder="Password"
            class="form-control mb-5">

          <input type="submit"
            value="Login"
            class="btn btn-success btn-block w-100 d-block">
        </form>

        <p class="text-center mt-4">
          Don't have an account? <a href="register.php">Register here</a>
        </p>
      </div>
    </div>
  </div>
</div>

<!-- إخفاء الرسائل -->
<script>
  setTimeout(() => {

    const loginMsg = document.getElementById('message_login');
    if (loginMsg) loginMsg.style.display = 'none';

    const successMsg = document.getElementById('success');
    if (successMsg) successMsg.style.display = 'none';

  }, 3000);
</script>

<?php include "includes/temp/footer.php"; ?>