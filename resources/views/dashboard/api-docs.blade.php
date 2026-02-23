@extends('layouts.app')

@section('title', 'RESTful API Documentation')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">RESTful API Documentation</h1>
            <p class="text-gray-500 mt-1">Complete API reference for WhatsApp integration</p>
        </div>
    </div>

    <!-- API Info Card -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
        <h3 class="font-semibold text-blue-800 mb-2">API Base URL</h3>
        <code class="bg-white px-3 py-1 rounded text-blue-600">{{ url('/') }}</code>
        <p class="text-sm text-blue-600 mt-2">All API endpoints require authentication via Laravel session. Include CSRF token in requests.</p>
    </div>

    <!-- Sessions API -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800">Sessions API</h2>
            <p class="text-gray-500 text-sm mt-1">Manage WhatsApp sessions</p>
        </div>
        <div class="p-6 space-y-6">
            <!-- Connect Session -->
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">POST</span>
                    <code class="text-sm">/api/sessions/connect</code>
                </div>
                <p class="text-gray-600 text-sm mt-2">Create and connect a new WhatsApp session</p>
                <div class="mt-3 bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm font-medium text-gray-700 mb-2">Request Body:</p>
                    <pre class="text-xs bg-gray-800 text-green-400 p-3 rounded overflow-x-auto"><code>{
  "sessionId": "my_session",
  "metadata": {"created_by": 1},
  "webhooks": [{"url": "https://example.com/webhook", "events": []}]
}</code></pre>
                </div>
            </div>

            <!-- Get QR Code -->
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">POST</span>
                    <code class="text-sm">/api/sessions/qr</code>
                </div>
                <p class="text-gray-600 text-sm mt-2">Get QR code for session authentication</p>
                <div class="mt-3 bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm font-medium text-gray-700 mb-2">Request Body:</p>
                    <pre class="text-xs bg-gray-800 text-green-400 p-3 rounded overflow-x-auto"><code>{
  "sessionId": "my_session"
}</code></pre>
                </div>
            </div>

            <!-- Delete Session -->
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-bold rounded">POST</span>
                    <code class="text-sm">/api/sessions/delete</code>
                </div>
                <p class="text-gray-600 text-sm mt-2">Delete a WhatsApp session</p>
                <div class="mt-3 bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm font-medium text-gray-700 mb-2">Request Body:</p>
                    <pre class="text-xs bg-gray-800 text-green-400 p-3 rounded overflow-x-auto"><code>{
  "sessionId": "my_session"
}</code></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Messaging API -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800">Messaging API</h2>
            <p class="text-gray-500 text-sm mt-1">Send messages via WhatsApp</p>
        </div>
        <div class="p-6 space-y-6">
            <!-- Send Text -->
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">POST</span>
                    <code class="text-sm">/api/send/text</code>
                </div>
                <p class="text-gray-600 text-sm mt-2">Send a text message</p>
                <div class="mt-3 bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm font-medium text-gray-700 mb-2">Request Body:</p>
                    <pre class="text-xs bg-gray-800 text-green-400 p-3 rounded overflow-x-auto"><code>{
  "sessionId": "my_session",
  "phone": "1234567890",
  "message": "Hello!"
}</code></pre>
                </div>
            </div>

            <!-- Send Image -->
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">POST</span>
                    <code class="text-sm">/api/send/image</code>
                </div>
                <p class="text-gray-600 text-sm mt-2">Send an image with optional caption</p>
                <div class="mt-3 bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm font-medium text-gray-700 mb-2">Request Body:</p>
                    <pre class="text-xs bg-gray-800 text-green-400 p-3 rounded overflow-x-auto"><code>{
  "sessionId": "my_session",
  "phone": "1234567890",
  "image": "https://example.com/image.jpg",
  "caption": "Image description"
}</code></pre>
                </div>
            </div>

            <!-- Send Document -->
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">POST</span>
                    <code class="text-sm">/api/send/document</code>
                </div>
                <p class="text-gray-600 text-sm mt-2">Send a document file</p>
                <div class="mt-3 bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm font-medium text-gray-700 mb-2">Request Body:</p>
                    <pre class="text-xs bg-gray-800 text-green-400 p-3 rounded overflow-x-auto"><code>{
  "sessionId": "my_session",
  "phone": "1234567890",
  "document": "https://example.com/file.pdf",
  "fileName": "document.pdf"
}</code></pre>
                </div>
            </div>

            <!-- Send Audio -->
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">POST</span>
                    <code class="text-sm">/api/send/audio</code>
                </div>
                <p class="text-gray-600 text-sm mt-2">Send an audio file (voice note)</p>
                <div class="mt-3 bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm font-medium text-gray-700 mb-2">Request Body:</p>
                    <pre class="text-xs bg-gray-800 text-green-400 p-3 rounded overflow-x-auto"><code>{
  "sessionId": "my_session",
  "phone": "1234567890",
  "audio": "https://example.com/audio.mp3"
}</code></pre>
                </div>
            </div>

            <!-- Send Location -->
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">POST</span>
                    <code class="text-sm">/api/send/location</code>
                </div>
                <p class="text-gray-600 text-sm mt-2">Send a location</p>
                <div class="mt-3 bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm font-medium text-gray-700 mb-2">Request Body:</p>
                    <pre class="text-xs bg-gray-800 text-green-400 p-3 rounded overflow-x-auto"><code>{
  "sessionId": "my_session",
  "phone": "1234567890",
  "latitude": -6.2088,
  "longitude": 106.8456,
  "title": "Location Name"
}</code></pre>
                </div>
            </div>

            <!-- Send Contact -->
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">POST</span>
                    <code class="text-sm">/api/send/contact</code>
                </div>
                <p class="text-gray-600 text-sm mt-2">Send a contact card</p>
                <div class="mt-3 bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm font-medium text-gray-700 mb-2">Request Body:</p>
                    <pre class="text-xs bg-gray-800 text-green-400 p-3 rounded overflow-x-auto"><code>{
  "sessionId": "my_session",
  "phone": "1234567890",
  "contactId": "contact_id"
}</code></pre>
                </div>
            </div>

            <!-- Send Poll -->
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">POST</span>
                    <code class="text-sm">/api/send/poll</code>
                </div>
                <p class="text-gray-600 text-sm mt-2">Send a poll message</p>
                <div class="mt-3 bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm font-medium text-gray-700 mb-2">Request Body:</p>
                    <pre class="text-xs bg-gray-800 text-green-400 p-3 rounded overflow-x-auto"><code>{
  "sessionId": "my_session",
  "phone": "1234567890",
  "question": "What do you prefer?",
  "options": ["Option 1", "Option 2", "Option 3"],
  "selectableCount": 1
}</code></pre>
                </div>
            </div>

            <!-- Check Number -->
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">POST</span>
                    <code class="text-sm">/api/check-number</code>
                </div>
                <p class="text-gray-600 text-sm mt-2">Check if a number is registered on WhatsApp</p>
                <div class="mt-3 bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm font-medium text-gray-700 mb-2">Request Body:</p>
                    <pre class="text-xs bg-gray-800 text-green-400 p-3 rounded overflow-x-auto"><code>{
  "sessionId": "my_session",
  "phone": "1234567890"
}</code></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Messaging API -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800">Bulk Messaging API</h2>
            <p class="text-gray-500 text-sm mt-1">Send messages to multiple recipients</p>
        </div>
        <div class="p-6 space-y-6">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">POST</span>
                    <code class="text-sm">/api/bulk/text</code>
                </div>
                <p class="text-gray-600 text-sm mt-2">Send bulk text messages</p>
                <div class="mt-3 bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm font-medium text-gray-700 mb-2">Request Body:</p>
                    <pre class="text-xs bg-gray-800 text-green-400 p-3 rounded overflow-x-auto"><code>{
  "sessionId": "my_session",
  "phones": ["1234567890", "0987654321"],
  "message": "Bulk message!"
}</code></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Groups API -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800">Groups API</h2>
            <p class="text-gray-500 text-sm mt-1">Manage WhatsApp groups</p>
        </div>
        <div class="p-6 space-y-6">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">POST</span>
                    <code class="text-sm">/api/groups/create</code>
                </div>
                <p class="text-gray-600 text-sm mt-2">Create a new group</p>
                <div class="mt-3 bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm font-medium text-gray-700 mb-2">Request Body:</p>
                    <pre class="text-xs bg-gray-800 text-green-400 p-3 rounded overflow-x-auto"><code>{
  "sessionId": "my_session",
  "subject": "Group Name",
  "participants": ["1234567890", "0987654321"]
}</code></pre>
                </div>
            </div>

            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">POST</span>
                    <code class="text-sm">/api/groups/participants/add</code>
                </div>
                <p class="text-gray-600 text-sm mt-2">Add participants to a group</p>
            </div>

            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">POST</span>
                    <code class="text-sm">/api/groups/participants/remove</code>
                </div>
                <p class="text-gray-600 text-sm mt-2">Remove participants from a group</p>
            </div>
        </div>
    </div>

    <!-- Chat History API -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800">Chat History API</h2>
            <p class="text-gray-500 text-sm mt-1">Retrieve chat messages and information</p>
        </div>
        <div class="p-6 space-y-6">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">POST</span>
                    <code class="text-sm">/api/chats/messages</code>
                </div>
                <p class="text-gray-600 text-sm mt-2">Get chat messages</p>
                <div class="mt-3 bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm font-medium text-gray-700 mb-2">Request Body:</p>
                    <pre class="text-xs bg-gray-800 text-green-400 p-3 rounded overflow-x-auto"><code>{
  "sessionId": "my_session",
  "phone": "1234567890",
  "limit": 50
}</code></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Common Response Format -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800">Common Response Format</h2>
        </div>
        <div class="p-6">
            <p class="text-gray-600 text-sm mb-4">All API responses follow a consistent format:</p>
            <pre class="text-xs bg-gray-800 text-green-400 p-4 rounded overflow-x-auto"><code>{
  "success": true,
  "message": "Operation completed successfully",
  "data": { ... }
}

// Error response
{
  "success": false,
  "message": "Error description",
  "error": "Error details"
}</code></pre>
        </div>
    </div>
</div>
@endsection
