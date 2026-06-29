document.addEventListener('submit', async (event) => {
    const form = event.target;

    if (!form.matches('[data-ajax-coupon]')) {
        return;
    }

    event.preventDefault();

    const target = document.querySelector(form.dataset.target);
    const formData = new FormData(form);

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData,
        });

        const payload = await response.json();
        target.textContent = payload.message;
        target.classList.toggle('text-danger', !response.ok);
        target.classList.toggle('text-success', response.ok);
    } catch (error) {
        target.textContent = 'Coupon validation is temporarily unavailable.';
        target.classList.add('text-danger');
    }
});

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-panel-target]');

    if (!trigger) {
        return;
    }

    const group = trigger.closest('[data-panel-group]');

    if (!group) {
        return;
    }

    const panelName = trigger.dataset.panelTarget;

    group.querySelectorAll('[data-panel-target]').forEach((button) => {
        button.classList.toggle('active', button === trigger);
        button.setAttribute('aria-pressed', button === trigger ? 'true' : 'false');
    });

    group.querySelectorAll('[data-panel]').forEach((panel) => {
        panel.hidden = panel.dataset.panel !== panelName;
    });
});
