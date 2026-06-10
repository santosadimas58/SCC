import './bootstrap';
import Chart from 'chart.js/auto';

window.Chart = Chart;

const applySccTheme = () => {
    document.documentElement.setAttribute('data-theme', 'dark');
    document.body.classList.add('scc-shell');
};

applySccTheme();

document.addEventListener('livewire:navigated', applySccTheme);

window.SCCTheme = {
    chart: {
        gridColor: 'rgba(148, 163, 184, 0.14)',
        labelColor: '#cbd5f5',
        titleColor: '#f8fafc',
        battery: '#4ade80',
        panel: '#a78bfa',
        soc: '#60a5fa',
        duty: '#8b5cf6',
    },
};

function initSccRealtimeCharts() {
    const vbatCanvas = document.getElementById('chartVbat');
    const socCanvas = document.getElementById('chartSoc');

    if (!vbatCanvas || !socCanvas) {
        return;
    }

    if (window.sccRealtimeCharts?.vbatChart && vbatCanvas.dataset.ready === 'true') {
        return;
    }

    if (window.sccRealtimeCharts?.interval) {
        clearInterval(window.sccRealtimeCharts.interval);
    }

    window.sccRealtimeCharts?.vbatChart?.destroy();
    window.sccRealtimeCharts?.socChart?.destroy();

    const chartTheme = window.SCCTheme?.chart ?? {};
    const maxPoints = 30;
    const labels = [];
    const vbatSeries = [];
    const socSeries = [];
    const timeFormatter = new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    });

    const baseOptions = (yTitle) => ({
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 180 },
        interaction: { mode: 'index', intersect: false },
        elements: {
            line: { borderJoinStyle: 'round' },
            point: { hitRadius: 8 },
        },
        plugins: {
            legend: {
                position: 'top',
                labels: { color: chartTheme.labelColor || '#cbd5f5' },
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.94)',
                titleColor: chartTheme.titleColor || '#f8fafc',
                bodyColor: chartTheme.labelColor || '#cbd5f5',
                borderColor: 'rgba(96, 165, 250, 0.22)',
                borderWidth: 1,
            },
        },
        scales: {
            x: {
                ticks: { color: chartTheme.labelColor || '#cbd5f5', maxRotation: 0 },
                grid: { color: chartTheme.gridColor || 'rgba(148, 163, 184, 0.14)' },
            },
            y: {
                ticks: { color: chartTheme.labelColor || '#cbd5f5' },
                title: { display: true, text: yTitle, color: chartTheme.labelColor || '#cbd5f5' },
                grid: { color: chartTheme.gridColor || 'rgba(148, 163, 184, 0.14)' },
            },
        },
    });

    const vbatChart = new Chart(vbatCanvas, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Vbat (V)',
                data: vbatSeries,
                tension: 0.35,
                fill: true,
                borderWidth: 2,
                pointRadius: 2.5,
                borderColor: chartTheme.battery || '#36d399',
                backgroundColor: 'rgba(54, 211, 153, 0.12)',
            }],
        },
        options: baseOptions('Volt'),
    });

    const socChart = new Chart(socCanvas, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'SoC (%)',
                data: socSeries,
                tension: 0.35,
                fill: true,
                borderWidth: 2,
                pointRadius: 2.5,
                borderColor: chartTheme.soc || '#60a5fa',
                backgroundColor: 'rgba(96, 165, 250, 0.12)',
            }],
        },
        options: baseOptions('Persen'),
    });

    const pushPoint = (point) => {
        if (!point || !point.created_at) {
            return;
        }

        const label = timeFormatter.format(new Date(point.created_at));
        const lastLabel = labels[labels.length - 1];

        if (label === lastLabel) {
            vbatSeries[vbatSeries.length - 1] = Number(point.vbat);
            socSeries[socSeries.length - 1] = Number(point.soc);
        } else {
            labels.push(label);
            vbatSeries.push(Number(point.vbat));
            socSeries.push(Number(point.soc));
        }

        while (labels.length > maxPoints) {
            labels.shift();
            vbatSeries.shift();
            socSeries.shift();
        }
    };

    const updateCharts = () => {
        const applyRange = (chart, values, padding, floor = null, ceil = null) => {
            const valid = values.filter((value) => Number.isFinite(value));

            if (!valid.length) {
                return;
            }

            let min = Math.min(...valid) - padding;
            let max = Math.max(...valid) + padding;

            if (min === max) {
                min -= padding;
                max += padding;
            }

            if (floor !== null) {
                min = Math.max(floor, min);
            }

            if (ceil !== null) {
                max = Math.min(ceil, max);
            }

            chart.options.scales.y.min = Number(min.toFixed(1));
            chart.options.scales.y.max = Number(max.toFixed(1));
        };

        vbatChart.data.labels = [...labels];
        vbatChart.data.datasets[0].data = [...vbatSeries];
        socChart.data.labels = [...labels];
        socChart.data.datasets[0].data = [...socSeries];
        applyRange(vbatChart, vbatSeries, 0.35);
        applyRange(socChart, socSeries, 4, 0, 100);
        vbatChart.update();
        socChart.update();
    };

    const loadHistory = () => {
        const bootstrapData = document.getElementById('scc-chart-bootstrap')?.textContent;

        if (bootstrapData) {
            try {
                JSON.parse(bootstrapData).slice(-maxPoints).forEach(pushPoint);
                updateCharts();
            } catch {
                // Ignore malformed inline data and fall back to the API request below.
            }
        }

        fetch('/api/scc/history', { credentials: 'same-origin' })
            .then((response) => response.json())
            .then((result) => {
                labels.splice(0);
                vbatSeries.splice(0);
                socSeries.splice(0);

                [...(result?.data ?? [])].reverse().slice(-maxPoints).forEach(pushPoint);
                updateCharts();
            })
            .catch(() => {});
    };

    const loadLatest = () => {
        if (document.hidden) {
            return;
        }

        fetch('/api/scc/latest', { credentials: 'same-origin' })
            .then((response) => response.json())
            .then((result) => {
                pushPoint(result?.data);
                updateCharts();
            })
            .catch(() => {});
    };

    vbatCanvas.dataset.ready = 'true';
    socCanvas.dataset.ready = 'true';

    loadHistory();

    window.sccRealtimeCharts = {
        vbatChart,
        socChart,
        interval: setInterval(loadLatest, 2000),
    };
}

document.addEventListener('DOMContentLoaded', initSccRealtimeCharts);
document.addEventListener('livewire:navigated', initSccRealtimeCharts);

function initSccDutySocChart() {
    const canvas = document.getElementById('chartDutySoc');
    const dataElement = document.getElementById('scc-duty-soc-data');

    if (!canvas || !dataElement || canvas.dataset.ready === 'true') {
        return;
    }

    let points = [];

    try {
        points = JSON.parse(dataElement.textContent || '[]');
    } catch {
        points = [];
    }

    const chartTheme = window.SCCTheme?.chart ?? {};

    new Chart(canvas, {
        type: 'scatter',
        data: {
            datasets: [{
                label: 'Duty vs SoC',
                data: points,
                parsing: false,
                borderColor: chartTheme.duty || '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.75)',
                pointRadius: 4,
                pointHoverRadius: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    labels: { color: chartTheme.labelColor || '#cbd5f5' },
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.94)',
                    titleColor: chartTheme.titleColor || '#f8fafc',
                    bodyColor: chartTheme.labelColor || '#cbd5f5',
                    callbacks: {
                        label: (context) => {
                            const point = context.raw;
                            return `SoC ${point.x}% | Duty ${point.y}% | ${point.phase} ${point.time ?? ''}`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    min: 0,
                    max: 100,
                    title: { display: true, text: 'SoC (%)', color: chartTheme.labelColor || '#cbd5f5' },
                    ticks: { color: chartTheme.labelColor || '#cbd5f5' },
                    grid: { color: chartTheme.gridColor || 'rgba(148, 163, 184, 0.14)' },
                },
                y: {
                    min: 0,
                    max: 100,
                    title: { display: true, text: 'Duty Cycle (%)', color: chartTheme.labelColor || '#cbd5f5' },
                    ticks: { color: chartTheme.labelColor || '#cbd5f5' },
                    grid: { color: chartTheme.gridColor || 'rgba(148, 163, 184, 0.14)' },
                },
            },
        },
    });

    canvas.dataset.ready = 'true';
}

document.addEventListener('DOMContentLoaded', initSccDutySocChart);
document.addEventListener('livewire:navigated', initSccDutySocChart);

function initSccMamdaniCharts() {
    const dataElement = document.getElementById('scc-mamdani-timeline-data');
    const errorCanvas = document.getElementById('chartMamdaniError');
    const comparisonCanvas = document.getElementById('chartMamdaniComparison');

    if (!dataElement || !errorCanvas || !comparisonCanvas || errorCanvas.dataset.ready === 'true') {
        return;
    }

    let points = [];

    try {
        points = JSON.parse(dataElement.textContent || '[]');
    } catch {
        points = [];
    }

    const chartTheme = window.SCCTheme?.chart ?? {};
    const labels = points.map((point) => point.time || '');
    const baseOptions = (yTitle) => ({
        responsive: true,
        maintainAspectRatio: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: {
                labels: { color: chartTheme.labelColor || '#cbd5f5' },
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.94)',
                titleColor: chartTheme.titleColor || '#f8fafc',
                bodyColor: chartTheme.labelColor || '#cbd5f5',
                borderColor: 'rgba(96, 165, 250, 0.22)',
                borderWidth: 1,
            },
        },
        scales: {
            x: {
                ticks: { color: chartTheme.labelColor || '#cbd5f5', maxRotation: 0, autoSkip: true, maxTicksLimit: 8 },
                grid: { color: chartTheme.gridColor || 'rgba(148, 163, 184, 0.14)' },
            },
            y: {
                title: { display: true, text: yTitle, color: chartTheme.labelColor || '#cbd5f5' },
                ticks: { color: chartTheme.labelColor || '#cbd5f5' },
                grid: { color: chartTheme.gridColor || 'rgba(148, 163, 184, 0.14)' },
            },
        },
    });

    new Chart(errorCanvas, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Error (V)',
                    data: points.map((point) => point.error),
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 1.8,
                    borderColor: '#60a5fa',
                    backgroundColor: 'rgba(96, 165, 250, 0.12)',
                },
                {
                    label: 'Delta Error',
                    data: points.map((point) => point.delta_error),
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 1.8,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.12)',
                },
            ],
        },
        options: baseOptions('Volt / perubahan error'),
    });

    new Chart(comparisonCanvas, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Duty Mamdani (%)',
                    data: points.map((point) => point.mamdani_duty),
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 1.8,
                    borderColor: chartTheme.duty || '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.12)',
                },
                {
                    label: 'Duty Threshold (%)',
                    data: points.map((point) => point.threshold_duty),
                    tension: 0,
                    stepped: true,
                    borderWidth: 2,
                    pointRadius: 1.8,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                },
            ],
        },
        options: {
            ...baseOptions('Duty Cycle (%)'),
            scales: {
                ...baseOptions('Duty Cycle (%)').scales,
                y: {
                    ...baseOptions('Duty Cycle (%)').scales.y,
                    min: 0,
                    max: 100,
                },
            },
        },
    });

    errorCanvas.dataset.ready = 'true';
    comparisonCanvas.dataset.ready = 'true';
}

document.addEventListener('DOMContentLoaded', initSccMamdaniCharts);
document.addEventListener('livewire:navigated', initSccMamdaniCharts);
