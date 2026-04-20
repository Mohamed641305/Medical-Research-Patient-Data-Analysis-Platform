<?php
session_start();

if (isset($_SESSION['admin_login'])) {

  include('includes/temp/init.php');
  include('includes/temp/navbar.php');

  $page = $_GET['page'] ?? 'All';
  $id   = isset($_GET['id']) ? (int)$_GET['id'] : null;
  $error = '';

  /* ================= CSV IMPORT ================= */
  if (isset($_POST['upload_csv'])) {

    if (!empty($_FILES['csv_file']['tmp_name'])) {

      $file = fopen($_FILES['csv_file']['tmp_name'], "r");

      while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {

        if (count($data) >= 2) {

          $file_name   = trim($data[0]);
          $uploaded_by = trim($data[1]);

          if ($file_name != "" && $uploaded_by != "") {

            $stmt = $connect->prepare("
              INSERT INTO uploads (file_name, uploaded_by, uploaded_at)
              VALUES (?, ?, ?)
            ");

            $stmt->execute([
              $file_name,
              $uploaded_by,
              date('Y-m-d H:i:s')
            ]);
          }
        }
      }

      fclose($file);

      $_SESSION['message'] = "CSV Imported Successfully";
      header("Location: uploads.php");
      exit;
    }
  }

  /* ================= CREATE + EDIT ================= */
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['upload_csv'])) {

    $file_name   = trim($_POST['file_name'] ?? '');
    $uploaded_by = trim($_POST['uploaded_by'] ?? '');

    if (empty($file_name) || empty($uploaded_by)) {
      $error = 'Please fill all fields.';
    } else {

      if ($page === 'create') {

        $stmt = $connect->prepare("
          INSERT INTO uploads (file_name, uploaded_by, uploaded_at)
          VALUES (?, ?, ?)
        ");

        $stmt->execute([
          $file_name,
          $uploaded_by,
          date('Y-m-d H:i:s')
        ]);

        $_SESSION['message'] = 'Upload created successfully.';
        header('Location: uploads.php');
        exit;
      }

      if ($page === 'edit' && $id) {

        $stmt = $connect->prepare("
          UPDATE uploads 
          SET file_name = ?, uploaded_by = ?
          WHERE id = ?
        ");

        $stmt->execute([
          $file_name,
          $uploaded_by,
          $id
        ]);

        $_SESSION['message'] = 'Upload updated successfully.';
        header('Location: uploads.php');
        exit;
      }
    }
  }

  /* ================= DELETE ================= */
  if ($page === 'delete' && $id) {

    $stmt = $connect->prepare("DELETE FROM uploads WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['message'] = 'Upload deleted successfully.';
    header('Location: uploads.php');
    exit;
  }

  /* ================= GET SINGLE ================= */
  $upload = null;

  if (($page === 'edit' || $page === 'show') && $id) {

    $stmt = $connect->prepare("SELECT * FROM uploads WHERE id = ?");
    $stmt->execute([$id]);
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

  /* ================= GET RESEARCHERS ================= */
  $researchers = $connect->query("SELECT id, name FROM researchers")->fetchAll();
?>

  <style>
    .table-box {
      background: white;
      border-radius: 15px;
      padding: 15px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, .08);
    }

    table {
      border-radius: 12px;
      overflow: hidden;
    }

    th {
      background: #0d6efd;
      color: white;
      text-align: center;
    }

    td {
      text-align: center;
      vertical-align: middle;
    }

    tr:hover {
      background: #f1f7ff;
      transition: 0.2s;
    }

    .btn i {
      margin-right: 4px;
    }
  </style>

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

            <div>
              <a href="csv.php" class="btn btn-info btn-sm">
                <i class="fas fa-file-csv"></i> CSV
              </a>

              <button class="btn btn-warning btn-sm" data-bs-toggle="collapse" data-bs-target="#csvBox">
                <i class="fas fa-upload"></i> Import
              </button>

              <a href="?page=create" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Add
              </a>
            </div>
          </div>

          <!-- CSV BOX -->
          <div id="csvBox" class="collapse mb-3">
            <div class="table-box p-3">
              <form method="POST" enctype="multipart/form-data">
                <input type="file" name="csv_file" class="form-control mb-2" required>
                <button name="upload_csv" class="btn btn-primary btn-sm">
                  <i class="fas fa-file-upload"></i> Upload CSV
                </button>
              </form>
            </div>
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
                <?php foreach ($uploads as $u): ?>
                  <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['file_name']) ?></td>
                    <td><?= htmlspecialchars($u['uploaded_by']) ?></td>
                    <td><?= $u['uploaded_at'] ?></td>
                    <td>
                      <a href="?page=show&id=<?= $u['id'] ?>" class="btn btn-success btn-sm"><i class="fas fa-eye"></i></a>
                      <a href="?page=edit&id=<?= $u['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                      <a href="?page=delete&id=<?= $u['id'] ?>" class="btn btn-danger btn-sm"
                        onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- ================= CREATE (FIXED) ================= -->
        <?php elseif ($page === 'create'): ?>

          <h3 class="mb-4">Add Upload</h3>

          <div class="table-box p-4">
            <form method="post">

              <input type="text" name="file_name" class="form-control mb-3" placeholder="File Name" required>

              <select name="uploaded_by" class="form-control mb-3" required>
                <option value="">Select Researcher</option>
                <?php foreach ($researchers as $r): ?>
                  <option value="<?= $r['id'] ?>">
                    <?= htmlspecialchars($r['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>

              <button class="btn btn-primary">Create</button>
              <a href="uploads.php" class="btn btn-secondary">Cancel</a>
            </form>
          </div>

          <!-- ================= EDIT ================= -->
        <?php elseif ($page === 'edit'): ?>

          <h3 class="mb-4">Edit Upload</h3>

          <div class="table-box p-4">
            <form method="post">

              <input type="text" name="file_name" class="form-control mb-3"
                value="<?= htmlspecialchars($upload['file_name']) ?>" required>

              <select name="uploaded_by" class="form-control mb-3" required>
                <?php foreach ($researchers as $r): ?>
                  <option value="<?= $r['id'] ?>"
                    <?= $upload['uploaded_by'] == $r['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($r['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>

              <button class="btn btn-primary">Update</button>
              <a href="uploads.php" class="btn btn-secondary">Cancel</a>
            </form>
          </div>

          <!-- ================= SHOW ================= -->
        <?php elseif ($page === 'show'): ?>

          <h3 class="mb-4">Upload Details</h3>

          <div class="table-box p-4">
            <table class="table table-borderless">
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
                <th>Date</th>
                <td><?= $upload['uploaded_at'] ?></td>
              </tr>
            </table>

            <a href="uploads.php" class="btn btn-secondary">Back</a>
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
  include('includes/temp/footer.php');
} else {
  $_SESSION['message_login'] = "Login First";
  header("Location: ../login.php");
  exit();
}
?>