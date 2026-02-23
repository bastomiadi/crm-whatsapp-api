<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Sessions Routes
    Route::get('/sessions', [DashboardController::class, 'sessions'])->name('dashboard.sessions');
    Route::post('/api/sessions/connect', [DashboardController::class, 'connectSession'])->name('api.sessions.connect');
    Route::post('/api/sessions/status', [DashboardController::class, 'getSessionStatus'])->name('api.sessions.status');
    Route::post('/api/sessions/qr', [DashboardController::class, 'getQrCode'])->name('api.sessions.qr');
    Route::post('/api/sessions/delete', [DashboardController::class, 'deleteSession'])->name('api.sessions.delete');

    // Messaging Routes
    Route::get('/messaging', [DashboardController::class, 'messaging'])->name('dashboard.messaging');
    Route::post('/api/send/text', [DashboardController::class, 'sendText'])->name('api.send.text');
    Route::post('/api/send/image', [DashboardController::class, 'sendImage'])->name('api.send.image');
    Route::post('/api/send/document', [DashboardController::class, 'sendDocument'])->name('api.send.document');
    Route::post('/api/send/audio', [DashboardController::class, 'sendAudio'])->name('api.send.audio');
    Route::post('/api/send/location', [DashboardController::class, 'sendLocation'])->name('api.send.location');
    Route::post('/api/send/contact', [DashboardController::class, 'sendContact'])->name('api.send.contact');
    Route::post('/api/send/poll', [DashboardController::class, 'sendPoll'])->name('api.send.poll');
    Route::post('/api/check-number', [DashboardController::class, 'checkNumber'])->name('api.check-number');

    // Bulk Messaging Routes
    Route::get('/bulk-messaging', [DashboardController::class, 'bulkMessaging'])->name('dashboard.bulk-messaging');
    Route::post('/api/bulk/text', [DashboardController::class, 'sendBulkText'])->name('api.bulk.text');
    Route::post('/api/bulk/image', [DashboardController::class, 'sendBulkImage'])->name('api.bulk.image');
    Route::post('/api/bulk/document', [DashboardController::class, 'sendBulkDocument'])->name('api.bulk.document');
    Route::post('/api/bulk/status', [DashboardController::class, 'getBulkJobStatus'])->name('api.bulk.status');
    Route::post('/api/bulk/jobs', [DashboardController::class, 'getBulkJobs'])->name('api.bulk.jobs');

    // Chat History Routes
    Route::get('/chat-history', [DashboardController::class, 'chatHistory'])->name('dashboard.chat-history');
    Route::post('/api/chats/overview', [DashboardController::class, 'getChatOverview'])->name('api.chats.overview');
    Route::post('/api/chats/messages', [DashboardController::class, 'getMessages'])->name('api.chats.messages');
    Route::post('/api/chats/info', [DashboardController::class, 'getChatInfo'])->name('api.chats.info');
    Route::post('/api/chats/mark-read', [DashboardController::class, 'markAsRead'])->name('api.chats.mark-read');
    Route::post('/api/contacts', [DashboardController::class, 'getContacts'])->name('api.contacts');
    Route::post('/api/profile-picture', [DashboardController::class, 'getProfilePicture'])->name('api.profile-picture');

    // Groups Routes
    Route::get('/groups', [DashboardController::class, 'groups'])->name('dashboard.groups');
    Route::post('/api/groups', [DashboardController::class, 'getGroups'])->name('api.groups');
    Route::post('/api/groups/create', [DashboardController::class, 'createGroup'])->name('api.groups.create');
    Route::post('/api/groups/metadata', [DashboardController::class, 'getGroupMetadata'])->name('api.groups.metadata');
    Route::post('/api/groups/participants/add', [DashboardController::class, 'addParticipants'])->name('api.groups.participants.add');
    Route::post('/api/groups/participants/remove', [DashboardController::class, 'removeParticipants'])->name('api.groups.participants.remove');
    Route::post('/api/groups/participants/promote', [DashboardController::class, 'promoteParticipants'])->name('api.groups.participants.promote');
    Route::post('/api/groups/participants/demote', [DashboardController::class, 'demoteParticipants'])->name('api.groups.participants.demote');
    Route::post('/api/groups/subject', [DashboardController::class, 'updateGroupSubject'])->name('api.groups.subject');
    Route::post('/api/groups/description', [DashboardController::class, 'updateGroupDescription'])->name('api.groups.description');
    Route::post('/api/groups/settings', [DashboardController::class, 'updateGroupSettings'])->name('api.groups.settings');
    Route::post('/api/groups/picture', [DashboardController::class, 'updateGroupPicture'])->name('api.groups.picture');
    Route::post('/api/groups/leave', [DashboardController::class, 'leaveGroup'])->name('api.groups.leave');
    Route::post('/api/groups/join', [DashboardController::class, 'joinGroup'])->name('api.groups.join');
    Route::post('/api/groups/invite-code', [DashboardController::class, 'getInviteCode'])->name('api.groups.invite-code');
    Route::post('/api/groups/revoke-invite', [DashboardController::class, 'revokeInviteCode'])->name('api.groups.revoke-invite');

    // WebSocket Routes
    Route::get('/websocket', [DashboardController::class, 'websocket'])->name('dashboard.websocket');
    Route::get('/api-docs', [DashboardController::class, 'apiDocs'])->name('dashboard.api-docs');
    Route::get('/api/websocket/stats', [DashboardController::class, 'getWebSocketStats'])->name('api.websocket.stats');

    // Profile Route
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    // CRM Routes
    require __DIR__ . '/crm.php';
});

// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
