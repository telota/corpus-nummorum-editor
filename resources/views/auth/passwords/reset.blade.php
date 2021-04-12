@extends('base.layout')

@section('content')
<div style="display: flex; justify-content: center;">

    <div>
        <div class="card">
            <div class="card-header text-center">{{ __('Reset Password') }}</div>

            <div class="card-body">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf

                    <!-- Token -->
                    <input type="hidden" name="token" value="{{ $token }}">

                    <!-- Email -->
                    <div class="form-element">
                        <label for="email" class="form-label">{{ __('E-Mail Address') }}</label>

                        <div>
                            <input
                                id="email"
                                type="email"
                                class="form-fiel"
                                name="email"
                                value="{{ $email ?? old('email') }}"
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
                                class="form-field"
                                name="password"
                                required
                                autocomplete="new-password"
                            />

                            @error('password')
                            <div class="validation-error">
                                <strong>{{ $message }}</strong>
                            </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Confirm PW -->
                    <div class="form-element">
                        <label for="password-confirm" class="form-label">{{ __('Confirm Password') }}</label>

                        <div>
                            <input
                                id="password-confirm"
                                type="password"
                                class="form-control"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                            />
                        </div>
                    </div>

                    <!-- Btn -->
                    <div class="form-element btn-group-center" style="margin-top: 20px">
                        <div>
                            <button type="submit" class="btn-tile btn-blue">
                                {{ __('Reset Password') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
