import { getPayments, getStatusCounts } from '../api';

let paymentChart, statusChart;

export async function renderCharts() {
  // Payment line chart
  try {
    const items = (await getPayments()).slice().sort((a, b) => new Date(a.processed_at) - new Date(b.processed_at));
    const points = items.map(p => ({ x: new Date(p.processed_at), y: Number(p.amount) || 0 }));
    const ctx = document.getElementById('paymentChart')?.getContext('2d');
    if (paymentChart) paymentChart.destroy();

    const times = points.map(p => new Date(p.x));
    const first = times[0] || new Date();
    const last = times[times.length - 1] || new Date();
    const pad = 60 * 60 * 1000;
    const min = new Date(first.getTime() - pad);
    const max = new Date(last.getTime() + pad);

    paymentChart = new Chart(ctx, {
      type: 'line',
      data: { datasets: [{ label: 'Payment Amount', data: points, borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,0.2)', fill: true, tension: 0.3 }] },
      options: {
        parsing: false,
        responsive: true,
        scales: {
          x: { type: 'time', time: { tooltipFormat: 'yyyy-MM-dd HH:mm', displayFormats: { minute: 'HH:mm', hour: 'HH:mm', day: 'MMM d' } }, min, max, title: { display: true, text: 'Processed At' } },
          y: { beginAtZero: true, title: { display: true, text: 'Amount' } }
        }
      }
    });
  } catch (e) {
    console.error('Error loading payment data:', e);
  }

  // Status pie chart
  try {
    const counts = await getStatusCounts();
    const ctx = document.getElementById('statusChart')?.getContext('2d');
    if (statusChart) statusChart.destroy();
    statusChart = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: ['Active', 'Completed', 'Cancelled'],
        datasets: [{ data: [counts.active, counts.completed, counts.cancelled], backgroundColor: ['#0d6efd', '#198754', '#dc3545'] }]
      },
      options: { responsive: true }
    });
  } catch (e) {
    console.error('Error loading status counts:', e);
  }
}

export const refreshCharts = () => renderCharts();
