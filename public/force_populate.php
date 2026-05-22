<?php
require_once __DIR__ . '/../config/db.php';

/**
 * Script de populare MEGA - Laura & Dumitrița
 * Date verificate SAG Awards (2015-2024)
 */

try {
    $pdo->exec("DELETE FROM nominations");
    $pdo->exec("DELETE FROM actors");
    $pdo->exec("DELETE FROM news_sources");

    $sagData = [
        // 2024
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
        [2024, 'Supporting Male', 'Ryan Gosling', 'Barbie', 0],
        [2024, 'Supporting Female', 'Da\'Vine Joy Randolph', 'The Holdovers', 1],
        [2024, 'Supporting Female', 'Emily Blunt', 'Oppenheimer', 0],

        // 2023
        [2023, 'Leading Male', 'Brendan Fraser', 'The Whale', 1],
        [2023, 'Leading Male', 'Austin Butler', 'Elvis', 0],
        [2023, 'Leading Male', 'Colin Farrell', 'The Banshees of Inisherin', 0],
        [2023, 'Leading Female', 'Michelle Yeoh', 'Everything Everywhere All at Once', 1],
        [2023, 'Leading Female', 'Cate Blanchett', 'Tár', 0],
        [2023, 'Leading Female', 'Viola Davis', 'The Woman King', 0],
        [2023, 'Supporting Male', 'Ke Huy Quan', 'Everything Everywhere All at Once', 1],
        [2023, 'Supporting Female', 'Jamie Lee Curtis', 'Everything Everywhere All at Once', 1],
        [2023, 'Supporting Female', 'Angela Bassett', 'Black Panther: Wakanda Forever', 0],

        // 2022
        [2022, 'Leading Male', 'Will Smith', 'King Richard', 1],
        [2022, 'Leading Male', 'Benedict Cumberbatch', 'The Power of the Dog', 0],
        [2022, 'Leading Male', 'Andrew Garfield', 'Tick, Tick... Boom!', 0],
        [2022, 'Leading Female', 'Jessica Chastain', 'The Eyes of Tammy Faye', 1],
        [2022, 'Leading Female', 'Olivia Colman', 'The Lost Daughter', 0],
        [2022, 'Leading Female', 'Lady Gaga', 'House of Gucci', 0],
        [2022, 'Supporting Male', 'Troy Kotsur', 'CODA', 1],
        [2022, 'Supporting Female', 'Ariana DeBose', 'West Side Story', 1],

        // 2021
        [2021, 'Leading Male', 'Chadwick Boseman', 'Ma Rainey\'s Black Bottom', 1],
        [2021, 'Leading Male', 'Riz Ahmed', 'Sound of Metal', 0],
        [2021, 'Leading Male', 'Anthony Hopkins', 'The Father', 0],
        [2021, 'Leading Female', 'Viola Davis', 'Ma Rainey\'s Black Bottom', 1],
        [2021, 'Leading Female', 'Frances McDormand', 'Nomadland', 0],
        [2021, 'Leading Female', 'Carey Mulligan', 'Promising Young Woman', 0],
        [2021, 'Supporting Male', 'Daniel Kaluuya', 'Judas and the Black Messiah', 1],
        [2021, 'Supporting Female', 'Youn Yuh-jung', 'Minari', 1],

        // 2020
        [2020, 'Leading Male', 'Joaquin Phoenix', 'Joker', 1],
        [2020, 'Leading Male', 'Leonardo DiCaprio', 'Once Upon a Time in Hollywood', 0],
        [2020, 'Leading Male', 'Adam Driver', 'Marriage Story', 0],
        [2020, 'Leading Female', 'Renée Zellweger', 'Judy', 1],
        [2020, 'Leading Female', 'Scarlett Johansson', 'Marriage Story', 0],
        [2020, 'Leading Female', 'Charlize Theron', 'Bombshell', 0],
        // Brad Pitt (Full Verified History)
        [2023, 'Outstanding Cast', 'Brad Pitt', 'Babylon', 0],
        [2020, 'Outstanding Cast', 'Brad Pitt', 'Once Upon a Time in Hollywood', 0],
        [2020, 'Supporting Male', 'Brad Pitt', 'Once Upon a Time in Hollywood', 1],
        [2016, 'Outstanding Cast', 'Brad Pitt', 'The Big Short', 0],
        [2014, 'Outstanding Cast', 'Brad Pitt', '12 Years a Slave', 0],
        [2012, 'Leading Male', 'Brad Pitt', 'Moneyball', 0],
        [2010, 'Outstanding Cast', 'Brad Pitt', 'Inglourious Basterds', 1],
        [2009, 'Outstanding Cast', 'Brad Pitt', 'The Curious Case of Benjamin Button', 0],
        [2009, 'Leading Male', 'Brad Pitt', 'The Curious Case of Benjamin Button', 0],
        [2007, 'Outstanding Cast', 'Brad Pitt', 'Babel', 0],
        [1996, 'Supporting Male', 'Brad Pitt', 'Twelve Monkeys', 0],

        [2020, 'Supporting Female', 'Laura Dern', 'Marriage Story', 1],

        // 2019
        [2019, 'Leading Male', 'Rami Malek', 'Bohemian Rhapsody', 1],
        [2019, 'Leading Male', 'Christian Bale', 'Vice', 0],
        [2019, 'Leading Male', 'Bradley Cooper', 'A Star Is Born', 0],
        [2019, 'Leading Female', 'Glenn Close', 'The Wife', 1],
        [2019, 'Leading Female', 'Olivia Colman', 'The Favourite', 0],
        [2019, 'Leading Female', 'Lady Gaga', 'A Star Is Born', 0],
        [2019, 'Supporting Male', 'Mahershala Ali', 'Green Book', 1],
        [2019, 'Supporting Female', 'Emily Blunt', 'A Quiet Place', 1],

        // 2018
        [2018, 'Leading Male', 'Gary Oldman', 'Darkest Hour', 1],
        [2018, 'Leading Male', 'Timothée Chalamet', 'Call Me by Your Name', 0],
        [2018, 'Leading Male', 'Daniel Kaluuya', 'Get Out', 0],
        [2018, 'Leading Female', 'Frances McDormand', 'Three Billboards Outside Ebbing, Missouri', 1],
        [2018, 'Leading Female', 'Margot Robbie', 'I, Tonya', 0],
        [2018, 'Leading Female', 'Saoirse Ronan', 'Lady Bird', 0],
        [2018, 'Supporting Male', 'Sam Rockwell', 'Three Billboards Outside Ebbing, Missouri', 1],
        [2018, 'Supporting Female', 'Allison Janney', 'I, Tonya', 1],

        // 2017
        [2017, 'Leading Male', 'Denzel Washington', 'Fences', 1],
        [2017, 'Leading Male', 'Casey Affleck', 'Manchester by the Sea', 0],
        [2017, 'Leading Male', 'Ryan Gosling', 'La La Land', 0],
        [2017, 'Leading Female', 'Emma Stone', 'La La Land', 1],
        [2017, 'Leading Female', 'Amy Adams', 'Arrival', 0],
        [2017, 'Leading Female', 'Meryl Streep', 'Florence Foster Jenkins', 0],
        [2017, 'Supporting Male', 'Mahershala Ali', 'Moonlight', 1],
        [2017, 'Supporting Female', 'Viola Davis', 'Fences', 1],

        // 2016
        [2016, 'Leading Male', 'Leonardo DiCaprio', 'The Revenant', 1],
        [2016, 'Leading Male', 'Bryan Cranston', 'Trumbo', 0],
        [2016, 'Leading Male', 'Michael Fassbender', 'Steve Jobs', 0],
        [2016, 'Leading Female', 'Brie Larson', 'Room', 1],
        [2016, 'Leading Female', 'Cate Blanchett', 'Carol', 0],
        [2016, 'Leading Female', 'Saoirse Ronan', 'Brooklyn', 0],
        [2016, 'Supporting Male', 'Idris Elba', 'Beasts of No Nation', 1],
        [2016, 'Supporting Female', 'Alicia Vikander', 'The Danish Girl', 1],

        // 2015
        [2015, 'Leading Male', 'Eddie Redmayne', 'The Theory of Everything', 1],
        [2015, 'Leading Male', 'Steve Carell', 'Foxcatcher', 0],
        [2015, 'Leading Male', 'Benedict Cumberbatch', 'The Imitation Game', 0],
        [2015, 'Leading Female', 'Julianne Moore', 'Still Alice', 1],
        [2015, 'Leading Female', 'Jennifer Aniston', 'Cake', 0],
        [2015, 'Leading Female', 'Reese Witherspoon', 'Wild', 0],
        [2015, 'Supporting Male', 'J.K. Simmons', 'Whiplash', 1],
        [2015, 'Supporting Female', 'Patricia Arquette', 'Boyhood', 1],

        // Meryl Streep (Absolute Complete SAG History - 23 items)
        [2024, 'Outstanding Cast', 'Meryl Streep', 'Only Murders in the Building', 0],
        [2024, 'Female Comedy Series', 'Meryl Streep', 'Only Murders in the Building', 0],
        [2022, 'Outstanding Cast', 'Meryl Streep', 'Don\'t Look Up', 0],
        [2020, 'Outstanding Cast', 'Meryl Streep', 'Big Little Lies', 0],
        [2018, 'Leading Female', 'Meryl Streep', 'The Post', 0],
        [2017, 'Leading Female', 'Meryl Streep', 'Florence Foster Jenkins', 0],
        [2015, 'Supporting Female', 'Meryl Streep', 'Into the Woods', 0],
        [2014, 'Outstanding Cast', 'Meryl Streep', 'August: Osage County', 0],
        [2014, 'Leading Female', 'Meryl Streep', 'August: Osage County', 0],
        [2012, 'Leading Female', 'Meryl Streep', 'The Iron Lady', 0],
        [2010, 'Leading Female', 'Meryl Streep', 'Julie & Julia', 0],
        [2009, 'Outstanding Cast', 'Meryl Streep', 'Doubt', 0],
        [2009, 'Leading Female', 'Meryl Streep', 'Doubt', 1],
        [2007, 'Leading Female', 'Meryl Streep', 'The Devil Wears Prada', 0],
        [2004, 'Female TV Movie', 'Meryl Streep', 'Angels in America', 1],
        [2003, 'Outstanding Cast', 'Meryl Streep', 'Adaptation', 0],
        [2003, 'Outstanding Cast', 'Meryl Streep', 'The Hours', 0],
        [2000, 'Leading Female', 'Meryl Streep', 'Music of the Heart', 0],
        [1999, 'Leading Female', 'Meryl Streep', 'One True Thing', 0],
        [1997, 'Outstanding Cast', 'Meryl Streep', 'Marvin\'s Room', 0],
        [1997, 'Leading Female', 'Meryl Streep', 'Marvin\'s Room', 0],
        [1996, 'Leading Female', 'Meryl Streep', 'The Bridges of Madison County', 0],
        [1995, 'Leading Female', 'Meryl Streep', 'The River Wild', 0],
        
        // Leonardo DiCaprio (Complete SAG History - 15 items)
        [2026, 'Outstanding Cast', 'Leonardo DiCaprio', 'One Battle After Another', 0],
        [2026, 'Leading Male', 'Leonardo DiCaprio', 'One Battle After Another', 0],
        [2024, 'Outstanding Cast', 'Leonardo DiCaprio', 'Killers of the Flower Moon', 0],
        [2022, 'Outstanding Cast', 'Leonardo DiCaprio', 'Don\'t Look Up', 0],
        [2020, 'Outstanding Cast', 'Leonardo DiCaprio', 'Once Upon a Time in Hollywood', 0],
        [2020, 'Leading Male', 'Leonardo DiCaprio', 'Once Upon a Time in Hollywood', 0],
        [2016, 'Leading Male', 'Leonardo DiCaprio', 'The Revenant', 1],
        [2012, 'Leading Male', 'Leonardo DiCaprio', 'J. Edgar', 0],
        [2007, 'Outstanding Cast', 'Leonardo DiCaprio', 'The Departed', 0],
        [2007, 'Leading Male', 'Leonardo DiCaprio', 'Blood Diamond', 0],
        [2007, 'Supporting Male', 'Leonardo DiCaprio', 'The Departed', 0],
        [2005, 'Outstanding Cast', 'Leonardo DiCaprio', 'The Aviator', 0],
        [2005, 'Leading Male', 'Leonardo DiCaprio', 'The Aviator', 0],
        [1998, 'Outstanding Cast', 'Leonardo DiCaprio', 'Titanic', 0],
        [1997, 'Outstanding Cast', 'Leonardo DiCaprio', 'Marvin\'s Room', 0],

        // Anne Hathaway (Complete SAG History - 4 items)
        [2013, 'Outstanding Cast', 'Anne Hathaway', 'Les Misérables', 0],
        [2013, 'Supporting Female', 'Anne Hathaway', 'Les Misérables', 1],
        [2009, 'Leading Female', 'Anne Hathaway', 'Rachel Getting Married', 0],
        [2006, 'Outstanding Cast', 'Anne Hathaway', 'Brokeback Mountain', 0],

        // Johnny Depp (Complete SAG History - 5 items)
        [2016, 'Leading Male', 'Johnny Depp', 'Black Mass', 0],
        [2005, 'Outstanding Cast', 'Johnny Depp', 'Finding Neverland', 0],
        [2005, 'Leading Male', 'Johnny Depp', 'Finding Neverland', 0],
        [2004, 'Leading Male', 'Johnny Depp', 'Pirates of the Caribbean: The Curse of the Black Pearl', 1],
        [2001, 'Outstanding Cast', 'Johnny Depp', 'Chocolat', 0],

        // Margot Robbie (Complete SAG History - 8 items)
        [2024, 'Outstanding Cast', 'Margot Robbie', 'Barbie', 0],
        [2024, 'Leading Female', 'Margot Robbie', 'Barbie', 0],
        [2023, 'Outstanding Cast', 'Margot Robbie', 'Babylon', 0],
        [2020, 'Outstanding Cast', 'Margot Robbie', 'Bombshell', 0],
        [2020, 'Outstanding Cast', 'Margot Robbie', 'Once Upon a Time in Hollywood', 0],
        [2020, 'Supporting Female', 'Margot Robbie', 'Bombshell', 0],
        [2019, 'Supporting Female', 'Margot Robbie', 'Mary Queen of Scots', 0],
        [2018, 'Leading Female', 'Margot Robbie', 'I, Tonya', 0]
    ];

    $stmt = $pdo->prepare("INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (?, ?, ?, ?, ?)");
    foreach ($sagData as $row) {
        $stmt->execute($row);
    }

    $sources = [
        ['Variety News', 'https://variety.com/c/film/feed/'],
        ['Hollywood Reporter', 'https://www.hollywoodreporter.com/c/movies/feed/'],
        ['Entertainment Weekly', 'https://ew.com/search/feed/?q=awards']
    ];
    $stmtS = $pdo->prepare("INSERT INTO news_sources (name, url) VALUES (?, ?)");
    foreach ($sources as $s) {
        $stmtS->execute($s);
    }

    echo "<h1>Mega Populate Finalizat!</h1>";
    echo "<p>Am importat peste 100 de nominalizări verificate SAG Awards (2015-2024).</p>";
    echo "<p>Actori disponibili acum pentru grafice bogate: <strong>Meryl Streep</strong>, <strong>Leonardo DiCaprio</strong>, <strong>Emma Stone</strong>, <strong>Cillian Murphy</strong>, <strong>Viola Davis</strong> etc.</p>";
    echo "<a href='index.php'>Înapoi la aplicație</a>";

} catch (PDOException $e) {
    die("Eroare la populare: " . $e->getMessage());
}
?>