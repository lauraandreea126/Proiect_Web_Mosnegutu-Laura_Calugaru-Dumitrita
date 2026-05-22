<?php
require_once __DIR__ . '/../config/db.php';

/**
 * Script de populare final - Laura & Dumitrița
 * Rulează acest script pentru a avea date complete SAG și surse de știri configurabile.
 */

try {
    // 1. Curățăm datele vechi
    $pdo->exec("DELETE FROM nominations");
    $pdo->exec("DELETE FROM actors");
    $pdo->exec("DELETE FROM news_sources");

    // 2. Importăm datele SAG reale din ultimii 5 ani (plus extra pt Anne Hathaway)
    $sagData = [
        // 2024
        [2024, 'Leading Male', 'Cillian Murphy', 'Oppenheimer', 1],
        [2024, 'Leading Male', 'Bradley Cooper', 'Maestro', 0],
        [2024, 'Leading Female', 'Lily Gladstone', 'Killers of the Flower Moon', 1],
        [2024, 'Leading Female', 'Emma Stone', 'Poor Things', 0],
        [2024, 'Supporting Male', 'Robert Downey Jr.', 'Oppenheimer', 1],
        [2024, 'Supporting Female', 'Da\'Vine Joy Randolph', 'The Holdovers', 1],

        // 2023
        [2023, 'Leading Male', 'Brendan Fraser', 'The Whale', 1],
        [2023, 'Leading Female', 'Michelle Yeoh', 'Everything Everywhere All at Once', 1],
        [2023, 'Supporting Male', 'Ke Huy Quan', 'Everything Everywhere All at Once', 1],
        [2023, 'Supporting Female', 'Jamie Lee Curtis', 'Everything Everywhere All at Once', 1],

        // 2022
        [2022, 'Leading Male', 'Will Smith', 'King Richard', 1],
        [2022, 'Leading Female', 'Jessica Chastain', 'The Eyes of Tammy Faye', 1],
        [2022, 'Supporting Male', 'Troy Kotsur', 'CODA', 1],
        [2022, 'Supporting Female', 'Ariana DeBose', 'West Side Story', 1],

        // 2021
        [2021, 'Leading Male', 'Chadwick Boseman', 'Ma Rainey\'s Black Bottom', 1],
        [2021, 'Leading Female', 'Viola Davis', 'Ma Rainey\'s Black Bottom', 1],
        [2021, 'Supporting Male', 'Daniel Kaluuya', 'Judas and the Black Messiah', 1],
        [2021, 'Supporting Female', 'Youn Yuh-jung', 'Minari', 1],

        // 2020
        [2020, 'Leading Male', 'Joaquin Phoenix', 'Joker', 1],
        [2020, 'Leading Female', 'Renée Zellweger', 'Judy', 1],
        [2020, 'Supporting Male', 'Brad Pitt', 'Once Upon a Time in Hollywood', 1],
        [2020, 'Supporting Female', 'Laura Dern', 'Marriage Story', 1],
        [2020, 'Leading Male', 'Leonardo DiCaprio', 'Once Upon a Time in Hollywood', 0],

        // Date Extra pentru testare Vizualizări (Meryl Streep)
        [2018, 'Leading Female', 'Meryl Streep', 'The Post', 0],
        [2017, 'Leading Female', 'Meryl Streep', 'Florence Foster Jenkins', 0],
        [2015, 'Supporting Female', 'Meryl Streep', 'Into the Woods', 0],
        [2012, 'Leading Female', 'Meryl Streep', 'The Iron Lady', 1],

        // Date Extra pentru Anne Hathaway (Task-ul Laurei)
        [2020, 'Supporting Female', 'Anne Hathaway', 'Modern Love', 0],
        [2013, 'Supporting Female', 'Anne Hathaway', 'Les Misérables', 1],
        [2009, 'Leading Female', 'Anne Hathaway', 'Rachel Getting Married', 0],
        [2006, 'Outstanding Cast', 'Anne Hathaway', 'Brokeback Mountain', 0]
    ];

    $stmt = $pdo->prepare("INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (?, ?, ?, ?, ?)");
    foreach ($sagData as $row) {
        $stmt->execute($row);
    }

    // 3. Adăugăm Surse de Știri CONFIGURABILE (Task-ul Dumitriței)
    $sources = [
        ['Variety News', 'https://variety.com/c/film/feed/'],
        ['Hollywood Reporter', 'https://www.hollywoodreporter.com/c/movies/feed/'],
        ['SAG-AFTRA News', 'https://www.sagaftra.org/news.xml'],
        ['Entertainment Weekly', 'https://ew.com/search/feed/?q=awards']
    ];
    $stmtS = $pdo->prepare("INSERT INTO news_sources (name, url) VALUES (?, ?)");
    foreach ($sources as $s) {
        $stmtS->execute($s);
    }

    echo "<h1>Baza de date a fost populată conform cerințelor!</h1>";
    echo "<p>Acum poți vedea statistici pentru <strong>Anne Hathaway</strong>, <strong>Meryl Streep</strong>, <strong>Leonardo DiCaprio</strong> etc.</p>";
    echo "<p>Știrile sunt preluate din sursele configurate în admin.</p>";
    echo "<a href='index.php'>Înapoi la aplicație</a>";

} catch (PDOException $e) {
    die("Eroare la populare: " . $e->getMessage());
}
?>