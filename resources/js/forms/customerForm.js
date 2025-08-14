// resources/js/forms/customerForm.js
import { createCustomer, updateCustomerApi, fetchCustomers } from '../api';
import { renderCustomers } from '../tables/customersTable';
import { showError, showSuccess } from '../ui/toast';

export function setupCustomerForm() {
  const form = document.getElementById('customerForm');
  const searchInput = document.getElementById('searchCustomer');
  let allCustomers = [];

  // Pre-fill when clicking "Edit"
  document.addEventListener('click', (e) => {
    if (!e.target.classList.contains('edit-customer-btn')) return;
    form.name.value = e.target.getAttribute('data-name');
    form.email.value = e.target.getAttribute('data-email');
    form.phone.value = e.target.getAttribute('data-phone') || '';
    form.address.value = e.target.getAttribute('data-address') || '';
    form.setAttribute('data-edit-id', e.target.getAttribute('data-customer-id'));
    form.classList.remove('was-validated');
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
  });

  // Open loan modal with selected customer
  document.addEventListener('click', (e) => {
    if (!e.target.classList.contains('open-loan-form-btn')) return;
    const customerId = e.target.getAttribute('data-customer-id');
    const input = document.getElementById('loanCustomerId');
    if (input) input.value = customerId;

    const row = e.target.closest('tr');
    const name = row.querySelector('td')?.textContent || '';
    const info = document.getElementById('selectedCustomerInfo');
    if (info) info.textContent = `Generating loan for: ${name}`;
  });

  // Submit (create/update)
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    // clear old validation messages
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

    const payload = {
      name: form.name.value.trim(),
      email: form.email.value.trim(),
      phone: form.phone.value.trim(),
      address: form.address.value.trim(),
    };

    // simple validations
    let valid = true;
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const addError = (input, msg) => {
      input.classList.add('is-invalid');
      const div = document.createElement('div');
      div.className = 'invalid-feedback';
      div.innerText = msg;
      input.after(div);
    };

    if (!payload.name) { valid = false; addError(form.name, 'Name is required'); }
    if (!payload.email || !emailPattern.test(payload.email)) { valid = false; addError(form.email, 'Valid email is required'); }
    if (payload.phone && !/^\d{7,15}$/.test(payload.phone)) { valid = false; addError(form.phone, 'Phone must be 7–15 digits'); }
    if (!valid) return;

    try {
      const editId = form.getAttribute('data-edit-id');
      const { ok, data } = editId ? await updateCustomerApi(editId, payload) : await createCustomer(payload);

      if (ok) {
        showSuccess(data.message || (editId ? 'Customer updated successfully!' : 'Customer registered successfully!'));
        form.reset();
        form.removeAttribute('data-edit-id');
        const modal = bootstrap.Modal.getInstance(document.getElementById('customerFormModal'));
        modal?.hide();

        // refresh list
        const res = await fetchCustomers();
        allCustomers = res.customers || [];
        renderCustomers(allCustomers);
      } else {
        if (data?.errors) {
          Object.keys(data.errors).forEach(field => {
            const input = form[field];
            if (input) addError(input, data.errors[field][0]);
          });
        } else {
          showError(data?.message || 'Something went wrong');
        }
      }
    } catch (err) {
      console.error(err);
      showError('Network error');
    }
  });

  // search filter
  searchInput?.addEventListener('input', () => {
    const q = searchInput.value.toLowerCase();
    const filtered = (allCustomers || []).filter(c =>
      c.name?.toLowerCase().includes(q) ||
      c.email?.toLowerCase().includes(q) ||
      (c.phone && c.phone.toLowerCase().includes(q))
    );
    renderCustomers(filtered);
  });

  return {
    async openCustomersTab() {
      const res = await fetchCustomers();
      allCustomers = res.customers || [];
      renderCustomers(allCustomers);
    }
  };
}
