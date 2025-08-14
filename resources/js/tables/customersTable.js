export function renderCustomers(customers) {
  const body = document.getElementById('customersTableBody');
  body.innerHTML = '';
  customers.forEach(c => {
    body.insertAdjacentHTML('beforeend', `
      <tr>
        <td>${c.name}</td>
        <td>${c.email}</td>
        <td>${c.phone || '-'}</td>
        <td>${c.address || '-'}</td>
        <td>
          <button type="button" class="btn btn-primary open-loan-form-btn"
                  data-bs-toggle="modal"
                  data-bs-target="#loanFormModal"
                  data-customer-id="${c.id}">
            Generate Loan
          </button>
          <button type="button" class="btn btn-outline-secondary edit-customer-btn"
                  data-bs-toggle="modal"
                  data-bs-target="#customerFormModal"
                  data-customer-id="${c.id}"
                  data-name="${c.name}"
                  data-email="${c.email}"
                  data-phone="${c.phone || ''}"
                  data-address="${c.address || ''}">
            Edit
          </button>
        </td>
      </tr>
    `);
  });
}
