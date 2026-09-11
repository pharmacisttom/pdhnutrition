<?php
use App\Config\AppConfig;
$baseUrl = AppConfig::get('APP_URL', '/pdhnutrition');
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'PDH Nutrition - ระบบบริหารจัดการภาวะโภชนาการผู้ป่วย') ?></title>
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <!-- SweetAlert2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
  
  <!-- Custom Application Styles -->
  <link href="<?= $baseUrl ?>/public/assets/css/custom.css" rel="stylesheet">
  <link href="<?= $baseUrl ?>/public/assets/css/print.css" rel="stylesheet" media="print">
</head>
<body class="bg-light">
<div class="d-flex" id="wrapper">
