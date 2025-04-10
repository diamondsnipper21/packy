<div lang="en"
     style="background-color: white; color: #2b2b2b; font-family: 'Avenir Next', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; font-size: 18px; font-weight: 400; line-height: 28px; margin: 0 auto; max-width: 720px; padding: 40px 20px 40px 20px;"
     role="article"
     aria-label="Packie">
    <main>
        <div style="font-size: 1.10em; max-width: 500px; line-height: 2em; margin-left: auto; margin-right: auto;">
            @include('emails.en.includes.header')
            @if(isset($title))
            <p style="text-align: center;">
                <strong>
                    <span style="font-size: 20px;">{{ $title }}</span>
                </strong><br />
            </p>
            @endif
            <div style="background-color: #fff; padding: 10px;">
                <div style="font-size: 16px; line-height: 1.5em;">
                    @yield('content')

                    <br /><br />
                    Need help? Email us <a href="mailto:{{ config('mail.support') }}" target="_blank">{{ config('mail.support') }}</a>
                    <br /><br />
                    The Packie Team
                </div>
            </div>
        </div>
    </main>
    @include('emails.en.includes.footer')
</div>