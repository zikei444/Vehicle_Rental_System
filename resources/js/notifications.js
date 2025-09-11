function fetchNotifications() {
    $.get("/notifications/fetch", function(res) {
        res.notifications.forEach(function(n) {
            // Create toast using Bootstrap 5
            const toastEl = document.createElement("div");
            toastEl.className = "toast align-items-center text-bg-primary border-0";
            toastEl.setAttribute("role", "alert");
            toastEl.setAttribute("aria-live", "assertive");
            toastEl.setAttribute("aria-atomic", "true");
            toastEl.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${n.message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            document.getElementById("toast-container").appendChild(toastEl);

            const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
            toast.show();

            // Mark as read
            $.post(`/notifications/mark-as-read/${n.id}`);
        });
    });
}


function showToast(message, reply=null) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toastEl = document.createElement('div');
    toastEl.className = 'toast align-items-center text-bg-success border-0';
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');

    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <strong>${message}</strong>
                ${reply ? '<br>Reply: '+reply : ''}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    container.appendChild(toastEl);

    const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
    toast.show();

    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

// Poll every 5 seconds
setInterval(fetchNotifications, 5000);
