/**
 * AwA - News Aggregator UI (Dumitrița's Task)
 * Această componentă se ocupă de preluarea și afișarea știrilor pentru actorul selectat.
 * Implementare securizată împotriva XSS prin utilizarea .textContent și .createElement.
 */

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-actor');
    if (!searchInput) return;

    // Monitorizăm input-ul pentru a oferi știri în timp real (opțional, dar util pentru UX)
    let searchTimeout = null;
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        clearTimeout(searchTimeout);

        if (query.length < 2) return;

        searchTimeout = setTimeout(() => {
            fetchNews(query);
        }, 600);
    });
});

/**
 * Preluarea asincronă a știrilor de la backend.
 * @param {string} query - Numele actorului căutat.
 */
async function fetchNews(query) {
    const container = document.getElementById('news-results');
    if (!container) return;

    // Pregătim containerul (afișăm starea de încărcare)
    container.classList.remove('hidden');
    container.innerHTML = '';
    
    const loadingMsg = document.createElement('p');
    loadingMsg.textContent = 'Se încarcă ultimele știri...';
    container.appendChild(loadingMsg);

    try {
        const response = await fetch(`fetch_news.php?query=${encodeURIComponent(query)}`);
        if (!response.ok) throw new Error('Eroare la comunicarea cu serverul');
        
        const news = await response.json();
        renderNews(news, container);
    } catch (error) {
        console.error('Eroare fetchNews:', error);
        container.innerHTML = '';
        const errorMsg = document.createElement('p');
        errorMsg.className = 'error-msg';
        errorMsg.textContent = 'Momentan nu am putut prelua știrile. Vă rugăm reîncercați.';
        container.appendChild(errorMsg);
    }
}

/**
 * Randarea securizată a știrilor în DOM.
 * @param {Array} news - Lista de obiecte știre (title, link, source).
 * @param {HTMLElement} container - Elementul unde se face randarea.
 */
function renderNews(news, container) {
    container.innerHTML = '';

    if (!news || news.length === 0) {
        const emptyMsg = document.createElement('p');
        emptyMsg.textContent = 'Nu am găsit știri recente pentru această căutare.';
        container.appendChild(emptyMsg);
        return;
    }

    const newsList = document.createElement('ul');
    newsList.className = 'news-list';

    news.forEach(item => {
        const li = document.createElement('li');
        li.className = 'news-item';

        const newsLink = document.createElement('a');
        newsLink.href = item.link;
        newsLink.target = '_blank';
        newsLink.rel = 'noopener noreferrer';
        // Securitate: folosim textContent pentru a preveni interpretarea HTML-ului din titlu
        newsLink.textContent = item.title;

        const sourceSpan = document.createElement('span');
        sourceSpan.className = 'news-source';
        // Securitate: textContent protejează împotriva XSS din sursele de date
        sourceSpan.textContent = ` (${item.source})`;

        li.appendChild(newsLink);
        li.appendChild(sourceSpan);
        newsList.appendChild(li);
    });

    container.appendChild(newsList);
}
