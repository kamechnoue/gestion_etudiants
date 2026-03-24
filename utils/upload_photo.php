<?php
session_start();
header('Content-Type: application/json');

// Disable error display for clean JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

$response = ["success" => false];

// Check authentication
if (!($_SESSION['user'] ?? false)) {
    $response["error"] = "Non authentifié";
    echo json_encode($response);
    exit;
}

// Check parameters
$id = $_POST['id'] ?? null;
if (!$id || empty($_FILES['photo']['name'])) {
    $response["error"] = "Paramètres manquants";
    echo json_encode($response);
    exit;
}

// Validate file
$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($_FILES['photo']['type'], $allowed)) {
    $response["error"] = "Type de fichier non autorisé";
    echo json_encode($response);
    exit;
}

// Check file size (max 10MB)
if ($_FILES['photo']['size'] > 10 * 1024 * 1024) {
    $response["error"] = "Fichier trop volumineux (max 10MB)";
    echo json_encode($response);
    exit;
}

$filename = $id . "_" . basename($_FILES['photo']['name']);
$target = "../photos/" . $filename;

// Create photos directory if it doesn't exist at project root
if (!is_dir("../photos")) {
    mkdir("../photos", 0755, true);
}

if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
    // Update CSV with new photo filename
    $rows = [];
    if (($file = fopen("../data/etudiants.csv", "r")) !== false) {
        while (($data = fgetcsv($file)) !== false) {
            $rows[] = $data;
        }
        fclose($file);
    }

    // Update the photo field for this student
    $found = false;
    foreach ($rows as $index => $etudiant) {
        if ($etudiant[0] == $id) {
            $rows[$index][5] = $filename;
            $found = true;
            break;
        }
    }

    // Save updated CSV
    if (($file = fopen("../data/etudiants.csv", "w")) !== false) {
        foreach ($rows as $row) {
            fputcsv($file, $row);
        }
        fclose($file);
    }

    $response = ["success" => true, "filename" => $filename];
} else {
    $response["error"] = "Échec du téléchargement du fichier";
}

echo json_encode($response);
?>