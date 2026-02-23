<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChateryApiService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $this->apiUrl = config('chatery.api_url', 'http://localhost:3000');
        $this->apiKey = config('chatery.api_key', '');
        $this->timeout = config('chatery.timeout', 30);
    }

    protected function getHeaders(): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if (!empty($this->apiKey)) {
            $headers['X-Api-Key'] = $this->apiKey;
        }

        return $headers;
    }

    protected function request(string $method, string $endpoint, array $data = [])
    {
        try {
            $url = $this->apiUrl . $endpoint;
            
            $response = Http::withHeaders($this->getHeaders())
                ->timeout($this->timeout)
                ->$method($url, $data);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Chatery API Error: ' . $e->getMessage(), [
                'endpoint' => $endpoint,
                'method' => $method,
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    // Health Check
    public function healthCheck(): array
    {
        return $this->request('get', '/api/health');
    }

    // WebSocket Stats
    public function getWebSocketStats(): array
    {
        return $this->request('get', '/api/websocket/stats');
    }

    // Sessions
    public function getSessions(): array
    {
        return $this->request('get', '/api/whatsapp/sessions');
    }

    public function connectSession(string $sessionId, array $options = []): array
    {
        return $this->request('post', "/api/whatsapp/sessions/{$sessionId}/connect", $options);
    }

    public function getSessionStatus(string $sessionId): array
    {
        return $this->request('get', "/api/whatsapp/sessions/{$sessionId}/status");
    }

    public function getQrCode(string $sessionId): array
    {
        return $this->request('get', "/api/whatsapp/sessions/{$sessionId}/qr");
    }

    public function deleteSession(string $sessionId): array
    {
        return $this->request('delete', "/api/whatsapp/sessions/{$sessionId}");
    }

    public function updateSessionConfig(string $sessionId, array $config): array
    {
        return $this->request('patch', "/api/whatsapp/sessions/{$sessionId}/config", $config);
    }

    public function addWebhook(string $sessionId, array $webhook): array
    {
        return $this->request('post', "/api/whatsapp/sessions/{$sessionId}/webhooks", $webhook);
    }

    public function removeWebhook(string $sessionId, string $url): array
    {
        return $this->request('delete', "/api/whatsapp/sessions/{$sessionId}/webhooks", ['url' => $url]);
    }

    // Messaging
    public function sendText(array $data): array
    {
        return $this->request('post', '/api/whatsapp/chats/send-text', $data);
    }

    public function sendImage(array $data): array
    {
        return $this->request('post', '/api/whatsapp/chats/send-image', $data);
    }

    public function sendDocument(array $data): array
    {
        return $this->request('post', '/api/whatsapp/chats/send-document', $data);
    }

    public function sendAudio(array $data): array
    {
        return $this->request('post', '/api/whatsapp/chats/send-audio', $data);
    }

    public function sendLocation(array $data): array
    {
        return $this->request('post', '/api/whatsapp/chats/send-location', $data);
    }

    public function sendContact(array $data): array
    {
        return $this->request('post', '/api/whatsapp/chats/send-contact', $data);
    }

    public function sendPoll(array $data): array
    {
        return $this->request('post', '/api/whatsapp/chats/send-poll', $data);
    }

    public function sendPresence(array $data): array
    {
        return $this->request('post', '/api/whatsapp/chats/presence', $data);
    }

    public function checkNumber(array $data): array
    {
        return $this->request('post', '/api/whatsapp/chats/check-number', $data);
    }

    // Bulk Messaging
    public function sendBulkText(array $data): array
    {
        return $this->request('post', '/api/whatsapp/chats/send-bulk', $data);
    }

    public function sendBulkImage(array $data): array
    {
        return $this->request('post', '/api/whatsapp/chats/send-bulk-image', $data);
    }

    public function sendBulkDocument(array $data): array
    {
        return $this->request('post', '/api/whatsapp/chats/send-bulk-document', $data);
    }

    public function getBulkJobStatus(string $jobId): array
    {
        return $this->request('get', "/api/whatsapp/chats/bulk-status/{$jobId}");
    }

    public function getBulkJobs(string $sessionId): array
    {
        return $this->request('post', '/api/whatsapp/chats/bulk-jobs', ['sessionId' => $sessionId]);
    }

    // Chat History
    public function getChatOverview(array $data): array
    {
        return $this->request('post', '/api/whatsapp/chats/overview', $data);
    }

    public function getMessages(array $data): array
    {
        return $this->request('post', '/api/whatsapp/chats/messages', $data);
    }

    public function getChatInfo(array $data): array
    {
        return $this->request('post', '/api/whatsapp/chats/info', $data);
    }

    public function markAsRead(array $data): array
    {
        return $this->request('post', '/api/whatsapp/chats/mark-read', $data);
    }

    public function getContacts(array $data): array
    {
        return $this->request('post', '/api/whatsapp/contacts', $data);
    }

    public function getProfilePicture(array $data): array
    {
        return $this->request('post', '/api/whatsapp/chats/profile-picture', $data);
    }

    public function getContactInfo(array $data): array
    {
        return $this->request('post', '/api/whatsapp/chats/contact-info', $data);
    }

    // Groups
    public function getGroups(string $sessionId): array
    {
        return $this->request('post', '/api/whatsapp/groups', ['sessionId' => $sessionId]);
    }

    public function createGroup(array $data): array
    {
        return $this->request('post', '/api/whatsapp/groups/create', $data);
    }

    public function getGroupMetadata(array $data): array
    {
        return $this->request('post', '/api/whatsapp/groups/metadata', $data);
    }

    public function addParticipants(array $data): array
    {
        return $this->request('post', '/api/whatsapp/groups/participants/add', $data);
    }

    public function removeParticipants(array $data): array
    {
        return $this->request('post', '/api/whatsapp/groups/participants/remove', $data);
    }

    public function promoteParticipants(array $data): array
    {
        return $this->request('post', '/api/whatsapp/groups/participants/promote', $data);
    }

    public function demoteParticipants(array $data): array
    {
        return $this->request('post', '/api/whatsapp/groups/participants/demote', $data);
    }

    public function updateGroupSubject(array $data): array
    {
        return $this->request('post', '/api/whatsapp/groups/subject', $data);
    }

    public function updateGroupDescription(array $data): array
    {
        return $this->request('post', '/api/whatsapp/groups/description', $data);
    }

    public function updateGroupSettings(array $data): array
    {
        return $this->request('post', '/api/whatsapp/groups/settings', $data);
    }

    public function updateGroupPicture(array $data): array
    {
        return $this->request('post', '/api/whatsapp/groups/picture', $data);
    }

    public function leaveGroup(array $data): array
    {
        return $this->request('post', '/api/whatsapp/groups/leave', $data);
    }

    public function joinGroup(array $data): array
    {
        return $this->request('post', '/api/whatsapp/groups/join', $data);
    }

    public function getInviteCode(array $data): array
    {
        return $this->request('post', '/api/whatsapp/groups/invite-code', $data);
    }

    public function revokeInviteCode(array $data): array
    {
        return $this->request('post', '/api/whatsapp/groups/revoke-invite', $data);
    }
}
