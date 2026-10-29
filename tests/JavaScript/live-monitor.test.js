import test from 'node:test';
import assert from 'node:assert/strict';
import { LiveMonitor } from '../../resources/js/live-monitor.js';

const flush = () => new Promise((resolve) => setImmediate(resolve));
const fixture = (options = {}) => {
    const timers = new Map(); let id = 0; let reads = 0;
    const statuses = []; const snapshots = [];
    const clock = { setTimeout: (fn) => { timers.set(++id, fn); return id; }, clearTimeout: (key) => timers.delete(key) };
    const monitor = new LiveMonitor({ fetchSnapshot: async () => { reads++; return { percent: 50 }; }, onSnapshot: (data) => snapshots.push(data), onStatus: (state) => statuses.push(state), clock, ...options });
    const tick = async () => { const [key, fn] = timers.entries().next().value; timers.delete(key); await fn(); await flush(); };
    return { monitor, timers, statuses, snapshots, tick, reads: () => reads };
};

test('offline fallback reads HTTP immediately and continues at the fallback interval', async () => {
    const f = fixture(); f.monitor.start(); await flush();
    assert.equal(f.reads(), 1); assert.equal(f.timers.size, 1);
    await f.tick(); assert.equal(f.reads(), 2); assert.equal(f.statuses.at(-1), 'Offline fallback');
});
test('connected transport cancels polling but notifications still refresh source', async () => {
    const f = fixture(); f.monitor.start(); await flush(); f.monitor.setConnected(true); await flush();
    assert.equal(f.timers.size, 0); assert.equal(f.statuses.at(-1), 'Connected');
    await f.monitor.refresh(); assert.equal(f.reads(), 3);
});
test('hidden tab does not issue polling requests', async () => {
    const f = fixture({ visible: () => false }); f.monitor.start(); await flush(); await f.tick(); assert.equal(f.reads(), 0);
});
test('lost authorization stops polling instead of retrying private data', async () => {
    const f = fixture({ fetchSnapshot: async () => { throw Object.assign(new Error('expired'), { status: 403 }); } });
    f.monitor.start(); await flush(); assert.equal(f.timers.size, 0); assert.equal(f.statuses.at(-1), 'Akses tidak tersedia'); assert.equal(f.snapshots.length, 0);
});
test('HTTP outage recovers from polling and stops polling when transport still works', async () => {
    let down = true;
    const f = fixture({ fetchSnapshot: async () => { if (down) throw new Error('offline'); return { percent: 100 }; } });
    f.monitor.setConnected(true); await flush(); assert.equal(f.statuses.at(-1), 'Offline fallback'); assert.equal(f.timers.size, 1);
    down = false; await f.tick(); assert.equal(f.statuses.at(-1), 'Connected'); assert.equal(f.timers.size, 0); assert.equal(f.snapshots[0].percent, 100);
});
test('overlapping updates do not issue parallel snapshot requests', async () => {
    let complete; let reads = 0;
    const f = fixture({ fetchSnapshot: () => { reads++; return new Promise((resolve) => { complete = resolve; }); } });
    f.monitor.start(); void f.monitor.refresh(); void f.monitor.refresh(); assert.equal(reads, 1);
    complete({ percent: 70 }); await flush(); assert.equal(f.snapshots[0].percent, 70);
    f.monitor.stop(); assert.equal(f.timers.size, 0);
});
