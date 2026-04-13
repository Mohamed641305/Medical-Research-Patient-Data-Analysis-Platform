<?php
session_start();

if (isset($_SESSION['admin_login'])) {

  include('includes/temp/init.php');
  include('includes/temp/navbar.php');

  /* COUNTS */
  $patientCount = $connect->query("SELECT COUNT(*) FROM patients")->fetchColumn();
  $researchCount = $connect->query("SELECT COUNT(*) FROM researchers")->fetchColumn();
  $studyCount = $connect->query("SELECT COUNT(*) FROM studies")->fetchColumn();
  $studyPatientCount = $connect->query("SELECT COUNT(*) FROM study_patients")->fetchColumn();
  $uploadCount = $connect->query("SELECT COUNT(*) FROM uploads")->fetchColumn();

?>


  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>




  <!-- 🌙 DARK MODE -->
  <button class="toggle-btn" onclick="toggleDark()">🌙</button>



  <!-- CONTENT -->
  <div class="container py-4">

    <!-- TITLE -->
    <div class="text-center mb-4">
      <h2>Medical <span style="color:#14b8a6;">Dashboard</span></h2>
      <p>Hospital Management Control Panel</p>
    </div>

    <!-- CARDS -->
    <div class="row g-3">

      <div class="col-12 col-md-6 col-lg-3">
        <div class="card-box c1">
          <div>
            <h3><?= $patientCount ?></h3>
            <p>Patients</p>
          </div>
          <i class="fa fa-user-injured"></i>
        </div>
      </div>

      <div class="col-12 col-md-6 col-lg-3">
        <div class="card-box c2">
          <div>
            <h3><?= $researchCount ?></h3>
            <p>Researchers</p>
          </div>
          <i class="fa fa-user-doctor"></i>
        </div>
      </div>

      <div class="col-12 col-md-6 col-lg-3">
        <div class="card-box c3">
          <div>
            <h3><?= $studyCount ?></h3>
            <p>Studies</p>
          </div>
          <i class="fa fa-notes-medical"></i>
        </div>
      </div>

      <div class="col-12 col-md-6 col-lg-3">
        <div class="card-box c4">
          <div>
            <h3><?= $studyPatientCount ?></h3>
            <p>Study Patients</p>
          </div>
          <i class="fa fa-bed-pulse"></i>
        </div>
      </div>

      <div class="col-12 col-md-6 col-lg-3">
        <div class="card-box c5">
          <div>
            <h3><?= $uploadCount ?></h3>
            <p>Uploads</p>
          </div>
          <i class="fa fa-file-upload"></i>
        </div>
      </div>

    </div>

    <!-- CHARTS -->
    <div class="row mt-4">

      <div class="col-12 col-lg-6">
        <div class="chart-box">
          <canvas id="barChart"></canvas>
        </div>
      </div>

      <div class="col-12 col-lg-6">
        <div class="chart-box">
          <canvas id="pieChart"></canvas>
        </div>
      </div>

    </div>

  </div>

  <script>
    /* DARK MODE */
    function toggleDark() {
      document.body.classList.toggle("dark");
    }

    /* BAR */
    new Chart(document.getElementById('barChart'), {
      type: 'bar',
      data: {
        labels: ['Patients', 'Researchers', 'Studies', 'Study Patients', 'Uploads'],
        datasets: [{
          data: [
            <?= $patientCount ?>,
            <?= $researchCount ?>,
            <?= $studyCount ?>,
            <?= $studyPatientCount ?>,
            <?= $uploadCount ?>
          ],
          backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#0ea5e9']
        }]
      }
    });

    /* PIE */
    new Chart(document.getElementById('pieChart'), {
      type: 'pie',
      data: {
        labels: ['Patients', 'Researchers', 'Studies', 'Study Patients', 'Uploads'],
        datasets: [{
          data: [
            <?= $patientCount ?>,
            <?= $researchCount ?>,
            <?= $studyCount ?>,
            <?= $studyPatientCount ?>,
            <?= $uploadCount ?>
          ],
          backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#0ea5e9']
        }]
      }
    });
  </script>



<?php
} else {
  $_SESSION['message_login'] = "Login First";
  header("Location: ../login.php");
  exit();
}
include "includes/temp/footer.php"
?>