import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { LiveMonitor } from './live-monitor';

const roots = [...document.querySelectorAll('[data-live-monitor]')];
let echo = null;
if (roots.length && import.meta.env.VITE_REVERB_APP_KEY) {
    try {
        window.Pusher = Pusher;
        echo = new Echo({
            broadcaster: 'reverb', key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
            wsPort: Number(import.meta.env.VITE_REVERB_PORT || 8080),
            wssPort: Number(import.meta.env.VITE_REVERB_PORT || 443),
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME || 'https') === 'https',
            enabledTransports: ['ws', 'wss'], disableStats: true,
            authEndpoint: '/broadcasting/auth',
            auth: { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', Accept: 'application/json' } },
        });
    } catch { echo = null; }
}

const getJson = async (url) => {
    const response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' }, cache: 'no-store', signal: AbortSignal.timeout(8000) });
    if (! response.ok) throw Object.assign(new Error('Status belum tersedia'), { status: response.status });
    return response.json();
};

const setText = (root, selector, text) => root.querySelectorAll(selector).forEach((node) => { node.textContent = text; });
const monitors = [];
roots.forEach((root) => {
    let runId;
    let refreshingFragment = false;
    let subscribed = false;
    let fallbackTimer;
    const showSnapshot = (snapshot) => {
        const progress = root.querySelector('[data-live-progress]');
        if (progress) {
            progress.setAttribute('aria-valuenow', String(snapshot.percent));
            root.querySelector('[data-live-progress-bar]').style.width = `${snapshot.percent}%`;
            setText(root, '[data-live-counts]', `${snapshot.submitted} / ${snapshot.expected} evaluasi · ${snapshot.percent}% · ${snapshot.disqualified} DQ`);
            document.querySelectorAll(`[data-live-summary-class="${snapshot.class_id}"]`).forEach((summary) => {
                setText(summary, '[data-summary-eligible]', snapshot.eligible);
                setText(summary, '[data-summary-assigned]', snapshot.assigned);
                setText(summary, '[data-summary-submitted]', `${snapshot.submitted} / ${snapshot.expected}`);
                setText(summary, '[data-summary-percent]', `${snapshot.percent}%`);
                summary.querySelector('[data-summary-progress]')?.setAttribute('aria-valuenow', String(snapshot.percent));
                const bar = summary.querySelector('[data-summary-bar]');
                if (bar) bar.style.width = `${snapshot.percent}%`;
            });
            const finalLink = root.querySelector('[data-live-final]');
            if (snapshot.result_url) { finalLink.href = snapshot.result_url; finalLink.hidden = false; }
            if (snapshot.registrations) snapshot.registrations.forEach((registration) => {
                if (registration.evaluation_status === 'SUBMITTED') {
                    document.querySelectorAll(`[data-live-evaluation-form="${registration.id}"] button[type="submit"]`).forEach((button) => { button.disabled = true; });
                }
                document.querySelectorAll(`[data-live-registration="${registration.id}"] [data-live-registration-status]`).forEach((node) => {
                    node.textContent = registration.status === 'DISQUALIFIED' ? 'Diskualifikasi · Tidak dapat dinilai lagi' : (registration.evaluation_status === 'SUBMITTED' ? 'Nilai Anda sudah dikirim' : 'Terverifikasi');
                });
                if (registration.status !== 'VERIFIED' || snapshot.status === 'FINALIZED') {
                    document.querySelectorAll(`[data-live-registration-form="${registration.id}"] button[type="submit"]`).forEach((button) => { button.disabled = true; });
                    setText(root, '[data-live-change]', 'Status peserta/kelas berubah. Penilaian tidak dapat dilanjutkan; nilai di formulir tidak dihapus.');
                    root.querySelector('[data-live-change]').hidden = false;
                }
            });
            Object.entries(snapshot.by_judge || {}).forEach(([id, counts]) => {
                document.querySelectorAll(`[data-live-judge-submitted="${id}"]`).forEach((node) => { node.textContent = `${counts.SUBMITTED || 0} / ${snapshot.eligible} sudah dikirim`; });
                document.querySelectorAll(`[data-live-judge-drafts="${id}"]`).forEach((node) => { node.textContent = `${counts.DRAFT || 0} draft tersimpan`; });
            });
            if (root.dataset.liveFragmentUrl && runId !== snapshot.ranking_run_id && ! refreshingFragment) {
                refreshingFragment = true;
                void getJson(root.dataset.liveFragmentUrl).then((fragment) => {
                    const target = document.querySelector(`[data-live-ranking="${root.dataset.liveClass}"]`);
                    if (target) target.innerHTML = fragment.html;
                    runId = fragment.run_id;
                    setText(root, '[data-live-change]', 'Tabel hasil telah diperbarui. Muat ulang halaman sebelum meninjau draw/finalisasi agar seluruh formulir mengikuti snapshot terbaru.');
                    root.querySelector('[data-live-change]').hidden = false;
                }).catch(() => {}).finally(() => { refreshingFragment = false; });
            }
        }
        if (snapshot.certificates) snapshot.certificates.forEach((entry) => {
            const card = document.querySelector(`[data-certificate-result="${entry.result_id}"]`);
            if (! card || ! entry.ready || card.dataset.ready === 'true') return;
            const pending = card.querySelector('[data-certificate-pending]');
            const actions = document.createElement('div');
            actions.className = 'mt-5 flex flex-wrap gap-3';
            [['Lihat / cetak', entry.preview_url], ['Unduh PDF', entry.download_url]].forEach(([label, url]) => {
                const link = document.createElement('a');
                link.textContent = label; link.href = url;
                link.className = 'inline-flex min-h-11 items-center rounded-xl bg-forest-700 px-4 py-3 text-sm font-bold text-white';
                if (label === 'Lihat / cetak') { link.target = '_blank'; link.rel = 'noopener'; }
                actions.append(link);
            });
            pending?.replaceWith(actions);
            card.dataset.ready = 'true';
        });
        setText(root, '[data-live-updated]', `Diperbarui ${new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Jakarta' }).format(new Date())} WIB`);
    };
    const monitor = new LiveMonitor({ fetchSnapshot: () => getJson(root.dataset.liveUrl), onSnapshot: showSnapshot,
        onStatus: (state) => {
            setText(root, '[data-live-status]', state);
            setText(root, '[data-live-description]', state === 'Connected' ? 'Pembaruan langsung aktif.' : state === 'Akses tidak tersedia' ? 'Akses berubah atau sesi berakhir. Muat ulang halaman untuk masuk kembali.' : 'Progres diperiksa otomatis setiap 20 detik saat koneksi langsung terputus.');
        }, visible: () => ! document.hidden,
    });
    monitor.start(); monitors.push(monitor);
    if (echo) {
        const channel = root.dataset.livePrivate === 'true' ? echo.private(root.dataset.liveChannel) : echo.channel(root.dataset.liveChannel);
        channel.subscribed(() => { subscribed = true; clearTimeout(fallbackTimer); monitor.setConnected(true); });
        channel.error(() => { subscribed = false; monitor.setConnected(false); });
        channel.listen(root.dataset.liveCertificate === 'true' ? '.certificate.ready' : '.class.updated', (message) => {
            if (root.dataset.liveCertificate === 'true') { const link = root.querySelector('[data-live-new-certificate]'); if (link) link.hidden = false; }
            if (root.dataset.liveCertificate === 'true' || Number(message.class_id) === Number(root.dataset.liveClass)) void monitor.refresh();
        });
        echo.connector.pusher.connection.bind('state_change', ({ current }) => {
            clearTimeout(fallbackTimer);
            if (current === 'connected' && subscribed) monitor.setConnected(true);
            else { subscribed = false; monitor.reconnecting(); fallbackTimer = setTimeout(() => monitor.setConnected(false), 4000); }
        });
    }
});
document.addEventListener('visibilitychange', () => { if (! document.hidden) monitors.forEach((monitor) => { void monitor.refresh(); }); });
window.addEventListener('pagehide', () => { monitors.forEach((monitor) => monitor.stop()); echo?.disconnect(); });
window.addEventListener('pageshow', (event) => {
    if (event.persisted) { monitors.forEach((monitor) => monitor.start()); echo?.connector.pusher.connect(); }
});
