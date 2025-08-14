// resources/js/state.js
export const state = {
  allLoans: Array.isArray(window.__initialLoans__) ? window.__initialLoans__ : [],
  dataTable: null,
};

export function transformLoan(raw) {
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
