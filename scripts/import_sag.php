<?php
$dbPath = __DIR__ . '/../data/awa.db';

try {
    $db = new PDO("sqlite:$dbPath");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Datele SAG pentru ultimii 5 ani (2020-2024)
    // Format: [year, category, nominee, production, is_winner]
    $sagData = [
        // 2024 (30th SAG Awards)
        [2024, 'Leading Male', 'Cillian Murphy', 'Oppenheimer', 1],
        [2024, 'Leading Male', 'Bradley Cooper', 'Maestro', 0],
        [2024, 'Leading Male', 'Colman Domingo', 'Rustin', 0],
        [2024, 'Leading Male', 'Paul Giamatti', 'The Holdovers', 0],
        [2024, 'Leading Male', 'Jeffrey Wright', 'American Fiction', 0],
        
        [2024, 'Leading Female', 'Lily Gladstone', 'Killers of the Flower Moon', 1],
        [2024, 'Leading Female', 'Annette Bening', 'Nyad', 0],
        [2024, 'Leading Female', 'Carey Mulligan', 'Maestro', 0],
        [2024, 'Leading Female', 'Margot Robbie', 'Barbie', 0],
        [2024, 'Leading Female', 'Emma Stone', 'Poor Things', 0],

        [2024, 'Supporting Male', 'Robert Downey Jr.', 'Oppenheimer', 1],
        [2024, 'Supporting Male', 'Sterling K. Brown', 'American Fiction', 0],
        [2024, 'Supporting Male', 'Willem Dafoe', 'Poor Things', 0],
        [2024, 'Supporting Male', 'Robert De Niro', 'Killers of the Flower Moon', 0],
        [2024, 'Supporting Male', 'Ryan Gosling', 'Barbie', 0],

        [2024, 'Supporting Female', 'Da\'Vine Joy Randolph', 'The Holdovers', 1],
        [2024, 'Supporting Female', 'Emily Blunt', 'Oppenheimer', 0],
        [2024, 'Supporting Female', 'Danielle Brooks', 'The Color Purple', 0],
        [2024, 'Supporting Female', 'Penélope Cruz', 'Ferrari', 0],
        [2024, 'Supporting Female', 'Jodie Foster', 'Nyad', 0],

        // 2023 (29th SAG Awards)
        [2023, 'Leading Male', 'Brendan Fraser', 'The Whale', 1],
        [2023, 'Leading Male', 'Austin Butler', 'Elvis', 0],
        [2023, 'Leading Male', 'Colin Farrell', 'The Banshees of Inisherin', 0],
        [2023, 'Leading Male', 'Bill Nighy', 'Living', 0],
        [2023, 'Leading Male', 'Adam Sandler', 'Hustle', 0],

        [2023, 'Leading Female', 'Michelle Yeoh', 'Everything Everywhere All at Once', 1],
        [2023, 'Leading Female', 'Cate Blanchett', 'Tár', 0],
        [2023, 'Leading Female', 'Viola Davis', 'The Woman King', 0],
        [2023, 'Leading Female', 'Ana de Armas', 'Blonde', 0],
        [2023, 'Leading Female', 'Danielle Deadwyler', 'Till', 0],

        [2023, 'Supporting Male', 'Ke Huy Quan', 'Everything Everywhere All at Once', 1],
        [2023, 'Supporting Male', 'Paul Dano', 'The Fabelmans', 0],
        [2023, 'Supporting Male', 'Brendan Gleeson', 'The Banshees of Inisherin', 0],
        [2023, 'Supporting Male', 'Barry Keoghan', 'The Banshees of Inisherin', 0],
        [2023, 'Supporting Male', 'Eddie Redmayne', 'The Good Nurse', 0],

        [2023, 'Supporting Female', 'Jamie Lee Curtis', 'Everything Everywhere All at Once', 1],
        [2023, 'Supporting Female', 'Angela Bassett', 'Black Panther: Wakanda Forever', 0],
        [2023, 'Supporting Female', 'Hong Chau', 'The Whale', 0],
        [2023, 'Supporting Female', 'Kerry Condon', 'The Banshees of Inisherin', 0],
        [2023, 'Supporting Female', 'Stephanie Hsu', 'Everything Everywhere All at Once', 0],

        // 2022 (28th SAG Awards)
        [2022, 'Leading Male', 'Will Smith', 'King Richard', 1],
        [2022, 'Leading Male', 'Javier Bardem', 'Being the Ricardos', 0],
        [2022, 'Leading Male', 'Benedict Cumberbatch', 'The Power of the Dog', 0],
        [2022, 'Leading Male', 'Andrew Garfield', 'Tick, Tick... Boom!', 0],
        [2022, 'Leading Male', 'Denzel Washington', 'The Tragedy of Macbeth', 0],

        [2022, 'Leading Female', 'Jessica Chastain', 'The Eyes of Tammy Faye', 1],
        [2022, 'Leading Female', 'Olivia Colman', 'The Lost Daughter', 0],
        [2022, 'Leading Female', 'Lady Gaga', 'House of Gucci', 0],
        [2022, 'Leading Female', 'Jennifer Hudson', 'Respect', 0],
        [2022, 'Leading Female', 'Nicole Kidman', 'Being the Ricardos', 0],

        [2022, 'Supporting Male', 'Troy Kotsur', 'CODA', 1],
        [2022, 'Supporting Male', 'Ben Affleck', 'The Tender Bar', 0],
        [2022, 'Supporting Male', 'Bradley Cooper', 'Licorice Pizza', 0],
        [2022, 'Supporting Male', 'Kodi Smit-McPhee', 'The Power of the Dog', 0],
        [2022, 'Supporting Male', 'Jared Leto', 'House of Gucci', 0],

        [2022, 'Supporting Female', 'Ariana DeBose', 'West Side Story', 1],
        [2022, 'Supporting Female', 'Caitríona Balfe', 'Belfast', 0],
        [2022, 'Supporting Female', 'Cate Blanchett', 'Nightmare Alley', 0],
        [2022, 'Supporting Female', 'Kirsten Dunst', 'The Power of the Dog', 0],
        [2022, 'Supporting Female', 'Ruth Negga', 'Passing', 0],

        // 2021 (27th SAG Awards)
        [2021, 'Leading Male', 'Chadwick Boseman', 'Ma Rainey\'s Black Bottom', 1],
        [2021, 'Leading Male', 'Riz Ahmed', 'Sound of Metal', 0],
        [2021, 'Leading Male', 'Anthony Hopkins', 'The Father', 0],
        [2021, 'Leading Male', 'Gary Oldman', 'Mank', 0],
        [2021, 'Leading Male', 'Steven Yeun', 'Minari', 0],

        [2021, 'Leading Female', 'Viola Davis', 'Ma Rainey\'s Black Bottom', 1],
        [2021, 'Leading Female', 'Amy Adams', 'Hillbilly Elegy', 0],
        [2021, 'Leading Female', 'Vanessa Kirby', 'Pieces of a Woman', 0],
        [2021, 'Leading Female', 'Frances McDormand', 'Nomadland', 0],
        [2021, 'Leading Female', 'Carey Mulligan', 'Promising Young Woman', 0],

        [2021, 'Supporting Male', 'Daniel Kaluuya', 'Judas and the Black Messiah', 1],
        [2021, 'Supporting Male', 'Sacha Baron Cohen', 'The Trial of the Chicago 7', 0],
        [2021, 'Supporting Male', 'Chadwick Boseman', 'Da 5 Bloods', 0],
        [2021, 'Supporting Male', 'Jared Leto', 'The Little Things', 0],
        [2021, 'Supporting Male', 'Leslie Odom Jr.', 'One Night in Miami', 0],

        [2021, 'Supporting Female', 'Youn Yuh-jung', 'Minari', 1],
        [2021, 'Supporting Female', 'Maria Bakalova', 'Borat Subsequent Moviefilm', 0],
        [2021, 'Supporting Female', 'Glenn Close', 'Hillbilly Elegy', 0],
        [2021, 'Supporting Female', 'Olivia Colman', 'The Father', 0],
        [2021, 'Supporting Female', 'Helena Zengel', 'News of the World', 0],

        // 2020 (26th SAG Awards)
        [2020, 'Leading Male', 'Joaquin Phoenix', 'Joker', 1],
        [2020, 'Leading Male', 'Christian Bale', 'Ford v Ferrari', 0],
        [2020, 'Leading Male', 'Leonardo DiCaprio', 'Once Upon a Time in Hollywood', 0],
        [2020, 'Leading Male', 'Adam Driver', 'Marriage Story', 0],
        [2020, 'Leading Male', 'Taron Egerton', 'Rocketman', 0],

        [2020, 'Leading Female', 'Renée Zellweger', 'Judy', 1],
        [2020, 'Leading Female', 'Cynthia Erivo', 'Harriet', 0],
        [2020, 'Leading Female', 'Scarlett Johansson', 'Marriage Story', 0],
        [2020, 'Leading Female', 'Saoirse Ronan', 'Little Women', 0],
        [2020, 'Leading Female', 'Charlize Theron', 'Bombshell', 0],

        [2020, 'Supporting Male', 'Brad Pitt', 'Once Upon a Time in Hollywood', 1],
        [2020, 'Supporting Male', 'Jamie Foxx', 'Just Mercy', 0],
        [2020, 'Supporting Male', 'Tom Hanks', 'A Beautiful Day in the Neighborhood', 0],
        [2020, 'Supporting Male', 'Al Pacino', 'The Irishman', 0],
        [2020, 'Supporting Male', 'Joe Pesci', 'The Irishman', 0],

        [2020, 'Supporting Female', 'Laura Dern', 'Marriage Story', 1],
        [2020, 'Supporting Female', 'Scarlett Johansson', 'Jojo Rabbit', 0],
        [2020, 'Supporting Female', 'Nicole Kidman', 'Bombshell', 0],
        [2020, 'Supporting Female', 'Jennifer Lopez', 'Hustlers', 0],
        [2020, 'Supporting Female', 'Margot Robbie', 'Bombshell', 0],
    ];

    $stmt = $db->prepare("INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (?, ?, ?, ?, ?)");
    $actorStmt = $db->prepare("INSERT OR IGNORE INTO actors (name) VALUES (?)");

    $db->beginTransaction();
    foreach ($sagData as $row) {
        $stmt->execute($row);
        // Also add the actor to the actors table if they are a person nominee
        // For simplicity, we assume nominees here are individuals
        $actorStmt->execute([$row[2]]);
    }
    $db->commit();

    echo "Importul datelor SAG s-a finalizat cu succes! (" . count($sagData) . " înregistrări)\n";

} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "Eroare la importul datelor: " . $e->getMessage() . "\n";
}
?>
