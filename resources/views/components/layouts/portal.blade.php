<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
    <style>
        .portal-nav-inner,
        .portal-main {
            width: min(100% - 32px, 1440px);
            margin-inline: auto;
        }

        .portal-main {
            padding-block: 28px;
        }

        .portal-shell {
            min-height: 100vh;
            background:
                radial-gradient(circle at 18% 0%, rgba(0, 137, 203, .18), transparent 30rem),
                radial-gradient(circle at 82% 0%, rgba(254, 137, 38, .14), transparent 28rem),
                #09090b;
            color: #f4f4f5;
        }

        .portal-nav {
            position: sticky;
            top: 0;
            z-index: 50;
            border-bottom: 1px solid rgba(63, 63, 70, .75);
            background: rgba(9, 9, 11, .86);
            backdrop-filter: blur(16px);
        }

        .portal-nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding-block: 12px;
        }

        .portal-brand {
            display: flex;
            align-items: center;
            gap: 11px;
            min-width: 0;
            color: #ffffff;
            text-decoration: none;
        }

        .portal-brand-mark {
            display: grid;
            width: 38px;
            height: 38px;
            place-items: center;
            border-radius: 12px;
            background: linear-gradient(135deg, #0089CB, #22c55e);
            color: #ffffff;
            font-size: 15px;
            font-weight: 900;
            box-shadow: 0 10px 26px rgba(0, 137, 203, .28);
        }

        .portal-brand-title {
            display: block;
            color: #ffffff;
            font-size: 15px;
            font-weight: 850;
            line-height: 1.15;
        }

        .portal-brand-subtitle {
            display: block;
            margin-top: 2px;
            color: #a1a1aa;
            font-size: 12px;
            line-height: 1.15;
        }

        .portal-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }

        .portal-link,
        .portal-logout {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 36px;
            border: 1px solid transparent;
            border-radius: 10px;
            background: transparent;
            color: #d4d4d8;
            padding: 8px 11px;
            text-decoration: none;
            font: inherit;
            font-size: 13px;
            font-weight: 750;
            cursor: pointer;
            transition: background .16s ease, border-color .16s ease, color .16s ease, transform .16s ease;
        }

        .portal-link:hover,
        .portal-logout:hover,
        .portal-link-active {
            border-color: rgba(255, 255, 255, .10);
            background: rgba(255, 255, 255, .08);
            color: #ffffff;
        }

        .portal-link-primary {
            border-color: rgba(0, 137, 203, .55);
            background: #0089CB;
            color: #ffffff;
            box-shadow: 0 8px 20px rgba(0, 137, 203, .24);
        }

        .portal-link-primary:hover {
            border-color: rgba(0, 137, 203, .7);
            background: #0077b3;
        }

        .portal-icon {
            display: inline-grid;
            width: 18px;
            height: 18px;
            place-items: center;
            line-height: 1;
        }

        .portal-icon svg {
            width: 18px;
            height: 18px;
            stroke-width: 2;
        }

        .portal-user {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: 4px;
            padding-left: 12px;
            border-left: 1px solid rgba(255, 255, 255, .10);
        }

        .portal-avatar {
            display: grid;
            width: 34px;
            height: 34px;
            place-items: center;
            border-radius: 999px;
            background: #18181b;
            color: #e4e4e7;
            font-size: 12px;
            font-weight: 850;
            border: 1px solid rgba(255, 255, 255, .10);
        }

        .portal-menu {
            display: none;
            position: relative;
        }

        .portal-menu summary {
            list-style: none;
        }

        .portal-menu summary::-webkit-details-marker {
            display: none;
        }

        .portal-menu-button {
            display: inline-grid;
            width: 40px;
            height: 40px;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, .10);
            border-radius: 12px;
            background: rgba(255, 255, 255, .08);
            color: #ffffff;
            cursor: pointer;
        }

        .portal-menu-button svg {
            width: 22px;
            height: 22px;
            stroke-width: 2;
        }

        .portal-menu-panel {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            display: grid;
            gap: 8px;
            width: min(88vw, 320px);
            border: 1px solid rgba(255, 255, 255, .10);
            border-radius: 16px;
            background: rgba(24, 24, 27, .98);
            padding: 10px;
            box-shadow: 0 20px 48px rgba(0, 0, 0, .38);
        }

        .portal-menu-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 9px 12px;
            border-bottom: 1px solid rgba(255, 255, 255, .10);
            color: #e4e4e7;
        }

        .portal-menu-user strong {
            display: block;
            color: #ffffff;
            font-size: 13px;
            line-height: 1.2;
        }

        .portal-menu-user span:last-child {
            display: block;
            margin-top: 2px;
            color: #a1a1aa;
            font-size: 12px;
            line-height: 1.2;
        }

        .portal-menu-panel .portal-link,
        .portal-menu-panel .portal-logout {
            width: 100%;
            justify-content: flex-start;
        }

        @media (max-width: 760px) {
            .portal-nav-inner {
                flex-direction: row;
            }

            .portal-actions {
                display: none;
            }

            .portal-brand-subtitle {
                display: none;
            }

            .portal-menu {
                display: block;
            }
        }
    </style>
</head>
<body class="portal-shell antialiased">
    <nav class="portal-nav">
        <div class="portal-nav-inner">
            <a href="{{ route('portal.tickets.index') }}" wire:navigate class="portal-brand" aria-label="BUSRS portal home">
                <span class="portal-brand-mark">BU</span>
                <span>
                    <span class="portal-brand-title">BUSRS Portal</span>
                    <span class="portal-brand-subtitle">Requests and support</span>
                </span>
            </a>

            <div class="portal-actions" aria-label="Primary navigation">
                <a href="{{ route('offices.index') }}"
                   class="portal-link {{ request()->routeIs('offices.*') ? 'portal-link-active' : '' }}">
                    <span class="portal-icon"><x-heroicon-o-building-office-2 /></span>
                    <span>Offices</span>
                </a>
                <a href="{{ route('portal.tickets.index') }}" wire:navigate
                   class="portal-link {{ request()->routeIs('portal.tickets.index') || request()->routeIs('portal.tickets.show') ? 'portal-link-active' : '' }}">
                    <span class="portal-icon"><x-heroicon-o-inbox-stack /></span>
                    <span>My Requests</span>
                </a>
                <a href="{{ route('portal.tickets.create') }}" wire:navigate
                   class="portal-link portal-link-primary {{ request()->routeIs('portal.tickets.create') ? 'portal-link-active' : '' }}">
                    <span class="portal-icon"><x-heroicon-o-plus /></span>
                    <span>New Request</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="portal-logout">
                        <span class="portal-icon"><x-heroicon-o-arrow-right-start-on-rectangle /></span>
                        <span>Log out</span>
                    </button>
                </form>

                <div class="portal-user" aria-label="Signed in user">
                    <span class="portal-avatar">{{ auth()->user()?->initials() ?? 'U' }}</span>
                </div>
            </div>

            <details class="portal-menu">
                <summary class="portal-menu-button" aria-label="Open navigation menu">
                    <x-heroicon-o-bars-3 />
                </summary>

                <div class="portal-menu-panel" aria-label="Mobile navigation">
                    <div class="portal-menu-user">
                        <span class="portal-avatar">{{ auth()->user()?->initials() ?? 'U' }}</span>
                        <span>
                            <strong>{{ auth()->user()?->name }}</strong>
                            <span>Student portal</span>
                        </span>
                    </div>

                    <a href="{{ route('offices.index') }}"
                       class="portal-link {{ request()->routeIs('offices.*') ? 'portal-link-active' : '' }}">
                        <span class="portal-icon"><x-heroicon-o-building-office-2 /></span>
                        <span>Offices</span>
                    </a>

                    <a href="{{ route('portal.tickets.index') }}" wire:navigate
                       class="portal-link {{ request()->routeIs('portal.tickets.index') || request()->routeIs('portal.tickets.show') ? 'portal-link-active' : '' }}">
                        <span class="portal-icon"><x-heroicon-o-inbox-stack /></span>
                        <span>My Requests</span>
                    </a>

                    <a href="{{ route('portal.tickets.create') }}" wire:navigate
                       class="portal-link portal-link-primary {{ request()->routeIs('portal.tickets.create') ? 'portal-link-active' : '' }}">
                        <span class="portal-icon"><x-heroicon-o-plus /></span>
                        <span>New Request</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="portal-logout">
                            <span class="portal-icon"><x-heroicon-o-arrow-right-start-on-rectangle /></span>
                            <span>Log out</span>
                        </button>
                    </form>
                </div>
            </details>
        </div>
    </nav>

    @livewire('portal.onboarding-notice')

    <main class="portal-main">
        {{ $slot }}
    </main>
</body>
</html>
