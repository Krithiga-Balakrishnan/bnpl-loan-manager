<!-- Customer Registration Modal -->
<div class="modal fade" id="customerFormModal" tabindex="-1" aria-labelledby="customerFormModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content customer-modal">
      <!-- Header -->
       @push('styles')
        <link rel="stylesheet" href="{{ mix('css/dashboard.css') }}">
       @endpush
      <div class="modal-header border-0 pb-0">
        <div class="d-flex align-items-center gap-2 w-100">
          <div class="modal-avatar d-inline-flex align-items-center justify-content-center">
            <i class="bi bi-person-plus"></i>
          </div>
          <div class="flex-grow-1">
            <h5 class="modal-title mb-0 fw-semibold" id="customerFormModalLabel">Register Customer</h5>
            <small class="text-muted">Add a new customer to the database</small>
          </div>
          <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
        </div>
      </div>

      <!-- Body -->
      <div class="modal-body pt-3">
        <form id="customerForm" class="needs-validation" novalidate>
          @csrf
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Full Name</label>
              <div class="input-group modern-input">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" name="name" class="form-control" required placeholder="Enter full name">
                <div class="invalid-feedback">Name is required.</div>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Email</label>
              <div class="input-group modern-input">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" class="form-control" placeholder="Enter email">
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Phone</label>
              <div class="input-group modern-input">
                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                <input type="text" name="phone" class="form-control" required placeholder="Enter phone number">
                <div class="invalid-feedback">Phone number is required.</div>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Address</label>
              <div class="input-group modern-input">
                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                <input type="text" name="address" class="form-control" placeholder="Enter address">
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-lg w-100 mt-4 shadow-sm rounded-pill">
            <i class="bi bi-save me-2"></i>Register Customer
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
