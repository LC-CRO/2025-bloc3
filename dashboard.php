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

$result = $conn->query("SELECT COUNT(*) AS total_clients FROM clients");
$row = $result->fetch_assoc();
$totalClients = $row['total_clients'];

$result = $conn->query("SELECT COUNT(*) AS total_vehicules FROM vehicules");
$row = $result->fetch_assoc();
$totalVehicules = $row['total_vehicules'];

$result = $conn->query("SELECT COUNT(*) AS total_rendezvous FROM rendezvous");
$row = $result->fetch_assoc();
$totalRendezvous = $row['total_rendezvous'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Garage Train</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/styles.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="text-center mb-5">Tableau de Bord <span class="text-primary">Garage Train</span></h1>

    <div class="row g-4">
        <!-- Carte des clients -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h2 class="card-title text-secondary">Clients</h2>
                    <p class="display-4 text-primary fw-bold"><?= htmlspecialchars($totalClients) ?></p>
                    <p class="text-muted">Nombre total de clients enregistrés</p>
                </div>
            </div>
        </div>

        <!-- Carte des véhicules -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h2 class="card-title text-secondary">Véhicules</h2>
                    <p class="display-4 text-success fw-bold"><?= htmlspecialchars($totalVehicules) ?></p>
                    <p class="text-muted">Nombre total de véhicules enregistrés</p>
                    <form method="POST" action="crud.php" class="d-grid mt-3">
                        <input type="hidden" name="action" value="list">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <button type="submit" class="btn btn-primary">Gérer les véhicules</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Carte des rendez-vous -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h2 class="card-title text-secondary">Rendez-vous</h2>
                    <p class="display-4 text-danger fw-bold"><?= htmlspecialchars($totalRendezvous) ?></p>
                    <p class="text-muted">Nombre total de rendez-vous pris</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
