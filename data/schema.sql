CREATE TABLE IF NOT EXISTS nominations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    year INTEGER NOT NULL,
    category TEXT NOT NULL,
    nominee TEXT NOT NULL,
    production TEXT NOT NULL,
    is_winner INTEGER DEFAULT 0
);

CREATE TABLE IF NOT EXISTS actors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT UNIQUE NOT NULL,
    tmdb_id INTEGER,
    bio TEXT,
    image_url TEXT
);

CREATE TABLE IF NOT EXISTS news_sources (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    url TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL
);

-- Seed some SAG data
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2024, 'Leading Male', 'Cillian Murphy', 'Oppenheimer', 1);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2024, 'Leading Female', 'Lily Gladstone', 'Killers of the Flower Moon', 1);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2023, 'Leading Male', 'Brendan Fraser', 'The Whale', 1);
INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (2023, 'Leading Female', 'Michelle Yeoh', 'Everything Everywhere All at Once', 1);

INSERT OR IGNORE INTO actors (name) VALUES ('Cillian Murphy');
INSERT OR IGNORE INTO actors (name) VALUES ('Lily Gladstone');
INSERT OR IGNORE INTO actors (name) VALUES ('Brendan Fraser');
INSERT OR IGNORE INTO actors (name) VALUES ('Michelle Yeoh');
