<?php
session_start();
if (isset($_SESSION['admin_login'])) {

  include('includes/temp/init.php');
  include('includes/temp/navbar.php');


  $page = $_GET['page'] ?? 'All';
  $id = isset($_GET['id']) ? (int)$id = $_GET['id'] ?? null : null;
  $error = '';

  $record = null;
  $records = [];

  /* ================= CREATE + EDIT ================= */
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $patient_id = trim($_POST['patient_id'] ?? '');
    $study_id = trim($_POST['study_id'] ?? '');

    if ($patient_id == '' || $study_id == '') {
      $error = "Please fill all fields.";
    } else {

      if ($page === 'create') {
        $connect->prepare("
        INSERT INTO study_patients(patient_id, study_id)
        VALUES (?, ?)
      ")->execute([$patient_id, $study_id]);

        $_SESSION['message'] = "Created successfully.";
        header("Location: study_patients.php");
        exit();
      }

      if ($page === 'edit' && $id) {
        $connect->prepare("
        UPDATE study_patients 
        SET patient_id=?, study_id=?
        WHERE id=?
      ")->execute([$patient_id, $study_id, $id]);

        $_SESSION['message'] = "Updated successfully.";
        header("Location: study_patients.php");
        exit();
      }
    }
  }

  /* ================= DELETE ================= */
  if ($page === 'delete' && $id) {
    $connect->prepare("DELETE FROM study_patients WHERE id=?")->execute([$id]);

    $_SESSION['message'] = "Deleted successfully.";
    header("Location: study_patients.php");
    exit();
  }

  /* ================= GET ONE ================= */
  if (($page === 'edit' || $page === 'show') && $id) {
    $stmt = $connect->prepare("SELECT * FROM study_patients WHERE id=?");
    $stmt->execute([$id]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
      $_SESSION['message'] = "Not found.";
      header("Location: study_patients.php");
      exit();
    }
  }

  /* ================= GET ALL ================= */
  if ($page === 'All') {
    $records = $connect->query("SELECT * FROM study_patients")->fetchAll(PDO::FETCH_ASSOC);
  }
?>

  <div class="container my-5">

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
        <h3 class="page-title"><i class="fa fa-procedures"></i> Study Patients</h3>
        <a href="?page=create" class="btn btn-success btn-sm">+ Add Record</a>
      </div>

      <div class="table-box">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Patient ID</th>
              <th>Study ID</th>
              <th>Action</th>
            </tr>
          </thead>

          <tbody>
            <?php foreach ($records as $r): ?>
              <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['patient_id']) ?></td>
                <td><?= htmlspecialchars($r['study_id']) ?></td>
                <td>
                  <a href="?page=show&id=<?= $r['id'] ?>" class="btn btn-sm btn-success"><i class="fas fa-eye"></i></a>
                  <a href="?page=edit&id=<?= $r['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                  <a href="?page=delete&id=<?= $r['id'] ?>" class="btn btn-sm btn-danger"
                    onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- ================= CREATE / EDIT ================= -->
    <?php elseif ($page === 'create' || $page === 'edit'): ?>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="page-title">
          <?= $page === 'create' ? 'Add Record' : 'Edit Record' ?>
        </h3>
      </div>

      <div class="table-box p-4">
        <form method="post">

          <div class="row g-3">

            <div class="col-md-6">
              <label>Patient ID</label>
              <input class="form-control" name="patient_id"
                value="<?= $record['patient_id'] ?? '' ?>">
            </div>

            <div class="col-md-6">
              <label>Study ID</label>
              <input class="form-control" name="study_id"
                value="<?= $record['study_id'] ?? '' ?>">
            </div>

          </div>

          <div class="mt-4">
            <button class="btn btn-primary">Save</button>
            <a href="study_patients.php" class="btn btn-secondary">Cancel</a>
          </div>

        </form>
      </div>

      <!-- ================= SHOW ================= -->
    <?php elseif ($page === 'show'): ?>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="page-title">Details</h3>
      </div>

      <div class="table-box p-4">
        <table class="table table-borderless">
          <tr>
            <th>ID</th>
            <td><?= $record['id'] ?></td>
          </tr>
          <tr>
            <th>Patient ID</th>
            <td><?= $record['patient_id'] ?></td>
          </tr>
          <tr>
            <th>Study ID</th>
            <td><?= $record['study_id'] ?></td>
          </tr>
        </table>

        <a href="study_patients.php" class="btn btn-secondary btn-sm">Back</a>
      </div>

    <?php endif; ?>

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