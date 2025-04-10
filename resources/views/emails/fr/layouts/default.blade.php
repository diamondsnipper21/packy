<div lang="fr"
     style="background-color: white; color: #2b2b2b; font-family: 'Avenir Next', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; font-size: 18px; font-weight: 400; line-height: 28px; margin: 0 auto; max-width: 720px; padding: 40px 20px 40px 20px;"
     role="article"
     aria-label="Packie">
    <main>
        <div style="font-size: 1.10em; max-width: 500px; line-height: 2em; margin-left: auto; margin-right: auto;">
            @include('emails.fr.includes.header')
            @if(isset($title))
                <p style="text-align: center;">
                    <strong>
                        <span style="font-size: 20px;">{{ $title }}</span>
                    </strong><br/>
                </p>
            @endif
            <div style="background-color: #fff; padding: 10px;">
                <div style="font-size: 16px; line-height: 1.5em;">
                    @if(isset($communityLogo))
                        <div style="text-align: center;">
                            <img src="{{ $communityLogo }}"
                                 style="width: 50px; height: 50px; object-fit: cover; border: 1px solid #eee; border-radius: 10px; padding: 10px;"/>
                        </div>
                    @endif

                    @yield('content')

                    <br/><br/>
                    Si vous avez des questions, n'hésitez pas à <a href="mailto:{{ config('mail.support') }}">nous contacter</a>.
                    <br/><br/>
                    Merci,
                    <br/>
                    L'équipe Packie
                </div>
            </div>
        </div>
    </main>
    @include('emails.fr.includes.footer')
</div>