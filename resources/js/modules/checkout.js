/* Checkout page enhancements only; order validation and totals stay server-side. */
document.addEventListener('change', (event) => {
    const input = event.target.closest('[name="payment_method"]');
    if (!input) return;
    document.querySelectorAll('[data-checkout-payment]').forEach((option) => {
        option.classList.toggle('is-selected', option.querySelector('input')?.checked === true);
    });
});
