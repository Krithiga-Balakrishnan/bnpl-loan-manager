import './bootstrap';
import { state } from './state';
import { setupTabs } from './ui/tabs';
import { showError } from './ui/toast';
import { initLoansDataTable, attachLoansTableEvents, filterLoans, renderLoanTable } from './tables/loansTable';
import { setupCustomerForm } from './forms/customerForm';
import { setupLoanForm } from './forms/loanForm';
import { renderCharts, refreshCharts } from './charts/charts';

document.addEventListener('DOMContentLoaded', async () => {
  // state.allLoans = Array.from(document.querySelectorAll('#loansTable tbody tr')).map(tr => ({
  //   id: parseInt(tr.dataset.loanId),
  //   status: tr.dataset.status,
  //   amount: parseFloat(tr.querySelector('td:nth-child(2)').textContent.replace(/,/g, '')),
  //   paid_count: parseInt(tr.querySelector('.paid-count').textContent.split('/')[0].trim()),
  //   total_installments: parseInt(tr.querySelector('.paid-count').textContent.split('/')[1].trim()),
  //   paid_sum: parseFloat(tr.querySelector('.paid-sum').textContent.replace(/,/g, '')),
  //   next_due: tr.querySelector('.next-due').textContent
  // }));
  state.allLoans = Array.from(document.querySelectorAll('#loansTable tbody tr')).map(tr => {
  const amountText = tr.querySelector('td:nth-child(2)')?.textContent || '0';
  const paidText = tr.querySelector('.paid-count')?.textContent || '0 / 0';
  const paidSumText = tr.querySelector('.paid-sum')?.textContent || '0';
  const payBtn = tr.querySelector('.pay-btn');

  return {
    id: parseInt(tr.dataset.loanId),
    status: tr.dataset.status,
    amount: parseFloat(amountText.replace(/,/g, '')),
    paid_count: parseInt(paidText.split('/')[0].trim()),
    total_installments: parseInt(paidText.split('/')[1].trim()),
    paid_sum: parseFloat(paidSumText.replace(/,/g, '')),
    next_due: tr.querySelector('.next-due')?.textContent || 'N/A',
    // ✅ seed this so re-renders keep Pay buttons for *all* active rows
    next_installment_id: payBtn ? parseInt(payBtn.dataset.installmentId) : null,
  };
});


  // init table
  initLoansDataTable();
  renderLoanTable(state.allLoans);

  // charts
  await renderCharts();

  // filters
  document.getElementById('statusFilter')?.addEventListener('change', filterLoans);
  document.getElementById('searchLoan')?.addEventListener('input', filterLoans);

  // loans table actions
  attachLoansTableEvents(refreshCharts, filterLoans);

  const tabs = setupTabs();
  const customerForm = setupCustomerForm();

  document.getElementById('viewCustomersBtn')?.addEventListener('click', async () => {
    try {
      await customerForm.openCustomersTab();
      tabs.toggleCustomers();
    } catch (e) {
      console.error(e);
      showError('Failed to load customers');
    }
  });

  setupLoanForm(refreshCharts);
});
