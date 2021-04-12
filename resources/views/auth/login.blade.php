@extends('base.layout')

@section('content')
<div style="display: flex; justify-content: center; min-height: 100%; align-items: center">

    <!-- Login Card -->
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
                <div class="form-element">
                    <label for="email" class="form-label">{{ __('E-Mail Address') }}</label>

                    <div>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="form-field"
                            required
                            autocomplete="email"
                            autofocus
                        />

                        @error('email')
                        <div class="validation-error">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </div>

                <!-- Password -->
                <div class="form-element">
                    <label for="password" class="form-label">{{ __('Password') }}</label>

                    <div>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="form-field"
                            required
                            autocomplete="current-password"
                        />

                        @error('password')
                        <div class="validation-error">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </div>

                <!-- Remember -->
                <div class="form-group row">
                    <div class="col-md-6 offset-md-4">
                        <div class="form-check">
                            <input
                                type="checkbox"
                                name="remember"
                                id="remember"
                                {{ old('remember') ? 'checked' : '' }}
                            />

                            <label class="form-check-label" for="remember">
                                {{ __('Remember Me') }}
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Login -->
                <div class="form-element btn-group-center">
                    <div>
                        <button type="submit" class="btn-tile btn-blue">
                            {{ __('Login') }}
                        </button>
                    </div>
                </div>

                <!-- Forgotten -->
                <div class="form-group text-center" style="margin-bottom: -10px">
                    <a class="btn btn-link" href="{{ route('password.request') }}">
                        {{ __('Forgot Your Password?') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
