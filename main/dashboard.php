<?php
session_start();
if (!($_SESSION['user'] ?? false)) { 
    header("Location: ../index.php"); 
    exit; 
}

$message = "";

// Export CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=etudiants.csv');
    readfile("etudiants.csv");
    exit;
}

// Suppression sélection étudiants
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_selected'])) {
    $idsToDelete = $_POST['selected'] ?? [];
    if (!empty($idsToDelete)) {
        $rows = [];
        if (($file = fopen("../data/etudiants.csv", "r")) !== false) {
            while (($data = fgetcsv($file)) !== false) {
                if (!in_array($data[0], $idsToDelete)) {
                    $rows[] = $data;
                } else {
                    $photo = $data[5] ?? '';
                    if ($photo && file_exists("photos/" . $photo)) {
                        unlink("photos/" . $photo);
                    }
                }
            }
            fclose($file);
        }
        $file = fopen("../data/etudiants.csv", "w");
        foreach ($rows as $row) fputcsv($file, $row);
        fclose($file);
        $message = "✅ Suppression effectuée";
    }
}

// Import CSV + photos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $csvTmp = $_FILES['csv_file']['tmp_name'];
    $rows = [];
    if (($handle = fopen($csvTmp, "r")) !== false) {
        while (($data = fgetcsv($handle)) !== false) $rows[] = $data;
        fclose($handle);
    }
    $existing = [];
    if (($file = fopen("../data/etudiants.csv", "r")) !== false) {
        while (($data = fgetcsv($file)) !== false) {
            $existing[] = strtolower(($data[1] ?? '') . "_" . ($data[2] ?? ''));
        }
        fclose($file);
    }
    $file = fopen("../data/etudiants.csv", "a");
    foreach ($rows as $row) {
        $nom    = strtolower($row[1] ?? '');
        $prenom = strtolower($row[2] ?? '');
        $key    = $nom . "_" . $prenom;
        if (!in_array($key, $existing)) {
            fputcsv($file, $row);
            $existing[] = $key;
        } else {
            $message .= "⚠️ Doublon ignoré : $nom $prenom<br>";
        }
    }
    fclose($file);
    foreach ($_FILES['photos']['tmp_name'] as $index => $tmpName) {
        $filename = $_FILES['photos']['name'][$index];
        if ($tmpName) move_uploaded_file($tmpName, "photos/" . basename($filename));
    }
    if (!$message) $message = "✅ Import terminé avec succès";
}

// Gestion des utilisateurs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $username = $_POST['new_username'];
        $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $role = $_POST['new_role'];
        $file = fopen("../data/users.csv", "a");
        fputcsv($file, [$username, $password, $role]);
        fclose($file);
        $message = "✅ Utilisateur ajouté";
    }
    if (isset($_POST['delete_user'])) {
        $username = $_POST['delete_username'];
        $rows = [];
        if (($file = fopen("../data/users.csv", "r")) !== false) {
            while (($data = fgetcsv($file)) !== false) {
                if ($data[0] !== $username) $rows[] = $data;
            }
            fclose($file);
        }
        $file = fopen("../data/users.csv", "w");
        foreach ($rows as $row) fputcsv($file, $row);
        fclose($file);
        $message = "✅ Utilisateur supprimé";
    }
    if (isset($_POST['change_password'])) {
        $username = $_POST['change_username'];
        $newPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $rows = [];
        if (($file = fopen("../data/users.csv", "r")) !== false) {
            while (($data = fgetcsv($file)) !== false) {
                if ($data[0] === $username) $data[1] = $newPassword;
                $rows[] = $data;
            }
            fclose($file);
        }
        $file = fopen("../data/users.csv", "w");
        foreach ($rows as $row) fputcsv($file, $row);
        fclose($file);
        $message = "✅ Mot de passe changé";
    }
    if (isset($_POST['change_role'])) {
        $username = $_POST['role_username'];
        $role = $_POST['role'];
        $rows = [];
        if (($file = fopen("../data/users.csv", "r")) !== false) {
            while (($data = fgetcsv($file)) !== false) {
                if ($data[0] === $username) $data[2] = $role;
                $rows[] = $data;
            }
            fclose($file);
        }
        $file = fopen("../data/users.csv", "w");
        foreach ($rows as $row) fputcsv($file, $row);
        fclose($file);
        $message = "✅ Rôle modifié";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand">Gestion Étudiants</a>
    <a href="../auth/logout.php" class="btn btn-outline-light">Logout</a>
  </div>
</nav>

<div class="container mt-4">
  <div class="d-flex justify-content-center gap-3 mb-4">
    <a href="add.php" class="btn btn-success btn-lg shadow"><i class="bi bi-person-plus"></i> Ajouter</a>
    <a href="dashboard.php?export=csv" class="btn btn-info btn-lg shadow"><i class="bi bi-download"></i> Exporter</a>
    <button class="btn btn-primary btn-lg shadow" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bi bi-upload"></i> Importer</button>
    <button class="btn btn-danger btn-lg shadow" form="deleteForm"><i class="bi bi-trash"></i> Supprimer sélection</button>
    <button class="btn btn-secondary btn-lg shadow" data-bs-toggle="offcanvas" data-bs-target="#userMenu"><i class="bi bi-gear"></i> Utilisateurs</button>
  </div>

  <?php if (!empty($message)): ?>
    <div class="alert alert-info mt-3"><?= $message ?></div>
  <?php endif; ?>

  <!-- Tableau des étudiants -->
  <form method="post" id="deleteForm">
    <input type="hidden" name="delete_selected" value="1">
    <table class="table table-striped table-bordered align-middle shadow-sm mt-4">
      <thead class="table-dark">
        <tr>
          <th><input type="checkbox" id="selectAll"></th>
          <th>ID</th>
          <th>Nom</th>
          <th>Prénom</th>
          <th>Téléphone</th>
          <th>Email</th>
          <th>Photo</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if (($file = fopen("../data/etudiants.csv", "r")) !== false) {
          while (($data = fgetcsv($file)) !== false) {
            $id     = $data[0] ?? '';
            $nom    = $data[1] ?? '';
            $prenom = $data[2] ?? '';
            $tel    = $data[3] ?? '';
            $email  = $data[4] ?? '';
            $photo  = $data[5] ?? 'default.png';
            echo "<tr>";
            echo "<td><input type='checkbox' name='selected[]' value='{$id}'></td>";
            echo "<td>{$id}</td><td>{$nom}</td><td>{$prenom}</td><td>{$tel}</td><td>{$email}</td>";
            echo "<td><img src='photos/{$photo}' class='img-thumbnail' width='80'></td>";
            echo "<td>
                    <div class='btn-group'>
                      <a href='detail.php?id={$id}' class='btn btn-outline-primary btn-sm' title='Détails'>
                        <i class='bi bi-eye-fill'></i>
                      </a>
                      <a href='delete.php?id={$id}' class='btn btn-outline-danger btn-sm' title='Supprimer'>
                        <i class='bi bi-trash-fill'></i>
                      </a>
                    </div>
                  </td>";
            echo "</tr>";
          }
          fclose($file);
        }
        ?>
      </tbody>
    </table>
  </form>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-cloud-arrow-up"></i> Importer des étudiants</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="post" enctype="multipart/form-data">
          <div class="mb-3">
            <label class="form-label fw-bold"><i class="bi bi-file-earmark-spreadsheet"></i> Fichier CSV</label>
            <input type="file" name="csv_file" class="form-control" accept=".csv" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold"><i class="bi bi-images"></i> Photos des étudiants</label>
            <input type="file" name="photos[]" class="form-control" accept="image/*" multiple required>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-upload"></i> Importer CSV + Photos</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Menu utilisateurs -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="userMenu">
  <div class="offcanvas-header bg-dark text-white">
    <h5 class="offcanvas-title"><i class="bi bi-people"></i> Gestion des utilisateurs</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <!-- Ajout utilisateur -->
    <form method="post" class="mb-3">
      <h6><i class="bi bi-person-plus"></i> Ajouter un utilisateur</h6>
      <input type="text" name="new_username" class="form-control mb-2" placeholder="Nom d'utilisateur" required>
      <input type="password" name="new_password" class="form-control mb-2" placeholder="Mot de passe" required>
      <select name="new_role" class="form-select mb-2">
        <option value="student">Student</option>
        <option value="admin">Admin</option>
      </select>
      <button type="submit" name="add_user" class="btn btn-success w-100">Ajouter</button>
    </form>

    <!-- Suppression utilisateur -->
    <form method="post" class="mb-3">
      <h6><i class="bi bi-person-x"></i> Supprimer un utilisateur</h6>
      <input type="text" name="delete_username" class="form-control mb-2" placeholder="Nom d'utilisateur" required>
      <button type="submit" name="delete_user" class="btn btn-danger w-100">Supprimer</button>
    </form>

    <!-- Changement mot de passe -->
    <form method="post" class="mb-3">
      <h6><i class="bi bi-key"></i> Changer mot de passe</h6>
      <input type="text" name="change_username" class="form-control mb-2" placeholder="Nom d'utilisateur" required>
      <input type="password" name="new_password" class="form-control mb-2" placeholder="Nouveau mot de passe" required>
      <button type="submit" name="change_password" class="btn btn-warning w-100">Changer</button>
    </form>

    <!-- Changement rôle -->
    <form method="post">
      <h6><i class="bi bi-shield-lock"></i> Modifier rôle</h6>
      <input type="text" name="role_username" class="form-control mb-2" placeholder="Nom d'utilisateur" required>
      <select name="role" class="form-select mb-2">
        <option value="student">Student</option>
        <option value="admin">Admin</option>
      </select>
      <button type="submit" name="change_role" class="btn btn-primary w-100">Modifier</button>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Sélectionner/désélectionner tous les étudiants
  document.getElementById('selectAll').addEventListener('click', function() {
    const checkboxes = document.querySelectorAll('input[name="selected[]"]');
    checkboxes.forEach(cb => cb.checked = this.checked);
  });
</script>
</body>
</html>
