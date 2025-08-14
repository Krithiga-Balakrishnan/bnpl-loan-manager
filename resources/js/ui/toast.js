export function showSuccess(message) {
  const toast = document.getElementById('successToast');
  if (!toast) return;
  document.getElementById('toastMessage').textContent = message || 'Success';
  new bootstrap.Toast(toast).show();
}

export function showError(message) {
  const toast = document.getElementById('errorToast');
  if (!toast) return;
  document.getElementById('errorToastMessage').textContent = message || 'Something went wrong';
  new bootstrap.Toast(toast).show();
}
