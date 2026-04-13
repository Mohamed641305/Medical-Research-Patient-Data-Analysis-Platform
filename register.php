<?php
session_start();
include("includes/db/db.php");
include("includes/temp/header.php");

$message = "";
$name = "";
$email = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

  $name  = trim($_POST['name']);
  $email = trim($_POST['email']);
  $pass  = trim($_POST['pass']);
  $cpass = trim($_POST['cpass']);

  /* ========================= VALIDATION ========================= */
  if (empty($name) || empty($email) || empty($pass) || empty($cpass)) {
    $message = "Please fill in all fields.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $message = "Enter a valid email address.";
  } elseif (strlen($pass) < 5) {
    $message = "Password must be at least 5 characters long.";
  } elseif ($pass !== $cpass) {
    $message = "Passwords do not match.";
  }

  /* ========================= REGISTER ========================= */
  if (empty($message)) {

    $stmt = $connect->prepare("SELECT * FROM researchers WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
      $message = "Email is already registered.";
    } else {

      $stmt = $connect->prepare("INSERT INTO researchers (`name`, email, `password`, `role`, created_at) VALUES (?, ?, ?, 'researcher', NOW())");
      $stmt->execute([$name, $email, $pass]);

      $_SESSION['success'] = "Registration successful! You can login now.";
      header("Location: login.php");
      exit();
    }
  }
}
?>

<div class="container mt-3 pt-3">
  <div class="row">
    <div class="col-md-5 m-auto">
      <div class="card shadow p-4 ">

        <h3 class="auth-title">
          <i class="fa-solid fa-user-plus text-success"></i> Create Account
        </h3>

        <!-- الرسائل -->
        <?php if (!empty($message)) { ?>
          <h4 class="alert alert-danger text-center mb-4" id="message">
            <?php echo $message; ?>
          </h4>
        <?php } ?>

        <form method="post">
          <input type="text" name="name"
            value="<?php echo htmlspecialchars($name); ?>"
            placeholder="Full Name"
            class="form-control mb-4">

          <input type="email" name="email"
            value="<?php echo htmlspecialchars($email); ?>"
            placeholder="E-mail"
            class="form-control mb-4">

          <input type="password" name="pass"
            placeholder="Password"
            class="form-control mb-4">

          <input type="password" name="cpass"
            placeholder="Confirm Password"
            class="form-control mb-5">

          <input type="submit" class="btn btn-primary d-block w-100" value="Register">
        </form>

        <p class="text-center mt-4 mb-3">
          Already have an account? <a href="login.php">Login here</a>
        </p>
      </div>
    </div>
  </div>
</div>

<!-- إخفاء الرسائل -->
<script>
  setTimeout(() => {
    const regMsg = document.getElementById('message');
    if (regMsg) regMsg.style.display = 'none';
  }, 3000);
</script>

<?php include("includes/temp/footer.php"); ?>