@extends('base.layout')

@section('content')
<div style="display: flex; justify-content: center;">

    <div>
        <div class="card">
                <div class="card-header text-center">{{ __('Verify Your Email Address') }}</div>

                <div class="card-body">
                    {{ __('Before proceeding, please check your email for a verification link.') }}

                    @if (session('resent'))
                    <div class="alert alert-success" role="alert">
                        {{ __('A fresh verification link has been sent to your email address.') }}
                    </div>
                    @endif

                    <form method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn-tile btn-blue">Resend Link</button>.
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
