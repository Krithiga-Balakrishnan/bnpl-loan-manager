import { state } from '../state';
import { payInstallment, updateLoanStatus } from '../api';
import { showError } from '../ui/toast';

const loansTable = () => document.getElementById('loansTable');
const loansTbody = () => document.querySelector('#loansTable tbody');

export function initLoansDataTable() {
  const table = loansTable();
  const currentPageSize = state.dataTable?.perPage || 15;
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
    // derive if not present (same as Blade approach)
    const nextId =
      loan.next_installment_id ??
      loan.installments?.find(i => i.status === 'pending')?.id ??
      null;

    const paid    = loan.paid_count ?? 0;
    const total   = loan.total_installments ?? 0;
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
          ${loan.status === 'active' && nextId
            ? `<button type="button" class="btn btn-sm btn-success pay-btn" data-installment-id="${nextId}">Pay Next</button>`
            : ''
          }
          <select class="form-select form-select-sm d-inline-block status-select" style="width:auto;"
                  ${['completed','cancelled'].includes(loan.status) ? 'disabled' : ''}>
            <option value="active" ${loan.status === 'active' ? 'selected' : ''}>Active</option>
            <option value="completed" ${loan.status === 'completed' ? 'selected' : ''}>Completed</option>
            <option value="cancelled" ${loan.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
          </select>
          <button type="button" class="btn btn-sm btn-outline-primary edit-update-btn"
                  ${['completed','cancelled'].includes(loan.status) ? 'style="display:none;"' : ''}>Edit</button>
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
    next_installment_id:
      loan.next_installment_id ??
      loan.installments?.find(i => i.status === 'pending')?.id ??
      null,
    installments: loan.installments ?? undefined,
  };

  if (idx >= 0) state.allLoans[idx] = { ...state.allLoans[idx], ...normalized };
  else state.allLoans.push(normalized);

  const tr = document.getElementById(`loan-${loan.id}`);
  if (tr) {
    tr.dataset.status = normalized.status;
    tr.querySelector('.paid-count').textContent = `${normalized.paid_count} / ${normalized.total_installments}`;
    tr.querySelector('.paid-sum').textContent = Number(normalized.paid_sum).toFixed(2);
    tr.querySelector('.loan-status').textContent = normalized.status.charAt(0).toUpperCase() + normalized.status.slice(1);
    tr.querySelector('.next-due').textContent = normalized.next_due;

    const payBtn = tr.querySelector('.pay-btn');
    if (normalized.status === 'active' && normalized.next_installment_id) {
      if (payBtn) {
        payBtn.style.display = '';
        payBtn.dataset.installmentId = normalized.next_installment_id;
      } else {
        tr.querySelector('td:last-child')?.insertAdjacentHTML('afterbegin',
          `<button type="button" class="btn btn-sm btn-success pay-btn" data-installment-id="${normalized.next_installment_id}">Pay Next</button>`
        );
      }
    } else if (payBtn) {
      // hide for this row only if not active or no next installment
      payBtn.style.display = 'none';
    }

    const editBtn = tr.querySelector('.edit-update-btn');
    const terminal = ['completed','cancelled'].includes(normalized.status);
    if (editBtn) editBtn.style.display = terminal ? 'none' : '';
    const select = tr.querySelector('.status-select');
    if (select) {
      select.value = normalized.status;
      select.disabled = terminal; // enable for non-terminal, disabled for terminal
    }
  }
}

export function attachLoansTableEvents(onChartsRefresh, onFilterRefresh) {
  const container = document.getElementById('loansTableContainer');
  if (!container) return;
  if (container._payHandlersAttached) return;
  container._payHandlersAttached = true;

  container.addEventListener('click', async (e) => {
    const tr = e.target.closest('tr');
    if (!tr) return;
    const loanId = tr.dataset.loanId;

    // Pay Next
    const payBtn = e.target.closest('.pay-btn');
    if (payBtn) {
      let installmentId = payBtn.dataset.installmentId;

      // fallback from cached state (mirrors Blade)
      if (!installmentId) {
        const loan = state.allLoans.find(l => String(l.id) === String(loanId));
        installmentId = loan?.next_installment_id ?? loan?.installments?.find(i => i.status === 'pending')?.id ?? '';
      }

      if (!installmentId) {
        showError('No installment to pay');
        return;
      }

      payBtn.disabled = true;
      try {
        const res = await payInstallment(installmentId);
        if (res?.loan) {
          updateLoanRow(res.loan);
          onChartsRefresh?.();
          onFilterRefresh?.();
        }
      } catch (err) {
        showError(err?.response?.data?.message || 'Error paying installment');
      } finally {
        payBtn.disabled = false;
      }
      return;
    }

    // Edit / Update (use trim to be robust)
    const editBtn = e.target.closest('.edit-update-btn');
    if (editBtn) {
      const select = tr.querySelector('.status-select');
      if (!select) return;

      if (editBtn.textContent.trim() === 'Edit') {
        select.disabled = false;
        select.focus();
        editBtn.textContent = 'Update';
        document.querySelectorAll('.edit-update-btn').forEach(b => {
          if (b !== editBtn) b.disabled = true;
        });
      } else {
        try {
          const res = await updateLoanStatus(loanId, select.value);
          if (res?.loan) {
            updateLoanRow(res.loan);
            select.disabled = true;
            editBtn.textContent = 'Edit';
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
  const searchTerm   = document.getElementById('searchLoan')?.value.toLowerCase() || '';

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
