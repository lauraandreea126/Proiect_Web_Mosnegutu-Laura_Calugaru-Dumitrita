/**
 * News Aggregator UI for Dumitrița's tasks (integrated by Laura)
 */

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-actor'); // Folosim același input pentru a filtra știri
    const newsContainer = document.querySelector('#news-feed .placeholder-content') || document.getElementById('news-results');

    if (!searchInput) return;

    // Redenumim containerul pentru claritate dacă există
    if (newsContainer && newsContainer.classList.contains('placeholder-content')) {
        newsContainer.id = 'news-results';
        newsContainer.classList.remove('placeholder-content');
        newsContainer.innerHTML = '<p>Introdu un nume pentru a vedea știri relevante.</p>';
    }

    let timeout = null;
    searchInput.addEventListener('input', (e) => {
        clearTimeout(timeout);
        const query = e.target.value.trim();

        if (query.length < 2) {
            document.getElementById('news-results').innerHTML = '<p>Introdu un nume pentru a vedea știri relevante.</p>';
            return;
        }

        timeout = setTimeout(() => {
            fetchNews(query);
        }, 500);
    });
});

async function fetchNews(query) {
    const container = document.getElementById('news-results');
    if (!container) return;

    container.innerHTML = '<p>Se încarcă știri...</p>';

    try {
        const response = await fetch(`fetch_news.php?query=${encodeURIComponent(query)}`);
        const news = await response.json();

        renderNews(news, container);
    } catch (error) {
        console.error('Eroare la preluarea știrilor:', error);
        container.innerHTML = '<p class="error">Nu s-au putut încărca știrile.</p>';
    }
}

function renderNews(news, container) {
    container.innerHTML = '';

    if (news.length === 0) {
        container.innerHTML = '<p>Nu s-au găsit știri pentru această căutare.</p>';
        return;
    }

    const list = document.createElement('ul');
    list.className = 'news-list';

    news.forEach(item => {
        const li = document.createElement('li');
        li.className = 'news-item';

        const link = document.createElement('a');
        link.href = item.link;
        link.target = '_blank';
        link.textContent = item.title;

        const source = document.createElement('span');
        source.className = 'news-source';
        source.textContent = ` (${item.source})`;

        li.appendChild(link);
        li.appendChild(source);
        list.appendChild(li);
    });

    container.appendChild(list);
}
