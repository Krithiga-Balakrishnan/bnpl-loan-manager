<div class="modal fade" id="loanFormModal" tabindex="-1" aria-labelledby="loanFormModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content loan-modal">
      @push('styles')
        <link rel="stylesheet" href="{{ mix('css/dashboard.css') }}">
      @endpush
      <div class="modal-header border-0 pb-0">
        <div class="d-flex align-items-center gap-2 w-100">
          <div class="modal-avatar d-inline-flex align-items-center justify-content-center">
            <i class="bi bi-cash-coin"></i>
          </div>
          <div class="flex-grow-1">
            <h5 class="modal-title mb-0 fw-semibold" id="loanFormModalLabel">Generate Loan</h5>
            <small class="text-muted">Create a new loan and schedule installments</small>
          </div>
          <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>

      <div class="modal-body pt-3">
        <form id="loanForm" class="needs-validation" novalidate>
          @csrf
          <input type="hidden" id="loanCustomerId" name="customer_id">

          <div id="selectedCustomerInfo" class="alert alert-info soft-alert d-flex align-items-center gap-2 py-2 px-3 mb-3" role="alert">
            <i class="bi bi-person-circle fs-5 text-primary"></i>
            <div>
              <strong>Generating loan for:</strong>
              <span id="customerNameDisplay" class="fw-semibold">[Customer Name]</span>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Loan Amount</label>
            <div class="input-group input-group-lg modern-input">
              <span class="input-group-text">₹</span>
              <input type="number" step="0.01" class="form-control" name="loan_amount" required min="0.01" placeholder="0.00">
            </div>
            <div class="invalid-feedback">Please enter a loan amount greater than 0.</div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Number of Loans</label>
              <input type="number" class="form-control modern-input" name="number_of_loans" required min="1" placeholder="e.g. 1">
              <div class="invalid-feedback">Number of loans must be at least 1.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">
                Installments per Loan <small class="text-muted">(def 4)</small>
              </label>
              <input type="number" class="form-control modern-input" name="installments_per_loan" min="1" placeholder="e.g. 4">
              <div class="invalid-feedback">Please enter a valid number of installments.</div>
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label fw-semibold">Installment Period (minutes)</label>
            <input type="number" class="form-control modern-input" name="installment_period_minutes" min="1" required placeholder="e.g. 30">
            <div class="invalid-feedback">Installment period must be at least 1 minute.</div>
          </div>

          <button type="submit" class="btn btn-primary btn-lg w-100 mt-4 shadow-sm rounded-pill">
            <i class="bi bi-magic me-2"></i>Generate Loan
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
