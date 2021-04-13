<div id="header">
    <div id="header-container">
        <!-- Left Section -->
        <div>
            <a href="/" class="header-link">Corpus Nummorum Data</a>
        </div>

        <!-- Right Section -->
        <div style="display: flex; flex-wrap: wrap;">
            @auth
                <a
                    href="/"
                    class="header-link"
                >
                    Start
                </a>
                <a
                    href="/editor"
                    class="header-link"
                >
                    Editor
                </a>
                <a
                    href="{{ route('logout') }}"
                    class="header-link"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                >
                    Logout
                </a>

                <form
                    id="logout-form"
                    action="{{ route('logout') }}"
                    method="POST"
                    style="display: none;"
                >
                    @csrf
                </form>
            @else
                @if (url()->current() !== env('APP_URL').'/login')
                    <a
                        href="{{ route('login') }}"
                        class="header-link"
                    >
                        Login
                    </a>
                @endif

                @if (url()->current() !== env('APP_URL').'/register')
                    <a
                        href="{{ route('register') }}"
                        class="header-link"
                    >
                        Register
                    </a>
                @endif
            @endauth
        </div>
    </div>
</div>

<style>
    #header {
        width: 100%;
        background-color: #272727;
        position: fixed;
        top: 0;
        z-index: 100;
        box-shadow: 2px 3px 5px rgba(0, 0, 0, 0.15);
        height: 40px;
    }

    #header-container {
        padding: 5px 10px 5px 10px;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        height: 30px;
    }

    .header-link {
        text-transform: uppercase;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .1rem;
        margin-right: 15px;
    }
</style>
