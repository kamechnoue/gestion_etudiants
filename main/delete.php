<?php
session_start();
if (!($_SESSION['user'] ?? false)) { 
    header("Location: index.php"); 
    exit; 
}
$id = $_GET['id'] ?? null;
if ($id === null || $id === '') {
    die("<div class='fixed inset-0 bg-red-50 flex items-center justify-center p-4'><div class='card max-w-md text-center'><h3 class='text-lg font-semibold text-red-600 mb-4'>Aucun étudiant sélectionné</h3><a href='dashboard.php' class='btn-primary inline-block'>Retour</a></div></div>");
}
// Lecture du fichier CSV
$rows = [];
if (($file = fopen("../data/etudiants.csv", "r")) !== false) {
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
        if (!empty($row[5]) && file_exists("../photos/" . $row[5])) {
            unlink("../photos/" . $row[5]);
        }
        continue; // ne pas ajouter cette ligne
    }
    $newRows[] = $row;
}
// Réécriture du fichier CSV
$file = fopen("../data/etudiants.csv", "w");
foreach ($newRows as $row) {
    fputcsv($file, $row);
}
fclose($file);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppression étudiant</title>
    <link rel="stylesheet" href="../styles/output.css">
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen flex items-center justify-center p-4">
    <!-- Navbar -->
    <nav class="navbar fixed top-0 left-0 right-0 shadow-lg z-50">
        <div class="flex items-center justify-between px-6 py-4">
            <a href="dashboard.php" class="flex items-center gap-3 hover:opacity-80 transition">
                <div class="bg-white text-blue-500 rounded-lg p-2">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.5 1.5H5.75A2.25 2.25 0 003.5 3.75v12.5A2.25 2.25 0 005.75 18.5h8.5a2.25 2.25 0 002.25-2.25V8.5m-7-5v5h5M8 13.5h4m-4 2h4"></path>
                    </svg>
                </div>
                <h1 class="text-xl font-bold">Gestion Étudiants</h1>
            </a>
            <a href="dashboard.php" class="btn-outline-light">Retour au tableau de bord</a>
        </div>
    </nav>

    <div class="w-full max-w-md mt-20">
        <div class="card text-center">
            <?php if ($deleted): ?>
                <div class="mb-4">
                    <div class="inline-block bg-green-100 text-green-600 rounded-full p-4 mb-4">
                        <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <h2 class="text-3xl font-bold text-green-600 mb-2">Succès!</h2>
                <p class="text-slate-600 text-lg mb-2">Étudiant supprimé avec succès</p>
                <p class="text-slate-500">L'étudiant avec l'ID <span class="font-mono text-blue-600 font-semibold"><?= htmlspecialchars($id) ?></span> a été retiré de la liste et sa photo a été supprimée.</p>

                <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-800"><strong>Toutes les données</strong> associées à cet étudiant ont été supprimées du système.</p>
                </div>
            <?php else: ?>
                <div class="mb-4">
                    <div class="inline-block bg-red-100 text-red-600 rounded-full p-4 mb-4">
                        <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <h2 class="text-3xl font-bold text-red-600 mb-2">Erreur</h2>
                <p class="text-slate-600 text-lg">Impossible de trouver l'étudiant</p>
                <p class="text-slate-500">L'étudiant avec l'ID <span class="font-mono text-blue-600 font-semibold"><?= htmlspecialchars($id) ?></span> n'existe pas ou a déjà été supprimé.</p>

                <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-800">Veuillez vérifier l'ID et réessayer.</p>
                </div>
            <?php endif; ?>

            <a href="dashboard.php" class="btn-primary-lg w-full mt-6 inline-block flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Retour au tableau de bord
            </a>
        </div>
    </div>
</body>
</html>