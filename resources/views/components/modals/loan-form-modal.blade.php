<div class="modal fade" id="loanFormModal" tabindex="-1" aria-labelledby="loanFormModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="loanFormModalLabel">Generate Loan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="loanForm" class="needs-validation" novalidate>
            @csrf
            <input type="hidden" id="loanCustomerId" name="customer_id">

            <div id="selectedCustomerInfo" class="alert alert-info d-flex align-items-center gap-2 py-2 px-3 mb-3" role="alert">
                <i class="bi bi-person-circle fs-4 text-primary"></i>
                <div>
                    <strong>Generating loan for:</strong> <span id="customerNameDisplay">[Customer Name]</span>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Loan Amount</label>
                <input type="number" step="0.01" class="form-control" name="loan_amount" required min="0.01">
                <div class="invalid-feedback">Please enter a loan amount greater than 0.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Number of Loans</label>
                <input type="number" class="form-control" name="number_of_loans" required min="1">
                <div class="invalid-feedback">Number of loans must be at least 1.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Installments per Loan (default 4)</label>
                <input type="number" class="form-control" name="installments_per_loan" min="1">
                <div class="invalid-feedback">Please enter a valid number of installments.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Installment Period (minutes)</label>
                <input type="number" class="form-control" name="installment_period_minutes" min="1" required>
                <div class="invalid-feedback">Installment period must be at least 1 minute.</div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Generate Loan</button>
        </form>
      </div>
    </div>
  </div>
</div>