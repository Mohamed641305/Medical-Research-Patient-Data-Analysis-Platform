<?php
session_start();

// الحماية
if (isset($_SESSION['user_login'])) {

  include("includes/temp/header.php");
  include('includes/temp/navbar.php');
?>

  <!-- <!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>MediCare System</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> -->

  <style>
    body {
      background: #f4f7fb;
    }

    .hero {
      background: linear-gradient(rgba(0, 0, 0, .6), rgba(0, 0, 0, .6)),
        url('https://images.unsplash.com/photo-1580281657527-47f249e8f96b') center/cover;
      color: white;
      padding: 120px 20px;
      text-align: center;
    }

    .section {
      padding: 60px 0;
    }

    .card-hover:hover {
      transform: translateY(-5px);
      transition: .3s;
    }

    .stat {
      font-size: 28px;
      font-weight: bold;
    }
  </style>
  </head>

  <body>

    <div class="hero">
      <h1>MediCare System</h1>
      <p>Smart Medical Research & Patient Management Platform</p>
      <a href="#" class="btn btn-danger mt-3">Get Started</a>
    </div>

    <div class="container section">
      <div class="row text-center">

        <div class="col-md-3">
          <div class="card p-3 shadow card-hover">
            <i class="fa fa-user-injured fa-2x text-primary"></i>
            <h5 class="mt-2">Patients</h5>
            <p>Manage medical records easily</p>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card p-3 shadow card-hover">
            <i class="fa fa-user-doctor fa-2x text-success"></i>
            <h5 class="mt-2">Doctors</h5>
            <p>Track researchers & doctors</p>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card p-3 shadow card-hover">
            <i class="fa fa-notes-medical fa-2x text-warning"></i>
            <h5 class="mt-2">Studies</h5>
            <p>Clinical research system</p>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card p-3 shadow card-hover">
            <i class="fa fa-shield fa-2x text-danger"></i>
            <h5 class="mt-2">Secure</h5>
            <p>Protected medical data</p>
          </div>
        </div>

      </div>
    </div>

    <div class="container section">
      <h2 class="text-center mb-4">Our Medical Team</h2>

      <div class="row">

        <div class="col-md-4">
          <div class="card shadow">
            <img src="https://images.unsplash.com/photo-1537368910025-700350fe46c7" class="card-img-top">
            <div class="card-body text-center">
              <h5>Dr. Ahmed</h5>
              <p>Cardiology Specialist</p>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card shadow">
            <img src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2" class="card-img-top">
            <div class="card-body text-center">
              <h5>Dr. Sara</h5>
              <p>Neurology Expert</p>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card shadow">
            <img src="https://images.unsplash.com/photo-1612349317150-e413f6a5b16d" class="card-img-top">
            <div class="card-body text-center">
              <h5>Dr. Mohamed</h5>
              <p>Research Scientist</p>
            </div>
          </div>
        </div>

      </div>
    </div>

    <div class="container section text-center">
      <div class="row">
        <div class="col-md-3">
          <div class="stat text-primary">120+</div>Patients
        </div>
        <div class="col-md-3">
          <div class="stat text-success">25+</div>Doctors
        </div>
        <div class="col-md-3">
          <div class="stat text-warning">18+</div>Studies
        </div>
        <div class="col-md-3">
          <div class="stat text-danger">24/7</div>Availability
        </div>
      </div>
    </div>

  <?php
} else {
  $_SESSION['message_login'] = "Login First";
  header("Location: login.php");
}
include("includes/temp/footer.php");
  ?>