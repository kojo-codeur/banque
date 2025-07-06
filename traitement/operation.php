<?php
include 'db/db.php';

$stmt = $pdo->query("SELECT idCompte, numeroCompte FROM Compte");
$comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $typeOperation = $_POST['typeOperation'];
    $montant = $_POST['montant'];
    $idCompteDebiteur = $_POST['idCompteDebiteur'];
    $idCompteCrediteur = $_POST['idCompteCrediteur'] ?? null;  

    if ($typeOperation == 'Retrait') {
        
        $stmt = $pdo->prepare("SELECT solde FROM Compte WHERE idCompte = ?");
        $stmt->execute([$idCompteDebiteur]);
        $compteDebiteur = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($compteDebiteur['solde'] >= $montant) {
            
            $pdo->beginTransaction();
            try {
                
                $stmt = $pdo->prepare("UPDATE Compte SET solde = solde - ? WHERE idCompte = ?");//calcule sur retrait solde - montant entre
                $stmt->execute([$montant, $idCompteDebiteur]);

                
                $stmt = $pdo->prepare("INSERT INTO Operation (typeOperation, montant, idCompteDebiteur) VALUES (?, ?, ?)");
                $stmt->execute([$typeOperation, $montant, $idCompteDebiteur]);

                $pdo->commit();
                echo "<p>Retrait effectué avec succès.<p>";
            } catch (Exception $e) {
                $pdo->rollBack();
                echo "<p>Erreur : <p>" . $e->getMessage();
            }

        } else {
            echo "<p>Solde insuffisant pour effectuer ce retrait.<p>";
        }
    }

    
    if ($typeOperation == 'Depot') {
        
        $pdo->beginTransaction();
        try {
            
            $stmt = $pdo->prepare("UPDATE Compte SET solde = solde + ? WHERE idCompte = ?");// update solde + montant deposee
            $stmt->execute([$montant, $idCompteDebiteur]);

            
            $stmt = $pdo->prepare("INSERT INTO Operation (typeOperation, montant, idCompteDebiteur) VALUES (?, ?, ?)");
            $stmt->execute([$typeOperation, $montant, $idCompteDebiteur]);

            $pdo->commit();
            echo "<p>Dépôt effectué avec succès.<p>";
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "<p>Erreur : <p>" . $e->getMessage();
        }
    }

    if ($typeOperation == 'Virement') {
        
        $stmt = $pdo->prepare("SELECT solde FROM Compte WHERE idCompte = ?");//selection des comptes
        $stmt->execute([$idCompteDebiteur]);
        $compteDebiteur = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($compteDebiteur['solde'] >= $montant) {
            
            $pdo->beginTransaction();
            try {
            
                $stmt = $pdo->prepare("UPDATE Compte SET solde = solde - ? WHERE idCompte = ?"); // montant - montant du compte debuteur
                $stmt->execute([$montant, $idCompteDebiteur]);

            
                $stmt = $pdo->prepare("UPDATE Compte SET solde = solde + ? WHERE idCompte = ?"); // montant + montant du compte creduteur
                $stmt->execute([$montant, $idCompteCrediteur]);

            
                $stmt = $pdo->prepare("INSERT INTO Operation (typeOperation, montant, idCompteDebiteur, idCompteCrediteur) VALUES (?, ?, ?, ?)");
                $stmt->execute([$typeOperation, $montant, $idCompteDebiteur, $idCompteCrediteur]);

                $pdo->commit();
                echo "Virement effectué avec succès.";
            } catch (Exception $e) {
                $pdo->rollBack();
                echo "<p>Erreur : <p>" . $e->getMessage();
            }
        } else {
            echo "<p>Solde insuffisant pour effectuer ce virement.<p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Effectuer une opération</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h1>Effectuer une opération</h1>


    <form method="POST">
        <label for="typeOperation">Type d'Opération :</label>
        <select name="typeOperation" required>
            <option value="Retrait">Retrait</option>
            <option value="Depot">Dépôt</option>
            <option value="Virement">Virement</option>
        </select><br><br>

        <label for="montant">Montant :</label>
        <input type="number" name="montant" required step="0.01"><br><br>

        <label for="idCompteDebiteur">Compte débiteur :</label>
        <select name="idCompteDebiteur" required>
            <?php foreach ($comptes as $compte): ?>
                <option value="<?= $compte['idCompte'] ?>"><?= $compte['numeroCompte'] ?></option>
            <?php endforeach; ?>
        </select><br><br>

        
        <div id="compteCrediteurContainer" style="display:none;">
            <label for="idCompteCrediteur">Compte créditeur :</label>
            <select name="idCompteCrediteur">
                <?php foreach ($comptes as $compte): ?>
                    <option value="<?= $compte['idCompte'] ?>"><?= $compte['numeroCompte'] ?></option>
                <?php endforeach; ?>
            </select><br><br>
        </div>

        <button type="submit">Effectuer l'opération</button>
    </form>

    <script>
        
        document.querySelector('select[name="typeOperation"]').addEventListener('change', function() {
            var typeOperation = this.value;
            var compteCrediteurContainer = document.getElementById('compteCrediteurContainer');
            if (typeOperation == 'Virement') {
                compteCrediteurContainer.style.display = 'block';
            } else {
                compteCrediteurContainer.style.display = 'none';
            }
        });
    </script>
</body>
</html>
