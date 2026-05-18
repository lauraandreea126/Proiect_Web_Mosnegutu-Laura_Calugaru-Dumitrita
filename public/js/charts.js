/**
 * grafice svg native
 */

document.addEventListener('DOMContentLoaded', () => {
    fetchStats();
});

async function fetchStats(actorName = null) {
    try {
        let url = 'get_stats.php';
        if (actorName) {
            url += `?actor=${encodeURIComponent(actorName)}`;
        }
        const response = await fetch(url);
        const data = await response.json();
        
        const titleSuffix = actorName ? ` - ${actorName}` : ' (global)';
        renderBarChart(data.byYear, 'bar-chart-container', titleSuffix);
        renderPieChart(data.byCategory, 'pie-chart-container', titleSuffix);
        renderDonutChart(data.winLoss, 'donut-chart-container', titleSuffix);
    } catch (error) {
        console.error('eroare stats:', error);
    }
}

function renderBarChart(data, containerId, titleSuffix = '') {
    const container = document.getElementById(containerId);
    if (!container) return;
    if (!data || data.length === 0) {
        container.innerHTML = `<h3>nominalizari pe ani${titleSuffix}</h3><p>nu exista date</p>`;
        return;
    }

    const width = 600, height = 300, padding = 40;
    const barWidth = Math.min((width - 2 * padding) / data.length, 60);
    const maxVal = Math.max(...data.map(d => parseInt(d.count)), 1);
    
    let svg = `<svg width="${width}" height="${height}" viewBox="0 0 ${width} ${height}" style="background: #fff; border: 1px solid #eee; border-radius: 8px;">`;
    
    // linii de fundal
    for (let i = 0; i <= 4; i++) {
        const y = padding + (i * (height - 2 * padding) / 4);
        svg += `<line x1="${padding}" y1="${y}" x2="${width - padding}" y2="${y}" stroke="#f0f0f0" />`;
    }

    data.forEach((d, i) => {
        const val = parseInt(d.count);
        const h = (val / maxVal) * (height - 2 * padding);
        const x = padding + (i * (width - 2 * padding) / data.length) + ( (width - 2 * padding) / data.length - barWidth ) / 2;
        const y = height - padding - h;
        svg += `<rect x="${x}" y="${y}" width="${barWidth}" height="${h}" fill="#4a90e2" rx="4"></rect>
                <text x="${x + barWidth / 2}" y="${height - padding + 20}" font-size="10" text-anchor="middle" fill="#666">${d.year}</text>
                <text x="${x + barWidth / 2}" y="${y - 5}" font-size="10" text-anchor="middle" font-weight="bold">${val}</text>`;
    });
    svg += `<line x1="${padding}" y1="${height - padding}" x2="${width - padding}" y2="${height - padding}" stroke="#333" stroke-width="2" /></svg>`;
    container.innerHTML = `<h3>nominalizari pe ani${titleSuffix}</h3>` + svg;
}

function renderPieChart(data, containerId, titleSuffix = '') {
    const container = document.getElementById(containerId);
    if (!container) return;
    if (!data || data.length === 0) {
        container.innerHTML = `<h3>categorii${titleSuffix}</h3><p>nu exista date</p>`;
        return;
    }

    const size = 300, radius = 100, centerX = size / 2, centerY = size / 2;
    const total = data.reduce((sum, d) => sum + parseInt(d.count), 0);
    let svg = `<svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">`;
    const colors = ['#ff6384', '#36a2eb', '#cc65fe', '#ffce56', '#4bc0c0', '#9966ff', '#ff9f40'];

    if (data.length === 1) {
        // cerc plin pentru o singura categorie
        svg += `<circle cx="${centerX}" cy="${centerY}" r="${radius}" fill="${colors[0]}"></circle>`;
    } else {
        let currentAngle = 0;
        data.forEach((d, i) => {
            const val = parseInt(d.count);
            const sliceAngle = (val / total) * 2 * Math.PI;
            const x1 = centerX + radius * Math.cos(currentAngle), y1 = centerY + radius * Math.sin(currentAngle);
            currentAngle += sliceAngle;
            const x2 = centerX + radius * Math.cos(currentAngle), y2 = centerY + radius * Math.sin(currentAngle);
            const largeArcFlag = sliceAngle > Math.PI ? 1 : 0;
            const pathData = `M ${centerX} ${centerY} L ${x1} ${y1} A ${radius} ${radius} 0 ${largeArcFlag} 1 ${x2} ${y2} Z`;
            svg += `<path d="${pathData}" fill="${colors[i % colors.length]}"></path>`;
        });
    }
    svg += '</svg>';
    
    // legenda
    let legend = '<div style="display: grid; gap: 5px; margin-top: 15px; font-size: 0.8rem;">';
    data.forEach((d, i) => {
        legend += `<div style="display:flex; align-items:center;"><span style="width:12px; height:12px; background:${colors[i % colors.length]}; display:inline-block; margin-right:5px;"></span> ${d.category} (${d.count})</div>`;
    });
    legend += '</div>';

    container.innerHTML = `<h3>categorii${titleSuffix}</h3>` + svg + legend;
}

function renderDonutChart(data, containerId, titleSuffix = '') {
    const container = document.getElementById(containerId);
    if (!container) return;

    const size = 300, radius = 100, innerRadius = 65, centerX = size / 2, centerY = size / 2;
    const winners = parseInt(data.winners || 0);
    const nominees = parseInt(data.nominees || 0);
    const total = winners + nominees;
    
    if (total === 0) {
        container.innerHTML = `<h3>rata castig${titleSuffix}</h3><p>nu exista date</p>`;
        return;
    }

    const chartData = [
        { label: 'castigatori', count: winners, color: '#4bc0c0' },
        { label: 'nominalizati', count: nominees, color: '#ff6384' }
    ];

    let svg = `<svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">`;
    const activeData = chartData.filter(d => d.count > 0);

    if (activeData.length === 1) {
        // inel complet
        const d = activeData[0];
        const pathData = `M ${centerX} ${centerY - radius} A ${radius} ${radius} 0 1 1 ${centerX} ${centerY + radius} A ${radius} ${radius} 0 1 1 ${centerX} ${centerY - radius} Z 
                          M ${centerX} ${centerY - innerRadius} A ${innerRadius} ${innerRadius} 0 1 0 ${centerX} ${centerY + innerRadius} A ${innerRadius} ${innerRadius} 0 1 0 ${centerX} ${centerY - innerRadius} Z`;
        svg += `<path d="${pathData}" fill="${d.color}" fill-rule="evenodd"></path>`;
    } else {
        let currentAngle = -Math.PI / 2; 
        chartData.forEach(d => {
            const sliceAngle = (d.count / total) * 2 * Math.PI;
            if (sliceAngle === 0) return;
            const x1_out = centerX + radius * Math.cos(currentAngle), y1_out = centerY + radius * Math.sin(currentAngle);
            const x1_in = centerX + innerRadius * Math.cos(currentAngle), y1_in = centerY + innerRadius * Math.sin(currentAngle);
            currentAngle += sliceAngle;
            const x2_out = centerX + radius * Math.cos(currentAngle), y2_out = centerY + radius * Math.sin(currentAngle);
            const x2_in = centerX + innerRadius * Math.cos(currentAngle), y2_in = centerY + innerRadius * Math.sin(currentAngle);
            const largeArcFlag = sliceAngle > Math.PI ? 1 : 0;
            const pathData = `M ${x1_out} ${y1_out} A ${radius} ${radius} 0 ${largeArcFlag} 1 ${x2_out} ${y2_out} L ${x2_in} ${y2_in} A ${innerRadius} ${innerRadius} 0 ${largeArcFlag} 0 ${x1_in} ${y1_in} Z`;
            svg += `<path d="${pathData}" fill="${d.color}"></path>`;
        });
    }
    svg += `<text x="${centerX}" y="${centerY + 5}" font-size="14" text-anchor="middle" font-weight="bold">total: ${total}</text></svg>`;
    
    let legend = '<div style="display: flex; justify-content: center; gap: 20px; margin-top: 15px;">';
    chartData.forEach(d => {
        legend += `<div style="display:flex; align-items:center;"><span style="width:15px; height:15px; background:${d.color}; display:inline-block; margin-right:8px;"></span> ${d.label}: ${d.count}</div>`;
    });
    legend += '</div>';

    container.innerHTML = `<h3>rata castig${titleSuffix}</h3>` + svg + legend;
}