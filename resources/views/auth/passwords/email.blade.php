@extends('base.layout')

@section('content')
<div style="display: flex; justify-content: center;">

    <div>
        <div class="card">
            <div class="card-header text-center">{{ __('Reset Password') }}</div>

            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="form-element">
                        <label for="email" class="form-label">{{ __('E-Mail Address') }}</label>

                        <div>
                            <input
                            id="email"
                            type="email"
                            class="form-field"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email" a
                            utofocus
                            />

                            @error('email')
                            <div class="validation-error">
                                <strong>{{ $message }}</strong>
                            </div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-element btn-group-center" style="margin-top: 10px">
                        <div>
                            <button type="submit" class="btn-tile btn-blue">
                                Send Reset Link
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
