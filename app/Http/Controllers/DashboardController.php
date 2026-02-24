<?php

namespace App\Http\Controllers;

use App\Services\ChateryApiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected ChateryApiService $api;

    public function __construct(ChateryApiService $api)
    {
        $this->api = $api;
    }

    public function index()
    {
        $health = $this->api->healthCheck();
        $sessions = $this->api->getSessions();
        $wsStats = $this->api->getWebSocketStats();
        
        // Filter sessions by user ownership (if not admin)
        $user = Auth::user();
        $canViewAll = $user && $user->canViewAllData();
        
        if (!$canViewAll && $user) {
            $userId = $user->id;
            if (isset($sessions['data'])) {
                $sessions['data'] = array_filter($sessions['data'], function($session) use ($userId) {
                    $metadata = $session['metadata'] ?? [];
                    return isset($metadata['created_by']) && $metadata['created_by'] == $userId;
                });
            }
        }

        return view('dashboard.index', compact('health', 'sessions', 'wsStats'));
    }

    public function sessions()
    {
        $sessions = $this->api->getSessions();
        
        // Filter sessions by user ownership (if not admin)
        $user = Auth::user();
        $canViewAll = $user && $user->canViewAllData();
        
        if (!$canViewAll && $user) {
            $userId = $user->id;
            if (isset($sessions['data'])) {
                $sessions['data'] = array_filter($sessions['data'], function($session) use ($userId) {
                    $metadata = $session['metadata'] ?? [];
                    return isset($metadata['created_by']) && $metadata['created_by'] == $userId;
                });
            }
        }
        
        $currentUserId = Auth::id();
        $chateryWebhook = env('CHATERY_WEBHOOK', '');
        return view('dashboard.sessions', compact('sessions', 'currentUserId', 'chateryWebhook'));
    }

    public function connectSession(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'metadata' => 'nullable|array',
            'webhooks' => 'nullable|array',
        ]);

        // Include user_id in metadata for ownership tracking
        $metadata = $request->metadata ?? [];
        $metadata['created_by'] = Auth::id();

        $result = $this->api->connectSession($request->sessionId, [
            'metadata' => $metadata,
            'webhooks' => $request->webhooks,
        ]);

        return response()->json($result);
    }

    public function getSessionStatus(Request $request): JsonResponse
    {
        $request->validate(['sessionId' => 'required|string']);
        $result = $this->api->getSessionStatus($request->sessionId);
        return response()->json($result);
    }

    public function getQrCode(Request $request): JsonResponse
    {
        $request->validate(['sessionId' => 'required|string']);
        $result = $this->api->getQrCode($request->sessionId);
        return response()->json($result);
    }

    public function deleteSession(Request $request): JsonResponse
    {
        $request->validate(['sessionId' => 'required|string']);
        
        // Check ownership before deleting
        $user = Auth::user();
        $canViewAll = $user && $user->canViewAllData();
        
        if (!$canViewAll) {
            // Get sessions and check ownership
            $sessions = $this->api->getSessions();
            $sessionId = $request->sessionId;
            $owned = false;
            
            if (isset($sessions['data'])) {
                foreach ($sessions['data'] as $session) {
                    if ($session['sessionId'] === $sessionId) {
                        $metadata = $session['metadata'] ?? [];
                        if (isset($metadata['created_by']) && $metadata['created_by'] == $user->id) {
                            $owned = true;
                        }
                        break;
                    }
                }
            }
            
            if (!$owned) {
                return response()->json(['success' => false, 'message' => 'You can only delete your own sessions'], 403);
            }
        }
        
        $result = $this->api->deleteSession($request->sessionId);
        return response()->json($result);
    }

    public function messaging()
    {
        $sessions = $this->api->getSessions();
        
        // Filter sessions by user ownership
        $user = Auth::user();
        $canViewAll = $user && $user->canViewAllData();
        
        if (!$canViewAll && $user) {
            $userId = $user->id;
            if (isset($sessions['data'])) {
                $sessions['data'] = array_filter($sessions['data'], function($session) use ($userId) {
                    $metadata = $session['metadata'] ?? [];
                    return isset($metadata['created_by']) && $metadata['created_by'] == $userId;
                });
            }
        }
        
        return view('dashboard.messaging', compact('sessions'));
    }

    public function sendText(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'chatId' => 'required|string',
            'message' => 'required|string',
            'typingTime' => 'nullable|integer',
            'replyTo' => 'nullable|string',
        ]);

        $result = $this->api->sendText($request->only(['sessionId', 'chatId', 'message', 'typingTime', 'replyTo']));
        return response()->json($result);
    }

    public function sendImage(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'chatId' => 'required|string',
            'imageUrl' => 'required|url',
            'caption' => 'nullable|string',
            'typingTime' => 'nullable|integer',
            'replyTo' => 'nullable|string',
        ]);

        $result = $this->api->sendImage($request->only(['sessionId', 'chatId', 'imageUrl', 'caption', 'typingTime', 'replyTo']));
        return response()->json($result);
    }

    public function sendDocument(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'chatId' => 'required|string',
            'documentUrl' => 'required|url',
            'filename' => 'required|string',
            'mimetype' => 'nullable|string',
            'caption' => 'nullable|string',
            'typingTime' => 'nullable|integer',
            'replyTo' => 'nullable|string',
        ]);

        $result = $this->api->sendDocument($request->only(['sessionId', 'chatId', 'documentUrl', 'filename', 'mimetype', 'caption', 'typingTime', 'replyTo']));
        return response()->json($result);
    }

    public function sendAudio(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'chatId' => 'required|string',
            'audioUrl' => 'required|url',
            'ptt' => 'nullable|boolean',
            'typingTime' => 'nullable|integer',
            'replyTo' => 'nullable|string',
        ]);

        $result = $this->api->sendAudio($request->only(['sessionId', 'chatId', 'audioUrl', 'ptt', 'typingTime', 'replyTo']));
        return response()->json($result);
    }

    public function sendLocation(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'chatId' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'name' => 'nullable|string',
            'typingTime' => 'nullable|integer',
            'replyTo' => 'nullable|string',
        ]);

        $result = $this->api->sendLocation($request->only(['sessionId', 'chatId', 'latitude', 'longitude', 'name', 'typingTime', 'replyTo']));
        return response()->json($result);
    }

    public function sendContact(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'chatId' => 'required|string',
            'contactName' => 'required|string',
            'contactPhone' => 'required|string',
            'typingTime' => 'nullable|integer',
            'replyTo' => 'nullable|string',
        ]);

        $result = $this->api->sendContact($request->only(['sessionId', 'chatId', 'contactName', 'contactPhone', 'typingTime', 'replyTo']));
        return response()->json($result);
    }

    public function sendPoll(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'chatId' => 'required|string',
            'question' => 'required|string',
            'options' => 'required|array|min:2|max:12',
            'selectableCount' => 'nullable|integer',
            'typingTime' => 'nullable|integer',
            'replyTo' => 'nullable|string',
        ]);

        $result = $this->api->sendPoll($request->only(['sessionId', 'chatId', 'question', 'options', 'selectableCount', 'typingTime', 'replyTo']));
        return response()->json($result);
    }

    public function checkNumber(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'phone' => 'required|string',
        ]);

        $result = $this->api->checkNumber($request->only(['sessionId', 'phone']));
        return response()->json($result);
    }

    public function bulkMessaging()
    {
        $sessions = $this->api->getSessions();
        
        // Filter sessions by user ownership
        $user = Auth::user();
        $canViewAll = $user && $user->canViewAllData();
        
        if (!$canViewAll && $user) {
            $userId = $user->id;
            if (isset($sessions['data'])) {
                $sessions['data'] = array_filter($sessions['data'], function($session) use ($userId) {
                    $metadata = $session['metadata'] ?? [];
                    return isset($metadata['created_by']) && $metadata['created_by'] == $userId;
                });
            }
        }
        
        return view('dashboard.bulk-messaging', compact('sessions'));
    }

    public function sendBulkText(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'recipients' => 'required|array|max:100',
            'message' => 'required|string',
            'delayBetweenMessages' => 'nullable|integer',
            'typingTime' => 'nullable|integer',
        ]);

        $result = $this->api->sendBulkText($request->only(['sessionId', 'recipients', 'message', 'delayBetweenMessages', 'typingTime']));
        return response()->json($result);
    }

    public function sendBulkImage(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'recipients' => 'required|array|max:100',
            'imageUrl' => 'required|url',
            'caption' => 'nullable|string',
            'delayBetweenMessages' => 'nullable|integer',
            'typingTime' => 'nullable|integer',
        ]);

        $result = $this->api->sendBulkImage($request->only(['sessionId', 'recipients', 'imageUrl', 'caption', 'delayBetweenMessages', 'typingTime']));
        return response()->json($result);
    }

    public function sendBulkDocument(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'recipients' => 'required|array|max:100',
            'documentUrl' => 'required|url',
            'filename' => 'required|string',
            'mimetype' => 'nullable|string',
            'delayBetweenMessages' => 'nullable|integer',
            'typingTime' => 'nullable|integer',
        ]);

        $result = $this->api->sendBulkDocument($request->only(['sessionId', 'recipients', 'documentUrl', 'filename', 'mimetype', 'delayBetweenMessages', 'typingTime']));
        return response()->json($result);
    }

    public function getBulkJobStatus(Request $request): JsonResponse
    {
        $request->validate(['jobId' => 'required|string']);
        $result = $this->api->getBulkJobStatus($request->jobId);
        return response()->json($result);
    }

    public function getBulkJobs(Request $request): JsonResponse
    {
        $request->validate(['sessionId' => 'required|string']);
        $result = $this->api->getBulkJobs($request->sessionId);
        return response()->json($result);
    }

    public function chatHistory()
    {
        $sessions = $this->api->getSessions();
        
        // Filter sessions by user ownership
        $user = Auth::user();
        $canViewAll = $user && $user->canViewAllData();
        
        if (!$canViewAll && $user) {
            $userId = $user->id;
            if (isset($sessions['data'])) {
                $sessions['data'] = array_filter($sessions['data'], function($session) use ($userId) {
                    $metadata = $session['metadata'] ?? [];
                    return isset($metadata['created_by']) && $metadata['created_by'] == $userId;
                });
            }
        }
        
        return view('dashboard.chat-history', compact('sessions'));
    }

    public function getChatOverview(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'limit' => 'nullable|integer',
            'offset' => 'nullable|integer',
            'type' => 'nullable|in:all,individual,group',
        ]);

        $result = $this->api->getChatOverview($request->only(['sessionId', 'limit', 'offset', 'type']));
        return response()->json($result);
    }

    public function getMessages(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'chatId' => 'required|string',
            'limit' => 'nullable|integer',
            'cursor' => 'nullable|string',
        ]);

        $result = $this->api->getMessages($request->only(['sessionId', 'chatId', 'limit', 'cursor']));
        return response()->json($result);
    }

    public function getChatInfo(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'chatId' => 'required|string',
        ]);

        $result = $this->api->getChatInfo($request->only(['sessionId', 'chatId']));
        return response()->json($result);
    }

    public function markAsRead(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'chatId' => 'required|string',
        ]);

        $result = $this->api->markAsRead($request->only(['sessionId', 'chatId']));
        return response()->json($result);
    }

    public function getContacts(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'limit' => 'nullable|integer',
            'offset' => 'nullable|integer',
            'search' => 'nullable|string',
        ]);

        $result = $this->api->getContacts($request->only(['sessionId', 'limit', 'offset', 'search']));
        return response()->json($result);
    }

    public function getProfilePicture(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'phone' => 'required|string',
        ]);

        $result = $this->api->getProfilePicture($request->only(['sessionId', 'phone']));
        return response()->json($result);
    }

    public function groups()
    {
        $sessions = $this->api->getSessions();
        
        // Filter sessions by user ownership
        $user = Auth::user();
        $canViewAll = $user && $user->canViewAllData();
        
        if (!$canViewAll && $user) {
            $userId = $user->id;
            if (isset($sessions['data'])) {
                $sessions['data'] = array_filter($sessions['data'], function($session) use ($userId) {
                    $metadata = $session['metadata'] ?? [];
                    return isset($metadata['created_by']) && $metadata['created_by'] == $userId;
                });
            }
        }
        
        return view('dashboard.groups', compact('sessions'));
    }

    public function getGroups(Request $request): JsonResponse
    {
        $request->validate(['sessionId' => 'required|string']);
        $result = $this->api->getGroups($request->sessionId);
        return response()->json($result);
    }

    public function createGroup(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'name' => 'required|string',
            'participants' => 'required|array',
        ]);

        $result = $this->api->createGroup($request->only(['sessionId', 'name', 'participants']));
        return response()->json($result);
    }

    public function getGroupMetadata(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'groupId' => 'required|string',
        ]);

        $result = $this->api->getGroupMetadata($request->only(['sessionId', 'groupId']));
        return response()->json($result);
    }

    public function addParticipants(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'groupId' => 'required|string',
            'participants' => 'required|array',
        ]);

        $result = $this->api->addParticipants($request->only(['sessionId', 'groupId', 'participants']));
        return response()->json($result);
    }

    public function removeParticipants(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'groupId' => 'required|string',
            'participants' => 'required|array',
        ]);

        $result = $this->api->removeParticipants($request->only(['sessionId', 'groupId', 'participants']));
        return response()->json($result);
    }

    public function promoteParticipants(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'groupId' => 'required|string',
            'participants' => 'required|array',
        ]);

        $result = $this->api->promoteParticipants($request->only(['sessionId', 'groupId', 'participants']));
        return response()->json($result);
    }

    public function demoteParticipants(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'groupId' => 'required|string',
            'participants' => 'required|array',
        ]);

        $result = $this->api->demoteParticipants($request->only(['sessionId', 'groupId', 'participants']));
        return response()->json($result);
    }

    public function updateGroupSubject(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'groupId' => 'required|string',
            'subject' => 'required|string',
        ]);

        $result = $this->api->updateGroupSubject($request->only(['sessionId', 'groupId', 'subject']));
        return response()->json($result);
    }

    public function updateGroupDescription(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'groupId' => 'required|string',
            'description' => 'required|string',
        ]);

        $result = $this->api->updateGroupDescription($request->only(['sessionId', 'groupId', 'description']));
        return response()->json($result);
    }

    public function updateGroupSettings(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'groupId' => 'required|string',
            'setting' => 'required|in:announcement,not_announcement,locked,unlocked',
        ]);

        $result = $this->api->updateGroupSettings($request->only(['sessionId', 'groupId', 'setting']));
        return response()->json($result);
    }

    public function updateGroupPicture(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'groupId' => 'required|string',
            'imageUrl' => 'required|url',
        ]);

        $result = $this->api->updateGroupPicture($request->only(['sessionId', 'groupId', 'imageUrl']));
        return response()->json($result);
    }

    public function leaveGroup(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'groupId' => 'required|string',
        ]);

        $result = $this->api->leaveGroup($request->only(['sessionId', 'groupId']));
        return response()->json($result);
    }

    public function joinGroup(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'inviteCode' => 'required|string',
        ]);

        $result = $this->api->joinGroup($request->only(['sessionId', 'inviteCode']));
        return response()->json($result);
    }

    public function getInviteCode(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'groupId' => 'required|string',
        ]);

        $result = $this->api->getInviteCode($request->only(['sessionId', 'groupId']));
        return response()->json($result);
    }

    public function revokeInviteCode(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => 'required|string',
            'groupId' => 'required|string',
        ]);

        $result = $this->api->revokeInviteCode($request->only(['sessionId', 'groupId']));
        return response()->json($result);
    }

    public function websocket()
    {
        $wsStats = $this->api->getWebSocketStats();
        return view('dashboard.websocket', compact('wsStats'));
    }

    public function getWebSocketStats(): JsonResponse
    {
        $result = $this->api->getWebSocketStats();
        return response()->json($result);
    }

    public function apiDocs()
    {
        return view('dashboard.api-docs');
    }
}
