<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Webhook for Chatery (no auth required)
Route::post('/webhook/chatery', [WebhookController::class, 'handle']);

// Public routes - create API token (no middleware, allow guest)
Route::post('/create-token', [AuthController::class, 'createApiToken'])->middleware('guest:sanctum');

// Protected routes - require valid API token
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (\Illuminate\Http\Request $request) {
        return $request->user();
    });

    // Sessions Routes
    Route::post('/sessions/connect', [ApiController::class, 'connectSession']);
    Route::post('/sessions/status', [ApiController::class, 'getSessionStatus']);
    Route::post('/sessions/qr', [ApiController::class, 'getQrCode']);
    Route::post('/sessions/delete', [ApiController::class, 'deleteSession']);

    // Messaging Routes
    Route::post('/send/text', [ApiController::class, 'sendText']);
    Route::post('/send/image', [ApiController::class, 'sendImage']);
    Route::post('/send/document', [ApiController::class, 'sendDocument']);
    Route::post('/send/audio', [ApiController::class, 'sendAudio']);
    Route::post('/send/location', [ApiController::class, 'sendLocation']);
    Route::post('/send/contact', [ApiController::class, 'sendContact']);
    Route::post('/send/poll', [ApiController::class, 'sendPoll']);
    Route::post('/check-number', [ApiController::class, 'checkNumber']);

    // Bulk Messaging Routes
    Route::post('/bulk/text', [ApiController::class, 'sendBulkText']);
    Route::post('/bulk/image', [ApiController::class, 'sendBulkImage']);
    Route::post('/bulk/document', [ApiController::class, 'sendBulkDocument']);
    Route::post('/bulk/status', [ApiController::class, 'getBulkJobStatus']);
    Route::post('/bulk/jobs', [ApiController::class, 'getBulkJobs']);

    // Chat History Routes
    Route::post('/chats/overview', [ApiController::class, 'getChatOverview']);
    Route::post('/chats/messages', [ApiController::class, 'getMessages']);
    Route::post('/chats/info', [ApiController::class, 'getChatInfo']);
    Route::post('/chats/mark-read', [ApiController::class, 'markAsRead']);
    Route::post('/contacts', [ApiController::class, 'getContacts']);
    Route::post('/profile-picture', [ApiController::class, 'getProfilePicture']);

    // Groups Routes
    Route::post('/groups', [ApiController::class, 'getGroups']);
    Route::post('/groups/create', [ApiController::class, 'createGroup']);
    Route::post('/groups/metadata', [ApiController::class, 'getGroupMetadata']);
    Route::post('/groups/participants/add', [ApiController::class, 'addParticipants']);
    Route::post('/groups/participants/remove', [ApiController::class, 'removeParticipants']);
    Route::post('/groups/participants/promote', [ApiController::class, 'promoteParticipants']);
    Route::post('/groups/participants/demote', [ApiController::class, 'demoteParticipants']);
    Route::post('/groups/subject', [ApiController::class, 'updateGroupSubject']);
    Route::post('/groups/description', [ApiController::class, 'updateGroupDescription']);
    Route::post('/groups/settings', [ApiController::class, 'updateGroupSettings']);
    Route::post('/groups/picture', [ApiController::class, 'updateGroupPicture']);
    Route::post('/groups/leave', [ApiController::class, 'leaveGroup']);
    Route::post('/groups/join', [ApiController::class, 'joinGroup']);
    Route::post('/groups/invite-code', [ApiController::class, 'getInviteCode']);
    Route::post('/groups/revoke-invite', [ApiController::class, 'revokeInviteCode']);

    // WebSocket Routes
    Route::get('/websocket/stats', [ApiController::class, 'getWebSocketStats']);
});
