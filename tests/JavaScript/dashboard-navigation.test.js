import test from 'node:test';
import assert from 'node:assert/strict';
import { setupDashboardNavigation } from '../../resources/js/dashboard-navigation.js';

function fixture(width = 390) {
    const document = { activeElement: null, listeners: {}, addEventListener(type, callback) { this.listeners[type] = callback; } };
    const element = () => ({ attributes: {}, listeners: {}, inert: false, hidden: false,
        classList: { values: new Set(), toggle(name, on) { if (on) this.values.add(name); else this.values.delete(name); } },
        setAttribute(name, value) { this.attributes[name] = value; }, removeAttribute(name) { delete this.attributes[name]; },
        addEventListener(type, callback) { this.listeners[type] = callback; }, getClientRects() { return [1]; },
        focus() { document.activeElement = this; }, click() { this.listeners.click?.(); },
    });
    const panel = element(), trigger = element(), backdrop = element(), content = element(), closeButton = element(), lastLink = element(), body = element();
    const controls = [closeButton, lastLink];
    panel.querySelectorAll = () => controls;
    panel.contains = (target) => controls.includes(target);
    const desktop = { matches: width >= 1024, addEventListener(type, callback) { this.change = callback; } };
    setupDashboardNavigation({ panel, trigger, backdrop, content, closeButton, body, document, desktop });
    const key = (value, shiftKey = false) => {
        const event = { key: value, shiftKey, prevented: false, preventDefault() { this.prevented = true; } };
        document.listeners.keydown(event);
        return event;
    };
    return { panel, trigger, backdrop, content, closeButton, lastLink, body, document, desktop, key };
}

for (const width of [360, 390, 768]) {
    test(`closed mobile navigation at ${width}px is excluded from keyboard and accessibility navigation`, () => {
        const f = fixture(width);
        assert.equal(f.panel.inert, true);
        assert.equal(f.panel.attributes['aria-hidden'], 'true');
        assert.equal(f.content.inert, false);
        assert.equal(f.trigger.attributes['aria-expanded'], 'false');
    });
}

test('opening focuses the close control, contains keyboard focus, and Escape restores the trigger', () => {
    const f = fixture();
    f.trigger.click();
    assert.equal(f.document.activeElement, f.closeButton);
    assert.equal(f.panel.inert, false);
    assert.equal(f.content.inert, true);
    assert.equal(f.panel.attributes['aria-modal'], 'true');
    assert.equal(f.key('Tab', true).prevented, true);
    assert.equal(f.document.activeElement, f.lastLink);
    assert.equal(f.key('Tab').prevented, true);
    assert.equal(f.document.activeElement, f.closeButton);
    f.key('Escape');
    assert.equal(f.document.activeElement, f.trigger);
    assert.equal(f.panel.inert, true);
    assert.equal(f.content.inert, false);
    assert.equal(f.body.classList.values.has('overflow-hidden'), false);
});

test('close button and backdrop both restore navigation trigger focus', () => {
    const f = fixture();
    for (const control of [f.closeButton, f.backdrop]) {
        f.trigger.click(); control.click();
        assert.equal(f.document.activeElement, f.trigger);
        assert.equal(f.trigger.attributes['aria-expanded'], 'false');
    }
});

for (const width of [1024, 1280, 1440]) {
    test(`desktop navigation at ${width}px stays accessible with no modal or page scroll lock`, () => {
        const f = fixture(width);
        assert.equal(f.panel.inert, false);
        assert.equal(f.panel.attributes['aria-hidden'], undefined);
        f.trigger.click();
        assert.equal(f.panel.attributes['aria-modal'], undefined);
        assert.equal(f.content.inert, false);
    });
}

test('resizing from open mobile to desktop clears modal state, and returning mobile restores focus outside hidden panel', () => {
    const f = fixture();
    f.trigger.click();
    f.desktop.matches = true; f.desktop.change();
    assert.equal(f.panel.inert, false);
    assert.equal(f.panel.attributes['aria-modal'], undefined);
    assert.equal(f.content.inert, false);
    f.lastLink.focus();
    f.desktop.matches = false; f.desktop.change();
    assert.equal(f.document.activeElement, f.trigger);
    assert.equal(f.panel.inert, true);
});
