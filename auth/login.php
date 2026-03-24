<?php
session_start();

if (isset($_SESSION['user'])) {
    header("Location: ../main/dashboard.php");
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (($handle = fopen("../data/users.csv", "r")) !== false) {
        fgetcsv($handle); // Ignorer l'en-tête
        while (($data = fgetcsv($handle)) !== false) {
            if ($data[0] === $username && password_verify($password, $data[1])) {
                $_SESSION['user'] = $username;
                fclose($handle);
                header("Location: ../main/dashboard.php");
                exit;
            }
        }
        fclose($handle);
    }
    $error = "Identifiants incorrects.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-4">
      <div class="card shadow">
        <div class="card-body">
          <h3 class="card-title text-center">Connexion</h3>
          <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
          <form method="post">
            <div class="mb-3">
              <input type="text" name="username" class="form-control" placeholder="Utilisateur">
            </div>
            <div class="mb-3">
              <input type="password" name="password" class="form-control" placeholder="Mot de passe">
            </div>
            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
