<?php
session_start();
if (isset($_SESSION['admin_login'])) {

  include('includes/temp/init.php');
  include('includes/temp/navbar.php');


  $page = isset($_GET['page']) ? $_GET['page'] : 'All';
  $id   = isset($_GET['id']) ? (int)$_GET['id'] : null;
  $error = '';

  /* ================= CREATE + EDIT ================= */
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $file_name   = trim($_POST['file_name'] ?? '');
    $uploaded_by = trim($_POST['uploaded_by'] ?? '');

    if (empty($file_name) || empty($uploaded_by)) {
      $error = 'Please fill all fields.';
    } else {

      if ($page === 'create') {

        $stmt = $connect->prepare("
                INSERT INTO uploads (file_name, uploaded_by, uploaded_at)
                VALUES (:file_name, :uploaded_by, :uploaded_at)
            ");

        $stmt->execute([
          ':file_name'   => $file_name,
          ':uploaded_by' => $uploaded_by,
          ':uploaded_at' => date('Y-m-d H:i:s'),
        ]);

        $_SESSION['message'] = 'Upload created successfully.';
        header('Location: uploads.php');
        exit;
      }

      if ($page === 'edit' && $id) {

        $stmt = $connect->prepare("
                UPDATE uploads 
                SET file_name = :file_name, uploaded_by = :uploaded_by 
                WHERE id = :id
            ");

        $stmt->execute([
          ':file_name'   => $file_name,
          ':uploaded_by' => $uploaded_by,
          ':id'          => $id,
        ]);

        $_SESSION['message'] = 'Upload updated successfully.';
        header('Location: uploads.php');
        exit;
      }
    }
  }

  /* ================= DELETE ================= */
  if ($page === 'delete' && $id) {

    $stmt = $connect->prepare("DELETE FROM uploads WHERE id = :id");
    $stmt->execute([':id' => $id]);

    $_SESSION['message'] = 'Upload deleted successfully.';
    header('Location: uploads.php');
    exit;
  }

  /* ================= GET SINGLE ================= */
  $upload = null;

  if (($page === 'edit' || $page === 'show') && $id) {

    $stmt = $connect->prepare("SELECT * FROM uploads WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $upload = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$upload) {
      $_SESSION['message'] = 'Upload not found.';
      header('Location: uploads.php');
      exit;
    }
  }

  /* ================= GET ALL ================= */
  if ($page === 'All') {
    $uploads = $connect->query("SELECT * FROM uploads")->fetchAll();
  }
?>

  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-md-12">

        <!-- MESSAGE -->
        <?php if (!empty($_SESSION['message'])): ?>
          <div class="alert alert-success text-center py-2 my-3 auto-hide">
            <?= htmlspecialchars($_SESSION['message']) ?>
          </div>
          <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- ERROR -->
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger text-center py-2 my-3 auto-hide">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <!-- ================= ALL ================= -->
        <?php if ($page === 'All'): ?>

          <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="page-title mb-0">
              <i class="fa fa-file-upload"></i> Uploads
            </h3>
            <a href="?page=create" class="btn btn-success btn-sm">+ Add Upload</a>
          </div>

          <div class="table-box">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>File Name</th>
                  <th>Uploaded By</th>
                  <th>Uploaded At</th>
                  <th>Action</th>
                </tr>
              </thead>

              <tbody>
                <?php if (!empty($uploads)): ?>
                  <?php foreach ($uploads as $u): ?>
                    <tr>
                      <td><?= htmlspecialchars($u['id']) ?></td>
                      <td><?= htmlspecialchars($u['file_name']) ?></td>
                      <td><?= htmlspecialchars($u['uploaded_by']) ?></td>
                      <td><?= htmlspecialchars($u['uploaded_at']) ?></td>
                      <td>
                        <a href="?page=show&id=<?= $u['id'] ?>" class="btn btn-sm btn-success"><i class="fas fa-eye"></i></a>
                        <a href="?page=edit&id=<?= $u['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                        <a href="?page=delete&id=<?= $u['id'] ?>" class="btn btn-sm btn-danger"
                          onclick="return confirm('Delete this upload?');">
                          <i class="fas fa-trash"></i>
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="5">No uploads found</td>
                  </tr>
                <?php endif; ?>
              </tbody>

            </table>
          </div>

          <!-- ================= CREATE ================= -->
        <?php elseif ($page === 'create'): ?>

          <h3 class="page-title mb-4">
            <i class="fa fa-file-upload"></i> Add Upload
          </h3>

          <div class="table-box p-4">
            <form method="post">

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">File Name</label>
                  <input type="text" name="file_name" class="form-control" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Uploaded By</label>
                  <input type="text" name="uploaded_by" class="form-control" required>
                </div>
              </div>

              <div class="mt-4">
                <button class="btn btn-primary">Create</button>
                <a href="uploads.php" class="btn btn-secondary ms-2">Cancel</a>
              </div>

            </form>
          </div>

          <!-- ================= EDIT ================= -->
        <?php elseif ($page === 'edit'): ?>

          <h3 class="page-title mb-4">
            <i class="fa fa-file-upload"></i> Edit Upload
          </h3>

          <div class="table-box p-4">
            <form method="post">

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">File Name</label>
                  <input type="text" name="file_name" class="form-control"
                    value="<?= htmlspecialchars($upload['file_name']) ?>" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Uploaded By</label>
                  <input type="text" name="uploaded_by" class="form-control"
                    value="<?= htmlspecialchars($upload['uploaded_by']) ?>" required>
                </div>
              </div>

              <div class="mt-4">
                <button class="btn btn-primary">Update</button>
                <a href="uploads.php" class="btn btn-secondary ms-2">Cancel</a>
              </div>

            </form>
          </div>

          <!-- ================= SHOW ================= -->
        <?php elseif ($page === 'show'): ?>

          <h3 class="page-title mb-4">
            <i class="fa fa-file-upload"></i> Upload Details
          </h3>

          <div class="table-box p-4">
            <table class="table table-borderless">
              <tbody>
                <tr>
                  <th>ID</th>
                  <td><?= $upload['id'] ?></td>
                </tr>
                <tr>
                  <th>File Name</th>
                  <td><?= htmlspecialchars($upload['file_name']) ?></td>
                </tr>
                <tr>
                  <th>Uploaded By</th>
                  <td><?= htmlspecialchars($upload['uploaded_by']) ?></td>
                </tr>
                <tr>
                  <th>Uploaded At</th>
                  <td><?= htmlspecialchars($upload['uploaded_at']) ?></td>
                </tr>
              </tbody>
            </table>

            <a href="uploads.php" class="btn btn-secondary btn-sm mt-3">Back</a>
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