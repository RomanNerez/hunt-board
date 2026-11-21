<!DOCTYPE html>
<html lang="uk" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hunt Board — твій помічник у пошуку роботи</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body class="h-full bg-slate-950 lg:overflow-hidden font-sans text-slate-100 antialiased">
    @php($loginUrl = filament()->getLoginUrl())
    @php($panelUrl = filament()->getUrl())

    <div class="relative isolate flex min-h-full flex-col overflow-hidden lg:h-screen">
        {{-- Background glow --}}
        <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80">
            <div class="relative left-1/2 aspect-[1155/678] w-[36rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-indigo-500 to-sky-400 opacity-25 sm:w-[72rem]"></div>
        </div>

        {{-- Header --}}
        <header class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-6 lg:px-8 lg:py-5">
            <a href="{{ url('/') }}" class="flex items-center gap-2 text-lg font-bold tracking-tight">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-500 text-white">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                </span>
                Hunt Board
            </a>

            @auth
                <a href="{{ $panelUrl }}" class="text-sm font-semibold text-slate-300 transition hover:text-white">Мій кабінет &rarr;</a>
            @else
                <a href="{{ $loginUrl }}" class="text-sm font-semibold text-slate-300 transition hover:text-white">Увійти &rarr;</a>
            @endauth
        </header>

        {{-- Hero --}}
        <main class="mx-auto flex min-h-0 w-full max-w-7xl flex-1 items-center px-6 py-16 lg:px-8 lg:py-4">
            <div class="grid w-full items-center gap-16 lg:grid-cols-2 lg:gap-12">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-indigo-500/10 px-3 py-1 text-sm font-medium text-indigo-300 ring-1 ring-inset ring-indigo-500/30">
                        <span class="h-2 w-2 rounded-full bg-indigo-400"></span>
                        Пошук роботи без хаосу
                    </span>

                    <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-white sm:text-6xl lg:mt-4 lg:text-5xl [@media(min-width:1280px)_and_(min-height:800px)]:text-6xl">
                        Знайди роботу мрії —
                        <span class="bg-gradient-to-r from-indigo-400 to-sky-400 bg-clip-text text-transparent">організовано</span>
                    </h1>

                    <p class="mt-6 max-w-xl text-lg leading-8 text-slate-300 lg:mt-4 lg:text-base lg:leading-7">
                        Hunt Board збирає всі твої вакансії, відгуки та співбесіди в одному місці.
                        Відстежуй кожен етап, не пропускай дедлайни й бач свій прогрес — щоб
                        пошук роботи став зрозумілим процесом, а не стресом.
                    </p>

                    <div class="mt-10 flex flex-wrap items-center gap-4 lg:mt-6">
                        @auth
                            <a href="{{ $panelUrl }}" class="rounded-lg bg-indigo-500 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-400">
                                Перейти до кабінету
                            </a>
                        @else
                            <a href="{{ $loginUrl }}" class="rounded-lg bg-indigo-500 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-400">
                                Увійти та почати
                            </a>
                        @endauth
                    </div>
                </div>

                {{-- Board preview --}}
                <div aria-hidden="true" class="hidden lg:block">
                    <div class="rounded-2xl bg-white/5 p-4 shadow-2xl ring-1 ring-white/10">
                        <div class="grid grid-cols-3 gap-3">
                            @foreach ([
                                ['Відгукнувся', 'bg-sky-400', [['Frontend Developer', 'Remote'], ['React Engineer', 'Київ']]],
                                ['Співбесіда', 'bg-amber-400', [['PHP Developer', 'Львів'], ['Laravel Engineer', 'Remote']]],
                                ['Оффер', 'bg-emerald-400', [['Backend Developer', 'Remote']]],
                            ] as [$column, $color, $cards])
                                <div class="rounded-xl bg-slate-900/80 p-3">
                                    <div class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        <span class="h-2 w-2 rounded-full {{ $color }}"></span>
                                        {{ $column }}
                                    </div>
                                    <div class="space-y-2">
                                        @foreach ($cards as [$title, $place])
                                            <div class="rounded-lg bg-slate-800 p-3 ring-1 ring-white/5">
                                                <p class="text-sm font-medium text-white">{{ $title }}</p>
                                                <p class="mt-1 text-xs text-slate-400">{{ $place }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="mx-auto w-full max-w-7xl px-6 py-8 text-sm text-slate-500 lg:px-8 lg:py-5">
            &copy; {{ date('Y') }} Hunt Board
        </footer>
    </div>
</body>
</html>
