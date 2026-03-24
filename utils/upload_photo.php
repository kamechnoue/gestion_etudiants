<?php
session_start();
header('Content-Type: application/json');

// Désactiver l'affichage des erreurs HTML
ini_set('display_errors', 0);
error_reporting(E_ALL);

$response = ["success" => false];

if (!($_SESSION['user'] ?? false)) {
    $response["error"] = "Non authentifié";
    echo json_encode($response);
    exit;
}

$id = $_POST['id'] ?? null;
if (!$id || empty($_FILES['photo']['name'])) {
    $response["error"] = "Paramètres manquants";
    echo json_encode($response);
    exit;
}

$filename = $id . "_" . basename($_FILES['photo']['name']);
$target = "photos/" . $filename;

if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
    // Mise à jour du CSV
    $rows = [];
    if (($file = fopen("etudiants.csv", "r")) !== false) {
        while (($data = fgetcsv($file)) !== false) {
            $rows[] = $data;
        }
        fclose($file);
    }

    foreach ($rows as $index => $etudiant) {
        if ($etudiant[0] == $id) {
            $rows[$index][5] = $filename;
            break;
        }
    }

    if (($file = fopen("etudiants.csv", "w")) !== false) {
        foreach ($rows as $row) {
            fputcsv($file, $row);
        }
        fclose($file);
    }

    $response = ["success" => true, "filename" => $filename];
} else {
    $response["error"] = "Échec de l'upload";
}

echo json_encode($response);
