<?php
session_start();
if (isset($_SESSION['admin_login'])) {

  include('includes/temp/init.php');
  include('includes/temp/navbar.php');


  $page = $_GET['page'] ?? 'All';
  $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
  $error = '';

  /* ================= CREATE + EDIT ================= */
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? '');

    if (empty($name) || empty($email) || empty($password) || empty($role)) {
      $error = "Please fill all fields.";
    } else {

      if ($page === 'create') {
        $stmt = $connect->prepare("
                INSERT INTO researchers (name, email, password, role, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
        $stmt->execute([$name, $email, $password, $role]);

        $_SESSION['message'] = "Researcher created successfully.";
        header("Location: researchers.php");
        exit();
      }

      if ($page === 'edit' && $id) {
        $stmt = $connect->prepare("
                UPDATE researchers
                SET name=?, email=?, password=?, role=?
                WHERE id=?
            ");
        $stmt->execute([$name, $email, $password, $role, $id]);

        $_SESSION['message'] = "Updated successfully.";
        header("Location: researchers.php");
        exit();
      }
    }
  }

  /* ================= DELETE ================= */
  if ($page === 'delete' && $id) {
    $stmt = $connect->prepare("DELETE FROM researchers WHERE id=?");
    $stmt->execute([$id]);

    $_SESSION['message'] = "Deleted successfully.";
    header("Location: researchers.php");
    exit();
  }

  /* ================= GET ONE ================= */
  $researcher = null;

  if (($page === 'edit' || $page === 'show') && $id) {
    $stmt = $connect->prepare("SELECT * FROM researchers WHERE id=?");
    $stmt->execute([$id]);
    $researcher = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$researcher) {
      $_SESSION['message'] = "Researcher not found.";
      header("Location: researchers.php");
      exit();
    }
  }

  /* ================= GET ALL ================= */
  if ($page === 'All') {
    $researchers = $connect->query("SELECT * FROM researchers")->fetchAll(PDO::FETCH_ASSOC);
  }
?>

  <!-- ================= UI ================= -->
  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-md-12">

        <!-- MESSAGE -->
        <?php if (!empty($_SESSION['message'])): ?>
          <div class="alert alert-success text-center py-2 my-3 auto-hide">
            <?= $_SESSION['message'];
            unset($_SESSION['message']); ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger text-center py-2 my-3 auto-hide">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <!-- ================= ALL ================= -->
        <?php if ($page === 'All'): ?>

          <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="page-title"><i class="fa fa-user-md"></i> Researchers</h3>
            <a href="?page=create" class="btn btn-success btn-sm">+ Add Researcher</a>
          </div>

          <div class="table-box">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>

                <?php if (!empty($researchers)): ?>
                  <?php foreach ($researchers as $r): ?>
                    <tr>
                      <td><?= $r['id'] ?></td>
                      <td><?= htmlspecialchars($r['name']) ?></td>
                      <td><?= htmlspecialchars($r['email']) ?></td>
                      <td><?= htmlspecialchars($r['role']) ?></td>
                      <td>
                        <a href="?page=show&id=<?= $r['id'] ?>" class="btn btn-sm btn-success"><i class="fas fa-eye"></i></a>
                        <a href="?page=edit&id=<?= $r['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                        <a href="?page=delete&id=<?= $r['id'] ?>" class="btn btn-sm btn-danger"
                          onclick="return confirm('Delete this researcher?');">
                          <i class="fas fa-trash"></i>
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="5" class="text-center">No data found</td>
                  </tr>
                <?php endif; ?>

              </tbody>
            </table>
          </div>

          <!-- ================= CREATE / EDIT ================= -->
        <?php elseif ($page === 'create' || $page === 'edit'): ?>

          <h3 class="page-title mb-4">
            <?= $page === 'create' ? 'Add Researcher' : 'Edit Researcher' ?>
          </h3>

          <div class="table-box p-4">
            <form method="post">

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Name</label>
                  <input type="text" name="name" class="form-control"
                    value="<?= $researcher['name'] ?? '' ?>">
                </div>

                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input type="email" name="email" class="form-control"
                    value="<?= $researcher['email'] ?? '' ?>">
                </div>

                <div class="col-md-6">
                  <label class="form-label">Password</label>
                  <input type="text" name="password" class="form-control"
                    value="<?= $researcher['password'] ?? '' ?>">
                </div>

                <div class="col-md-6">
                  <label class="form-label">Role</label>
                  <input type="text" name="role" class="form-control"
                    value="<?= $researcher['role'] ?? '' ?>">
                </div>
              </div>

              <div class="mt-4">
                <button class="btn btn-primary">Save</button>
                <a href="researchers.php" class="btn btn-secondary">Cancel</a>
              </div>

            </form>
          </div>

          <!-- ================= SHOW ================= -->
        <?php elseif ($page === 'show'): ?>

          <h3 class="page-title mb-4">Researcher Details</h3>

          <div class="table-box p-4">
            <table class="table table-borderless">

              <tr>
                <th>ID</th>
                <td><?= $researcher['id'] ?? '' ?></td>
              </tr>
              <tr>
                <th>Name</th>
                <td><?= $researcher['name'] ?? '' ?></td>
              </tr>
              <tr>
                <th>Email</th>
                <td><?= $researcher['email'] ?? '' ?></td>
              </tr>
              <tr>
                <th>Role</th>
                <td><?= $researcher['role'] ?? '' ?></td>
              </tr>
              <tr>
                <th>Created At</th>
                <td><?= $researcher['created_at'] ?? '' ?></td>
              </tr>

            </table>

            <a href="researchers.php" class="btn btn-secondary btn-sm">Back</a>
          </div>

        <?php endif; ?>

      </div>
    </div>
  </div>

  <script>
    setTimeout(() => {
      document.querySelectorAll('.auto-hide').forEach(el => el.style.display = 'none');
    }, 3000);
  </script>

<?php
} else {
  $_SESSION['message_login'] = "Login First";
  header("Location: ../login.php");
  exit();
}
include('includes/temp/footer.php');
?>