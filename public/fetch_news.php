<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

ini_set('display_errors', 0);
error_reporting(E_ALL);

// query-ul de cautare (de obicei numele actorului)
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($query)) {
    echo json_encode(['actor_news' => [], 'general_news' => []]);
    exit;
}

try {
    // ============================================================
    // pregatire surse de stiri
    // ============================================================

    // luam sursele din baza de date
    $stmt = $pdo->query("SELECT name, url FROM news_sources");
    $dbSources = $stmt->fetchAll();

    // sursa google news pentru cautare specifica
    $googleNewsSource = [
        'name' => 'Google News',
        'url'  => 'https://news.google.com/rss/search?q=' . urlencode($query . ' actor') . '&hl=ro&gl=RO&ceid=RO:ro'
    ];

    $actorNews   = [];
    $generalNews = [];
    $seenTitles  = [];

    // prioritate surse: cele din db apoi google news
    $allSources   = $dbSources;
    $allSources[] = $googleNewsSource;

    // ============================================================
    // parcurgem fiecare sursa si descarcam feed-ul rss/atom
    // ============================================================
    foreach ($allSources as $source) {
        $url = $source['url'];
        if (!preg_match('/^https?:\/\//i', $url)) continue;

        // initializare cerere curl catre sursa de stiri
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8); // timp
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $content  = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($content && $httpCode == 200) {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($content);
            if ($xml) {
                // suport pentru diferite formate rss/atom
                if (isset($xml->channel->item)) {
                    $items = $xml->channel->item;
                } elseif (isset($xml->item)) {
                    $items = $xml->item;
                } elseif (isset($xml->entry)) {
                    $items = $xml->entry;
                } else {
                    $items = [];
                }

                $foundActorInThisSource   = false;
                $firstGeneralFromThisSource = null;

                // parcurgem fiecare articol din feed
                foreach ($items as $item) {
                    $title = (string)($item->title ?? '');
                    $link  = (string)($item->link['href'] ?? $item->link ?? '');

                    if (empty($title) || empty($link)) continue;
                    if (isset($seenTitles[$title])) continue;

                    $newsItem = ['title' => $title, 'link' => $link, 'source' => $source['name']];

                    // daca titlul contine numele cautat, e o stire despre actor
                    if (stripos($title, $query) !== false) {
                        if (count($actorNews) < 5) {
                            if ($source['name'] !== 'Google News') {
                                array_unshift($actorNews, $newsItem); // sursele proprii primele
                            } else {
                                $actorNews[] = $newsItem;
                            }
                            $seenTitles[$title] = true;
                            $foundActorInThisSource = true;
                        }
                    }
                    // retinem prima stire generala din fiecare sursa db
                    elseif ($source['name'] !== 'Google News' && $firstGeneralFromThisSource === null) {
                        $firstGeneralFromThisSource = $newsItem;
                    }
                }

                // daca sursa e din db, punem o stire la general (chiar daca am gasit si despre actor)
                // sa punem cate una din fiecare sursa
                if ($source['name'] !== 'Google News' && $firstGeneralFromThisSource && count($generalNews) < 5) {
                    $generalNews[] = $firstGeneralFromThisSource;
                    $seenTitles[$firstGeneralFromThisSource['title']] = true;
                }
            }
            libxml_clear_errors();
        }
    }

    // trimitem maximum 5 stiri din fiecare categorie
    echo json_encode([
        'actor_news'   => array_slice($actorNews, 0, 5),
        'general_news' => array_slice($generalNews, 0, 5)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server error', 'actor_news' => [], 'general_news' => []]);
}
?>