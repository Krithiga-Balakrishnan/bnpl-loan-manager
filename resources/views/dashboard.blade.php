@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Loan Dashboard</h1>
 <div>
        <div class="row g-8 mb-3">

                    <div class="col-md-8 text-end">

        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#customerFormModal">
            Register Customer
        </button>
             <!-- <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loanFormModal">
                Open Generate Loan Form
            </button> -->
            <button id="refreshBtn" class="btn btn-secondary">Refresh</button>
        </div>
</div>
        
<div class="mt-3">
    <button type="button" class="btn btn-outline-primary" id="viewLoansBtn">View Loans</button>
    <button type="button" class="btn btn-outline-info" id="viewCustomersBtn">View Registered Customers</button>
</div>
    </div>

    <!-- Registered Customers Table -->
<div class="table-responsive mt-3" id="customersTableContainer" style="display: none;">
    <div class="row g-2 mb-3">
    <div class="col-md-4">
        <input type="text" id="searchCustomer" class="form-control" placeholder="Search by name, email, or phone...">
    </div>
</div>
    <table class="table table-bordered" id="customersTable">
        <thead class="table-light">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="customersTableBody">
            {{-- Will be loaded dynamically --}}
        </tbody>
    </table>
</div>
<!-- Customer's Loan Table -->
    <div class="table-responsive mt-3" id="loansTableContainer" style="display: none;">
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

        </div>
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
                <tbody id="loansTableBody">
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

                                <select class="form-select form-select-sm d-inline-block status-select" style="width:auto;"
                                 {{ in_array($loan->status, ['completed','cancelled']) ? 'disabled' : 'disabled' }}
                                 >
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
           <!-- <div id="pagination" class="mt-3 d-flex justify-content-center"></div> -->

    </div>

   {{-- Charts Row --}}
    <div class="row mt-4 g-3">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">Payment Trends</div>
                <div class="card-body">
                    <canvas id="paymentChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">Loan Status Distribution</div>
                <div class="card-body">
                    <canvas id="statusChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>
@include('components.modals.loan-form-modal')
@include('components.modals.register-customer-modal')
@include('components.toast-containers')
@endsection

@push('scripts')
<!-- <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css">
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" defer></script> -->
 @vite('resources/js/app.js')
<!-- <script>
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
                .then(res => {
            if(res.data.installment) {
                const loanId = res.data.installment.loan_id;
                updateLoanRow(res.data.loan); // update row
                refreshCharts();               // refresh both charts immediately
            }
        })
        .catch(err => handleError(err.response?.data?.message || 'Error paying installment'));
        }

        // Edit / Update button
        if (e.target.classList.contains('edit-update-btn')) {
            const btn = e.target;
            const select = tr.querySelector('.status-select');

            if (btn.textContent === 'Edit') {
        // Enable dropdown
        select.disabled = false;
        select.focus();
        btn.textContent = 'Update';

        // Optional: disable other "Edit" buttons while one is being updated
        document.querySelectorAll('.edit-update-btn').forEach(b => {
            if (b !== btn) b.disabled = true;
        });

    } else if (btn.textContent === 'Update') {
        const loanId = tr.dataset.loanId;
        const status = select.value;

        axios.patch(`/api/loans/${loanId}/status`, { status })
            .then(res => {
                if (res.data.loan) {
                    updateLoanRow(res.data.loan);
                    refreshCharts();
                    select.disabled = true;
                    btn.textContent = 'Edit';

                    // Re-enable other buttons
                    document.querySelectorAll('.edit-update-btn').forEach(b => b.disabled = false);

                    const loanId = res.data.loan.id;
                    const newStatus = res.data.loan.status;
                    const loanInMemory = window.allLoans.find(l => l.id === loanId);
                    if (loanInMemory) {
                        loanInMemory.status = newStatus;
                    } else {
                        window.allLoans.push({ id: loanId, status: newStatus });
                    }

                    if (['completed','cancelled'].includes(newStatus)) {
                        const payBtn = tr.querySelector('.pay-btn');
                        if (payBtn) payBtn.remove();
                        btn.remove();
                    }
                }
            })
            .catch(() => handleError('Error updating status'));
    }

        }
    });
// Tab view
    let allCustomers = [];
    const loansBtn = document.getElementById('viewLoansBtn');
    const customersBtn = document.getElementById('viewCustomersBtn');

    const loansTableContainer = document.getElementById('loansTableContainer');
    const customersTableContainer = document.getElementById('customersTableContainer');
    const customerSearchInput = document.getElementById('searchCustomer'); // Add this input in Blade

    loansBtn.addEventListener('click', () => {
        if (loansTableContainer.style.display === 'none') {
            loansTableContainer.style.display = 'block';
            customersTableContainer.style.display = 'none';
        } else {
            loansTableContainer.style.display = 'none';
        }
    });

    customersBtn.addEventListener('click', () => {
        if (customersTableContainer.style.display === 'none') {
            fetch('/api/customers')
                .then(res => res.json())
                .then(data => {
                    allCustomers = data.customers;
                    renderCustomers(allCustomers); // Now includes Edit buttons
                    customersTableContainer.style.display = 'block';
                    loansTableContainer.style.display = 'none';
                });
        } else {
            customersTableContainer.style.display = 'none';
        }
    });


    // Render Customer Rows
    function renderCustomers(customers) {
        const tableBody = document.getElementById('customersTableBody');
        tableBody.innerHTML = '';
        customers.forEach(customer => {
            tableBody.innerHTML += `
                <tr>
                    <td>${customer.name}</td>
                    <td>${customer.email}</td>
                    <td>${customer.phone || '-'}</td>
                    <td>${customer.address || '-'}</td>
                    <td>
                        <button type="button" class="btn btn-primary open-loan-form-btn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#loanFormModal"
                                data-customer-id="${customer.id}">
                            Generate Loan
                        </button>
                        <button type="button" class="btn btn-outline-secondary edit-customer-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#customerFormModal"
                                data-customer-id="${customer.id}"
                                data-name="${customer.name}"
                                data-email="${customer.email}"
                                data-phone="${customer.phone || ''}"
                                data-address="${customer.address || ''}">
                            Edit
                        </button>
                    </td>
                </tr>
            `;
        });
    }
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('edit-customer-btn')) {
            const form = document.getElementById('customerForm');
            form.name.value = e.target.getAttribute('data-name');
            form.email.value = e.target.getAttribute('data-email');
            form.phone.value = e.target.getAttribute('data-phone');
            form.address.value = e.target.getAttribute('data-address');
            form.setAttribute('data-edit-id', e.target.getAttribute('data-customer-id'));
            form.classList.remove('was-validated');
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        }
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('open-loan-form-btn')) {
            const customerId = e.target.getAttribute('data-customer-id');
            const input = document.getElementById('loanCustomerId');
            if (input) input.value = customerId;

            const customerRow = e.target.closest('tr');
            const customerName = customerRow.querySelector('td').textContent;
            const info = document.getElementById('selectedCustomerInfo');
            if (info) info.textContent = `Generating loan for: ${customerName}`;
        }
    });



    // Search Customers
    customerSearchInput.addEventListener('input', () => {
        const query = customerSearchInput.value.toLowerCase();
        const filtered = allCustomers.filter(c =>
            c.name.toLowerCase().includes(query) ||
            c.email.toLowerCase().includes(query) ||
            (c.phone && c.phone.toLowerCase().includes(query))
        );
        renderCustomers(filtered);
    });
// Register Customer Form Post
    document.getElementById('customerForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const form = this;

        // Reset previous validation states
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

        const name = form.name.value.trim();
        const email = form.email.value.trim();
        const phone = form.phone.value.trim();
        const address = form.address.value.trim();

        let valid = true;

        // Name validation
        if (!name) {
            valid = false;
            form.name.classList.add('is-invalid');
            const error = document.createElement('div');
            error.className = 'invalid-feedback';
            error.innerText = 'Name is required';
            form.name.after(error);
        }

        // Email validation
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email || !emailPattern.test(email)) {
            valid = false;
            form.email.classList.add('is-invalid');
            const error = document.createElement('div');
            error.className = 'invalid-feedback';
            error.innerText = 'Valid email is required';
            form.email.after(error);
        }

        // Phone validation (optional)
        if (phone && !/^\d{7,15}$/.test(phone)) {
            valid = false;
            form.phone.classList.add('is-invalid');
            const error = document.createElement('div');
            error.className = 'invalid-feedback';
            error.innerText = 'Phone must be 7–15 digits';
            form.phone.after(error);
        }

        if (!valid) return;

        const formData = { name, email, phone, address };
        const customerId = form.getAttribute('data-edit-id');
        const method = customerId ? 'PATCH' : 'POST';
        const url = customerId 
            ? `http://127.0.0.1:8000/api/customers/${customerId}` 
            : 'http://127.0.0.1:8000/api/customers';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(formData)
        })
        .then(async res => {
            const data = await res.json();
            if (res.ok) {
                document.getElementById('toastMessage').textContent = data.message || 
                    (customerId ? 'Customer updated successfully!' : 'Customer registered successfully!');
                new bootstrap.Toast(document.getElementById('successToast')).show();

                form.reset();
                form.removeAttribute('data-edit-id');
                const modal = bootstrap.Modal.getInstance(document.getElementById('customerFormModal'));
                modal.hide();

                // Refresh customer table
                fetch('/api/customers')
                    .then(res => res.json())
                    .then(data => {
                        allCustomers = data.customers;
                        renderCustomers(allCustomers);
                    });
            } else if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const input = form[field];
                    if (input) {
                        input.classList.add('is-invalid');
                        const error = document.createElement('div');
                        error.className = 'invalid-feedback';
                        error.innerText = data.errors[field][0];
                        input.after(error);
                    }
                });
            } else {
                document.getElementById('errorToastMessage').textContent = data.message || 'Something went wrong';
                new bootstrap.Toast(document.getElementById('errorToast')).show();
            }
        })
        .catch(err => {
            console.error(err);
            document.getElementById('errorToastMessage').textContent = 'Network error';
            new bootstrap.Toast(document.getElementById('errorToast')).show();
        });
    });

    // //Generate Loan
    document.getElementById('loanForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;

        // Reset previous validation states
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        const loanAmount = parseFloat(form.loan_amount.value);
        const numberOfLoans = parseInt(form.number_of_loans.value);
        const installmentsPerLoan = parseInt(form.installments_per_loan.value) || 4;
        const installmentPeriod = parseInt(form.installment_period_minutes.value);

        let valid = true;

        // Loan amount validation
        if (isNaN(loanAmount) || loanAmount <= 0) {
            valid = false;
            form.loan_amount.classList.add('is-invalid');
            form.loan_amount.nextElementSibling?.remove();
            const error = document.createElement('div');
            error.className = 'invalid-feedback';
            error.innerText = 'Loan amount must be greater than 0';
            form.loan_amount.after(error);
        }

        // Number of loans validation
        if (isNaN(numberOfLoans) || numberOfLoans < 1) {
            valid = false;
            form.number_of_loans.classList.add('is-invalid');
            form.number_of_loans.nextElementSibling?.remove();
            const error = document.createElement('div');
            error.className = 'invalid-feedback';
            error.innerText = 'Number of loans must be at least 1';
            form.number_of_loans.after(error);
        }

        // Installments per loan validation (optional, default is 4)
        if (isNaN(installmentsPerLoan) || installmentsPerLoan < 1) {
            valid = false;
            form.installments_per_loan.classList.add('is-invalid');
            form.installments_per_loan.nextElementSibling?.remove();
            const error = document.createElement('div');
            error.className = 'invalid-feedback';
            error.innerText = 'Installments per loan must be at least 1';
            form.installments_per_loan.after(error);
        }

        // Installment period validation
        if (isNaN(installmentPeriod) || installmentPeriod < 1) {
            valid = false;
            form.installment_period_minutes.classList.add('is-invalid');
            form.installment_period_minutes.nextElementSibling?.remove();
            const error = document.createElement('div');
            error.className = 'invalid-feedback';
            error.innerText = 'Installment period must be at least 1 minute';
            form.installment_period_minutes.after(error);
        }

        if (!valid) return; // Stop if validation fails

        const data = {
            customer_id: document.getElementById('loanCustomerId').value,
            loan_amount: loanAmount,
            number_of_loans: numberOfLoans,
            installments_per_loan: installmentsPerLoan,
            installment_period_minutes: installmentPeriod
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

            const modalEl = document.getElementById('loanFormModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();

             // Ensure window.allLoans exists
            window.allLoans = window.allLoans || [];

            // Update table with new loan(s)
            if (res.loan) {
                window.allLoans.push({ id: res.loan.id, status: res.loan.status });
                updateLoanRow(res.loan);
            } else if (res.loans) {
                res.loans.forEach(l => {
                    window.allLoans.push({ status: l.status });
                    updateLoanRow(l);
                });
            }

            refreshCharts();
            filterLoans();
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
            } else {
                console.error(err);
            }
        });
    });
function transformLoan(raw) {
    return {
        id: raw.id,
        status: raw.status,
        amount: raw.amount,
        paid_count: raw.paid_count ?? 0,
        total_installments: raw.total_installments ?? 0,
        paid_sum: raw.paid_sum ?? 0,
        next_due: raw.next_due ?? 'N/A'
    };
}

    document.addEventListener('DOMContentLoaded', () => {
        const table = document.querySelector("#loansTable");
        const currentPageSize = window.dataTable?.perPage || 5;
        window.dataTable = new simpleDatatables.DataTable(table, {
        searchable: false, 
        perPage: currentPageSize,
        pagination: true,
        labels: {
            perPage: "rows per page",
            noRows: "No loans found",
            info: "Showing {start} to {end} of {rows} loans"
        }
    });

        const originalUpdateLoanRow = window.updateLoanRow; 
        window.updateLoanRow = function(loan) {
            originalUpdateLoanRow(loan); 
            filterLoans();         
    dataTable.refresh();   
    refreshCharts();       
        };
    });


    // Status filter
    document.getElementById('statusFilter').addEventListener('change', filterLoans);
    document.getElementById('searchLoan').addEventListener('input', filterLoans);
    let filteredLoans = [];
    function filterLoans() {
    const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
    const searchTerm = document.getElementById('searchLoan').value.toLowerCase();

    filteredLoans = window.allLoans.filter(loan => {
        const matchesStatus = !statusFilter || loan.status.toLowerCase() === statusFilter;
        const matchesSearch =
            String(loan.id).toLowerCase().includes(searchTerm) ||
            String(loan.amount).toLowerCase().includes(searchTerm);
        return matchesStatus && matchesSearch;
    });

    // Re-render table
    if (window.dataTable) {
        window.dataTable.destroy();
    }
    renderLoanTable(filteredLoans);
}

function renderLoanTable(loans) {
    const tableBody = document.querySelector('#loansTable tbody');
    tableBody.innerHTML = '';

    if (loans.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="7" class="text-center text-muted">No loans found.</td></tr>`;
        return;
    }

    loans.forEach(loan => {
        const paid = loan.paid_count ?? 0;
        const total = loan.total_installments ?? 0;
        const paidSum = loan.paid_sum ?? 0;
        const nextDue = loan.next_due ?? 'N/A';

        tableBody.innerHTML += `
            <tr data-loan-id="${loan.id}" data-status="${loan.status}">
                <td>${loan.id}</td>
                <td>${Number(loan.amount).toFixed(2)}</td>
                <td class="paid-count">${paid} / ${total}</td>
                <td class="paid-sum">${Number(paidSum).toFixed(2)}</td>
                <td class="loan-status">${loan.status.charAt(0).toUpperCase() + loan.status.slice(1)}</td>
                <td class="next-due">${nextDue}</td>
                <td>
                    <select class="form-select form-select-sm status-select" style="width:auto;" disabled>
                        <option value="active" ${loan.status === 'active' ? 'selected' : ''}>Active</option>
                        <option value="completed" ${loan.status === 'completed' ? 'selected' : ''}>Completed</option>
                        <option value="cancelled" ${loan.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                    </select>
                    <button class="btn btn-sm btn-outline-primary edit-update-btn" ${['completed','cancelled'].includes(loan.status) ? 'disabled' : ''}>Edit</button>
                </td>
            </tr>
        `;
    });

    // Re-render pagination WITHOUT resetting perPage
    if (window.dataTable) {
        const perPage = window.dataTable.options.perPage; // keep user preference
        window.dataTable.destroy();
        window.dataTable = new simpleDatatables.DataTable("#loansTable", {
            searchable: false,
            perPage: perPage,
            labels: {
                perPage: "rows per page",
                noRows: "No loans found",
                info: "Showing {start} to {end} of {rows} loans"
            }
        });
    }
}



    // Payment Trends Line Chart
    let paymentChart;
    let statusChart;
    // --- charts ---
    async function renderCharts() {
        // Line chart from backend transactions
        try {
            const { data } = await axios.get('/api/loans/payments');
            const items = Array.isArray(data) ? data.slice() : [];

            // sort ascending by processed_at (just in case)
            items.sort((a, b) => new Date(a.processed_at) - new Date(b.processed_at));

            // Build {x,y} points for Chart.js time scale
            const points = items.map(p => ({
                x: new Date(p.processed_at),  // X = processed_at
                y: Number(p.amount) || 0      // Y = amount
            }));

            const ctx = document.getElementById('paymentChart').getContext('2d');
            if (paymentChart) paymentChart.destroy();
                
            const times = points.map(p => new Date(p.x));
            const firstTime = new Date(Math.min(...times));
            const lastTime = new Date(Math.max(...times));

            // Add padding before and after
            const padding = 1 * 60 * 60 * 1000; // 1 hour in ms
            const minTime = new Date(firstTime.getTime() - padding);
            const maxTime = new Date(lastTime.getTime() + padding);

            paymentChart = new Chart(ctx, {
                type: 'line',
                data: {
                    datasets: [{
                        label: 'Payment Amount',
                        data: points,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13,110,253,0.2)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    parsing: false,
                    responsive: true,
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                tooltipFormat: 'yyyy-MM-dd HH:mm',
                                displayFormats: { minute: 'HH:mm', hour: 'HH:mm', day: 'MMM d' }
                            },
                            title: { display: true, text: 'Processed At' },
                            min: minTime,
                            max: maxTime
                        },
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Amount' }
                        }
                    }
                }
            });

        } catch (err) {
            console.error('Error loading payment data:', err);
        }

        try {
        const res = await axios.get('/api/loans/status-counts');
            const statusCounts = res.data;

            const statusCtx = document.getElementById('statusChart').getContext('2d');
            if (statusChart) statusChart.destroy();
            statusChart = new Chart(statusCtx, {
                type: 'pie',
                data: {
                    labels: ['Active', 'Completed', 'Cancelled'],
                    datasets: [{
                        data: [statusCounts.active, statusCounts.completed, statusCounts.cancelled],
                        backgroundColor: ['#0d6efd', '#198754', '#dc3545']
                    }]
                },
                options: { responsive: true }
            });
        }catch (err) {
            console.error('Error loading status counts:', err);
        }
    }

    // --- hooks already in your code ---
    function refreshCharts() { renderCharts(); }

    // Initial render (call once after DOM is ready)
    document.addEventListener('DOMContentLoaded', () => {
    window.allLoans = {!! json_encode($loans->map(function ($loan) {
        $paid = $loan->installments->where('status', 'paid');
        $next = $loan->installments->where('status', 'pending')->sortBy('due_date')->first();
        return [
            'id' => $loan->id,
            'status' => $loan->status,
            'amount' => $loan->amount,
            'paid_count' => $paid->count(),
            'total_installments' => $loan->installments->count(),
            'paid_sum' => $paid->sum('amount'),
            'next_due' => $next ? $next->due_date->timezone(config('app.timezone'))->format('Y-m-d H:i') : 'N/A'
        ];
    })) !!};

        renderCharts();
    }); -->
<!-- </script> -->
 <script>
        window.__initialLoans__ = {!! json_encode($loans->map(function ($loan) {
            $paid = $loan->installments->where('status', 'paid');
            $next = $loan->installments->where('status', 'pending')->sortBy('due_date')->first();
            return [
                'id' => $loan->id,
                'status' => $loan->status,
                'amount' => $loan->amount,
                'paid_count' => $paid->count(),
                'total_installments' => $loan->installments->count(),
                'paid_sum' => $paid->sum('amount'),
                'next_due' => $next ? $next->due_date->timezone(config('app.timezone'))->format('Y-m-d H:i') : 'N/A'
            ];
        })) !!};
    </script>
@endpush
