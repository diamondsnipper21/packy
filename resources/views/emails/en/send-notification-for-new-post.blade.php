@extends('emails.en.layouts.default')

@section('content')
    <div style="background-color: #fff; padding: 15px; border-radius: 5px; border: 1px solid #ddd; margin-top: 5px;">
        <div style="display: flex; align-items: center; background-color: #fff;">
            <img style="border-radius: 50%; width: 40px; height: 40px; object-fit: contain;" src="{{ $creatorPhoto }}">
            <strong style="font-size: 15px; margin-left: 15px;">{{ $creatorName }} (Admin)</strong>
        </div>

        <div style="font-size: 18px; font-weight: 600; line-height: 1.5em; margin-top: 15px; word-break: break-word;">
            {{ $postTitle }}
        </div>

        <div style="font-size: 16px; margin-top: 15px; white-space: pre-wrap; word-break: break-word;">{!! $postContent !!}</div>

        <?php
        $mediaPath = '';
        $mediaType = '';
        $mediaExt = '';
        $mediaName = '';
        if (!empty($firstMedia)) {
            $mediaPath = $firstMedia['path'];
            $mediaType = $firstMedia['type'];
            $mediaExt = $firstMedia['ext'];
            $mediaName = $firstMedia['filename'];
        }
        ?>

        @if(!empty($mediaPath))
            @if($mediaType == 'image')
                <div style="font-size: 16px; line-height: 1.5em; margin-top: 15px;">
                    <a href="<?php echo strip_tags($inviteLink); ?>" target="_blank">
                        <img style="border-radius: 5px; width: 100%; object-fit: contain;" src="{{ $mediaPath }}">
                    </a>
                </div>
            @elseif($mediaType == 'audio')
                <div style="font-size: 16px; line-height: 1.5em; margin-top: 15px;">
                    <audio controls style="border-radius: 5px; width: 100%; object-fit: contain;">
                        @if($mediaExt == 'ogg')
                            <source src="{{ $mediaPath }}" type="audio/ogg" />
                        @else
                            <source src="{{ $mediaPath }}" type="audio/mpeg" />
                        @endif
                    </audio>
                </div>
            @elseif($mediaType == 'video')
                <div style="font-size: 16px; line-height: 1.5em; margin-top: 15px;">
                    @if(str_contains($mediaPath, 'youtube'))
                        <iframe
                                src="{{ 'https://www.youtube.com/embed/' + str_replace('youtube-', '', $mediaPath) + '?autoplay=0' + addtionalProp }}"
                                width="100%"
                                height="100%"
                                frameborder="0"
                        ></iframe>
                    @elseif(str_contains($mediaPath, 'vimeo'))
                        <iframe
                                src="{{ 'https://player.vimeo.com/video/' + str_replace('vimeo-', '', $mediaPath) + '?autoplay=0&loop=false' + addtionalProp }}"
                                width="100%"
                                height="100%"
                                frameborder="0"
                                allow="autoplay; fullscreen; picture-in-picture"
                                allowFullScreen
                        ></iframe>
                    @else
                        <video key="{{ $mediaPath }}" controls>
                            <source src="{{ $mediaPath }}" type="video/mp4" />
                        </video>
                    @endif
                </div>
            @elseif(!empty($mediaName))
                <div style="font-size: 16px; line-height: 1.5em; margin-top: 15px;">
                    <a
                            href="{{ $mediaPath }}"
                            style="font-weight: 500;"
                            target="_blank"
                    >
                        {{ $mediaName }}
                    </a>
                </div>
            @endif
        @endif
    </div>

    <div style="text-align: center; margin-top: 25px;">
        <a style="padding: 8px 15px; background-color: #9198ff; color: #ffffff; font-weight: 700; text-transform: uppercase; border-radius: 50px; text-decoration: none; font-size: 16px;" href="<?php echo strip_tags($inviteLink); ?>" target="_blank">
            View content
        </a>
    </div>
@stop
