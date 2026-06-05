<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

ini_set('display_errors', 0);
error_reporting(E_ALL);

$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($query)) {
    echo json_encode(['actor_news' => [], 'general_news' => []]);
    exit;
}

try {
    // luam sursele din baza de date
    $stmt = $pdo->query("SELECT name, url FROM news_sources");
    $dbSources = $stmt->fetchAll();
    
    //sursa Google News pentru cautare specifica
    $googleNewsSource = [
        'name' => 'Google News',
        'url' => 'https://news.google.com/rss/search?q=' . urlencode($query . ' actor') . '&hl=ro&gl=RO&ceid=RO:ro'
    ];

    $actorNews = [];
    $generalNews = [];
    $seenTitles = [];

    // prioritate surse: cele din DB apoi Google News
    $allSources = $dbSources;
    $allSources[] = $googleNewsSource;

    foreach ($allSources as $source) {
        $url = $source['url'];
        if (!preg_match('/^https?:\/\//i', $url)) continue; 

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8); // timp
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($content && $httpCode == 200) {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($content);
            if ($xml) {
                // suport pentru dif formate RSS/Atom
                if (isset($xml->channel->item)) {
                    $items = $xml->channel->item;
                } elseif (isset($xml->item)) {
                    $items = $xml->item;
                } elseif (isset($xml->entry)) {
                    $items = $xml->entry;
                } else {
                    $items = [];
                }

                $foundActorInThisSource = false;
                $firstGeneralFromThisSource = null;

                foreach ($items as $item) {
                    $title = (string)($item->title ?? '');
                    $link = (string)($item->link['href'] ?? $item->link ?? '');
                    
                    if (empty($title) || empty($link)) continue;
                    if (isset($seenTitles[$title])) continue;

                    $newsItem = ['title' => $title, 'link' => $link, 'source' => $source['name']];

                    // daca e despre actor, il punem la actor_news
                    if (stripos($title, $query) !== false) {
                        if (count($actorNews) < 5) {
                            if ($source['name'] !== 'Google News') {
                                array_unshift($actorNews, $newsItem); // Sursele proprii primele
                            } else {
                                $actorNews[] = $newsItem;
                            }
                            $seenTitles[$title] = true;
                            $foundActorInThisSource = true;
                        }
                    } 
                    //retinem prima stire generala din fiecare sursa DB
                    elseif ($source['name'] !== 'Google News' && $firstGeneralFromThisSource === null) {
                        $firstGeneralFromThisSource = $newsItem;
                    }
                }

                // daca sursa e din DB, punem o stire la general (chiar daca am gasit si despre actor)
                // sa punem cate una din fiecare sursa 
                if ($source['name'] !== 'Google News' && $firstGeneralFromThisSource && count($generalNews) < 5) {
                    $generalNews[] = $firstGeneralFromThisSource;
                    $seenTitles[$firstGeneralFromThisSource['title']] = true;
                }
            }
            libxml_clear_errors();
        }
    }

    echo json_encode([
        'actor_news' => array_slice($actorNews, 0, 5),
        'general_news' => array_slice($generalNews, 0, 5)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server error', 'actor_news' => [], 'general_news' => []]);
}
?>