<!-- Customer Registration Modal -->
<div class="modal fade" id="customerFormModal" tabindex="-1" aria-labelledby="customerFormModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="customerFormModalLabel">Register Customer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="customerForm" class="needs-validation" novalidate>
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" required>
                    <div class="invalid-feedback">Name is required.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" required>
                    <div class="invalid-feedback">Phone number is required.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>
