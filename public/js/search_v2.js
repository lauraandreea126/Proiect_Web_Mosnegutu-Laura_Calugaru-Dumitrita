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
            div.innerHTML = `<strong>${actor.nominee}</strong>`;
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

        const profileBox = document.getElementById('actor-profile');
        if (profileBox) {
            profileBox.innerHTML = `<h2>profil actor: ${actor.nominee}</h2><p>se incarca...</p>`;
            profileBox.scrollIntoView({ behavior: 'smooth' });
        }

        try {
            const response = await fetch(`get_actor_details.php?name=${encodeURIComponent(actor.nominee)}`);
            const details = await response.json();

            if (profileBox) {
                profileBox.innerHTML = `
                    <h2>profil actor: ${actor.nominee}</h2>
                    <div class="tmdb-details" style="display: flex; gap: 20px; margin-top: 20px;">
                        ${details.image_url ? `<img src="${details.image_url}" alt="${actor.nominee}" class="actor-photo" style="width: 150px; border-radius: 8px;">` : ''}
                        <div><p class="actor-bio" style="font-size: 1.1rem;">${details.bio || 'biografie indisponibila'}</p></div>
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