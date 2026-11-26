/* Cart interactions are progressive enhancement; all mutations remain form posts. */
document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-cart-quantity-step]');
    if (!button) return;
    const wrapper = button.closest('[data-cart-quantity]');
    const input = wrapper?.querySelector('input[type="number"]');
    if (!input) return;
    const min = Number(input.min || 1);
    const max = Number(input.max || 999);
    const step = Number(button.dataset.cartQuantityStep || 0);
    input.value = String(Math.min(max, Math.max(min, Number(input.value || min) + step)));
    input.dispatchEvent(new Event('change', { bubbles: true }));
});

document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-confirm-remove]');
    if (form && !window.confirm('Xóa sản phẩm này khỏi giỏ hàng?')) event.preventDefault();
});
