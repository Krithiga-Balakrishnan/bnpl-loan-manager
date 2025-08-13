@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Loan Dashboard</h1>

    {{-- Filters --}}
    <div class="row g-2 mb-3">
        <div class="col-md-3">
            <select id="statusFilter" class="form-select">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        <div class="col-md-4">
            <input type="text" id="searchLoan" class="form-control" placeholder="Search Loan ID or Amount...">
        </div>
        <div class="col-md-5 text-end">
            <a href="{{ url('/loan-form') }}" class="btn btn-primary">Open Generate Loan Form</a>
            <button id="refreshBtn" class="btn btn-secondary">Refresh</button>
        </div>
    </div>

    {{-- Table --}}
    <table class="table table-bordered align-middle" id="loansTable">
        <thead class="table-light">
            <tr>
                <th>Loan ID</th>
                <th>Total Amount</th>
                <th>Installments Paid / Total</th>
                <th>Total Amount Paid</th>
                <th>Status</th>
                <th>Next Due</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($loans as $loan)
                @php
                    $paid = $loan->installments->where('status', 'paid');
                    $next = $loan->installments->where('status', 'pending')->sortBy('due_date')->first();
                @endphp
                <!-- <tr data-loan-id="{{ $loan->id }}" data-status="{{ $loan->status }}"> -->
                  <tr data-loan-id="{{ $loan->id }}" id="loan-{{ $loan->id }}" data-status="{{ $loan->status }}">
                    <td>{{ $loan->id }}</td>
                    <td>{{ number_format($loan->amount, 2) }}</td>
                    <td class="paid-count">{{ $paid->count() }} / {{ $loan->installments->count() }}</td>
                    <td class="paid-sum">{{ number_format($paid->sum('amount'), 2) }}</td>
                    <td class="loan-status">{{ ucfirst($loan->status) }}</td>
                    <td class="next-due">
                        {{ $next ? $next->due_date->timezone(config('app.timezone'))->format('Y-m-d H:i') : 'N/A' }}
                    </td>
                    <td>
                          @if($next && $loan->status == 'active')
                          <button class="btn btn-sm btn-success pay-btn" data-installment-id="{{ $next->id }}">Pay Next</button>
                          @endif

                          <select class="form-select form-select-sm d-inline-block status-select" style="width:auto;" {{ in_array($loan->status, ['completed','cancelled']) ? 'disabled' : 'disabled' }}>
                              <option value="active" {{ $loan->status == 'active' ? 'selected' : '' }}>Active</option>
                              <option value="completed" {{ $loan->status == 'completed' ? 'selected' : '' }}>Completed</option>
                              <option value="cancelled" {{ $loan->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                          </select>

                          @if(!in_array($loan->status, ['completed','cancelled']))
                              <button class="btn btn-sm btn-outline-primary edit-update-btn">Edit</button>
                          @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">No loans available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Chart --}}
    <div class="card mt-4">
        <div class="card-header">Payment Trends</div>
        <div class="card-body">
            <canvas id="paymentChart" height="100"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.getElementById('refreshBtn').addEventListener('click', () => location.reload());

// Replace alert() with toast
function handleError(message) {
    showErrorToast(message || 'An unexpected error occurred');
}
// Pay Installment & Edit/Update buttons
    document.querySelector('#loansTable').addEventListener('click', function(e) {
        const tr = e.target.closest('tr');
        if (!tr) return;

        // Pay Next button
        if (e.target.classList.contains('pay-btn')) {
            const id = e.target.dataset.installmentId;
            axios.post(`/api/installments/${id}/pay`)
                .then(res => console.log(res.data))
                .catch(err => handleError(err.response?.data?.message || 'Error paying installment'));
        }

        // Edit / Update button
        if (e.target.classList.contains('edit-update-btn')) {
            const btn = e.target;
            const select = tr.querySelector('.status-select');

            if (btn.textContent === 'Edit') {
                select.disabled = false;
                btn.textContent = 'Update';
            } else if (btn.textContent === 'Update') {
                const loanId = tr.dataset.loanId;
                const status = select.value;

                axios.patch(`/api/loans/${loanId}/status`, { status })
                    .then(res => {
                        if (res.data.loan) {
                            updateLoanRow(res.data.loan); // refresh row
                            select.disabled = true;
                            btn.textContent = 'Edit';

                            if(['completed','cancelled'].includes(res.data.loan.status)){
                                const payBtn = tr.querySelector('.pay-btn');
                                if(payBtn) payBtn.remove();
                                btn.remove();
                            }
                        }
                    })
                    .catch(() => handleError('Error updating status'));
            }
        }
    });

// Status filter
document.getElementById('statusFilter').addEventListener('change', filterLoans);
document.getElementById('searchLoan').addEventListener('input', filterLoans);

function filterLoans() {
    const status = document.getElementById('statusFilter').value.toLowerCase();
    const search = document.getElementById('searchLoan').value.toLowerCase();
    document.querySelectorAll('#loansTable tbody tr').forEach(row => {
        const matchesStatus = !status || row.dataset.status === status;
        const matchesSearch = row.innerText.toLowerCase().includes(search);
        row.style.display = (matchesStatus && matchesSearch) ? '' : 'none';
    });
}

// Chart.js payment trends
const ctx = document.getElementById('paymentChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($chartLabels ?? []),
        datasets: [{
            label: 'Total Paid Amount',
            data: @json($chartData ?? []),
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.2)',
            fill: true,
            tension: 0.1
        }]
    }
});
</script>
@endpush
