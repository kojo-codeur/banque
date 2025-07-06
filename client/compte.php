<?php
// Récupérer les informations du compte
$stmt = $pdo->prepare("SELECT c.*, co.numeroCompte, co.typeCompte, co.solde ,co.dateCreation
                      FROM client c
                      JOIN compte co ON c.idClient = co.idClient
                      WHERE c.idClient = ?");
$stmt->execute([$_SESSION['user']['id']]);
$compte = $stmt->fetch();
?>

<div>
    <div class="card">
        <div class="card-header">
            <h2>Informations personnelles</h2>
        </div>
        <div class="card-body">
            <div class="account-info">
                <div class="info-item">
                    <span class="info-label">Nom complet:</span>
                    <span class="info-value"><?= htmlspecialchars($compte['prenom'] . ' ' . $compte['nom']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?= htmlspecialchars($compte['email']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Téléphone:</span>
                    <span class="info-value"><?= htmlspecialchars($compte['telephone']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Numéro de compte:</span>
                    <span class="info-value"><?= htmlspecialchars($compte['numeroCompte']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Type de compte:</span>
                    <span class="info-value"><?= htmlspecialchars($compte['typeCompte']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Date d'ouverture:</span>
                    <span class="info-value"><?= date('d/m/Y', strtotime($compte['dateCreation'])) ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2>Documents</h2>
        </div>
        <div class="card-body">
            <div class="documents-list">
                <div class="document-item">
                    <i class='bx bx-file'></i>
                    <span>Relevé d'identité bancaire (RIB)</span>
                    <a href="#" class="btn-download">
                        <i class='bx bx-download'></i> Télécharger
                    </a>
                </div>
                <div class="document-item">
                    <i class='bx bx-file'></i>
                    <span>Contrat de compte</span>
                    <a href="#" class="btn-download">
                        <i class='bx bx-download'></i> Télécharger
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .account-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
    }
    
    .info-label {
        font-weight: 500;
        color: var(--gray-color);
        font-size: 14px;
    }
    
    .info-value {
        font-size: 16px;
        margin-top: 5px;
    }
    
    .documents-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .document-item {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        background-color: #f9f9f9;
        border-radius: 6px;
    }
    
    .document-item i:first-child {
        font-size: 24px;
        margin-right: 15px;
        color: var(--secondary-color);
    }
    
    .document-item span {
        flex: 1;
    }
    
    .btn-download {
        color: var(--secondary-color);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .btn-download:hover {
        text-decoration: underline;
    }
</style>