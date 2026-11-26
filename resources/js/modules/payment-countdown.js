const formatRemaining = (milliseconds) => {
    const totalSeconds = Math.max(0, Math.floor(milliseconds / 1000));
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    return [hours, minutes, seconds].map((part) => String(part).padStart(2, '0')).join(':');
};

const updatePaymentCountdowns = () => {
    document.querySelectorAll('[data-payment-countdown]').forEach((element) => {
        const deadline = Date.parse(element.dataset.expiresAt);
        const remaining = element.querySelector('[data-countdown-remaining]');
        if (!remaining || Number.isNaN(deadline)) return;

        const millisecondsLeft = deadline - Date.now();
        if (millisecondsLeft <= 0) {
            element.classList.add('is-expired');
            const triggerKey = `online-payment-expiry-${element.dataset.orderId}`;
            if (!window.sessionStorage.getItem(triggerKey)) {
                window.sessionStorage.setItem(triggerKey, 'submitted');
                remaining.textContent = 'Đã hết hạn · Đang hủy đơn hàng…';
                element.querySelector('[data-expire-form]')?.requestSubmit();
            } else {
                remaining.textContent = 'Đã hết hạn · Đơn hàng đang được hệ thống xử lý';
            }
            return;
        }

        remaining.textContent = `Còn ${formatRemaining(millisecondsLeft)}`;
    });
};

updatePaymentCountdowns();
window.setInterval(updatePaymentCountdowns, 1000);
