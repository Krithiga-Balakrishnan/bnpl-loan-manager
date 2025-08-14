export function setupTabs() {
  const loansBtn = document.getElementById('viewLoansBtn');
  const customersBtn = document.getElementById('viewCustomersBtn');
  const loansTableContainer = document.getElementById('loansTableContainer');
  const customersTableContainer = document.getElementById('customersTableContainer');

  document.getElementById('refreshBtn')?.addEventListener('click', () => location.reload());

  loansBtn?.addEventListener('click', () => {
    loansTableContainer.style.display = loansTableContainer.style.display === 'none' ? 'block' : 'none';
    customersTableContainer.style.display = 'none';
  });

  return {
    openCustomers: () => {
      customersTableContainer.style.display = 'block';
      loansTableContainer.style.display = 'none';
    },
    toggleCustomers: () => {
      customersTableContainer.style.display = customersTableContainer.style.display === 'none' ? 'block' : 'none';
      loansTableContainer.style.display = 'none';
    }
  };
}
