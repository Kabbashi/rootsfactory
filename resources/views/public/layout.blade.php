<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Roots Factory') — Roots Factory</title>
    <meta name="description" content="@yield('description', 'A grassroots think-tank for development cooperation — ideas, briefs and analysis with cited sources.')">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:400,600,700|inter:400,500,600" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        serif: ['Fraunces', 'Georgia', 'serif'],
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        root: {
                            50: '#f6f5f0', 100: '#e9e6d9', 600: '#6b6a3f',
                            700: '#54532f', 800: '#3d3c22', 900: '#2a2917',
                        },
                    },
                },
            },
        };
    </script>
    <style>
        .prose { line-height: 1.75; color: #2a2917; }
        .prose h1, .prose h2, .prose h3 { font-family: 'Fraunces', serif; font-weight: 600; line-height: 1.25; margin: 1.6em 0 .5em; }
        .prose h2 { font-size: 1.5rem; } .prose h3 { font-size: 1.25rem; }
        .prose p { margin: 0 0 1.1em; }
        .prose ul, .prose ol { margin: 0 0 1.1em 1.25em; } .prose li { margin: .25em 0; }
        .prose ul { list-style: disc; } .prose ol { list-style: decimal; }
        .prose a { color: #54532f; text-decoration: underline; text-underline-offset: 2px; }
        .prose blockquote { border-left: 3px solid #c9c6a8; padding-left: 1em; color: #54532f; font-style: italic; margin: 0 0 1.1em; }
        .prose code { background: #e9e6d9; padding: .1em .35em; border-radius: .25em; font-size: .9em; }
        .prose pre { background: #2a2917; color: #f6f5f0; padding: 1em; border-radius: .5em; overflow-x: auto; margin: 0 0 1.1em; }
        .prose pre code { background: none; padding: 0; }
        .prose hr { border: 0; border-top: 1px solid #d8d5c0; margin: 2em 0; }
    </style>
</head>
<body class="bg-root-50 font-sans text-root-900 antialiased">
    <header class="border-b border-root-100">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-5">
            <a href="{{ route('publications.index') }}" class="flex items-center gap-2 font-serif text-xl font-bold text-root-900">
                <span class="text-2xl">🌱</span> Roots Factory
            </a>
            <nav class="flex items-center gap-6 text-sm font-medium text-root-700">
                <a href="{{ route('publications.index') }}" class="hover:text-root-900">Publications</a>
                <a href="{{ route('ask') }}" class="hover:text-root-900">Ask</a>
                <a href="{{ url('/workspace') }}" class="rounded-full bg-root-800 px-4 py-2 text-root-50 hover:bg-root-900">Team workspace →</a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="mt-20 border-t border-root-100">
        <div class="mx-auto max-w-5xl px-6 py-10 text-sm text-root-600">
            <p class="font-serif text-base text-root-800">A workshop where ideas take root.</p>
            <p class="mt-2">Open source (MIT) · Built on the Wendland-IT / conceptnote ecosystem · Sign in with your conceptnote identity.</p>
        </div>
    </footer>
</body>
</html>
