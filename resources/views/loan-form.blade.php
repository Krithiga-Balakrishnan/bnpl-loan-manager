{{-- resources/views/loan-form.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Generate Loan</h1>

    <form id="loanForm" class="border p-4 rounded shadow-sm bg-light needs-validation" novalidate>
        @csrf

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

        <button type="submit" class="btn btn-primary">Generate Loan</button>
    </form>
</div>

<!-- Toast container -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="successToast" class="toast text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage">Loan generated successfully!</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('loanForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;

    if (!form.checkValidity()) {
        e.stopPropagation();
        form.classList.add('was-validated');
        return;
    }

    const data = {
        loan_amount: form.loan_amount.value,
        number_of_loans: form.number_of_loans.value,
        installments_per_loan: form.installments_per_loan.value || 4,
        installment_period_minutes: form.installment_period_minutes.value
    };

     fetch('/api/loans/generate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(res => {
        if (!res.ok) return res.json().then(err => { throw err; });
        return res.json();
    })
    .then(res => {
        document.getElementById('toastMessage').textContent = res.message || 'Loan generated successfully!';
        new bootstrap.Toast(document.getElementById('successToast')).show();
        form.reset();
        form.classList.remove('was-validated');
    })
    .catch(err => {
        if (err?.errors) {
            Object.keys(err.errors).forEach(field => {
                const input = form.querySelector(`[name="${field}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    input.nextElementSibling.textContent = err.errors[field][0];
                }
            });
        }
    });
});
</script>
@endpush
