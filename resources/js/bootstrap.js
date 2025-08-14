import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js/dist/web/pusher';
import * as bootstrap from 'bootstrap';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.Pusher = Pusher;
window.bootstrap = bootstrap;


window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY || 'app-key',
    wsHost: import.meta.env.VITE_PUSHER_HOST || window.location.hostname,
    wsPort: parseInt(import.meta.env.VITE_PUSHER_PORT) || 6001,
    forceTLS: false,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    cluster: 'mt1',
});

window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('Echo connected to Soketi');
});

// Global table updater with status & installment Paid buuton 
function updateLoanRow(loan) {
    try {
        if (!loan || !loan.id) {
            console.error('Invalid loan data:', loan);
            return;
        }

        const tbody = document.querySelector('#loansTable tbody');
        if (!tbody) {
            console.error('No data found for loan:', loan.id);
            return;
        }

        let tr = document.getElementById('loan-' + loan.id);
        const installments = Array.isArray(loan.installments) ? loan.installments : [];
        const paid = installments.filter(i => i.status === 'paid');
        const totalPaid = paid.reduce((s, i) => s + parseFloat(i.amount || 0), 0);
        const next = installments.find(i => i.status === 'pending');

        if (!tr) {
            tr = document.createElement('tr');
            tr.id = 'loan-' + loan.id;
            tr.dataset.loanId = loan.id;

            tr.innerHTML = `
                <td>${loan.id}</td>
                <td>${parseFloat(loan.amount).toFixed(2)}</td>
                <td class="paid-count">${paid.length} / ${installments.length}</td>
                <td class="paid-sum">${totalPaid.toFixed(2)}</td>
                <td class="loan-status">${loan.status ? loan.status.charAt(0).toUpperCase() + loan.status.slice(1) : 'Unknown'}</td>
                <td class="next-due">${next ? new Date(next.due_date).toLocaleString() : 'N/A'}</td>
                <td>
                    ${next ? `<button class="btn btn-sm btn-success pay-btn" data-installment-id="${next.id}">Pay Next</button>` : ''}
                    <select class="form-select form-select-sm d-inline-block status-select" style="width:auto;">
                        <option value="active" ${loan.status === 'active' ? 'selected' : ''}>Active</option>
                        <option value="completed" ${loan.status === 'completed' ? 'selected' : ''}>Completed</option>
                        <option value="cancelled" ${loan.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                    </select>
                    <button class="btn btn-sm btn-outline-primary edit-update-btn">Edit</button>
                </td>
            `;
            tbody.appendChild(tr);
        } else {
            tr.querySelector('.paid-count').textContent = `${paid.length} / ${installments.length}`;
            tr.querySelector('.paid-sum').textContent = totalPaid.toFixed(2);
            tr.querySelector('.loan-status').textContent = loan.status.charAt(0).toUpperCase() + loan.status.slice(1);
            tr.dataset.status = loan.status;
            tr.querySelector('.next-due').textContent = next ? new Date(next.due_date).toLocaleString() : 'N/A';

            const payBtn = tr.querySelector('.pay-btn');
            if (next) {
                if (payBtn) {
                    payBtn.dataset.installmentId = next.id;
                } else {
                    tr.querySelector('td:last-child').insertAdjacentHTML(
                        'afterbegin',
                        `<button class="btn btn-sm btn-success pay-btn" data-installment-id="${next.id}">Pay Next</button>`
                    );
                }
            } else if (payBtn) {
                payBtn.remove();
            }
        }
        const allPaid = installments.length > 0 && paid.length === installments.length;

        if (['completed', 'cancelled'].includes(loan.status) || allPaid) {
            const payBtn = tr.querySelector('.pay-btn');
            if (payBtn) payBtn.remove();

            const editBtn = tr.querySelector('.edit-update-btn');
            if (editBtn) editBtn.remove();

            const select = tr.querySelector('.status-select');
            if (select) select.disabled = true;
        } else {
            const select = tr.querySelector('.status-select');
            if (select) select.disabled = true; 
        }


        highlightRow(tr);
        console.log(`Loan row updated: #loan-${loan.id}`);
        filterLoans();
    } catch (err) {
        console.error(`Failed to update loan row:`, err);
    }
}

function highlightRow(row) {
    if (!row) return;
    row.classList.add('table-warning');
    setTimeout(() => row.classList.remove('table-warning'), 1500);
}

function filterLoans() {
    const status = document.getElementById('statusFilter').value.toLowerCase();
    const search = document.getElementById('searchLoan').value.toLowerCase();
    document.querySelectorAll('#loansTable tbody tr').forEach(row => {
        const matchesStatus = !status || row.dataset.status === status;
        const matchesSearch = row.innerText.toLowerCase().includes(search);
        row.style.display = (matchesStatus && matchesSearch) ? '' : 'none';
    });
}

window.updateLoanRow = updateLoanRow;

// Echo event listeners
document.addEventListener('DOMContentLoaded', () => {
    if (window.location.pathname === '/dashboard') {
        console.log('Subscribing to loan events...');
        const channel = window.Echo.channel('loans');

        channel.listen('.LoanGenerated', (e) => {
            try {
                console.group('LoanGenerated Event');
                console.log(e);
                const loans = e.loans || (e.loan ? [e.loan] : []);
                loans.forEach(l => updateLoanRow(l));
                refreshCharts();
                console.groupEnd();
            } catch (err) {
                console.error('Error processing LoanGenerated:', err);
            }
        });

        channel.listen('.InstallmentPaid', (e) => {
            try {
                console.group('InstallmentPaid Event');
                console.log(e);
                updateLoanRow(e.loan);
                refreshCharts();
                console.groupEnd();
            } catch (err) {
                console.error('Error processing InstallmentPaid:', err);
            }
        });

        channel.listen('.LoanCompleted', (e) => {
            try {
                console.group('LoanCompleted Event');
                console.log(e);
                updateLoanRow(e.loan);
                refreshCharts();
                console.groupEnd();
            } catch (err) {
                console.error('Error processing LoanCompleted:', err);
            }
        });

        channel.listen('.LoanStatusUpdated', (e) => {
            try {
                console.group('LoanStatusUpdated Event');
                console.log(e);
                updateLoanRow(e.loan);
                refreshCharts();
                console.groupEnd();
            } catch (err) {
                console.error('Error processing LoanStatusUpdated:', err);
            }
        });
    }
});

// Error Toast Helper
function showErrorToast(message) {
    try {
        const toastBody = document.getElementById('errorToastMsg');
        if (!toastBody) {
            console.warn('Error toast element not found in DOM.');
            return;
        }

        toastBody.textContent = message || 'An unexpected error occurred';
        const toastElement = document.getElementById('errorToast');

        if (toastElement) {
            let toast = bootstrap.Toast.getOrCreateInstance(toastElement);
            toast.show();
        }
    } catch (err) {
        console.error('Failed to display error toast:', err);
        alert(message);
    }
}

window.showErrorToast = showErrorToast;
