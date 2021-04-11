@extends('base.layout')

@section('content')

<div style="display: flex; justify-content: center;">

    <!-- Card -->
    <div class="card">
        <!-- Card Header -->
        <div class="card-header text-center text-uppercase">{{ __('Login') }}</div>

        <!-- Card Body -->
        <div class="card-body">
            <form method="POST" action="{{ route('login') }}">
                {{-- CSRF Token --}}
                @csrf

                {{-- Bot Defence (Honeypot) --}}
                <!-- Connection Type -->
                <div class="h-field">
                    <label for="connection">Connection</label>
                    <input type="text" name="connection" id="connection" value="" />
                </div>

                @if (!empty($showRedirect))
                {{-- Anchor won't be send to server, so we have to extract it using JS --}}
                <!-- Redirect -->
                <div class="h-field">
                    <label for="redirect-to">Redirect to</label>
                    <input type="text" name="redirect-to" id="redirect-to" value="" />

                    <script>
                        const url = window.location.href.split('#');
                        url.shift();
                        if (url[0]) {
                            document.getElementById("redirect-to").value = url.join('');
                        }
                    </script>
                </div>
                @endif

                <!-- Email -->
                <div class="form-group row">
                    <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                    <div class="col-md-6">
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            autofocus
                        >

                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group row">
                    <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                    <div class="col-md-6">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                        >

                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <!-- Remember -->
                <div class="form-group row">
                    <div class="col-md-6 offset-md-4">
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="remember"
                                id="remember"
                                {{ old('remember') ? 'checked' : '' }}
                            >

                            <label class="form-check-label" for="remember">
                                {{ __('Remember Me') }}
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Forgotten -->
                <div class="form-group row mb-0">
                    <div class="col-md-8 offset-md-4">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Login') }}
                        </button>

                        @if (Route::has('password.request'))
                            <a class="btn btn-link" href="{{ route('password.request') }}">
                                {{ __('Forgot Your Password?') }}
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
