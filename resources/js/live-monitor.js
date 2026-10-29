export class LiveMonitor {
    constructor({ fetchSnapshot, onSnapshot, onStatus, visible = () => true, clock = globalThis, interval = 20000 }) {
        Object.assign(this, { fetchSnapshot, onSnapshot, onStatus, visible, clock, interval });
        this.connected = false;
        this.transportConnected = false;
        this.stopped = false;
        this.busy = false;
        this.timer = null;
    }

    start() { this.stopped = false; this.setConnected(false); }

    setConnected(connected) {
        if (this.stopped) return;
        this.connected = connected;
        this.transportConnected = connected;
        this.clock.clearTimeout(this.timer);
        this.onStatus(connected ? 'Connected' : 'Offline fallback');
        void this.refresh();
        if (! connected) this.schedule();
    }

    reconnecting() {
        if (! this.stopped) this.onStatus('Reconnecting');
    }

    schedule() {
        this.clock.clearTimeout(this.timer);
        if (this.stopped || this.connected) return;
        this.timer = this.clock.setTimeout(async () => {
            await this.refresh();
            this.schedule();
        }, this.interval);
    }

    async refresh() {
        if (this.busy || this.stopped || ! this.visible()) return;
        this.busy = true;
        try {
            const snapshot = await this.fetchSnapshot();
            if (! this.stopped) {
                this.onSnapshot(snapshot);
                if (this.transportConnected && ! this.connected) {
                    this.connected = true;
                    this.clock.clearTimeout(this.timer);
                    this.onStatus('Connected');
                }
            }
        } catch (error) {
            if (error.status === 401 || error.status === 403 || error.status === 404) {
                this.stop();
                this.onStatus('Akses tidak tersedia');
            } else {
                this.onStatus('Offline fallback');
                // HTTP failure cannot leave an apparently Connected UI with no recovery.
                this.connected = false;
                this.schedule();
            }
        } finally { this.busy = false; }
    }

    stop() {
        this.stopped = true;
        this.clock.clearTimeout(this.timer);
    }
}
