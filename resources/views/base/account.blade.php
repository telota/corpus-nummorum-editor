@extends('base.layout')

@section('content')
<div style="display: flex; justify-content: center; min-height: 100%; align-items: center">

    <div class="card" style="width: 500px">
        <div class="card-body">
            @if ($level < 1)
            {{-- Account disabled --}}
            <div class="suspended">
                <p>This Account (ID {{ $user['ID'] }}) has been suspended/deleted.</p>
                <p>Please <a href="/contact" target="_blank">contact us</a> if you would like to join us again.</p>
            </div>
            @else

                <div class="card-header text-center" style="margin-top: -20px; margin-bottom: 20px">Your CN Account</div>

                {{-- Waiting for Approvial --}}
                @if ($level < 2)
                <div style="font-weight: 400; margin-bottom: 30px;">
                    <p>Thank you for your registration.<br/>
                        You will receive an Email as soon as your account has been activated.</p>
                    <p>Please <a href="/contact" target="_blank">contact us</a> if you have any further questions.</p>
                </div>
                @endif

                <table>
                    @foreach ($user as $key => $value)
                    <tr>
                        <td class="td-key">{{ $key }}:</td>
                        <td>{{ $value }}</td>
                    </tr>
                    @endforeach
                </table>

                <div style="font-size: 9pt; margin-top: 30px; margin-bottom: -10px">
                    <p>The data mentioned above are mandatory for the technical operation of the CN Editor.<br/>
                        We do not collect any additional personal data (<a href="/data-protection" target="_blank">our Data Protection Policy</a>).</p>
                    <p>Please provide your UserID or Email when <a href="/contact" target="_blank">contacting us</a> for questions.</p>
                </div>
            @endif
        </div>
    </div>

</div>

<style scoped>
    td {
        padding: 0 20px 10px 0
    }
    .suspended {
        font-weight: 400;
        font-size: 16pt;
        color: red;
        text-align: center;
        margin-top: 15px
    }
    .td-key {
        font-weight: 600;
        text-transform: capitalize
    }
</style>

@endsection
