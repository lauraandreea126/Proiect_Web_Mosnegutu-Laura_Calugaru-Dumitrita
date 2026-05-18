<?php
/**
 * ajutor tmdb
 */

define('TMDB_API_KEY', '9259655519f11c5dcc31a50d4ee07103'); 
define('TMDB_BASE_URL', 'https://api.themoviedb.org/3');
define('TMDB_IMAGE_BASE', 'https://image.tmdb.org/t/p/w500');

function fetchActorFromTMDb($name) {
    if (TMDB_API_KEY === 'YOUR_TMDB_API_KEY_HERE') return null;

    // cautare persoana
    $url = TMDB_BASE_URL . '/search/person?api_key=' . TMDB_API_KEY . '&query=' . urlencode($name);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) return null;
    $data = json_decode($response, true);
    if (empty($data['results'])) return null;

    $person = $data['results'][0];
    $personId = $person['id'];

    // detalii biografie
    $detailsUrl = TMDB_BASE_URL . '/person/' . $personId . '?api_key=' . TMDB_API_KEY;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $detailsUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $detailsResponse = curl_exec($ch);
    curl_close($ch);

    $details = json_decode($detailsResponse, true);

    return [
        'tmdb_id' => $personId,
        'bio' => $details['biography'] ?? 'biografie indisponibila',
        'image_url' => isset($person['profile_path']) ? TMDB_IMAGE_BASE . $person['profile_path'] : null
    ];
}

function searchActorsOnTMDb($query) {
    if (TMDB_API_KEY === 'YOUR_TMDB_API_KEY_HERE') return [];
    
    $url = TMDB_BASE_URL . '/search/person?api_key=' . TMDB_API_KEY . '&query=' . urlencode($query);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    return $data['results'] ?? [];
}

function updateLocalActor($pdo, $name, $tmdbData) {
    try {
        $stmt = $pdo->prepare("INSERT OR REPLACE INTO actors (name, tmdb_id, bio, image_url) VALUES (:name, :tmdb_id, :bio, :image_url)");
        $stmt->execute([
            'name' => $name,
            'tmdb_id' => $tmdbData['tmdb_id'],
            'bio' => $tmdbData['bio'],
            'image_url' => $tmdbData['image_url']
        ]);
    } catch (Exception $e) {}
}
?>