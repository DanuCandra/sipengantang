export function setupDashboardNavigation({ panel, trigger, backdrop, content, closeButton, body, document, desktop }) {
    if (! panel || ! trigger || ! backdrop || ! content) return;
    let open = false;
    const focusable = () => [...panel.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex="0"]')]
        .filter((element) => ! element.hidden && element.getClientRects().length > 0);
    const setOpen = (value, restoreFocus = false) => {
        open = value && ! desktop.matches;
        panel.classList.toggle('-translate-x-full', ! open);
        backdrop.classList.toggle('hidden', ! open);
        trigger.setAttribute('aria-expanded', String(open));
        panel.inert = ! open && ! desktop.matches;
        content.inert = open;
        body.classList.toggle('overflow-hidden', open);
        if (open) {
            panel.setAttribute('role', 'dialog');
            panel.setAttribute('aria-modal', 'true');
            panel.removeAttribute('aria-hidden');
            focusable()[0]?.focus();
        } else {
            panel.removeAttribute('role');
            panel.removeAttribute('aria-modal');
            if (desktop.matches) panel.removeAttribute('aria-hidden');
            else panel.setAttribute('aria-hidden', 'true');
            if (restoreFocus) trigger.focus();
        }
    };
    trigger.addEventListener('click', () => setOpen(! open));
    backdrop.addEventListener('click', () => setOpen(false, true));
    closeButton?.addEventListener('click', () => setOpen(false, true));
    document.addEventListener('keydown', (event) => {
        if (! open) return;
        if (event.key === 'Escape') { event.preventDefault(); setOpen(false, true); }
        if (event.key !== 'Tab') return;
        const controls = focusable();
        const first = controls.at(0);
        const last = controls.at(-1);
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
        else if (! event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    });
    desktop.addEventListener('change', () => {
        const panelHadFocus = panel.contains(document.activeElement);
        setOpen(false, ! desktop.matches && panelHadFocus);
    });
    setOpen(false);
}
