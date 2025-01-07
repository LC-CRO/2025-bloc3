<?php
session_start();
require_once('database/db.php');

// Vérification de l'authentification
if (!isset($_SESSION['token']) || empty($_SESSION['token'])) {
    header("Location: index.php");
    exit();
}

require_once('security/connexion.php');

if (!isTokenValid($_SESSION['token'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

$conn = connectDB();
$action = $_POST['action'] ?? 'list'; // Par défaut, afficher la liste
$vehicleId = $_POST['id'] ?? null; // ID du véhicule pour modification/suppression
$error = "";
$success = "";

// Récupérer les clients pour le <select>
function getClients($conn)
{
    $stmt = $conn->prepare("SELECT id, nom, email FROM clients ORDER BY nom");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getVehicles($conn)
{
    $stmt = $conn->prepare("SELECT v.*, c.nom AS client_nom FROM vehicules v LEFT JOIN clients c ON v.client_id = c.id");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Récupérer les informations d'un véhicule spécifique
function getVehicleById($conn, $id)
{
    $stmt = $conn->prepare("SELECT * FROM vehicules WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Exécuter les actions CREATE, UPDATE, DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create' && isset($_POST['submit_create'])) {
        $marque = htmlspecialchars($_POST['marque']);
        $modele = htmlspecialchars($_POST['modele']);
        $annee = intval($_POST['annee']);
        $clientId = empty($_POST['client_id']) ? null : intval($_POST['client_id']);

        if ($marque && $modele) {
            $stmt = $conn->prepare("INSERT INTO vehicules (marque, modele, annee, client_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssii", $marque, $modele, $annee, $clientId);
            if ($stmt->execute()) {
                $success = "Véhicule ajouté avec succès.";
            } else {
                $error = "Erreur lors de l'ajout du véhicule.";
            }
        } else {
            $error = "Les champs Marque et Modèle sont obligatoires.";
        }
        $action = 'list';
    }

    if ($action === 'update' && isset($_POST['submit_update']) && $vehicleId) {
        $marque = htmlspecialchars($_POST['marque']);
        $modele = htmlspecialchars($_POST['modele']);
        $annee = intval($_POST['annee']);
        $clientId = empty($_POST['client_id']) ? null : intval($_POST['client_id']);

        if ($marque && $modele) {
            $stmt = $conn->prepare("UPDATE vehicules SET marque = ?, modele = ?, annee = ?, client_id = ? WHERE id = ?");
            $stmt->bind_param("ssiii", $marque, $modele, $annee, $clientId, $vehicleId);
            if ($stmt->execute()) {
                $success = "Véhicule modifié avec succès.";
                $action = 'list';
            } else {
                $error = "Erreur lors de la modification du véhicule.";
            }
        } else {
            $error = "Les champs Marque et Modèle sont obligatoires.";
        }
    }

    if ($action === 'delete' && isset($_POST['submit_delete']) && $vehicleId) {
        $stmt = $conn->prepare("DELETE FROM vehicules WHERE id = ?");
        $stmt->bind_param("i", $vehicleId);
        if ($stmt->execute()) {
            $success = "Véhicule supprimé avec succès.";
        } else {
            $error = "Erreur lors de la suppression du véhicule.";
        }
        $action = 'list';
    }
}

$clients = getClients($conn);
$vehicles = getVehicles($conn);

// Si on est en mode "update", récupérer les données du véhicule
if ($action === 'update' && $vehicleId) {
    $vehicle = getVehicleById($conn, $vehicleId);
    if (!$vehicle) {
        $error = "Véhicule introuvable.";
        $action = 'list';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Véhicules</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/styles.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="text-center mb-4">Gestion des Véhicules</h1>

    <!-- Alertes -->
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
        <div class="mb-4 text-end">
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <button type="submit" class="btn btn-primary">Ajouter un véhicule</button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Marque</th>
                    <th>Modèle</th>
                    <th>Année</th>
                    <th>Client</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($vehicles as $vehicle): ?>
                    <tr>
                        <td><?= htmlspecialchars($vehicle['id']) ?></td>
                        <td><?= htmlspecialchars($vehicle['marque']) ?></td>
                        <td><?= htmlspecialchars($vehicle['modele']) ?></td>
                        <td><?= htmlspecialchars($vehicle['annee']) ?></td>
                        <td><?= htmlspecialchars($vehicle['client_nom'] ?? 'Non attribué') ?></td>
                        <td>
                            <div class="d-flex gap-2">
                                <form method="POST">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($vehicle['id']) ?>">
                                    <button type="submit" class="btn btn-warning btn-sm">Modifier</button>
                                </form>
                                <form method="POST" onsubmit="return confirm('Confirmez la suppression ?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($vehicle['id']) ?>">
                                    <button type="submit" name="submit_delete" class="btn btn-danger btn-sm">Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php elseif ($action === 'create' || $action === 'update'): ?>
        <div class="card">
            <div class="card-header"><?= $action === 'create' ? "Ajouter un véhicule" : "Modifier le véhicule" ?></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="<?= htmlspecialchars($action) ?>">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($vehicleId) ?>">
                    <div class="mb-3">
                        <label class="form-label">Marque</label>
                        <input type="text" class="form-control" name="marque" value="<?= htmlspecialchars($vehicle['marque'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Modèle</label>
                        <input type="text" class="form-control" name="modele" value="<?= htmlspecialchars($vehicle['modele'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Année</label>
                        <input type="number" class="form-control" name="annee" value="<?= htmlspecialchars($vehicle['annee'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Client</label>
                        <select name="client_id" class="form-select">
                            <option value="">Non attribué</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>" <?= isset($vehicle['client_id']) && $vehicle['client_id'] == $client['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($client['nom'] . ' (' . $client['email'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="submit_<?= $action ?>" class="btn btn-success">Enregistrer</button>
                    <a href="crud.php" class="btn btn-secondary">Annuler</a>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
