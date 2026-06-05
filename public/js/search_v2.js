document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-actor');
    const resultsContainer = document.getElementById('search-results');

    if (!searchInput || !resultsContainer) return;

    // inchide lista la click in afara
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.search-container')) {
            resultsContainer.innerHTML = '';
        }
    });

    let debounceTimeout = null;
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        clearTimeout(debounceTimeout);
        if (query.length < 2) {
            resultsContainer.innerHTML = '';
            resultsContainer.style.display = 'none';
            return;
        }

        debounceTimeout = setTimeout(async () => {
            try {
                const response = await fetch(`search_actors.php?q=${encodeURIComponent(query)}`);
                const data = await response.json();
                renderSuggestions(data);
            } catch (error) {
                console.error('eroare cautare:', error);
            }
        }, 200);
    });

    function renderSuggestions(data) {
        resultsContainer.innerHTML = '';
        if (data.length === 0) {
            resultsContainer.innerHTML = '<div class="search-item">niciun actor gasit</div>';
            resultsContainer.style.display = 'block';
            return;
        }

        resultsContainer.style.display = 'block';
        data.forEach(actor => {
            const div = document.createElement('div');
            div.className = 'search-item';
            // securizare: folosim escapeHTML ptnumele actorului
            const safeNominee = escapeHTML(actor.nominee);
            div.innerHTML = `<strong>${safeNominee}</strong>`;
            div.addEventListener('mousedown', (e) => {
                e.preventDefault();
                selectActor(actor);
            });
            resultsContainer.appendChild(div);
        });
    }

    async function selectActor(actor) {
        resultsContainer.innerHTML = '';
        searchInput.value = actor.nominee;

        // ascundem mesajele de placeholder și afisam structura de date
        document.querySelectorAll('.empty-state-msg').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.chart-controls').forEach(el => el.classList.remove('hidden'));
        
        const newsRes = document.getElementById('news-results');
        if (newsRes) newsRes.classList.remove('hidden');

        const profileBox = document.getElementById('actor-profile');
        const safeNominee = escapeHTML(actor.nominee);
        if (profileBox) {
            profileBox.innerHTML = `<h2>Profil actor: ${safeNominee}</h2><p>se incarca...</p>`;
            profileBox.scrollIntoView({ behavior: 'smooth' });
        }

        try {
            const response = await fetch(`get_actor_details.php?name=${encodeURIComponent(actor.nominee)}`);
            const details = await response.json();

            if (profileBox) {
                // securizare: bio poate conține HTML malițios dacă vine dintr-o sursă externă
                const safeBio = escapeHTML(details.bio || 'biografie indisponibila');
                const safeImageUrl = escapeHTML(details.image_url);

                profileBox.innerHTML = `
                    <h2>Profil actor: ${safeNominee}</h2>
                    <div class="tmdb-details" style="display: flex; gap: 20px; margin-top: 20px;">
                        ${safeImageUrl ? `<img src="${safeImageUrl}" alt="${safeNominee}" class="actor-photo" style="width: 150px; border-radius: 8px;">` : ''}
                        <div><p class="actor-bio" style="font-size: 1.1rem;">${safeBio}</p></div>
                    </div>
                `;
            }
        } catch (error) {
            console.error('eroare detalii:', error);
        }

        // actualizare grafice si stiri
        if (typeof fetchStats === 'function') fetchStats(actor.nominee);
        if (typeof fetchNews === 'function') fetchNews(actor.nominee);
    }
});