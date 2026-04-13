<?php
session_start();
if (isset($_SESSION['admin_login'])) {

  include('includes/temp/init.php');
  include('includes/temp/navbar.php');

  $page = $_GET['page'] ?? 'All';
  $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
  $error = '';

  $patient = null;
  $patients = [];

  /* ================= CREATE + EDIT ================= */
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $disease_type = trim($_POST['disease_type'] ?? '');
    $blood_pressure = trim($_POST['blood_pressure'] ?? '');
    $sugar_level = trim($_POST['sugar_level'] ?? '');
    $bmi = trim($_POST['bmi'] ?? '');

    if ($name == '' || $age == '' || $gender == '' || $disease_type == '' || $blood_pressure == '' || $sugar_level == '' || $bmi == '') {
      $error = "Please fill all fields.";
    } else {

      if ($page === 'create') {
        $stmt = $connect->prepare("
        INSERT INTO patients(name,age,gender,disease_type,blood_pressure,sugar_level,bmi,created_at)
        VALUES (?,?,?,?,?,?,?,NOW())
      ");
        $stmt->execute([$name, $age, $gender, $disease_type, $blood_pressure, $sugar_level, $bmi]);

        $_SESSION['message'] = "Patient created successfully.";
        header("Location: patients.php");
        exit();
      }

      if ($page === 'edit' && $id) {
        $stmt = $connect->prepare("
        UPDATE patients 
        SET name=?,age=?,gender=?,disease_type=?,blood_pressure=?,sugar_level=?,bmi=?
        WHERE id=?
      ");
        $stmt->execute([$name, $age, $gender, $disease_type, $blood_pressure, $sugar_level, $bmi, $id]);

        $_SESSION['message'] = "Patient updated successfully.";
        header("Location: patients.php");
        exit();
      }
    }
  }

  /* ================= DELETE ================= */
  if ($page === 'delete' && $id) {
    $connect->prepare("DELETE FROM patients WHERE id=?")->execute([$id]);

    $_SESSION['message'] = "Patient deleted successfully.";
    header("Location: patients.php");
    exit();
  }

  /* ================= GET ONE ================= */
  if (($page === 'edit' || $page === 'show') && $id) {
    $stmt = $connect->prepare("SELECT * FROM patients WHERE id=?");
    $stmt->execute([$id]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
      $_SESSION['message'] = "Patient not found.";
      header("Location: patients.php");
      exit();
    }
  }

  /* ================= GET ALL ================= */
  if ($page === 'All') {
    $patients = $connect->query("SELECT * FROM patients")->fetchAll(PDO::FETCH_ASSOC);
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
            <h3 class="page-title"><i class="fa fa-user-injured"></i> Patients</h3>
            <a href="?page=create" class="btn btn-success btn-sm">+ Add Patient</a>
          </div>

          <div class="table-box">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Age</th>
                  <th>Gender</th>
                  <th>Action</th>
                </tr>
              </thead>

              <tbody>
                <?php foreach ($patients as $p): ?>
                  <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= htmlspecialchars($p['age']) ?></td>
                    <td><?= htmlspecialchars($p['gender']) ?></td>
                    <td>
                      <a href="?page=show&id=<?= $p['id'] ?>" class="btn btn-sm btn-success"><i class="fas fa-eye"></i></a>
                      <a href="?page=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                      <a href="?page=delete&id=<?= $p['id'] ?>" class="btn btn-sm btn-danger"
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
            <?= $page === 'create' ? 'Add Patient' : 'Edit Patient' ?>
          </h3>

          <div class="table-box p-4">
            <form method="post">

              <div class="row g-3">
                <div class="col-md-6">
                  <label>Name</label>
                  <input class="form-control" name="name" value="<?= $patient['name'] ?? '' ?>">
                </div>

                <div class="col-md-6">
                  <label>Age</label>
                  <input class="form-control" name="age" value="<?= $patient['age'] ?? '' ?>">
                </div>

                <div class="col-md-6">
                  <label>Gender</label>
                  <input class="form-control" name="gender" value="<?= $patient['gender'] ?? '' ?>">
                </div>

                <div class="col-md-6">
                  <label>Disease</label>
                  <input class="form-control" name="disease_type" value="<?= $patient['disease_type'] ?? '' ?>">
                </div>

                <div class="col-md-6">
                  <label>Blood Pressure</label>
                  <input class="form-control" name="blood_pressure" value="<?= $patient['blood_pressure'] ?? '' ?>">
                </div>

                <div class="col-md-6">
                  <label>Sugar Level</label>
                  <input class="form-control" name="sugar_level" value="<?= $patient['sugar_level'] ?? '' ?>">
                </div>

                <div class="col-md-6">
                  <label>BMI</label>
                  <input class="form-control" name="bmi" value="<?= $patient['bmi'] ?? '' ?>">
                </div>
              </div>

              <div class="mt-4">
                <button class="btn btn-primary">Save</button>
                <a href="patients.php" class="btn btn-secondary">Cancel</a>
              </div>

            </form>
          </div>

          <!-- ================= SHOW ================= -->
        <?php elseif ($page === 'show'): ?>

          <h3 class="page-title mb-4">Patient Details</h3>

          <div class="table-box p-4">
            <table class="table table-borderless">
              <tr>
                <th>ID</th>
                <td><?= $patient['id'] ?></td>
              </tr>
              <tr>
                <th>Name</th>
                <td><?= $patient['name'] ?></td>
              </tr>
              <tr>
                <th>Age</th>
                <td><?= $patient['age'] ?></td>
              </tr>
              <tr>
                <th>Gender</th>
                <td><?= $patient['gender'] ?></td>
              </tr>
              <tr>
                <th>Disease</th>
                <td><?= $patient['disease_type'] ?></td>
              </tr>
              <tr>
                <th>Blood Pressure</th>
                <td><?= $patient['blood_pressure'] ?></td>
              </tr>
              <tr>
                <th>Sugar</th>
                <td><?= $patient['sugar_level'] ?></td>
              </tr>
              <tr>
                <th>BMI</th>
                <td><?= $patient['bmi'] ?></td>
              </tr>
            </table>

            <a href="patients.php" class="btn btn-secondary btn-sm">Back</a>
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
include 'includes/temp/footer.php';
?>