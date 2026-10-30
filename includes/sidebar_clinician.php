<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<a href="dashboard.php" class="<?= $current == 'dashboard.php' ? 'active' : '' ?>">
    <i class="fas fa-home"></i>    
    Dashboard</a>

<a href="patients.php" class="<?= $current == 'patients.php' ? 'active' : '' ?>">
    <i class="fas fa-user-injured"></i>    
   Patients</a>
<a href="register_patient.php" class="<?= $current == 'register_patient.php' ? 'active' : '' ?>">
    <i class="fas fa-user-plus"></i>
    Register Patient</a>
<a href="search_patient.php" class="<?= $current == 'search_patient.php' ? 'active' : '' ?>">
    <i class="fas fa-search"></i>    
    Search Patient</a>
<a href="record_consultation.php" class="<?= $current == 'record_consultation.php' ? 'active' : '' ?>">
    <i class="fas fa-stethoscope"></i>    
    Record Consultation</a>