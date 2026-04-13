<?php
session_start();
if (isset($_SESSION['admin_login'])) {

  include('includes/temp/init.php');
  include('includes/temp/navbar.php');


  $page = $_GET['page'] ?? 'All';
  $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
  $error = '';

  $study = null;
  $studies = [];

  /* ================= CREATE + EDIT ================= */
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $created_by = trim($_POST['created_by'] ?? '');

    if ($title == '' || $description == '' || $created_by == '') {
      $error = "Please fill all fields.";
    } else {

      if ($page === 'create') {
        $stmt = $connect->prepare("
        INSERT INTO studies(title,description,created_by,created_at)
        VALUES (?,?,?,NOW())
      ");
        $stmt->execute([$title, $description, $created_by]);

        $_SESSION['message'] = "Study created successfully.";
        header("Location: studies.php");
        exit();
      }

      if ($page === 'edit' && $id) {
        $stmt = $connect->prepare("
        UPDATE studies 
        SET title=?, description=?, created_by=?
        WHERE id=?
      ");
        $stmt->execute([$title, $description, $created_by, $id]);

        $_SESSION['message'] = "Study updated successfully.";
        header("Location: studies.php");
        exit();
      }
    }
  }

  /* ================= DELETE ================= */
  if ($page === 'delete' && $id) {
    $connect->prepare("DELETE FROM studies WHERE id=?")->execute([$id]);
    $_SESSION['message'] = "Study deleted successfully.";
    header("Location: studies.php");
    exit();
  }

  /* ================= GET ONE ================= */
  if (($page === 'edit' || $page === 'show') && $id) {
    $stmt = $connect->prepare("SELECT * FROM studies WHERE id=?");
    $stmt->execute([$id]);
    $study = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$study) {
      $_SESSION['message'] = "Study not found.";
      header("Location: studies.php");
      exit();
    }
  }

  /* ================= GET ALL ================= */
  if ($page === 'All') {
    $studies = $connect->query("SELECT * FROM studies")->fetchAll(PDO::FETCH_ASSOC);
  }
?>

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
            <h3 class="page-title"><i class="fa fa-notes-medical"></i> Studies</h3>
            <a href="?page=create" class="btn btn-success btn-sm">+ Add Study</a>
          </div>

          <div class="table-box">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Title</th>
                  <th>Description</th>
                  <th>Created By</th>
                  <th>Action</th>
                </tr>
              </thead>

              <tbody>
                <?php foreach ($studies as $s): ?>
                  <tr>
                    <td><?= $s['id'] ?></td>
                    <td><?= htmlspecialchars($s['title']) ?></td>
                    <td><?= htmlspecialchars($s['created_by']) ?></td>
                    <td><?= htmlspecialchars($s['description']) ?></td>
                    <td>
                      <a href="?page=show&id=<?= $s['id'] ?>" class="btn btn-sm btn-success"><i class="fas fa-eye"></i></a>
                      <a href="?page=edit&id=<?= $s['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                      <a href="?page=delete&id=<?= $s['id'] ?>" class="btn btn-sm btn-danger"
                        onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- ================= CREATE / EDIT ================= -->
        <?php elseif ($page === 'create' || $page === 'edit'): ?>

          <h3 class="page-title mb-4">
            <?= $page === 'create' ? 'Add Study' : 'Edit Study' ?>
          </h3>

          <div class="table-box p-4">
            <form method="post">

              <div class="row g-3">

                <div class="col-md-6">
                  <label>Title</label>
                  <input class="form-control" name="title" value="<?= $study['title'] ?? '' ?>">
                </div>

                <div class="col-md-6">
                  <label>Created By</label>
                  <input class="form-control" name="created_by" value="<?= $study['created_by'] ?? '' ?>">
                </div>

                <div class="col-md-12">
                  <label>Description</label>
                  <textarea class="form-control" name="description"><?= $study['description'] ?? '' ?></textarea>
                </div>

              </div>

              <div class="mt-4">
                <button class="btn btn-primary">Save</button>
                <a href="studies.php" class="btn btn-secondary">Cancel</a>
              </div>

            </form>
          </div>

          <!-- ================= SHOW ================= -->
        <?php elseif ($page === 'show'): ?>

          <h3 class="page-title mb-4">Study Details</h3>

          <div class="table-box p-4">
            <table class="table table-borderless">

              <tr>
                <th>ID</th>
                <td><?= $study['id'] ?></td>
              </tr>
              <tr>
                <th>Title</th>
                <td><?= $study['title'] ?></td>
              </tr>
              <tr>
                <th>Description</th>
                <td><?= $study['description'] ?></td>
              </tr>
              <tr>
                <th>Created By</th>
                <td><?= $study['created_by'] ?></td>
              </tr>
              <tr>
                <th>Date</th>
                <td><?= $study['created_at'] ?></td>
              </tr>

            </table>

            <a href="studies.php" class="btn btn-secondary btn-sm">Back</a>
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