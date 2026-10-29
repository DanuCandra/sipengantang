<?php

namespace App\View\Composers;

use App\Domain\Event\Queries\DashboardEventContext;
use App\Models\CompetitionClass;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardNavigationComposer
{
    public function __construct(private Request $request, private DashboardEventContext $context) {}

    public function compose(View $view): void
    {
        $area = $view->getData()['area'] ?? $view->getData()['attributes']?->get('area');
        $prefix = match ($area) {
            'Owner' => 'owner', 'Admin' => 'admin', 'Juri' => 'judge', default => 'participant'
        };
        $links = [$this->link('Beranda', $prefix.'.dashboard', [], $this->request->routeIs($prefix.'.dashboard'), 'home')];
        $context = ['events' => collect(), 'selected' => null];
        $section = $this->request->route('section');
        if ($area === 'Admin') {
            $context = $this->context->resolve($this->request);
            $links[] = $this->link('Semua Event', 'admin.events.index', [], $this->request->routeIs('admin.events.index'), 'event');
            foreach (['overview' => 'Ringkasan Event', 'classes' => 'Kelas Lomba', 'participants' => 'Peserta', 'payments' => 'Pembayaran', 'judges' => 'Juri', 'judging' => 'Monitor Penjurian', 'results' => 'Hasil & Piagam', 'prizes' => 'Hadiah', 'violations' => 'Pelanggaran', 'settings' => 'Pengaturan Event'] as $key => $label) {
                $active = $this->request->routeIs('admin.workspace') && $section === $key;
                $active = $active || match ($key) {
                    'overview' => $this->request->routeIs('admin.events.show'),
                    'classes' => $this->request->routeIs('admin.events.classes.show', 'admin.events.classes.create', 'admin.events.classes.edit') && $this->request->query('tab') !== 'prizes',
                    'participants' => $this->request->routeIs('admin.events.registrations.*'),
                    'payments' => $this->request->routeIs('admin.events.payments.*'),
                    'judges' => $this->request->routeIs('admin.events.classes.judges.*') && ! $this->request->boolean('monitor'),
                    'judging' => $this->request->routeIs('admin.events.classes.judges.*') && $this->request->boolean('monitor'),
                    'results' => $this->request->routeIs('admin.events.classes.ranking*'),
                    'prizes' => $this->request->query('tab') === 'prizes',
                    'violations' => $this->request->routeIs('admin.events.violations.*'),
                    'settings' => $this->request->routeIs('admin.events.edit'),
                };
                $links[] = $this->link($label, 'admin.workspace', ['section' => $key, ...($context['selected'] ? ['event_id' => $context['selected']->id] : [])], $active, $key);
            }
        } elseif ($area === 'Owner') {
            $links[] = $this->link('Event Saya', 'owner.events.index', [], $this->request->routeIs('owner.events.index', 'owner.events.show', 'owner.events.edit'), 'event');
            $links[] = $this->link('Buat Event', 'owner.events.create', [], $this->request->routeIs('owner.events.create'), 'add');
            $links[] = $this->link('Admin Event', 'owner.workspace', ['section' => 'admins'], $section === 'admins', 'judges');
            $links[] = $this->link('Pantau Penjurian', 'owner.workspace', ['section' => 'monitoring'], $section === 'monitoring', 'judging');
        } elseif ($area === 'Juri') {
            foreach (['assignments' => 'Penugasan', 'scoring' => 'Penilaian', 'history' => 'Riwayat Nilai'] as $mode => $label) {
                $active = $this->request->routeIs('judge.classes.index') && $this->request->query('view', 'assignments') === $mode;
                $links[] = $this->link($label, 'judge.classes.index', ['view' => $mode], $active, $mode === 'history' ? 'results' : 'classes');
            }
            $class = $this->request->route('competitionClass');
            if ($class instanceof CompetitionClass) {
                $links[] = $this->link('Peserta Kelas Ini', 'judge.classes.show', [$class], $this->request->routeIs('judge.classes.show', 'judge.evaluations.*'), 'participants');
            }
        } else {
            foreach (['events' => 'Event', 'birds' => 'Burung Saya', 'registrations' => 'Pendaftaran', 'certificates' => 'Piagam'] as $key => $label) {
                $links[] = $this->link($label, 'participant.'.$key.'.index', [], $this->request->routeIs('participant.'.$key.'.*'), $key);
            }
            $links[] = $this->link('Profil', 'participant.profile.edit', [], $this->request->routeIs('participant.profile.*'), 'profile');
        }
        $view->with('sidebar', ['links' => $links, 'dashboardRoute' => $prefix.'.dashboard', ...$context, 'section' => $section ?? 'classes']);
    }

    private function link(string $label, string $route, array $parameters, bool $active, string $icon): array
    {
        return ['label' => $label, 'url' => route($route, $parameters), 'active' => $active, 'icon' => $icon];
    }
}
