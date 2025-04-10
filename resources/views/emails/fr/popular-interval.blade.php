<div
    lang="fr"
    style="background-color: white; color: #2b2b2b; font-family: 'Avenir Next', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; font-size: 18px; font-weight: 400; line-height: 28px; margin: 0 auto; max-width: 720px; padding: 40px 20px 40px 20px;"
    role="article"
    aria-label="Packie"
>
    <main>
        <div style="font-size: 1.10em; max-width: 500px; line-height: 2em; margin-left: auto; margin-right: auto;">
            <div style="text-align: right;">
                <img style="display: inline-block;" src="https://packie.io/assets/logo/packie-logo.png" alt="" width="120px" />
            </div>
            <p>
                <strong>
                    <span style="font-size: 18px;">
                        {{ $title }}
                    </span>
                </strong>
            </p>
            <div style="margin-bottom: 20px; font-size: 16px; line-height: 20px;">
                {{ $date }}
            </div>
            @foreach($summary_infos as $summary_info)
                @if(!empty($summary_info['posts']))
                    <div style="padding: 10px;">
                        <div style="font-size: 18px; display: flex; align-items: center;">
                            @if(!empty($summary_info['communityLogo']))
                                <img style="border-radius: 50%; width: 40px; height: 40px; object-fit: contain; margin-right: 10px;" src="{{ $summary_info['communityLogo'] }}">
                            @endif
                            <strong>
                                {{ $summary_info['communityName'] }}
                            </strong>
                        </div>
                        <div style="background-color: #eee; padding: 5px; border: 1px solid #ddd; border-radius: 5px; margin-top: 5px;">
                            <?php
                                $i = 0;
                                $post_cnt = count($summary_info['posts']);
                            ?>
                            @foreach($summary_info['posts'] as $post)
                                <?php $i++; ?>
                                <div style="padding: 10px;  <?php echo ($i !== $post_cnt) ? 'border-bottom: 1px solid #ddd;' : ''; ?>">
                                    <div style="display: flex; align-items: center; line-height: 30px;">
                                        <img style="border-radius: 50%; width: 30px; height: 30px; object-fit: contain; margin-right: 10px;" src="{{ $post['gravatar'] }}">
                                        <strong style="font-size: 16px;">
                                            {{ $post['name'] }}
                                        </strong>
                                    </div>

                                    <a style="margin-top: 5px; font-size: 16px; font-weight: 500; line-height: 20px; word-break: break-all;" href="<?php echo strip_tags($post['link']); ?>" target="_blank">
                                        {{ $post['title'] }}
                                    </a>

                                    <div style="margin-top: 5px; font-size: 15px; line-height: 18px; word-break: break-all;">
                                        {!! $post['content'] !!}
                                    </div>

                                    <div style="margin-top: 5px; line-height: 15px; display: flex; align-items: center;">
                                        <img src="https://wolfeo.s3.eu-west-1.amazonaws.com/2024/3/20/thumb-up-1710946418.png" height="15" style="margin-right: 5px;" />
                                        <span style="font-size: 13px; margin-right: 15px;">
                                            {{ $post['likes'] }}
                                        </span>

                                        <img src="https://wolfeo.s3.eu-west-1.amazonaws.com/2024/3/20/chat-1710946737.png" height="15" style="margin-right: 5px;" />
                                        <span style="font-size: 13px; margin-right: 15px;">
                                            {{ $post['comments'] }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div style="text-align: right; margin-top: 10px; font-size: 16px;">
                            <a href="<?php echo strip_tags($summary_info['unsubscribeLink']); ?>" target="_blank">Se désabonner de {{ $summary_info['communityName'] }}</a>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
        <div style="font-size: 1em; max-width: 500px; line-height: 1.8em;"> </div>
    </main>
    @include('emails.fr.includes.footer')
</div>
