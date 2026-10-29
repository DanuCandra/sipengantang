import './realtime';
import { setupDashboardNavigation } from './dashboard-navigation';

setupDashboardNavigation({ panel: document.querySelector('[data-sidebar]'), trigger: document.querySelector('[data-sidebar-toggle]'),
    backdrop: document.querySelector('[data-sidebar-backdrop]'), content: document.querySelector('[data-dashboard-content]'),
    closeButton: document.querySelector('[data-sidebar-close]'), body: document.body, document, desktop: window.matchMedia('(min-width: 1024px)') });

const mobileNavToggle = document.querySelector('[data-mobile-nav-toggle]');
const mobileNavClose = document.querySelector('[data-mobile-nav-close]');
const mobileNav = document.querySelector('[data-mobile-nav]');
const mobileNavBackdrop = document.querySelector('[data-mobile-nav-backdrop]');
const mobileNavLinks = document.querySelectorAll('[data-mobile-nav-link]');

const setMobileNavOpen = (isOpen, restoreFocus = false) => {
    if (! mobileNav || ! mobileNavToggle || ! mobileNavBackdrop) {
        return;
    }

    mobileNav.classList.toggle('hidden', ! isOpen);
    mobileNav.classList.toggle('flex', isOpen);
    mobileNavBackdrop.classList.toggle('hidden', ! isOpen);
    mobileNavToggle.setAttribute('aria-expanded', String(isOpen));
    document.body.classList.toggle('overflow-hidden', isOpen);

    if (isOpen) {
        mobileNavClose?.focus();
    } else if (restoreFocus) {
        mobileNavToggle.focus();
    }
};

mobileNavToggle?.addEventListener('click', () => setMobileNavOpen(true));
mobileNavClose?.addEventListener('click', () => setMobileNavOpen(false, true));
mobileNavBackdrop?.addEventListener('click', () => setMobileNavOpen(false, true));
mobileNavLinks.forEach((link) => link.addEventListener('click', () => setMobileNavOpen(false)));

document.addEventListener('keydown', (event) => {
    const isOpen = mobileNavToggle?.getAttribute('aria-expanded') === 'true';

    if (event.key === 'Escape' && isOpen) {
        setMobileNavOpen(false, true);
    }

    if (event.key !== 'Tab' || ! isOpen || ! mobileNav) {
        return;
    }

    const focusableElements = [...mobileNav.querySelectorAll('a, button')];
    const firstElement = focusableElements.at(0);
    const lastElement = focusableElements.at(-1);

    if (event.shiftKey && document.activeElement === firstElement) {
        event.preventDefault();
        lastElement?.focus();
    } else if (! event.shiftKey && document.activeElement === lastElement) {
        event.preventDefault();
        firstElement?.focus();
    }
});

window.addEventListener('resize', () => {
    if (window.innerWidth >= 1024) {
        setMobileNavOpen(false);
    }
});

document.querySelectorAll('[data-submit-feedback]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        const button = event.submitter;
        if (button) {
            if (button.hasAttribute('formaction')) {
                form.action = button.formAction;
            }
            button.dataset.originalText = button.textContent;
            button.disabled = true;
            button.textContent = button.dataset.pendingText ?? 'Menyimpan…';
        }
        form.setAttribute('aria-busy', 'true');
    });
});

document.querySelectorAll('[data-dialog-open]').forEach((trigger) => {
    const dialog = document.getElementById(trigger.dataset.dialogOpen);
    const fallback = document.querySelector(`[data-dialog-fallback="${trigger.dataset.dialogOpen}"]`);
    if (! dialog || typeof dialog.showModal !== 'function') {
        return;
    }
    const content = fallback?.querySelector('[data-dialog-form], [data-rejection-form]');
    if (content) {
        dialog.querySelector('[data-dialog-content]')?.append(content);
    }
    fallback?.classList.add('hidden');
    trigger.addEventListener('click', (event) => {
        event.preventDefault();
        dialog.showModal();
    });
    const closeButton = dialog.querySelector('[data-dialog-close]');
    if (closeButton) {
        closeButton.hidden = false;
        closeButton.classList.remove('hidden');
        closeButton.addEventListener('click', () => dialog.close());
    }
    if (dialog.hasAttribute('data-auto-open')) {
        dialog.showModal();
    }
});

window.addEventListener('pageshow', () => {
    document.querySelectorAll('[data-submit-feedback][aria-busy="true"]').forEach((form) => {
        form.removeAttribute('aria-busy');
        form.querySelectorAll('button[type="submit"]').forEach((button) => {
            button.disabled = false;
            if (button.dataset.originalText) {
                button.textContent = button.dataset.originalText;
            }
        });
    });
});
