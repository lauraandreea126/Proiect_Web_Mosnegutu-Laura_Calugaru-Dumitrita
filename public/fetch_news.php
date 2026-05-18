<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($query)) {
    echo json_encode([]);
    exit;
}

try {
    // Preluăm toate sursele de știri din baza de date
    $stmt = $pdo->query("SELECT name, url FROM news_sources");
    $sources = $stmt->fetchAll();
    
    $allNews = [];

    foreach ($sources as $source) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $source['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'AwA News Aggregator/1.0');
        
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $content) {
            // Încercăm să parsam conținutul ca XML (RSS/Atom)
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($content);
            
            if ($xml) {
                // Suport pentru RSS 2.0
                if (isset($xml->channel->item)) {
                    foreach ($xml->channel->item as $item) {
                        $title = (string)$item->title;
                        $description = (string)$item->description;
                        $link = (string)$item->link;

                        if (stripos($title, $query) !== false || stripos($description, $query) !== false) {
                            $allNews[] = [
                                'title' => $title,
                                'link' => $link,
                                'source' => $source['name']
                            ];
                        }
                    }
                } 
                // Suport pentru Atom
                elseif (isset($xml->entry)) {
                    foreach ($xml->entry as $entry) {
                        $title = (string)$entry->title;
                        $summary = (string)$entry->summary;
                        $link = (string)$entry->link['href'];

                        if (stripos($title, $query) !== false || stripos($summary, $query) !== false) {
                            $allNews[] = [
                                'title' => $title,
                                'link' => $link,
                                'source' => $source['name']
                            ];
                        }
                    }
                }
            } else {
                // Dacă nu e XML, facem o căutare brută în text (fallback pentru pagini HTML simple)
                if (stripos($content, $query) !== false) {
                    // Notă: Parsarea HTML brută fără biblioteci e imprecisă, dar conform cerinței "filtrare nativă simplă"
                    // Vom returna doar un indicator că am găsit ceva în această sursă dacă nu e RSS
                    // Dar pentru un agregator de știri, RSS-ul este standardul.
                }
            }
        }
    }

    echo json_encode($allNews);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Eroare la procesarea știrilor.', 'message' => $e->getMessage()]);
}
?>
