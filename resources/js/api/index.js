// resources/js/api/index.js
import axios from "axios";

const token = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

export const http = axios.create({
  headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
});

export const payInstallment = (installmentId) =>
  http.post(`/api/installments/${installmentId}/pay`).then(r => r.data);

export const updateLoanStatus = (loanId, status) =>
  http.patch(`/api/loans/${loanId}/status`, { status }).then(r => r.data);

export const fetchCustomers = () =>
  fetch('/api/customers').then(r => r.json());

export const createCustomer = (payload) =>
  fetch('http://127.0.0.1:8000/api/customers', {
    method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
    body: JSON.stringify(payload)
  }).then(async r => ({ ok: r.ok, data: await r.json() }));

export const updateCustomerApi = (id, payload) =>
  fetch(`http://127.0.0.1:8000/api/customers/${id}`, {
    method: 'PATCH', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
    body: JSON.stringify(payload)
  }).then(async r => ({ ok: r.ok, data: await r.json() }));

export const generateLoans = (payload) =>
  fetch('/api/loans/generate', {
    method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
    body: JSON.stringify(payload)
  }).then(async r => {
    const data = await r.json().catch(() => ({}));
    if (!r.ok) throw data;
    return data;
  });

export const getPayments = () =>
  http.get('/api/loans/payments').then(r => r.data || []);

export const getStatusCounts = () =>
  http.get('/api/loans/status-counts').then(r => r.data || { active: 0, completed: 0, cancelled: 0 });
