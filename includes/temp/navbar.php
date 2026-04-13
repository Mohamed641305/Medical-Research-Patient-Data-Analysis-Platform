<?php if (session_status() === PHP_SESSION_NONE) {
  session_start();
} ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">

  <div class="container">

    <!-- Brand -->
    <a class="navbar-brand fw-bold" href="index.php">
      <i class="fa-solid fa-heart-pulse text-danger"></i> MediCare
    </a>

    <!-- Toggle -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Links -->
    <div class="collapse navbar-collapse" id="mainNav">

      <ul class="navbar-nav ms-auto">

        <?php if (isset($_SESSION['user_login'])): ?>
          <li class="nav-item">
            <a class="nav-link btn btn-outline-light btn-sm px-3" href="logout.php">Logout</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="login.php">Login</a>
          </li>

          <li class="nav-item">
            <a class="nav-link btn btn-danger btn-sm text-white px-3 ms-2" href="register.php">
              Register
            </a>
          </li>
        <?php endif; ?>

      </ul>

    </div>

  </div>
</nav>