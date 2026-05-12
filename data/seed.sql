DELETE FROM nominations;
DELETE FROM actors;

-- 2024
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2024, 'Leading Male', 'Cillian Murphy', 'Oppenheimer', 1);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2024, 'Leading Male', 'Bradley Cooper', 'Maestro', 0);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2024, 'Leading Male', 'Colman Domingo', 'Rustin', 0);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2024, 'Leading Male', 'Paul Giamatti', 'The Holdovers', 0);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2024, 'Leading Male', 'Jeffrey Wright', 'American Fiction', 0);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2024, 'Leading Female', 'Lily Gladstone', 'Killers of the Flower Moon', 1);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2024, 'Leading Female', 'Annette Bening', 'Nyad', 0);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2024, 'Leading Female', 'Carey Mulligan', 'Maestro', 0);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2024, 'Leading Female', 'Margot Robbie', 'Barbie', 0);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2024, 'Leading Female', 'Emma Stone', 'Poor Things', 0);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2024, 'Supporting Male', 'Robert Downey Jr.', 'Oppenheimer', 1);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2024, 'Supporting Female', 'Da''Vine Joy Randolph', 'The Holdovers', 1);

-- 2023
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2023, 'Leading Male', 'Brendan Fraser', 'The Whale', 1);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2023, 'Leading Female', 'Michelle Yeoh', 'Everything Everywhere All at Once', 1);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2023, 'Supporting Male', 'Ke Huy Quan', 'Everything Everywhere All at Once', 1);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2023, 'Supporting Female', 'Jamie Lee Curtis', 'Everything Everywhere All at Once', 1);

-- 2022
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2022, 'Leading Male', 'Will Smith', 'King Richard', 1);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2022, 'Leading Female', 'Jessica Chastain', 'The Eyes of Tammy Faye', 1);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2022, 'Supporting Male', 'Troy Kotsur', 'CODA', 1);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2022, 'Supporting Female', 'Ariana DeBose', 'West Side Story', 1);

-- 2021
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2021, 'Leading Male', 'Chadwick Boseman', 'Ma Rainey''s Black Bottom', 1);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2021, 'Leading Female', 'Viola Davis', 'Ma Rainey''s Black Bottom', 1);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2021, 'Supporting Male', 'Daniel Kaluuya', 'Judas and the Black Messiah', 1);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2021, 'Supporting Female', 'Youn Yuh-jung', 'Minari', 1);

-- 2020
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2020, 'Leading Male', 'Joaquin Phoenix', 'Joker', 1);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2020, 'Leading Female', 'Renée Zellweger', 'Judy', 1);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2020, 'Supporting Male', 'Brad Pitt', 'Once Upon a Time in Hollywood', 1);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2020, 'Supporting Female', 'Laura Dern', 'Marriage Story', 1);

-- Populate actors table from nominations
INSERT OR IGNORE INTO actors (name) SELECT DISTINCT nominee FROM nominations;
