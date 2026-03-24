<?php
session_start();
if (!($_SESSION['user'] ?? false)) { 
    header("Location: ../index.php"); 
    exit; 
}

$id = $_GET['id'] ?? null;
if ($id === null || $id === '') {
    die("<div class='alert alert-danger text-center mt-5'>Aucun étudiant sélectionné. <a href='dashboard.php' class='btn btn-primary'>Retour</a></div>");
}

$rows = [];
$file = fopen("../data/etudiants.csv", "r");
while (($data = fgetcsv($file)) !== FALSE) { $rows[] = $data; }
fclose($file);

$current = null;
foreach ($rows as $etudiant) {
    if ($etudiant[0] == $id) { $current = $etudiant; break; }
}
if ($current === null) {
    die("<div class='alert alert-warning text-center mt-5'>Étudiant introuvable. <a href='dashboard.php' class='btn btn-primary'>Retour</a></div>");
}

// Traitement du formulaire pour téléphone/email
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tel   = $_POST['tel']   ?? '';
    $email = $_POST['email'] ?? '';

    foreach ($rows as $index => $etudiant) {
        if ($etudiant[0] == $id) {
            $rows[$index][3] = $tel;
            $rows[$index][4] = $email;
            break;
        }
    }

    $file = fopen("../data/etudiants.csv", "w");
    foreach ($rows as $row) { fputcsv($file, $row); }
    fclose($file);

    header("Location: ../main/dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Détail étudiant</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .dropzone {
      border: 2px dashed #6c757d;
      padding: 20px;
      text-align: center;
      cursor: pointer;
      background-color: #f8f9fa;
    }
    .dropzone.dragover { background-color: #d1e7dd; }
  </style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">Gestion Étudiants</a>
    <a href="../auth/logout.php" class="btn btn-outline-light">Logout</a>
  </div>
</nav>

<div class="container mt-4">
  <h2 class="mb-4">Détails de l'étudiant</h2>
  <div class="card shadow p-4">
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Nom</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($current[1]) ?>" disabled>
      </div>
      <div class="mb-3">
        <label class="form-label">Prénom</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($current[2]) ?>" disabled>
      </div>
      <div class="mb-3">
        <label class="form-label">Téléphone</label>
        <input type="text" name="tel" value="<?= htmlspecialchars($current[3]) ?>" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="text" name="email" value="<?= htmlspecialchars($current[4]) ?>" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Photo actuelle</label><br>
        <img id="currentPhoto" src="photos/<?= htmlspecialchars($current[5]) ?>" class="img-thumbnail mb-3" width="150">
      </div>
      <div class="mb-3 dropzone" id="dropzone">
        Glissez-déposez une nouvelle photo ici ou cliquez pour sélectionner.
        <input type="file" id="photoInput" hidden>
        <div id="preview" class="mt-3"></div>
      </div>
      <button type="submit" class="btn btn-success w-100">Mettre à jour téléphone/email</button>
    </form>
  </div>
</div>

<script>
const dropzone = document.getElementById('dropzone');
const photoInput = document.getElementById('photoInput');
const preview = document.getElementById('preview');
const currentPhoto = document.getElementById('currentPhoto');
const studentId = "<?= $id ?>";

dropzone.addEventListener('click', () => photoInput.click());

dropzone.addEventListener('dragover', e => {
  e.preventDefault();
  dropzone.classList.add('dragover');
});
dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));

dropzone.addEventListener('drop', e => {
  e.preventDefault();
  dropzone.classList.remove('dragover');
  photoInput.files = e.dataTransfer.files;
  uploadPhoto(photoInput.files[0]);
});

photoInput.addEventListener('change', () => {
  if (photoInput.files.length > 0) {
    uploadPhoto(photoInput.files[0]);
  }
});

function uploadPhoto(file) {
  const formData = new FormData();
  formData.append("photo", file);
  formData.append("id", studentId);

  fetch("../utils/upload_photo.php", {
    method: "POST",
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      currentPhoto.src = "photos/" + data.filename;
      preview.innerHTML = `<img src="photos/${data.filename}" class="img-thumbnail" width="150">`;
    } else {
      alert("Erreur lors de l'upload : " + data.error);
    }
  })
  .catch(err => alert("Erreur réseau : " + err));
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
