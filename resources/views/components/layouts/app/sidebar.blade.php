<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Sezioni')" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="shopping-cart" :href="route('shops')" :current="request()->routeIs('shops')" wire:navigate>
                        {{ __('Piattaforme di vendita') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="shopping-cart" :href="route('sellers')" :current="request()->routeIs('sellers')" wire:navigate>
                        {{ __('Venditori') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="magnifying-glass" :href="route('pages')" :current="request()->routeIs('pages')" wire:navigate>
                        {{ __('Pagine') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="magnifying-glass" :href="route('sqs')" :current="request()->routeIs('sqs')" wire:navigate>
                        {{ __('Stringhe di ricerca') }}
                    </flux:navlist.item>
                </flux:navlist.group>


                @if (auth()->user()->isSuperadmin())
                <flux:navlist.group :heading="__('Superadmin')" class="grid">
                    <flux:navlist.item icon="building-office" :href="route('superadmin.tenants')" :current="request()->routeIs('superadmin.tenants')" wire:navigate>
                        {{ __('Gestione Tenants') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="users" :href="route('users-list')" :current="request()->routeIs('users-list')" wire:navigate>
                        {{ __('Lista utenti') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="newspaper" :href="route('admin.news')" :current="request()->routeIs('admin.news')" wire:navigate>
                        {{ __('Gestione News') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="tag" :href="route('admin.tracking-interests')" :current="request()->routeIs('admin.tracking-interests')" wire:navigate>
                        {{ __('Gestione Tracking Interests') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="link" :href="route('admin.tracking-interest-assignments')" :current="request()->routeIs('admin.tracking-interest-assignments')" wire:navigate>
                        {{ __('Assegnazioni Tracking Interest') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="arrow-up-tray" :href="route('csv-upload')" :current="request()->routeIs('csv-upload')" wire:navigate>
                        {{ __('Caricamento CSV') }}
                    </flux:navlist.item>
                </flux:navlist.group>
                @endif

                @if (auth()->user()->isTenantAdmin())
                <flux:navlist.group :heading="__('Tenant Admin')" class="grid">
                    <flux:navlist.item icon="tag" :href="route('tenant-admin.tracking-interests')" :current="request()->routeIs('tenant-admin.tracking-interests')" wire:navigate>
                        {{ __('Gestione Tracking Interest') }}
                    </flux:navlist.item>
                </flux:navlist.group>
                @endif

                @impersonating($guard = null)
                <flux:navlist.item icon="user" :href="route('impersonate.leave')" wire:navigate>
                    {{ __('Stop Impersonating') }}
                </flux:navlist.item>
                @endImpersonating
            </flux:navlist>



            <flux:spacer />

            <flux:navlist variant="outline">

            </flux:navlist>

            
                <livewire:tracking-interest-selector></livewire:tracking-interest-selector>
            

            <!-- Desktop User Menu -->
            <flux:dropdown position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>