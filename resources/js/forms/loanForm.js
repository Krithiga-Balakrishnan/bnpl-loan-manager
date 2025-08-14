import { generateLoans } from '../api';
import { state } from '../state';
import { showError, showSuccess } from '../ui/toast';
import { updateLoanRow, filterLoans } from '../tables/loansTable';

export function setupLoanForm(onChartsRefresh) {
  const form = document.getElementById('loanForm');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    const loanAmount = parseFloat(form.loan_amount.value);
    const numberOfLoans = parseInt(form.number_of_loans.value);
    const installmentsPerLoan = parseInt(form.installments_per_loan.value) || 4;
    const installmentPeriod = parseInt(form.installment_period_minutes.value);

    const invalid = (input, msg) => {
      input.classList.add('is-invalid');
      if (!input.nextElementSibling || !input.nextElementSibling.classList?.contains('invalid-feedback')) {
        const div = document.createElement('div'); div.className = 'invalid-feedback'; div.innerText = msg; input.after(div);
      } else {
        input.nextElementSibling.textContent = msg;
      }
    };

    let valid = true;
    if (isNaN(loanAmount) || loanAmount <= 0) { valid = false; invalid(form.loan_amount, 'Loan amount must be greater than 0'); }
    if (isNaN(numberOfLoans) || numberOfLoans < 1) { valid = false; invalid(form.number_of_loans, 'Number of loans must be at least 1'); }
    if (isNaN(installmentsPerLoan) || installmentsPerLoan < 1) { valid = false; invalid(form.installments_per_loan, 'Installments per loan must be at least 1'); }
    if (isNaN(installmentPeriod) || installmentPeriod < 1) { valid = false; invalid(form.installment_period_minutes, 'Installment period must be at least 1 minute'); }
    if (!valid) return;

    const payload = {
      customer_id: document.getElementById('loanCustomerId').value,
      loan_amount: loanAmount,
      number_of_loans: numberOfLoans,
      installments_per_loan: installmentsPerLoan,
      installment_period_minutes: installmentPeriod
    };

    try {
      const res = await generateLoans(payload);
      showSuccess(res.message || 'Loan generated successfully!');

      form.reset();
      form.classList.remove('was-validated');
      const modal = bootstrap.Modal.getInstance(document.getElementById('loanFormModal'));
      modal?.hide();

      state.allLoans = state.allLoans || [];
      if (res.loan) {
        updateLoanRow(res.loan);
      } else if (res.loans) {
        res.loans.forEach(l => updateLoanRow(l));
      }

      onChartsRefresh();
      filterLoans();
    } catch (err) {
      if (err?.errors) {
        Object.keys(err.errors).forEach(field => {
          const input = form.querySelector(`[name="${field}"]`);
          if (input) invalid(input, err.errors[field][0]);
        });
      } else {
        console.error(err);
        showError('Error generating loan');
      }
    }
  });
}
