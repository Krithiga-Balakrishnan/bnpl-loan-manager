import { state } from '../state';
import { payInstallment, updateLoanStatus } from '../api';
import { showError } from '../ui/toast';

const loansTable = () => document.getElementById('loansTable');
const loansTbody = () => document.querySelector('#loansTable tbody');

export function initLoansDataTable() {
  const table = loansTable();
  const currentPageSize = state.dataTable?.perPage || 5;
  state.dataTable = new simpleDatatables.DataTable(table, {
    searchable: false,
    perPage: currentPageSize,
    pagination: true,
    labels: {
      perPage: "rows per page",
      noRows: "No loans found",
      info: "Showing {start} to {end} of {rows} loans"
    }
  });
}

export function renderLoanTable(loans) {
  const body = loansTbody();
  body.innerHTML = '';

  if (!loans.length) {
    body.innerHTML = `<tr><td colspan="7" class="text-center text-muted">No loans found.</td></tr>`;
    return;
  }

  loans.forEach(loan => {
    const paid = loan.paid_count ?? 0;
    const total = loan.total_installments ?? 0;
    const paidSum = loan.paid_sum ?? 0;
    const nextDue = loan.next_due ?? 'N/A';

    body.insertAdjacentHTML('beforeend', `
      <tr data-loan-id="${loan.id}" id="loan-${loan.id}" data-status="${loan.status}">
        <td>${loan.id}</td>
        <td>${Number(loan.amount).toFixed(2)}</td>
        <td class="paid-count">${paid} / ${total}</td>
        <td class="paid-sum">${Number(paidSum).toFixed(2)}</td>
        <td class="loan-status">${loan.status.charAt(0).toUpperCase() + loan.status.slice(1)}</td>
        <td class="next-due">${nextDue}</td>
        <td>
          <button class="btn btn-sm btn-success pay-btn" data-installment-id="${loan.next_installment_id || ''}"  ${loan.status !== 'active' ? 'style="display:none;"' : ''}>Pay Next</button>
          <select class="form-select form-select-sm d-inline-block status-select" style="width:auto;" disabled>
            <option value="active" ${loan.status === 'active' ? 'selected' : ''}>Active</option>
            <option value="completed" ${loan.status === 'completed' ? 'selected' : ''}>Completed</option>
            <option value="cancelled" ${loan.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
          </select>
          <button class="btn btn-sm btn-outline-primary edit-update-btn" ${['completed','cancelled'].includes(loan.status) ? 'style="display:none;"' : ''}>Edit</button>
        </td>
      </tr>
    `);
  });
  const table = loansTable();
const currentPageSize = state.dataTable?.options?.perPage || 10;

if (state.dataTable) {
    state.dataTable.destroy();
  }

  state.dataTable = new simpleDatatables.DataTable(table, {
    searchable: false,
    perPage: currentPageSize,
    pagination: true,
    labels: {
      perPage: "rows per page",
      noRows: "No loans found",
      info: "Showing {start} to {end} of {rows} loans"
    }
  });

}

export function updateLoanRow(loan) {
  const idx = state.allLoans.findIndex(l => l.id === loan.id);
const normalized = {
  id: loan.id,
  loan_id: loan.loan_id ?? loan.id,
  customer_name: loan.customer_name ?? '',
  status: loan.status,
  amount: loan.amount,
  paid_count: loan.paid_count ?? (loan.installments_paid ?? 0),
  total_installments: loan.total_installments ?? (loan.installments_total ?? 0),
  paid_sum: loan.paid_sum ?? (loan.total_paid ?? 0),
  next_due: loan.next_due ?? 'N/A',
  next_installment_id: loan.next_installment_id ?? null
};

  if (idx >= 0) state.allLoans[idx] = normalized;
  else state.allLoans.push(normalized);

  // update DOM row if present
  const tr = document.getElementById(`loan-${loan.id}`);
  if (tr) {
    tr.dataset.status = normalized.status;
    tr.querySelector('.paid-count').textContent = `${normalized.paid_count} / ${normalized.total_installments}`;
    tr.querySelector('.paid-sum').textContent = Number(normalized.paid_sum).toFixed(2);
    tr.querySelector('.loan-status').textContent = normalized.status.charAt(0).toUpperCase() + normalized.status.slice(1);
    tr.querySelector('.next-due').textContent = normalized.next_due;

    // toggle actions
    const payBtn = tr.querySelector('.pay-btn');
    const editBtn = tr.querySelector('.edit-update-btn');
    const disabled = ['completed', 'cancelled'].includes(normalized.status);
    if (payBtn) payBtn.style.display = normalized.status === 'active' ? '' : 'none';
    if (editBtn) editBtn.style.display = disabled ? 'none' : '';
    const select = tr.querySelector('.status-select');
    if (select) {
      select.value = normalized.status;
      select.disabled = true;
    }
  }
}


export function attachLoansTableEvents(onChartsRefresh, onFilterRefresh) {
const container = document.getElementById('loansTableContainer');
if (!container) return;
    container.addEventListener('click', async (e) => {
    const tr = e.target.closest('tr');
    if (!tr) return;
    const loanId = tr.dataset.loanId;

    // Pay Next button
    if (e.target.classList.contains('pay-btn')) {
      const installmentId = e.target.dataset.installmentId;
      if (!installmentId) {
    showError('No installment to pay');
    return;
  }

     try {
    const res = await payInstallment(installmentId);
    if (res?.loan) {
      updateLoanRow(res.loan);
      onChartsRefresh?.();
      onFilterRefresh?.();
    }
  } catch (err) {
    showError(err?.response?.data?.message || 'Error paying installment');
  }
    }

    // Edit / Update button
    if (e.target.classList.contains('edit-update-btn')) {
      const btn = e.target;
      const select = tr.querySelector('.status-select');

      if (btn.textContent === 'Edit') {
        select.disabled = false;
        select.focus();
        btn.textContent = 'Update';

        document.querySelectorAll('.edit-update-btn').forEach(b => {
          if (b !== btn) b.disabled = true;
        });
      } else {
        try {
          const res = await updateLoanStatus(loanId, select.value);
          if (res?.loan) {
            updateLoanRow(res.loan);
            select.disabled = true;
            btn.textContent = 'Edit';

            document.querySelectorAll('.edit-update-btn').forEach(b => b.disabled = false);

            onChartsRefresh?.();
            onFilterRefresh?.();
          }
        } catch {
          showError('Error updating status');
        }
      }
    }
  });
}

export function filterLoans() {
  const statusFilter = document.getElementById('statusFilter')?.value.toLowerCase() || '';
  const searchTerm = document.getElementById('searchLoan')?.value.toLowerCase() || '';

  state.filteredLoans = state.allLoans.filter(loan => {
    const matchesStatus = !statusFilter || loan.status?.toLowerCase() === statusFilter;
    const matchesSearch =
      String(loan.id).includes(searchTerm) ||
      String(loan.amount).includes(searchTerm) ||
      loan.customer_name?.toLowerCase().includes(searchTerm) ||
      String(loan.loan_id).includes(searchTerm);

    return matchesStatus && matchesSearch;
  });

  renderLoanTable(state.filteredLoans);
  attachLoansTableEvents(); 
}
document.getElementById('clearFilters')?.addEventListener('click', () => {
  document.getElementById('statusFilter').value = '';
  document.getElementById('searchLoan').value = '';
  filterLoans();
});

