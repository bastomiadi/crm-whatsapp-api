<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;


class WebhookController extends Controller
{
    // Store SSE connections
    protected static $sseConnections = [];
    
    /**
     * Handle incoming webhook from Chatery
     */
    public function handle(Request $request)
    {
        $data = $request->all();
        
        Log::info('[Webhook] Received webhook:', $data);
        
        $sessionId = $data['sessionId'] ?? null;
        $type = $data['type'] ?? null;
        $eventData = $data['data'] ?? [];
        
        if (!$sessionId || !$type) {
            return response()->json(['success' => false, 'message' => 'Invalid webhook data'], 400);
        }
        
        switch ($type) {
            case 'message':
                $this->handleMessage($sessionId, $eventData);
                break;
                
            case 'message.sent':
                $this->handleMessageSent($sessionId, $eventData);
                break;
                
            case 'message.update':
                $this->handleMessageUpdate($sessionId, $eventData);
                break;
                
            case 'connection.update':
                $this->handleConnectionUpdate($sessionId, $eventData);
                break;
                
            case 'qr.update':
                $this->handleQrUpdate($sessionId, $eventData);
                break;
                
            case 'session.ready':
                $this->handleSessionReady($sessionId, $eventData);
                break;
                
            case 'session.disconnected':
                $this->handleSessionDisconnected($sessionId, $eventData);
                break;
                
            case 'group.update':
                $this->handleGroupUpdate($sessionId, $eventData);
                break;
                
            case 'group.participants.update':
                $this->handleGroupParticipantsUpdate($sessionId, $eventData);
                break;
                
            default:
                Log::info('[Webhook] Unknown event type:', ['type' => $type]);
                break;
        }
        
        // Broadcast to all SSE connections
        $this->broadcastToSSE($type, $data);
        
        // Broadcast event to frontend via Laravel broadcasting
        event(new \App\Events\ChateryWebhook($data));
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Server-Sent Events endpoint for real-time updates
     */
    public function sse(Request $request)
    {
        return response()->stream(function () {
            $lastEventId = null;
            
            while (true) {
                // Check for new events in cache
                $events = cache()->pull('sse_events', []);
                
                if (!empty($events)) {
                    foreach ($events as $event) {
                        echo "event: " . ($event['type'] ?? 'message') . "\n";
                        echo "data: " . json_encode($event['data']) . "\n\n";
                        ob_flush();
                        flush();
                    }
                }
                
                // Send heartbeat every 30 seconds
                echo ": heartbeat\n\n";
                ob_flush();
                flush();
                
                sleep(1);
                
                // Close connection after 5 minutes of inactivity
                if (connection_aborted()) {
                    break;
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
    
    /**
     * Broadcast event to all SSE connections
     */
    protected function broadcastToSSE($type, $data)
    {
        // Store event in cache for SSE to pick up
        $key = 'sse_events';
        $events = cache()->get($key, []);
        $events[] = [
            'type' => $type,
            'data' => $data,
            'timestamp' => time(),
        ];
        cache()->put($key, $events, now()->addMinutes(5));
        
        // Also store conversation-specific events
        if (isset($data['data']['from'])) {
            $phone = str_replace('@s.whatsapp.net', '', $data['data']['from']);
            $phone = str_replace('@g.us', '', $phone);
            $convKey = 'sse_events_conv_' . $phone;
            $convEvents = cache()->get($convKey, []);
            $convEvents[] = [
                'type' => $type,
                'data' => $data,
                'timestamp' => time(),
            ];
            cache()->put($convKey, $convEvents, now()->addMinutes(5));
        }
    }
    
    /**
     * Handle incoming message
     */
    protected function handleMessage($sessionId, $data)
    {
        Log::info('[Webhook] New message:', $data);
        
        $from = $data['from'] ?? '';
        $to = $data['to'] ?? '';
        $content = $data['content'] ?? '';
        $pushName = $data['pushName'] ?? null;
        $isGroup = $data['isGroup'] ?? false;
        $messageId = $data['id'] ?? null;
        
        // Extract phone number
        $phone = str_replace('@s.whatsapp.net', '', $from);
        $phone = str_replace('@g.us', '', $phone);
        
        // Find or create contact
        $contact = Contact::where('phone', $phone)->first();
        
        if (!$contact) {
            $contact = Contact::create([
                'name' => $pushName ?? $phone,
                'phone' => $phone,
            ]);
        }
        
        // Find or create conversation
        $conversation = ChatConversation::where('phone', $phone)
            ->where('session_id', $sessionId)
            ->first();
            
        if (!$conversation) {
            $conversation = ChatConversation::create([
                'phone' => $phone,
                'name' => $pushName ?? $phone,
                'session_id' => $sessionId,
                'contact_id' => $contact->id,
                'status' => 'active',
                'last_message_at' => now(),
                'unread_count' => 1,
            ]);
        } else {
            $conversation->update([
                'last_message_at' => now(),
                'unread_count' => $conversation->unread_count + 1,
            ]);
        }
        
        // Create message record
        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'contact_id' => $contact->id,
            'direction' => 'inbound',
            'message' => $content,
            'message_id' => $messageId,
            'status' => 'received',
        ]);
        
        Log::info('[Webhook] Message saved:', [
            'conversation_id' => $conversation->id,
            'phone' => $phone,
            'content' => $content,
        ]);
    }
    
    /**
     * Handle sent message
     */
    protected function handleMessageSent($sessionId, $data)
    {
        Log::info('[Webhook] Message sent:', $data);
        
        $to = $data['to'] ?? '';
        $content = $data['content'] ?? '';
        $messageId = $data['id'] ?? null;
        
        // Extract phone number
        $phone = str_replace('@s.whatsapp.net', '', $to);
        $phone = str_replace('@g.us', '', $phone);
        
        // Find conversation
        $conversation = ChatConversation::where('phone', $phone)
            ->where('session_id', $sessionId)
            ->first();
            
        if ($conversation) {
            $conversation->update([
                'last_message_at' => now(),
            ]);
            
            // Create outbound message record
            ChatMessage::create([
                'conversation_id' => $conversation->id,
                'direction' => 'outbound',
                'message' => $content,
                'message_id' => $messageId,
                'status' => 'sent',
            ]);
        }
    }
    
    /**
     * Handle message update (delivery status)
     */
    protected function handleMessageUpdate($sessionId, $data)
    {
        Log::info('[Webhook] Message update:', $data);
        
        $messageId = $data['id'] ?? null;
        $status = $data['status'] ?? 'unknown';
        
        if ($messageId) {
            $message = ChatMessage::where('message_id', $messageId)->first();
            if ($message) {
                $message->update(['status' => $status]);
            }
        }
    }
    
    /**
     * Handle connection update
     */
    protected function handleConnectionUpdate($sessionId, $data)
    {
        Log::info('[Webhook] Connection update:', $data);
        
        $status = $data['status'] ?? 'unknown';
        $phoneNumber = $data['phoneNumber'] ?? null;
        $name = $data['name'] ?? null;
        
        // You can update session status in database if needed
        // For now, just log it
    }
    
    /**
     * Handle QR code update
     */
    protected function handleQrUpdate($sessionId, $data)
    {
        Log::info('[Webhook] QR update:', $data);
        
        $qr = $data['qr'] ?? null;
        
        // Could store QR code for display
    }
    
    /**
     * Handle session ready
     */
    protected function handleSessionReady($sessionId, $data)
    {
        Log::info('[Webhook] Session ready:', $data);
    }
    
    /**
     * Handle session disconnected
     */
    protected function handleSessionDisconnected($sessionId, $data)
    {
        Log::info('[Webhook] Session disconnected:', $data);
    }
    
    /**
     * Handle group update
     */
    protected function handleGroupUpdate($sessionId, $data)
    {
        Log::info('[Webhook] Group update:', $data);
    }
    
    /**
     * Handle group participants update
     */
    protected function handleGroupParticipantsUpdate($sessionId, $data)
    {
        Log::info('[Webhook] Group participants update:', $data);
    }
}
