<?php
$dbPath = __DIR__ . '/../data/awa.db';
$csvPath = __DIR__ . '/../screen_actor_guild_awards.csv';

if (!file_exists($csvPath)) {
    die("Eroare: Fisierul CSV nu a fost gasit la $csvPath\n");
}

try {
    $db = new PDO("sqlite:$dbPath");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // curatam tabela de nominalizari inainte de un nou import complet
    $db->exec("DELETE FROM nominations");

    $stmt = $db->prepare("INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (?, ?, ?, ?, ?)");
    $actorStmt = $db->prepare("INSERT OR IGNORE INTO actors (name) VALUES (?)");

    $file = fopen($csvPath, 'r');
    
    // sarim peste header
    $header = fgetcsv($file);
    
    $count = 0;
    $db->beginTransaction();

    while (($row = fgetcsv($file)) !== FALSE) {
        // structura CSV: year,category,full_name,show,won
        if (count($row) < 5) continue;

        $yearRaw = $row[0];
        $category = $row[1];
        $nominee = $row[2];
        $production = $row[3];
        $wonRaw = strtolower(trim($row[4]));

        // extragem anul (primele 4 cifre)
        $year = (int)substr($yearRaw, 0, 4);
        
        // conversie won in integer (0 sau 1)
        $isWinner = ($wonRaw === 'true' || $wonRaw === '1') ? 1 : 0;

        // daca nominee e gol folosim numele productiei (pentru premii de ansamblu)
        $effectiveNominee = !empty($nominee) ? $nominee : $production;

        $stmt->execute([$year, $category, $effectiveNominee, $production, $isWinner]);

        // adaugam actorul in tabela actors daca nu e gol
        if (!empty($nominee)) {
            $actorStmt->execute([$nominee]);
        }

        $count++;
        
        // commit la fiecare 1000 de randuri pentru a nu bloca memoria/tranzactia prea mult
        if ($count % 1000 === 0) {
            $db->commit();
            $db->beginTransaction();
        }
    }

    $db->commit();
    fclose($file);

    echo "Importul din CSV s-a finalizat cu succes! ($count inregistrari adaugate)\n";

} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "Eroare la baza de date: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Eroare generala: " . $e->getMessage() . "\n";
}
?>
