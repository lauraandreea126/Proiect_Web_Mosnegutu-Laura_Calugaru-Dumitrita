<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($query)) {
    echo json_encode([]);
    exit;
}

try {
    // luam sursele din baza de date
    $stmt = $pdo->query("SELECT name, url FROM news_sources");
    $sources = $stmt->fetchAll();
    
    // adaugam o sursa google news pentru siguranta
    $sources[] = [
        'name' => 'Google News',
        'url' => 'https://news.google.com/rss/search?q=' . urlencode($query . ' actor') . '&hl=ro&gl=RO&ceid=RO:ro'
    ];

    $allNews = [];
    $seenTitles = [];

    foreach ($sources as $source) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $source['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $content = curl_exec($ch);
        curl_close($ch);

        if ($content) {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($content);
            
            if ($xml) {
                $items = isset($xml->channel->item) ? $xml->channel->item : (isset($xml->item) ? $xml->item : []);

                foreach ($items as $item) {
                    $title = (string)$item->title;
                    $link = (string)$item->link;

                    if (!isset($seenTitles[$title])) {
                        $allNews[] = [
                            'title' => $title,
                            'link' => $link,
                            'source' => $source['name']
                        ];
                        $seenTitles[$title] = true;
                    }
                    if (count($allNews) >= 15) break 2;
                }
            }
        }
    }

    echo json_encode($allNews);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>