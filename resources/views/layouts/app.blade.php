<!DOCTYPE html>
@include('partials.head')

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <body class="font-sans antialiased">

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('components.alerts')
            @include('partials.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="dark:bg-gray-800 shadow">
                    <div class="max-w-8xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
