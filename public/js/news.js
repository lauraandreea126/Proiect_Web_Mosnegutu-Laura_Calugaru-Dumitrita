// afisare stiri pentru actor
// protectie xss prin textcontent

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-actor');
    if (!searchInput) return;

    // cautare stiri la input
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

// preluare stiri server
async function fetchNews(query) {
    const container = document.getElementById('news-results');
    if (!container) return;

    // loading
    container.classList.remove('hidden');
    container.innerHTML = '';
    
    const loadingMsg = document.createElement('p');
    loadingMsg.textContent = 'se incarca ultimele stiri...';
    container.appendChild(loadingMsg);

    try {
        const response = await fetch(`fetch_news.php?query=${encodeURIComponent(query)}`);
        
        // vf daca raspunsul este ok si este JSON
        if (!response.ok) throw new Error('eroare server');
        
        const data = await response.json();
        
        // daca avem eroare in JSON
        if (data.error) throw new Error(data.error);

        renderNews(data, container);
    } catch (error) {
        console.error('eroare fetchnews:', error);
        container.innerHTML = '';
        const errorMsg = document.createElement('p');
        errorMsg.className = 'error-msg';
        errorMsg.textContent = 'nu am putut prelua stirile în acest moment.';
        container.appendChild(errorMsg);
    }
}

// randare securizata
function renderNews(data, container) {
    container.innerHTML = '';

    const actorNews = data.actor_news || [];
    const generalNews = data.general_news || [];

    if (actorNews.length === 0 && generalNews.length === 0) {
        const emptyMsg = document.createElement('p');
        emptyMsg.textContent = 'nu am gasit stiri recente.';
        container.appendChild(emptyMsg);
        return;
    }

    // sectiunea stiri specifice pentru actor
    if (actorNews.length > 0) {
        const actorHeading = document.createElement('h3');
        actorHeading.textContent = 'Ultimele noutăți';
        actorHeading.style.marginBottom = '1rem';
        actorHeading.style.fontSize = '1.1rem';
        actorHeading.style.fontFamily = 'var(--font-serif)';
        container.appendChild(actorHeading);

        const list = createNewsList(actorNews);
        container.appendChild(list);
    }

    // sectiunea "Te-ar mai putea interesa"
    if (generalNews.length > 0) {
        const extraHeading = document.createElement('h3');
        extraHeading.textContent = 'Te-ar mai putea interesa și...';
        extraHeading.style.marginTop = '2.5rem';
        extraHeading.style.marginBottom = '1rem';
        extraHeading.style.fontSize = '1.1rem';
        extraHeading.style.fontFamily = 'var(--font-serif)';
        container.appendChild(extraHeading);

        const list = createNewsList(generalNews);
        container.appendChild(list);
    }
}

// functie helper pentru creare lista de link uri
function createNewsList(items) {
    const newsList = document.createElement('ul');
    newsList.className = 'news-list';
    newsList.style.listStyle = 'none';

    items.forEach(item => {
        const li = document.createElement('li');
        li.className = 'news-item';
        li.style.marginBottom = '1rem';
        li.style.paddingBottom = '0.5rem';
        li.style.borderBottom = '1px solid #eee';

        // crearea link ului care duce la site ul de stiri
        const newsLink = document.createElement('a');
        newsLink.href = item.link;
        newsLink.target = '_blank';
        newsLink.rel = 'noopener noreferrer';
        newsLink.textContent = item.title;
        newsLink.style.display = 'block';
        newsLink.style.textDecoration = 'none';
        newsLink.style.color = 'var(--text-main)';
        newsLink.style.fontWeight = '500';
        
        // efect la hover pe link
        newsLink.addEventListener('mouseenter', () => newsLink.style.color = 'var(--accent-gold)');
        newsLink.addEventListener('mouseleave', () => newsLink.style.color = 'var(--text-main)');

        const sourceSpan = document.createElement('span');
        sourceSpan.className = 'news-source';
        sourceSpan.textContent = `Sursă: ${item.source}`;
        sourceSpan.style.fontSize = '0.75rem';
        sourceSpan.style.color = 'var(--accent-gold)';
        sourceSpan.style.textTransform = 'uppercase';

        li.appendChild(newsLink);
        li.appendChild(sourceSpan);
        newsList.appendChild(li);
    });
    return newsList;
}