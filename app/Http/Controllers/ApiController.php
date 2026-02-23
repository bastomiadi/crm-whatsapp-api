<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\ChateryApiService;

class ApiController extends Controller
{
    protected $api;

    public function __construct(ChateryApiService $api)
    {
        $this->api = $api;
    }

    // Sessions Routes
    public function connectSession(Request $request): JsonResponse
    {
        $sessionId = $request->input('sessionId');
        $options = $request->input('options', []);
        $result = $this->api->connectSession($sessionId, $options);
        return response()->json($result);
    }

    public function getSessionStatus(Request $request): JsonResponse
    {
        $sessionId = $request->input('sessionId');
        $result = $this->api->getSessionStatus($sessionId);
        return response()->json($result);
    }

    public function getQrCode(Request $request): JsonResponse
    {
        $sessionId = $request->input('sessionId');
        $result = $this->api->getQrCode($sessionId);
        return response()->json($result);
    }

    public function deleteSession(Request $request): JsonResponse
    {
        $sessionId = $request->input('sessionId');
        $result = $this->api->deleteSession($sessionId);
        return response()->json($result);
    }

    // Messaging Routes
    public function sendText(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->sendText($data);
        return response()->json($result);
    }

    public function sendImage(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->sendImage($data);
        return response()->json($result);
    }

    public function sendDocument(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->sendDocument($data);
        return response()->json($result);
    }

    public function sendAudio(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->sendAudio($data);
        return response()->json($result);
    }

    public function sendLocation(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->sendLocation($data);
        return response()->json($result);
    }

    public function sendContact(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->sendContact($data);
        return response()->json($result);
    }

    public function sendPoll(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->sendPoll($data);
        return response()->json($result);
    }

    public function checkNumber(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->checkNumber($data);
        return response()->json($result);
    }

    // Bulk Messaging Routes
    public function sendBulkText(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->sendBulkText($data);
        return response()->json($result);
    }

    public function sendBulkImage(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->sendBulkImage($data);
        return response()->json($result);
    }

    public function sendBulkDocument(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->sendBulkDocument($data);
        return response()->json($result);
    }

    public function getBulkJobStatus(Request $request): JsonResponse
    {
        $jobId = $request->input('jobId');
        $result = $this->api->getBulkJobStatus($jobId);
        return response()->json($result);
    }

    public function getBulkJobs(Request $request): JsonResponse
    {
        $sessionId = $request->input('sessionId');
        $result = $this->api->getBulkJobs($sessionId);
        return response()->json($result);
    }

    // Chat History Routes
    public function getChatOverview(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->getChatOverview($data);
        return response()->json($result);
    }

    public function getMessages(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->getMessages($data);
        return response()->json($result);
    }

    public function getChatInfo(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->getChatInfo($data);
        return response()->json($result);
    }

    public function markAsRead(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->markAsRead($data);
        return response()->json($result);
    }

    public function getContacts(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->getContacts($data);
        return response()->json($result);
    }

    public function getProfilePicture(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->getProfilePicture($data);
        return response()->json($result);
    }

    // Groups Routes
    public function getGroups(Request $request): JsonResponse
    {
        $sessionId = $request->input('sessionId');
        $result = $this->api->getGroups($sessionId);
        return response()->json($result);
    }

    public function createGroup(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->createGroup($data);
        return response()->json($result);
    }

    public function getGroupMetadata(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->getGroupMetadata($data);
        return response()->json($result);
    }

    public function addParticipants(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->addParticipants($data);
        return response()->json($result);
    }

    public function removeParticipants(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->removeParticipants($data);
        return response()->json($result);
    }

    public function promoteParticipants(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->promoteParticipants($data);
        return response()->json($result);
    }

    public function demoteParticipants(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->demoteParticipants($data);
        return response()->json($result);
    }

    public function updateGroupSubject(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->updateGroupSubject($data);
        return response()->json($result);
    }

    public function updateGroupDescription(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->updateGroupDescription($data);
        return response()->json($result);
    }

    public function updateGroupSettings(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->updateGroupSettings($data);
        return response()->json($result);
    }

    public function updateGroupPicture(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->updateGroupPicture($data);
        return response()->json($result);
    }

    public function leaveGroup(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->leaveGroup($data);
        return response()->json($result);
    }

    public function joinGroup(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->joinGroup($data);
        return response()->json($result);
    }

    public function getInviteCode(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->getInviteCode($data);
        return response()->json($result);
    }

    public function revokeInviteCode(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->api->revokeInviteCode($data);
        return response()->json($result);
    }

    // WebSocket Routes
    public function getWebSocketStats(): JsonResponse
    {
        $result = $this->api->getWebSocketStats();
        return response()->json($result);
    }
}
