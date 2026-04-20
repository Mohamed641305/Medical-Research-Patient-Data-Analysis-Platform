<?php
session_start();
include "includes/db/db.php";
include "includes/temp/init.php";
include "includes/temp/navbar.php";

/* ================= SECURITY ================= */
if (!isset($_SESSION['admin_login'])) {
  header("Location: login.php");
  exit();
}

/* ================= INPUTS ================= */
$search = $_GET["search"] ?? "";
$disease = $_GET["disease"] ?? "";

/* ================= AI ================= */
function predict($bp, $sugar, $bmi)
{
  $score =
    ($bp / 200) * 40 +
    ($sugar / 300) * 35 +
    ($bmi / 50) * 25;

  if ($score > 75) return "Critical 🔴";
  if ($score > 50) return "High 🟠";
  if ($score > 30) return "Medium 🟡";
  return "Low 🟢";
}

/* ================= QUERY ================= */
$sql = "SELECT * FROM patients WHERE 1=1";
$params = [];

if ($search != "") {
  $sql .= " AND name LIKE :search";
  $params["search"] = "%$search%";
}

if ($disease != "") {
  $sql .= " AND disease_type = :disease";
  $params["disease"] = $disease;
}

$stmt = $connect->prepare($sql);
$stmt->execute($params);
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= DATA ================= */
$rows = [];
$bpArr = [];
$sugarArr = [];
$healthy = 0;
$risk = 0;

foreach ($res as $r) {

  $bp = (float)($r["blood_pressure"] ?? 0);
  $sugar = (float)($r["sugar_level"] ?? 0);
  $bmi = (float)($r["bmi"] ?? 0);

  $bpArr[] = $bp;
  $sugarArr[] = $sugar;

  if ($bp > 140 || $sugar > 180 || $bmi > 30) $risk++;
  else $healthy++;

  $rows[] = $r;
}

/* ================= DISEASE ================= */
$diseaseData = [];
$dRes = $connect->query("SELECT disease_type, COUNT(*) c FROM patients GROUP BY disease_type");

if ($dRes) {
  while ($d = $dRes->fetch(PDO::FETCH_ASSOC)) {
    $diseaseData[$d["disease_type"]] = $d["c"];
  }
}

/* ================= CORRELATION ================= */
function correlation($x, $y)
{
  $n = count($x);
  if ($n < 2) return 0;

  $sx = array_sum($x);
  $sy = array_sum($y);

  $sxy = $sx2 = $sy2 = 0;

  for ($i = 0; $i < $n; $i++) {
    $sxy += $x[$i] * $y[$i];
    $sx2 += $x[$i] ** 2;
    $sy2 += $y[$i] ** 2;
  }

  $den = sqrt(($n * $sx2 - $sx ** 2) * ($n * $sy2 - $sy ** 2));

  return $den == 0 ? 0 : (($n * $sxy - $sx * $sy) / $den);
}

$correlation = correlation($bpArr, $sugarArr);
?>

<!DOCTYPE html>
<html>

<head>
  <title>Medical Dashboard</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    body {
      background: linear-gradient(135deg, #eef2f7, #dbeafe);
      font-family: Segoe UI;
      transition: 0.3s;
    }

    body.dark {
      background: #0b1220;
      color: white;
    }

    /* HEADER */
    .header {
      background: linear-gradient(135deg, #0f766e, #14b8a6);
      color: white;
      padding: 18px;
      border-radius: 18px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, .2);
    }

    /* CARDS */
    /* ================= CARDS ONLY ================= */
    .card {
      border: none;
      border-radius: 18px;
      transition: 0.3s;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
    }

    .card:hover {
      transform: translateY(-8px);
    }

    .card-healthy {
      background: linear-gradient(135deg, #22c55e, #16a34a);
      color: white;
    }

    .card-risk {
      background: linear-gradient(135deg, #f97316, #ef4444);
      color: white;
    }

    .card-corr {
      background: linear-gradient(135deg, #3b82f6, #6366f1);
      color: white;
    }

    /* TABLE */
    .table-box {
      background: white;
      padding: 18px;
      border-radius: 20px;
      margin-top: 20px;
    }

    body.dark .table-box {
      background: #1e293b;
    }

    table {
      width: 100%;
      border-radius: 15px;
      overflow: hidden;
    }

    th {
      background: #0f766e;
      color: white;
      text-align: center;
      padding: 12px;
    }

    td {
      text-align: center;
      padding: 10px;
    }

    /* CHARTS */
    .charts {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 15px;
      margin-top: 20px;
    }

    .chart-box {
      background: white;
      padding: 15px;
      border-radius: 20px;
      height: 340px;
    }

    /* BUTTONS */
    .btn-glow {
      background: #14b8a6;
      color: white;
      border: none;
      border-radius: 10px;
      padding: 10px;
    }

    .back-btn {
      background: white;
      color: #0f766e;
      padding: 8px 14px;
      border-radius: 10px;
      text-decoration: none;
      font-weight: 500;
      transition: 0.3s;
    }

    .back-btn:hover {
      transform: scale(1.05);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
  </style>
</head>

<body>

  <div class="container py-4">

    <!-- HEADER -->
    <div class="header d-flex justify-content-between align-items-center">
      <h4>🧠 Medical Dashboard</h4>

      <div class="d-flex gap-2">
        <button class="btn btn-light" onclick="document.body.classList.toggle('dark')">🌙</button>
        <a href="uploads.php" class="back-btn"><i class="fa-solid fa-backward"></i></a>
      </div>
    </div>

    <!-- CARDS -->
    <!-- ================= CARDS (ONLY MODIFIED PART) ================= -->
    <div class="row mt-3 g-3">

      <div class="col-md-4">
        <div class="card card-healthy text-center p-4">
          <i class="fa fa-check-circle fa-2x mb-2"></i>
          <h5>Healthy</h5>
          <h2><?= $healthy ?></h2>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card card-risk text-center p-4">
          <i class="fa fa-triangle-exclamation fa-2x mb-2"></i>
          <h5>Risk</h5>
          <h2><?= $risk ?></h2>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card card-corr text-center p-4">
          <i class="fa fa-wave-square fa-2x mb-2"></i>
          <h5>Correlation</h5>
          <h2><?= round($correlation, 2) ?></h2>
        </div>
      </div>

    </div>


    <!-- SEARCH -->
    <div class="table-box">

      <form class="row g-2">
        <div class="col-md-6">
          <input name="search" class="form-control" placeholder="Search patient...">
        </div>

        <div class="col-md-4">
          <select name="disease" class="form-control">
            <option value="">All Diseases</option>
            <option>Diabetes</option>
            <option>Hypertension</option>
            <option>Heart Disease</option>
            <option>Asthma</option>
          </select>
        </div>

        <div class="col-md-2">
          <button class="btn-glow w-100">Filter</button>
        </div>
      </form>

    </div>

    <!-- TABLE -->
    <div class="table-box text-center">

      <table>
        <tr>
          <th>Name</th>
          <th>Disease</th>
          <th>BP</th>
          <th>Sugar</th>
          <th>BMI</th>
          <th>Risk</th>
        </tr>

        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= $r["name"] ?></td>
            <td><?= $r["disease_type"] ?></td>
            <td><?= $r["blood_pressure"] ?></td>
            <td><?= $r["sugar_level"] ?></td>
            <td><?= $r["bmi"] ?></td>
            <td><?= predict($r["blood_pressure"], $r["sugar_level"], $r["bmi"]) ?></td>
          </tr>
        <?php endforeach; ?>

      </table>

    </div>

    <!-- CHARTS -->
    <div class="charts">

      <div class="chart-box"><canvas id="pie"></canvas></div>
      <div class="chart-box"><canvas id="scatter"></canvas></div>
      <div class="chart-box"><canvas id="disease"></canvas></div>

    </div>

  </div>

  <script>
    /* PIE */
    new Chart(document.getElementById("pie"), {
      type: "pie",
      data: {
        labels: ["Healthy", "Risk"],
        datasets: [{
          data: [<?= $healthy ?>, <?= $risk ?>]
        }]
      }
    });

    /* SCATTER */
    new Chart(document.getElementById("scatter"), {
      type: "scatter",
      data: {
        datasets: [{
          label: "BP vs Sugar",
          data: [
            <?php foreach ($bpArr as $i => $v): ?> {
                x: <?= $v ?>,
                y: <?= $sugarArr[$i] ?>
              },
            <?php endforeach; ?>
          ]
        }]
      }
    });

    /* DISEASE */
    new Chart(document.getElementById("disease"), {
      type: "pie",
      data: {
        labels: <?= json_encode(array_keys($diseaseData)) ?>,
        datasets: [{
          data: <?= json_encode(array_values($diseaseData)) ?>
        }]
      }
    });
  </script>

</body>

</html>