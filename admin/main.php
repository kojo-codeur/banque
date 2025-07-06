<div class="content-gr">
    <div class="card stats-card">
        <div class="card-header">
            <h2>Statistiques globales</h2>
        </div>
        <div class="card-body">
            <div class="stats-grid">
                <div class="stat-item">
                    <i class='bx bx-group'></i>
                    <h3>Clients</h3>
                    <p class="stat-value"><?= $totalClients ?></p>
                    <a href="?page=clients" class="stat-link">Voir tous <i class='bx bx-chevron-right'></i></a>
                </div>
                <div class="stat-item">
                    <i class='bx bx-wallet'></i>
                    <h3>Comptes</h3>
                    <p class="stat-value"><?= $totalComptes ?></p>
                    <a href="?page=comptes" class="stat-link">Voir tous <i class='bx bx-chevron-right'></i></a>
                </div>
                <div class="stat-item">
                    <i class='bx bx-user'></i>
                    <h3>Employés</h3>
                    <p class="stat-value"><?= $totalEmployes ?></p>
                    <a href="?page=employes" class="stat-link">Voir tous <i class='bx bx-chevron-right'></i></a>
                </div>
                <div class="stat-item">
                    <i class='bx bx-transfer'></i>
                    <h3>Opérations (auj.)</h3>
                    <p class="stat-value"><?= $operationsToday ?></p>
                    <a href="?page=rapport" class="stat-link">Voir rapports <i class='bx bx-chevron-right'></i></a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card quick-actions-card">
        <div class="card-header">
            <h2>Actions rapides</h2>
        </div>
        <div class="card-body">
            <div class="quick-actions-grid">
                <a href="?page=nouveauclient" class="quick-action">
                    <div class="action-icon">
                        <i class='bx bx-user-plus'></i>
                    </div>
                    <span>Nouveau client</span>
                </a>
                <a href="?page=nouveaucompte" class="quick-action">
                    <div class="action-icon">
                        <i class='bx bx-wallet'></i>
                    </div>
                    <span>Nouveau compte</span>
                </a>
                <a href="?page=nouveauemploye" class="quick-action">
                    <div class="action-icon">
                        <i class='bx bx-user-plus'></i>
                    </div>
                    <span>Nouvel employé</span>
                </a>
                <a href="?page=parametres" class="quick-action">
                    <div class="action-icon">
                        <i class='bx bx-cog'></i>
                    </div>
                    <span>Paramètres</span>
                </a>
            </div>
        </div>
    </div>
</div>
