@extends('emails.en.layouts.default')

@section('content')
    <p><strong>{{ $title }}</strong></p>

    {{ $date }}
    <br /><br />
    {{ $desc }}
    <br /><br />
    Here’s a summary:
    <br /><br />

    @foreach($summaryInfos as $summaryInfo)
        @if(!empty($summaryInfo['unreads']))
            <div style="padding: 10px;">
                <div style="font-size: 17px; display: flex; align-items: center;">
                    @if(!empty($summaryInfo['communityLogo']))
                        <img style="border-radius: 50%; width: 40px; height: 40px; object-fit: contain; margin-right: 10px;" src="{{ $summaryInfo['communityLogo'] }}">
                    @endif
                    <strong>
                        {{ $summaryInfo['communityName'] }}
                    </strong>
                </div>
                <div style="background-color: #eee; padding: 5px; border: 1px solid #ddd; border-radius: 5px; margin-top: 5px;">
                        <?php

                        $i = 0;
                        $unreadCnt = count($summaryInfo['unreads']);
                        ?>

                    @foreach($summaryInfo['unreads'] as $unread)
                            <?php
                            $i++;

                            // Get member gravatar url
                            $gravatar = $unread['gravatar'];
                            if (empty($gravatar)) {
                                $gravatar = '/assets/img/default.png';
                            }

                            // Get member name
                            $name = $unread['name'];
                            $name = trim($name);

                            // Generate notification title
                            $notificationTitle = '';
                            if ($unread['type'] === 'notification') {
                                $objectType = $unread['object_type'];

                                if ($objectType === 'approved_to_join') {
                                    $notificationTitle = '<span style="font-size: 15px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;">' . $summaryInfo['communityName'] . '</span>&nbsp;<span style="font-size: 15px;">membership approved</span>';
                                } elseif ($objectType === 'declined_to_join') {
                                    $notificationTitle = '<span style="font-size: 15px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;">' . $summaryInfo['communityName'] . '</span>&nbsp;<span style="font-size: 15px;">membership declined</span>';
                                } elseif ($objectType === 'like_to_post') {
                                    $notificationTitle = '<span style="font-size: 15px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;">' . $name . '</span>&nbsp;<span style="font-size: 15px;">added \'like\' to your post</span>';
                                } elseif ($objectType === 'like_to_comment') {
                                    $notificationTitle = '<span style="font-size: 15px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;">' . $name . '</span>&nbsp;<span style="font-size: 15px;">added \'like\' to your comment</span>';
                                } elseif ($objectType === 'dislike_to_post') {
                                    $notificationTitle = '<span style="font-size: 15px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;">' . $name . '</span>&nbsp;<span style="font-size: 15px;">removed \'like\' from your post</span>';
                                } elseif ($objectType === 'dislike_to_comment') {
                                    $notificationTitle = '<span style="font-size: 15px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;">' . $name . '</span>&nbsp;<span style="font-size: 15px;">removed \'like\' from your comment</span>';
                                } elseif ($objectType === 'reply_to_post') {
                                    $notificationTitle = '<span style="font-size: 15px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;">' . $name . '</span>&nbsp;<span style="font-size: 15px;">replied to your post</span>';
                                } elseif ($objectType === 'reply_to_comment') {
                                    $notificationTitle = '<span style="font-size: 15px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;">' . $name . '</span>&nbsp;<span style="font-size: 15px;">replied to your comment</span>';
                                }
                            } else if ($unread['type'] === 'chat') {
                                $notificationTitle = '<span style="font-size: 15px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;">' . $name . '</span>&nbsp;<span style="font-size: 15px;">sent message to you</span>';
                            }

                            // Generate notification summary
                            $summary = '';
                            if ($unread['type'] === 'notification') {
                                $summary = $unread['summary'];
                            } else if ($unread['type'] === 'chat') {
                                $summary = $unread['content'];
                            }

                            // Generate notification time
                            $notificationTime = '';
                            $timeDifference = time() - strtotime($unread['created_at']);
                            if ($timeDifference < 1) {
                                $notificationTime = '1 second ago';
                            }
                            $condition = [ 12 * 30 * 24 * 60 * 60 =>  'year',
                                30 * 24 * 60 * 60       =>  'month',
                                24 * 60 * 60            =>  'day',
                                60 * 60                 =>  'hour',
                                60                      =>  'minute',
                                1                       =>  'second',
                            ];

                            foreach ($condition as $secs => $str) {
                                $d = $timeDifference / $secs;

                                if ($d >= 1) {
                                    $t = round($d);
                                    $notificationTime = $t . ' ' . $str . ( $t > 1 ? 's' : '' ) . ' ago';

                                    break;
                                }
                            }
                            ?>
                        <div style="padding: 10px;  <?php echo $i !== $unreadCnt ? 'border-bottom: 1px solid #ddd;' : ''; ?>">
                            <div style="display: flex; align-items: center; line-height: 36px;">
                                <img style="border-radius: 50%; width: 36px; height: 36px; object-fit: contain; margin-right: 10px;" src="{{ $gravatar }}">
                                <a style="cursor: pointer; text-decoration: underline; color: #2b2b2b;" href="<?php echo strip_tags($unread['redirectUrl']); ?>" target="_blank">
                                    {!! $notificationTitle !!}
                                </a>
                            </div>

                            <div style="padding-left: 40px;">
                                <div style="margin-top: 5px; font-weight: 500; line-height: 20px; font-size: 15px; width: 420px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    - {!! $summary !!}
                                </div>

                                <div style="margin-top: 5px; font-size: 14px; line-height: 18px; word-break: break-all; color: #7957d5;">
                                    - {{ $notificationTime }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="text-align: right; margin-top: 10px; font-size: 16px;">
                    <a href="<?php echo strip_tags($summaryInfo['manageNotificationLink']); ?>" target="_blank">Manage notifications in {{ $summaryInfo['communityName'] }}</a>
                </div>
            </div>
        @endif
    @endforeach
@stop