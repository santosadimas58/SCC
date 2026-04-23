import './bootstrap';

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
