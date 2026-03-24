<?php
session_start();
if (!($_SESSION['user'] ?? false)) { 
    header("Location: ../index.php"); 
    exit; 
}

$message = "";
$usersPath = "../data/users.csv";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add User
    if (isset($_POST['add_user'])) {
        $username = $_POST['new_username'];
        $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $role = $_POST['new_role'];
        $file = fopen($usersPath, "a");
        fputcsv($file, [$username, $password, $role]);
        fclose($file);
        $message = "Utilisateur cree avec succes";
    }

    // Delete User
    if (isset($_POST['delete_user'])) {
        $target = $_POST['delete_username'];
        $rows = [];
        if (($file = fopen($usersPath, "r")) !== false) {
            while (($data = fgetcsv($file)) !== false) {
                if ($data[0] !== $target) $rows[] = $data;
            }
            fclose($file);
        }
        $file = fopen($usersPath, "w");
        foreach ($rows as $row) fputcsv($file, $row);
        fclose($file);
        $message = "Utilisateur supprime";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Utilisateurs</title>
    <link rel="stylesheet" href="../styles/output.css">
</head>
<body class="bg-slate-50 min-h-screen font-sans">
    
    <?php require_once(__DIR__ . "/components/navbar.php") ?>

    <main class="container mx-auto px-4 py-12 max-w-4xl">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900">Administration Utilisateurs</h1>
            <p class="text-slate-500">Gerez les acces et les roles du systeme.</p>
        </div>

        <?php if ($message): ?>
            <div class="bg-blue-50 border border-blue-100 text-blue-700 p-4 rounded-xl mb-8 font-bold">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="grid md:grid-cols-2 gap-8">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
                <h2 class="text-lg font-bold mb-6 text-slate-800">Ajouter un compte</h2>
                <form method="post" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Identifiant</label>
                        <input type="text" name="new_username" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-slate-900 transition-all" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Mot de passe</label>
                        <input type="password" name="new_password" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-slate-900 transition-all" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Role</label>
                        <select name="new_role" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none">
                            <option value="student">Etudiant</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>
                    <button type="submit" name="add_user" class="w-full py-4 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 transition-all shadow-lg shadow-slate-200">
                        Creer l'utilisateur
                    </button>
                </form>
            </div>

            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
                <h2 class="text-lg font-bold mb-6 text-red-600">Zone de suppression</h2>
                <form method="post" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Nom d'utilisateur</label>
                        <input type="text" name="delete_username" class="w-full px-4 py-3 bg-red-50/50 border border-red-100 rounded-xl outline-none focus:ring-2 focus:ring-red-500 transition-all" placeholder="Utilisateur a supprimer" required>
                    </div>
                    <p class="text-xs text-slate-400">Attention : cette action est irreversible. L'utilisateur perdra immediatement ses acces.</p>
                    <button type="submit" name="delete_user" class="w-full py-4 bg-red-50 text-red-600 border border-red-200 font-bold rounded-xl hover:bg-red-600 hover:text-white transition-all">
                        Supprimer le compte
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-12 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="p-4 text-xs font-bold uppercase text-slate-400">Username</th>
                        <th class="p-4 text-xs font-bold uppercase text-slate-400">Role</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php
                    if (($file = fopen($usersPath, "r")) !== false) {
                        while (($data = fgetcsv($file)) !== false) {
                            echo "<tr>";
                            echo "<td class='p-4 font-bold text-slate-700'>".htmlspecialchars($data[0])."</td>";
                            echo "<td class='p-4'><span class='px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-bold'>".htmlspecialchars($data[2])."</span></td>";
                            echo "</tr>";
                        }
                        fclose($file);
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>