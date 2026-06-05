// grafice globale cu chart.js

document.addEventListener('DOMContentLoaded', () => {
    // incarcam chart.js prin cdn daca nu e prezent
    if (typeof Chart === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
        script.onload = initProjectCharts;
        document.head.appendChild(script);
    } else {
        initProjectCharts();
    }
});

async function initProjectCharts() {
    try {
        const response = await fetch('get_project_stats.php');
        const data = await response.json();

        renderActorsChart(data.top_actors);
        renderVictoryChart(data.win_loss);
    } catch (error) {
        console.error('eroare initializare grafice:', error);
    }
}

function renderActorsChart(actorsData) {
    const ctx = document.getElementById('actorsChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: actorsData.map(a => a.nominee),
            datasets: [{
                label: 'numar nominalizari',
                data: actorsData.map(a => a.count),
                backgroundColor: 'rgba(197, 160, 89, 0.7)',
                borderColor: 'rgba(197, 160, 89, 1)',
                borderWidth: 1,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });
}

function renderVictoryChart(winLossData) {
    const ctx = document.getElementById('victoryChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: winLossData.map(d => d.label),
            datasets: [{
                data: winLossData.map(d => d.count),
                backgroundColor: ['#c5a059', '#e6b8af'],
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}
