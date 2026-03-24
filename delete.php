<?php
session_start();
if (!($_SESSION['user'] ?? false)) { 
    header("Location: index.php"); 
    exit; 
}

$id = $_GET['id'] ?? null;
if ($id === null || $id === '') {
    die("<div class='alert alert-danger text-center mt-5'>Aucun étudiant sélectionné. <a href='dashboard.php' class='btn btn-primary'>Retour</a></div>");
}

// Lecture du fichier CSV
$rows = [];
if (($file = fopen("etudiants.csv", "r")) !== false) {
    while (($data = fgetcsv($file)) !== false) {
        $rows[] = $data;
    }
    fclose($file);
}

// Suppression de l'étudiant
$newRows = [];
$deleted = false;
foreach ($rows as $row) {
    if ($row[0] == $id) {
        $deleted = true;
        // Supprimer aussi la photo si elle existe
        if (!empty($row[5]) && file_exists("photos/" . $row[5])) {
            unlink("photos/" . $row[5]);
        }
        continue; // ne pas ajouter cette ligne
    }
    $newRows[] = $row;
}

// Réécriture du fichier CSV
$file = fopen("etudiants.csv", "w");
foreach ($newRows as $row) {
    fputcsv($file, $row);
}
fclose($file);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Suppression étudiant</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand">Gestion Étudiants</a>
    <a href="dashboard.php" class="btn btn-outline-light">Retour</a>
  </div>
</nav>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow text-center">
        <div class="card-body">
          <?php if ($deleted): ?>
            <h3 class="card-title text-success">Étudiant supprimé avec succès</h3>
            <p class="card-text">L'étudiant avec l'ID <strong><?= htmlspecialchars($id) ?></strong> a été retiré de la liste.</p>
          <?php else: ?>
            <h3 class="card-title text-danger">Erreur</h3>
            <p class="card-text">Impossible de trouver l'étudiant avec l'ID <strong><?= htmlspecialchars($id) ?></strong>.</p>
          <?php endif; ?>
          <a href="dashboard.php" class="btn btn-primary mt-3">Retour au tableau de bord</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
