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
        <button id="clearFilters" class="btn btn-sm btn-secondary">Clear Filters</button>
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
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css">
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" defer></script>
 @vite('resources/js/app.js')
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
