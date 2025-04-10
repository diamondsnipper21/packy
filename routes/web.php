<?php

use App\Http\Controllers\App\AppController;
use App\Http\Controllers\App\Billing\CommunityCheckoutController;
use App\Http\Controllers\App\Billing\CommunityPricesController;
use App\Http\Controllers\App\Billing\CommunityInvoicesController;
use App\Http\Controllers\App\Billing\CommunityPayoutsController;
use App\Http\Controllers\App\Chats\CommunityChatsController;
use App\Http\Controllers\App\Chats\CommunityChatsMessagesController;
use App\Http\Controllers\App\Chats\CommunityChatsUsersController;
use App\Http\Controllers\App\Classrooms\ClassroomsController;
use App\Http\Controllers\App\Classrooms\LessonsController;
use App\Http\Controllers\App\Classrooms\SetsController;
use App\Http\Controllers\App\CommunityCategoriesController;
use App\Http\Controllers\App\CommunityCenterController;
use App\Http\Controllers\App\CommunityCenterInviteController;
use App\Http\Controllers\App\CommunityCenterMediaController;
use App\Http\Controllers\App\CommunityController;
use App\Http\Controllers\App\CommunityEventsController;
use App\Http\Controllers\App\CommunityGroupsController;
use App\Http\Controllers\App\CommunityLinksController;
use App\Http\Controllers\App\CommunityNotificationsController;
use App\Http\Controllers\App\CommunityRulesController;
use App\Http\Controllers\App\CommunityStatsController;
use App\Http\Controllers\App\Connect\StripeController;
use App\Http\Controllers\App\ExtensionsController;
use App\Http\Controllers\App\LangController;
use App\Http\Controllers\App\LoginController;
use App\Http\Controllers\App\MediasController;
use App\Http\Controllers\App\Members\CommunityMembersController;
use App\Http\Controllers\App\Members\MembersController;
use App\Http\Controllers\App\Members\MemberSettingsController;
use App\Http\Controllers\App\Members\MemberSubscriptionsController;
use App\Http\Controllers\App\PlansController;
use App\Http\Controllers\App\Posts\CommunityPostsController;
use App\Http\Controllers\App\Posts\PostCommentsController;
use App\Http\Controllers\App\Posts\SchedulePostsController;
use App\Http\Controllers\App\ResetPasswordController;
use App\Http\Controllers\App\RewardLevelController;
use App\Http\Controllers\App\SignupController;
use App\Http\Controllers\App\SuperAdmin\SuperAdminController;
use App\Http\Controllers\App\SuperAdmin\SuperAdminPayoutsController;
use App\Http\Controllers\App\SuperAdmin\SuperAdminStatsController;
use App\Http\Controllers\App\UserController;
use App\Http\Controllers\App\PrivacyController;
use App\Http\Middleware\FrontApi\ActiveMemberPermission;
use App\Http\Middleware\FrontApi\ClassroomMiddleware;
use App\Http\Middleware\FrontApi\CourseSetMiddleware;
use App\Http\Middleware\FrontApi\LessonMiddleware;
use App\Http\Middleware\FrontApi\ManagerMemberPermission;
use App\Http\Middleware\FrontApi\UserAuthenticate;
use App\Http\Middleware\FrontApi\MemberPermission;
use App\Http\Middleware\FrontApi\PostMiddleware;
use App\Http\Middleware\FrontApi\ValidateUploadFile;
use App\Http\Middleware\SuperAdminAuthenticate;
use Illuminate\Support\Facades\Route;

# Home
Route::get('/', [AppController::class, 'home'])->name('home');

// Route::get('/testdev', [\App\Http\Controllers\App\Test\TestController::class, 'test'])->name('test');

# Login
Route::post('validate-url', [LoginController::class, 'validateUrl']);
Route::post('login', [LoginController::class, 'doLogin']);
Route::post('logout', [LoginController::class, 'doLogout']);

# Reset password
Route::post('send-reset-password', [ResetPasswordController::class, 'sendResetPassword']);
Route::get('reset-password', [ResetPasswordController::class, 'receiveResetPassword']);
Route::post('reset-password', [ResetPasswordController::class, 'resetPassword']);

# Signup
Route::post('signup', [SignupController::class, 'doSignup']);
Route::post('profile/complete', [UserController::class, 'completeProfile']);
Route::post('profile/update', [UserController::class, 'updateProfile']);

# Application health check status (Envoyer.io)
Route::get('health-check-status', [AppController::class, 'healthCheckStatus']);

# Privacy pages
Route::group(['prefix' => 'legal'], function () {
    Route::get('/', [PrivacyController::class, 'index']);
    Route::get('/rules', [PrivacyController::class, 'index']);
    Route::get('/terms', [PrivacyController::class, 'index']);
    Route::get('/cookies', [PrivacyController::class, 'index']);
    Route::get('/privacy', [PrivacyController::class, 'index']);
});

# Lang
Route::post('lang/update', [LangController::class, 'update']);

Route::post('invite/send', [CommunityCenterInviteController::class, 'send']);
Route::post('invite/get', [CommunityCenterInviteController::class, 'get']);
Route::post('invite-share-link/get', [CommunityCenterInviteController::class, 'getInviteShareLink']);

Route::get('auth/login', [CommunityCenterInviteController::class, 'tokenAuth']);
Route::get('auth/join', [CommunityCenterInviteController::class, 'tokenJoin']);
Route::get('join', [CommunityCenterInviteController::class, 'join']);

Route::get('unsubscribe-digest/{url}', [CommunityCenterController::class, 'unsubscribeDigest']);
Route::get('unread-notification/{id}', [CommunityNotificationsController::class, 'unreadNotificationHandler']);

# Client API
Route::group(['prefix' => 'c', 'as' => 'api'], function () {
    Route::get('files/{uuid}', [CommunityCenterMediaController::class, 'view']);
    Route::get('/communities/{communityUrl}', [CommunityController::class, 'view']);

    // Posts for All
    Route::get('/communities/{communityId}/posts', [CommunityPostsController::class, 'list']);
    Route::get('/communities/{communityId}/posts/{postUrl}', [CommunityPostsController::class, 'view']);

    // Member for All
    Route::get('/communities/{communityId}/member/{memberId}', [MembersController::class, 'getMember']);

    // Classrooms for All
    Route::get('/communities/{communityId}/classrooms', [ClassroomsController::class, 'list']);

    Route::group(['prefix' => '/communities/{communityId}/classrooms/{classroomId}', 'middleware' => [ClassroomMiddleware::class]], function () {
        Route::get('/', [ClassroomsController::class, 'view']);

        Route::group(['prefix' => '/sets/{setId}', 'middleware' => [CourseSetMiddleware::class]], function () {
            Route::get('/', [SetsController::class, 'view']);

            Route::group(['prefix' => '/lessons/{lessonId}', 'middleware' => [LessonMiddleware::class]], function () {
                Route::get('/', [LessonsController::class, 'view']);
            });
        });
    });

    // Community Member Settings
    Route::group(['prefix' => 'members', 'middleware' => [UserAuthenticate::class]], function () {
        Route::get('/communities', [MembersController::class, 'communities']);
    });

    Route::group(['prefix' => 'communities', 'middleware' => [UserAuthenticate::class]], function () {
        Route::get('/{communityUrl}/members/me', [MembersController::class, 'view']);
        Route::post('/', [CommunityController::class, 'store']);

        ////////// Active Member endpoints ////////////////
        Route::group(['prefix' => '{communityId}', 'middleware' => [ActiveMemberPermission::class]], function () {
            // Reward Level
            Route::get('/levels', [RewardLevelController::class, 'list']);

            // Community Posts
            Route::post('/posts', [CommunityPostsController::class, 'create']);

            Route::group(['prefix' => '/posts/{postId}', 'middleware' => [PostMiddleware::class]], function () {
                Route::put('/', [CommunityPostsController::class, 'update']);
                Route::post('/like', [CommunityPostsController::class, 'like']);
                Route::post('/vote-poll', [CommunityPostsController::class, 'votePoll']);
                Route::post('/approve', [CommunityPostsController::class, 'approve'])->middleware(ManagerMemberPermission::class);
                Route::post('/decline', [CommunityPostsController::class, 'decline'])->middleware(ManagerMemberPermission::class);
                Route::post('/closeFromPage', [CommunityPostsController::class, 'closeFromPage'])->middleware(ManagerMemberPermission::class);
                Route::delete('/', [CommunityPostsController::class, 'delete']);

                Route::post('/comments', [PostCommentsController::class, 'create']);
                Route::put('/comments/{commentId}', [PostCommentsController::class, 'update']);
                Route::post('/comments/{commentId}/like', [PostCommentsController::class, 'like']);
                Route::delete('/comments/{commentId}', [PostCommentsController::class, 'delete']);
            });

            // Classrooms for Member
            Route::group(['prefix' => '/classrooms/{classroomId}', 'middleware' => [ClassroomMiddleware::class]], function () {
                Route::group(['prefix' => '/sets/{setId}', 'middleware' => [CourseSetMiddleware::class]], function () {
                    Route::group(['prefix' => '/lessons/{lessonId}', 'middleware' => [LessonMiddleware::class]], function () {
                        Route::post('/complete', [LessonsController::class, 'complete']);
                    });
                });
            });
        });

        ////////////////// Admin User endpoints ////////////////////////////////////////////////////////////////////////////////////////
        // Community Admin Settings
        Route::group(['prefix' => '{communityId}', 'middleware' => [ManagerMemberPermission::class]], function () {
            Route::put('/', [CommunityController::class, 'update']);
            Route::post('/reactivate', [CommunityController::class, 'reactivate']);
            Route::post('/save-about-description', [CommunityController::class, 'saveAboutDescription']);

            Route::post('/media/save', [MediasController::class, 'save']);
            Route::post('/media/delete', [MediasController::class, 'delete']);
            Route::post('/media/update-order', [MediasController::class, 'updateOrder']);

            Route::get('/members/assignable', [MembersController::class, 'assignable']);

            Route::get('/plans', [PlansController::class, 'view']);
            Route::get('/plans/invoices', [PlansController::class, 'invoices']);
            Route::post('/plan/invoice/download', [PlansController::class, 'invoiceDownload']);
            Route::post('/plan/card/update', [PlansController::class, 'updateCard']);
            Route::delete('/plans', [PlansController::class, 'cancel']);

            // Admin Stats
            Route::get('/stats', [CommunityStatsController::class, 'get']);

            // Community Scheduled Posts
            Route::get('/schedule-posts', [SchedulePostsController::class, 'list']);
            Route::post('/schedule-posts', [SchedulePostsController::class, 'create']);
            Route::get('/schedule-posts/{postId}', [SchedulePostsController::class, 'view']);
            Route::put('/schedule-posts/{postId}', [SchedulePostsController::class, 'update']);
            Route::delete('/schedule-posts/{postId}', [SchedulePostsController::class, 'delete']);

            // Reward Level
            Route::get('/levels/{levelId}', [RewardLevelController::class, 'view']);
            Route::put('/levels/{levelId}', [RewardLevelController::class, 'update']);

            // Classrooms for Admin member
            Route::post('/classrooms', [ClassroomsController::class, 'create']);
            Route::group(['prefix' => '/classrooms/{classroomId}', 'middleware' => [ClassroomMiddleware::class]], function () {
                Route::put('/', [ClassroomsController::class, 'update']);
                Route::delete('/', [ClassroomsController::class, 'delete']);
                Route::post('/move', [ClassroomsController::class, 'move']);

                // Set
                Route::post('/sets', [SetsController::class, 'create']);
                Route::group(['prefix' => '/sets/{setId}', 'middleware' => [CourseSetMiddleware::class]], function () {
                    Route::put('/', [SetsController::class, 'update']);
                    Route::delete('/', [SetsController::class, 'delete']);
                    Route::post('/move', [SetsController::class, 'move']);

                    Route::post('/lessons', [LessonsController::class, 'create']);
                    Route::group(['prefix' => '/lessons/{lessonId}', 'middleware' => [LessonMiddleware::class]], function () {
                        Route::put('/', [LessonsController::class, 'update']);
                        Route::delete('/', [LessonsController::class, 'delete']);
                        Route::post('/move', [LessonsController::class, 'move']);
                        Route::post('/clone', [LessonsController::class, 'clone']);

                        Route::put('/resources/{resourceId}', [LessonsController::class, 'resource_update']);
                        Route::delete('/resources/{resourceId}', [LessonsController::class, 'resource_delete']);
                    });
                });
            });

            // Manager action routes for member
            Route::group(['prefix' => '/member'], function () {
                Route::post('/remove', [CommunityMembersController::class, 'remove']);
                Route::post('/request/approve', [CommunityMembersController::class, 'approve']);
                Route::post('/request/decline', [CommunityMembersController::class, 'decline']);
                Route::post('/save', [CommunityMembersController::class, 'store']);
                Route::post('/ban', [CommunityMembersController::class, 'ban']);
                Route::post('/subscription/cancel', [CommunityMembersController::class, 'cancelSubscription']);
            });

            // Get all lessons of this community
            Route::get('/lessons', [LessonsController::class, 'list']);
        });

        // Community Member Setting
        Route::group(['prefix' => '{communityId}/members/{memberId}', 'middleware' => [MemberPermission::class]], function () {
            Route::get('/', [MembersController::class, 'getCommunityMember']);
            Route::get('settings', [MemberSettingsController::class, 'view']);
            Route::post('settings', [MemberSettingsController::class, 'store']);
            Route::put('settings/api_key', [MemberSettingsController::class, 'generateApiKey']);
        });
    });
});

Route::get('language/{locale}', function ($locale) {
    app()->setLocale($locale);
    session()->put('locale', $locale);
    return redirect()->back();
});

# Stripe onboarding
Route::prefix('stripe')->group(function () {
    Route::get('refresh', [StripeController::class, 'refresh']);
    Route::get('return', [StripeController::class, 'return']);
    // Route::get('account/{accountId}/delete', [StripeController::class, 'deleteAccount']);
});

# Super admin area
Route::group([
    'prefix' => 'super-admin',
    'middleware' => [
        UserAuthenticate::class,
        SuperAdminAuthenticate::class
    ]
], function () {
    Route::get('/', [SuperAdminController::class, 'home']);
    Route::post('data/get', [SuperAdminController::class, 'getData']);
    Route::get('payouts', [SuperAdminPayoutsController::class, 'view']);
    Route::post('payouts/get', [SuperAdminPayoutsController::class, 'get']);
    Route::post('payout/complete', [SuperAdminPayoutsController::class, 'complete']);
    Route::post('payout/refuse', [SuperAdminPayoutsController::class, 'refuse']);
    Route::get('stats', [SuperAdminStatsController::class, 'view']);
    Route::post('stats/get', [SuperAdminStatsController::class, 'get']);
});

# Community
Route::get('{url}', [CommunityCenterController::class, 'index'])->name('community_index');
Route::get('{url}/ressources/{cid?}/{lesson?}/{lid?}', [CommunityCenterController::class, 'index'])->name('community_classrooms');
Route::get('{url}/calendar', [CommunityCenterController::class, 'index'])->name('community_calendar');
Route::get('{url}/member', [CommunityCenterController::class, 'index']);
Route::get('{url}/rankings', [CommunityCenterController::class, 'index'])->name('community_leaderboard');
Route::get('{url}/about', [CommunityCenterController::class, 'index']);
Route::get('{url}/start', [CommunityCenterController::class, 'index']);
Route::get('{url}/users', [CommunityCenterController::class, 'index']);
Route::get('{url}/{postUrl}', [CommunityCenterController::class, 'index']);

// @todo - add community/ prefix to all routes related to community objects
Route::post('get', [CommunityCenterController::class, 'getCommunity']);
Route::post('temp/save', [CommunityCenterController::class, 'saveTempCommunity']);
Route::post('tabs/save', [CommunityCenterController::class, 'saveTabs']);

Route::prefix('community')->group(function () {
    Route::post('price/create', [CommunityPricesController::class, 'create']);
    Route::post('price/save', [CommunityPricesController::class, 'save']);
    Route::post('notifications/save', [CommunityCenterController::class, 'saveNotifications']);
    Route::post('free-trial-days/update', [CommunityPricesController::class, 'updateFreeTrialDays']);

    Route::post('payout/ask', [CommunityPayoutsController::class, 'askForPayout']);
    Route::post('payouts/data', [CommunityPayoutsController::class, 'getPayoutsData']);
    Route::post('publish', [CommunityController::class, 'publish']);
    Route::post('plan/cancel', [CommunityController::class, 'cancelPlan']);

    Route::post('invoice-data/save', [CommunityInvoicesController::class, 'saveInvoiceData']);

    Route::prefix('subscription')->group(function () {
        Route::post('checkout', [CommunityCheckoutController::class, 'checkout']);
        Route::post('vat-rate/get', [CommunityCheckoutController::class, 'getVatRate']);
        Route::post('reactivate', [MemberSubscriptionsController::class, 'reactivate']);
        Route::post('card/update', [MemberSubscriptionsController::class, 'updateCard']);
        Route::post('invoice/download', [MemberSubscriptionsController::class, 'invoiceDownload']);
    });
});

# Events
Route::post('monthly-events/get', [CommunityEventsController::class, 'getMonthlyEvents']);
Route::post('event/get', [CommunityEventsController::class, 'getOne']);
Route::post('events/get', [CommunityEventsController::class, 'get']);
Route::post('event/save', [CommunityEventsController::class, 'saveEvent']);
Route::post('event/delete', [CommunityEventsController::class, 'deleteEvent']);

# Members
Route::post('member/join', [CommunityMembersController::class, 'join']);
Route::post('member/leave', [CommunityMembersController::class, 'leave']);
Route::post('members/get', [CommunityMembersController::class, 'get']);

# Rules
Route::post('rule/move-up', [CommunityRulesController::class, 'moveUp']);
Route::post('rule/move-down', [CommunityRulesController::class, 'moveDown']);
Route::post('rule/save', [CommunityRulesController::class, 'save']);
Route::post('rule/delete', [CommunityRulesController::class, 'delete']);

# Groups
Route::post('group/save', [CommunityGroupsController::class, 'save']);
Route::post('group/delete', [CommunityGroupsController::class, 'delete']);

# Categories
Route::post('category/save', [CommunityCategoriesController::class, 'save']);
Route::post('category/delete', [CommunityCategoriesController::class, 'delete']);

# Extension Plugins
Route::post('extensions/get', [ExtensionsController::class, 'get']);
Route::post('extension/get', [ExtensionsController::class, 'getOne']);
Route::post('extensions/save', [ExtensionsController::class, 'save']);
Route::post('extensions/delete', [ExtensionsController::class, 'delete']);

Route::post('autodm/save', [ExtensionsController::class, 'autoDmSave']);

# Links
Route::post('link/save', [CommunityLinksController::class, 'save']);
Route::post('link/delete', [CommunityLinksController::class, 'delete']);

# Medias
Route::post('media/upload', [CommunityCenterMediaController::class, 'upload'])->middleware(ValidateUploadFile::class);

Route::prefix('account')->group(function () {
    Route::get('profile', [CommunityCenterController::class, 'index']);
});

# Notifications
Route::post('notifications/get', [CommunityNotificationsController::class, 'get']);
Route::post('notification/mark-as-read', [CommunityNotificationsController::class, 'markAsRead']);

# Chats
Route::get('chat/{id}', [CommunityChatsController::class, 'chatHandler']);
Route::post('chat-detail/get', [CommunityChatsController::class, 'get']);
Route::post('chat/get', [CommunityChatsController::class, 'getChat']);

# Chat users
Route::post('chat-users/get', [CommunityChatsUsersController::class, 'get']);
Route::post('block-user', [CommunityChatsUsersController::class, 'block']);
Route::post('unblock-user', [CommunityChatsUsersController::class, 'unblock']);

# Chat messages
Route::post('chat-message/save', [CommunityChatsMessagesController::class, 'save']);
Route::post('chat-message/get', [CommunityChatsMessagesController::class, 'get']);
Route::post('chat/mark-all-as-read', [CommunityChatsMessagesController::class, 'markAllAsRead']);

# Profile
Route::get('view-profile/{tag}', [CommunityCenterController::class, 'viewProfile']);
Route::post('get-profile', [CommunityCenterController::class, 'getProfile']);

# Users
Route::post('users/get', [UserController::class, 'get']);
Route::get('user/get/{id}', [UserController::class, 'getOne']);
Route::get('user/log/{id}', [UserController::class, 'loginAsUser']);

# Two-factor authentication
Route::post('2fa/init', [\App\Http\Controllers\App\TwoFactorAuthenticationController::class, 'init']);
Route::post('2fa/check', [\App\Http\Controllers\App\TwoFactorAuthenticationController::class, 'check']);

# save visitors
Route::post('visitor/save', [UserController::class, 'registerVisitor']);
