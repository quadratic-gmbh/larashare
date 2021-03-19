<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Das Lastenrad</title>    

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">    
    <link rel="stylesheet" href="/fontawesome/css/all.min.css">        

    <!-- Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @stack('styles')
    @isset($custom_css)
    <link href="{{ asset($custom_css) }}" rel="stylesheet">
    @else    
    <link href="{{ asset('css/default.css') }}" rel="stylesheet">
    @endisset
    <meta name="google" content="notranslate">
</head>
<body>
    <div id="app">
        @if(!($hide_navigation ?? false))
          @include('navigation.nav')    
        @endif 
        <main class="d-flex flex-column">
          @include('user_warnings')
          <div class="py-4 flex-fill">
            <div class="@isset( $container_fluid ) container-fluid @else container @endisset">
            @yield('content')
            </div>          
          </div>
        </main>
        @include('navigation.footer')
    </div>
    
    <!-- Scripts -->    
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    @stack('scripts')
</body>
</html>
