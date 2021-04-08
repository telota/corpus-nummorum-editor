@extends('base.layout')

@section('content')

<div style="display: flex; justify-content: center;">

    <div class="card">
        <div class="card-header">{{ __('Register') }}</div>

        <div class="card-body">
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Affiliation -->
                <div class="register-affiliation">
                    <label for="affiliation">Affiliation</label>
                    <input type="text" name="affiliation" id="affiliation" value="" />
                </div>

                <!-- Email -->
                <div class="form-group">
                    <div class="form-label">
                        <label for="email">E-Mail Address</label>
                    </div>

                    <div>
                        <input id="email" type="email" class="form-field" name="email" value="{{ old('email') }}" required autofocus />

                    @if (!$errors->has('email'))
                        <div class="validation-error">
                            Error! {{ $errors->first('email') }}
                        </div>
                    @endif
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <div class="form-label">
                        <label for="password">Password</label>
                    </div>

                    <div>
                        <input id="password" type="password" class="form-field" name="password" required />

                    @if ($errors->has('password'))
                        <div class="validation-error">
                            Error! {{ $errors->first('password') }}
                        </div>
                    @endif
                    </div>
                </div>

                <!-- Password Confirm -->
                <div class="form-group">
                    <div class="form-label">
                        <label for="password-confirm">Confirm Password</label>
                    </div>

                    <div>
                        <input id="password-confirm" type="password" class="form-field" name="password_confirmation" required />
                    </div>
                </div>

                <div class="form-group">
                    <hr />
                </div>

                <!-- Firstname -->
                <div class="form-group">
                    <div class="form-label">
                        <label for="firstname">Firstname</label>
                    </div>

                    <div>
                        <input id="firstname" type="text" class="form-field" name="firstname" value="{{ old('firstname') }}" required />

                    @if ($errors->has('firstname'))
                        <div class="validation-error">
                            Error! {{ $errors->first('firstname') }}
                        </div>
                    @endif
                    </div>
                </div>

                <!-- Lastname -->
                <div class="form-group">
                    <div class="form-label">
                        <label for="lastname">Lastname</label>
                    </div>

                    <div>
                        <input id="lastname" type="text" class="form-field" name="lastname" value="{{ old('lastname') }}" required />

                    @if ($errors->has('lastname'))
                        <div class="validation-error">
                            Error! {{ $errors->first('lastname') }}
                        </div>
                    @endif
                    </div>
                </div>

                <!-- Country -->
                <div class="form-group">
                    <div class="form-label">
                        <label for="country">Country</label>
                    </div>

                    <div>
                        <input id="country" type="text" class="form-field" name="country" value="{{ old('country') }}" required />

                    @if ($errors->has('country'))
                        <div class="validation-error">
                            Error! {{ $errors->first('country') }}
                        </div>
                    @endif
                    </div>
                </div>

                <!-- Button -->
                <div class="form-group">
                    <div>
                        <button type="submit" class="btn btn-primary">
                            {{ __('Register') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection
