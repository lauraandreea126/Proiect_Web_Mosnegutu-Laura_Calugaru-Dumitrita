document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-actor');
    const resultsContainer = document.getElementById('search-results');

    if (!searchInput || !resultsContainer) {
        console.warn('Elementele necesare pentru căutare (search-actor sau search-results) nu au fost găsite în DOM.');
        return;
    }

    searchInput.addEventListener('input', async (e) => {
        const query = e.target.value.trim();

        if (query.length < 2) {
            resultsContainer.innerHTML = '';
            return;
        }

        try {
            const response = await fetch(`search_actors.php?q=${encodeURIComponent(query)}`);
            if (!response.ok) {
                throw new Error('Eroare la preluarea datelor');
            }
            const data = await response.json();
            
            renderResults(data);
        } catch (error) {
            console.error('Eroare fetch:', error);
            resultsContainer.innerHTML = '<p class="error">A apărut o eroare la căutare.</p>';
        }
    });

    function renderResults(nominations) {
        resultsContainer.innerHTML = '';

        if (nominations.length === 0) {
            resultsContainer.innerHTML = '<p>Nu s-au găsit rezultate.</p>';
            return;
        }

        nominations.forEach(nomination => {
            const card = document.createElement('div');
            card.className = 'nomination-card';
            if (nomination.is_winner == 1) {
                card.classList.add('winner');
            }

            const nameEl = document.createElement('h3');
            nameEl.textContent = nomination.nominee;
            if (nomination.is_winner == 1) {
                nameEl.textContent += ' 🏆';
            }

            const categoryEl = document.createElement('p');
            categoryEl.className = 'category';
            categoryEl.textContent = nomination.category;

            const productionEl = document.createElement('p');
            productionEl.className = 'production';
            productionEl.textContent = nomination.production;

            const yearEl = document.createElement('p');
            yearEl.className = 'year';
            yearEl.textContent = `Anul: ${nomination.year}`;

            card.appendChild(nameEl);
            card.appendChild(categoryEl);
            card.appendChild(productionEl);
            card.appendChild(yearEl);

            resultsContainer.appendChild(card);
        });
    }
});
