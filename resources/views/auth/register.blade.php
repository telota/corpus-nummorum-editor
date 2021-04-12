@extends('base.layout')

@section('content')
<div style="display: flex; justify-content: center; min-height: 100%; align-items: center">

    <div class="card">
        <div class="card-header text-center">Registration for CN Editor</div>

        <div class="card-body">
            <form method="POST" action="{{ route('register') }}">
                {{-- CSRF Token --}}
                @csrf

                {{-- Bot Defence (Honeypot) --}}
                <div class="h-field">
                    <label for="connection">Connection</label>
                    <input type="text" name="connection" id="connection" value="" />
                </div>

                <div class="form-group">

                    <div class="text-center">
                        <!-- Email -->
                        <div class="form-element">
                            <div class="form-label">
                                <label for="email">E-Mail Address</label>
                            </div>

                            <div>
                                <input id="email" type="email" class="form-field" name="email" value="{{ old('email') }}" required autofocus />

                                @if ($errors->has('email'))
                                <div class="validation-error">
                                    Error! {{ $errors->first('email') }}
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="form-element">
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
                        <div class="form-element">
                            <div class="form-label">
                                <label for="password-confirm">Confirm Password</label>
                            </div>

                            <div>
                                <input id="password-confirm" type="password" class="form-field" name="password_confirmation" required />
                            </div>
                        </div>
                    </div>

                <!-- Divider
                <div class="form-element" style="margin-top: 9px">
                    <hr />
                </div> -->

                    <div class="text-center">
                        <!-- Firstname -->
                        <div class="form-element">
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
                        <div class="form-element">
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
                        <div class="form-element">
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
                    </div>

                </div>

                <!-- Button -->
                <div class="form-element btn-group-center" style="margin-top: 20px">
                    <div>
                        <button type="submit" class="btn-tile btn-blue">
                            {{ __('Register') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
