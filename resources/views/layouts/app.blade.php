<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @stack('head')
</head>
<body>
    <!-- Include FB JS SDK -->
    <div id="fb-root"></div>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <span style="font-size: 2rem; color: Mediumslateblue;">
                        <i class="fas fa-chart-bar"></i>
                        {{ config('app.name', 'Laravel') }}
                    </span>
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); logout_social();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @show
            <div class="container-fluid">
            <div class="row" style='z-index: 2000'>
                <div class="col-12">
                <alert
                :show="sharedState.state.alert.show"
                @update:close-alert="val => sharedState.state.alert.show = val"
                :msg="sharedState.state.alert.msg"
                :type="sharedState.state.alert.type"
                :fixed="sharedState.state.alert.fixed"
                :duration="sharedState.state.alert.duration"
                placement="top" width="100%" dismissable>
                </alert>
                </div>
            </div>
            @yield('content')
            <modal
            ref = "modal"
            :show="sharedState.state.modal.show"
            @update:close-modal="val => sharedState.state.modal.show = val"
            :title-icon = "sharedState.state.modal.titleIcon"
            :title = "sharedState.state.modal.title"
            :body="sharedState.state.modal.body"
            :ok-text='sharedState.state.modal.okText'
            :cancel-text="sharedState.state.modal.cancelText"
            :callback=sharedState.state.modal.callback
            :classes=sharedState.state.modal.classes
            :large=sharedState.state.modal.large
            :medium=sharedState.state.modal.medium
            :small=sharedState.state.modal.small>
            </modal>
            </div>
        </main>
    </div>
</body>
</html>
