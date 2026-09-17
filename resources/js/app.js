const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const sidebar = document.querySelector('[data-sidebar]');
const sidebarBackdrop = document.querySelector('[data-sidebar-backdrop]');

const setSidebarOpen = (isOpen) => {
    if (! sidebar || ! sidebarToggle || ! sidebarBackdrop) {
        return;
    }

    sidebar.classList.toggle('-translate-x-full', ! isOpen);
    sidebarBackdrop.classList.toggle('hidden', ! isOpen);
    sidebarToggle.setAttribute('aria-expanded', String(isOpen));
};

sidebarToggle?.addEventListener('click', () => {
    setSidebarOpen(sidebarToggle.getAttribute('aria-expanded') !== 'true');
});

sidebarBackdrop?.addEventListener('click', () => setSidebarOpen(false));

window.addEventListener('resize', () => {
    if (window.innerWidth >= 1024) {
        setSidebarOpen(false);
    }
});
