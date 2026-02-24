<?php

use App\Http\Controllers\CRMController;
use Illuminate\Support\Facades\Route;

// All CRM routes require authentication
Route::middleware(['auth'])->group(function () {

// CRM Dashboard
Route::get('/crm', [CRMController::class, 'dashboard'])->name('crm.dashboard');

// Contacts
Route::get('/crm/contacts', [CRMController::class, 'contacts'])->name('crm.contacts.index');
Route::get('/crm/contacts/create', [CRMController::class, 'createContact'])->name('crm.contacts.create');
Route::post('/crm/contacts', [CRMController::class, 'storeContact'])->name('crm.contacts.store');
Route::get('/crm/contacts/{contact}', [CRMController::class, 'showContact'])->name('crm.contacts.show');
Route::get('/crm/contacts/{contact}/send-message', [CRMController::class, 'sendContactMessage'])->name('crm.contacts.send-message');
Route::put('/crm/contacts/{contact}', [CRMController::class, 'updateContact'])->name('crm.contacts.update');
Route::delete('/crm/contacts/{contact}', [CRMController::class, 'destroyContact'])->name('crm.contacts.destroy');
Route::post('/crm/contacts/import', [CRMController::class, 'importContacts'])->name('crm.contacts.import');

// Segments
Route::get('/crm/segments', [CRMController::class, 'segments'])->name('crm.segments.index');
Route::get('/crm/segments/create', [CRMController::class, 'createSegment'])->name('crm.segments.create');
Route::post('/crm/segments', [CRMController::class, 'storeSegment'])->name('crm.segments.store');
Route::get('/crm/segments/{segment}', [CRMController::class, 'showSegment'])->name('crm.segments.show');
Route::get('/crm/segments/{segment}/edit', [CRMController::class, 'editSegment'])->name('crm.segments.edit');
Route::put('/crm/segments/{segment}', [CRMController::class, 'updateSegment'])->name('crm.segments.update');
Route::delete('/crm/segments/{segment}', [CRMController::class, 'destroySegment'])->name('crm.segments.destroy');

// Orders
Route::get('/crm/orders', [CRMController::class, 'orders'])->name('crm.orders.index');
Route::get('/crm/orders/create', [CRMController::class, 'createOrder'])->name('crm.orders.create');
Route::post('/crm/orders', [CRMController::class, 'storeOrder'])->name('crm.orders.store');
Route::get('/crm/orders/{order}', [CRMController::class, 'showOrder'])->name('crm.orders.show');
Route::get('/crm/orders/{order}/edit', [CRMController::class, 'editOrder'])->name('crm.orders.edit');
Route::put('/crm/orders/{order}', [CRMController::class, 'updateOrder'])->name('crm.orders.update');
Route::delete('/crm/orders/{order}', [CRMController::class, 'destroyOrder'])->name('crm.orders.destroy');
Route::put('/crm/orders/{order}/status', [CRMController::class, 'updateOrderStatus'])->name('crm.orders.status');

// Tickets
Route::get('/crm/tickets', [CRMController::class, 'tickets'])->name('crm.tickets.index');
Route::get('/crm/tickets/{ticket}', [CRMController::class, 'showTicket'])->name('crm.tickets.show');
Route::get('/crm/tickets/{ticket}/send-message', [CRMController::class, 'sendTicketMessage'])->name('crm.tickets.send-message');
Route::post('/crm/tickets', [CRMController::class, 'storeTicket'])->name('crm.tickets.store');
Route::post('/crm/tickets/{ticket}/reply', [CRMController::class, 'replyTicket'])->name('crm.tickets.reply');
Route::put('/crm/tickets/{ticket}/assign', [CRMController::class, 'assignTicket'])->name('crm.tickets.assign');
Route::put('/crm/tickets/{ticket}/status', [CRMController::class, 'updateTicketStatus'])->name('crm.tickets.status');

// Campaigns
Route::get('/crm/campaigns', [CRMController::class, 'campaigns'])->name('crm.campaigns.index');
Route::get('/crm/campaigns/create', [CRMController::class, 'createCampaign'])->name('crm.campaigns.create');
Route::post('/crm/campaigns', [CRMController::class, 'storeCampaign'])->name('crm.campaigns.store');
Route::get('/crm/campaigns/{campaign}', [CRMController::class, 'showCampaign'])->name('crm.campaigns.show');
Route::get('/crm/campaigns/{campaign}/edit', [CRMController::class, 'editCampaign'])->name('crm.campaigns.edit');
Route::put('/crm/campaigns/{campaign}', [CRMController::class, 'updateCampaign'])->name('crm.campaigns.update');
Route::delete('/crm/campaigns/{campaign}', [CRMController::class, 'destroyCampaign'])->name('crm.campaigns.destroy');
Route::post('/crm/campaigns/{campaign}/start', [CRMController::class, 'startCampaign'])->name('crm.campaigns.start');
Route::post('/crm/campaigns/{campaign}/pause', [CRMController::class, 'pauseCampaign'])->name('crm.campaigns.pause');
Route::get('/crm/campaigns/{campaign}/stats', [CRMController::class, 'campaignStats'])->name('crm.campaigns.stats');

// Templates
Route::get('/crm/templates', [CRMController::class, 'templates'])->name('crm.templates.index');
Route::get('/crm/templates/create', [CRMController::class, 'createTemplate'])->name('crm.templates.create');
Route::post('/crm/templates', [CRMController::class, 'storeTemplate'])->name('crm.templates.store');
Route::get('/crm/templates/{template}', [CRMController::class, 'showTemplate'])->name('crm.templates.show');
Route::get('/crm/templates/{template}/edit', [CRMController::class, 'editTemplate'])->name('crm.templates.edit');
Route::put('/crm/templates/{template}', [CRMController::class, 'updateTemplate'])->name('crm.templates.update');
Route::delete('/crm/templates/{template}', [CRMController::class, 'destroyTemplate'])->name('crm.templates.destroy');
Route::post('/crm/templates/{template}/approve', [CRMController::class, 'approveTemplate'])->name('crm.templates.approve');

// Automations
Route::get('/crm/automations', [CRMController::class, 'automations'])->name('crm.automations.index');
Route::get('/crm/automations/create', [CRMController::class, 'createAutomation'])->name('crm.automations.create');
Route::post('/crm/automations', [CRMController::class, 'storeAutomation'])->name('crm.automations.store');
Route::get('/crm/automations/{automation}', [CRMController::class, 'showAutomation'])->name('crm.automations.show');
Route::get('/crm/automations/{automation}/edit', [CRMController::class, 'editAutomation'])->name('crm.automations.edit');
Route::put('/crm/automations/{automation}', [CRMController::class, 'updateAutomation'])->name('crm.automations.update');
Route::delete('/crm/automations/{automation}', [CRMController::class, 'destroyAutomation'])->name('crm.automations.destroy');
Route::post('/crm/automations/{automation}/toggle', [CRMController::class, 'toggleAutomation'])->name('crm.automations.toggle');

// Chatbots
Route::get('/crm/chatbots', [CRMController::class, 'chatbots'])->name('crm.chatbots.index');
Route::get('/crm/chatbots/create', [CRMController::class, 'createChatbot'])->name('crm.chatbots.create');
Route::post('/crm/chatbots', [CRMController::class, 'storeChatbot'])->name('crm.chatbots.store');
Route::get('/crm/chatbots/{chatbot}', [CRMController::class, 'showChatbot'])->name('crm.chatbots.show');
Route::get('/crm/chatbots/{chatbot}/edit', [CRMController::class, 'editChatbot'])->name('crm.chatbots.edit');
Route::put('/crm/chatbots/{chatbot}', [CRMController::class, 'updateChatbot'])->name('crm.chatbots.update');
Route::delete('/crm/chatbots/{chatbot}', [CRMController::class, 'destroyChatbot'])->name('crm.chatbots.destroy');
Route::post('/crm/chatbots/{chatbot}/activate', [CRMController::class, 'activateChatbot'])->name('crm.chatbots.activate');
Route::post('/crm/chatbots/{chatbot}/deactivate', [CRMController::class, 'deactivateChatbot'])->name('crm.chatbots.deactivate');
Route::get('/crm/chatbots/{chatbot}/sessions', [CRMController::class, 'chatbotSessions'])->name('crm.chatbots.sessions');

// Products
Route::get('/crm/products', [CRMController::class, 'products'])->name('crm.products.index');
Route::get('/crm/products/create', [CRMController::class, 'createProduct'])->name('crm.products.create');
Route::post('/crm/products', [CRMController::class, 'storeProduct'])->name('crm.products.store');
Route::get('/crm/products/{product}/edit', [CRMController::class, 'editProduct'])->name('crm.products.edit');
Route::put('/crm/products/{product}', [CRMController::class, 'updateProduct'])->name('crm.products.update');
Route::delete('/crm/products/{product}', [CRMController::class, 'destroyProduct'])->name('crm.products.destroy');

// Analytics
Route::get('/crm/analytics', [CRMController::class, 'analytics'])->name('crm.analytics');

// Quick Replies
Route::get('/crm/quick-replies', [CRMController::class, 'quickReplies'])->name('crm.quick-replies.index');
Route::post('/crm/quick-replies', [CRMController::class, 'storeQuickReply'])->name('crm.quick-replies.store');
Route::get('/crm/quick-replies/{id}', [CRMController::class, 'showQuickReply'])->name('crm.quick-replies.show');
Route::put('/crm/quick-replies/{id}', [CRMController::class, 'updateQuickReply'])->name('crm.quick-replies.update');
Route::delete('/crm/quick-replies/{id}', [CRMController::class, 'destroyQuickReply'])->name('crm.quick-replies.destroy');

// Contact Assignments
Route::post('/crm/contacts/{contact}/assign', [CRMController::class, 'assignContact'])->name('crm.contacts.assign');
Route::post('/crm/contacts/assign-bulk', [CRMController::class, 'bulkAssignContact'])->name('crm.contacts.assign-bulk');

// Export Functions
Route::get('/crm/contacts/export', [CRMController::class, 'exportContacts'])->name('crm.contacts.export');
Route::get('/crm/products/export', [CRMController::class, 'exportProducts'])->name('crm.products.export');
Route::get('/crm/orders/export', [CRMController::class, 'exportOrders'])->name('crm.orders.export');

// Bulk Delete
Route::delete('/crm/contacts/bulk-delete', [CRMController::class, 'bulkDeleteContacts'])->name('crm.contacts.bulk-delete');
Route::delete('/crm/products/bulk-delete', [CRMController::class, 'bulkDeleteProducts'])->name('crm.products.bulk-delete');
Route::delete('/crm/orders/bulk-delete', [CRMController::class, 'bulkDeleteOrders'])->name('crm.orders.bulk-delete');

// Activity Log
Route::get('/crm/activity', [CRMController::class, 'activityLog'])->name('crm.activity.index');

// Tags Management
Route::get('/crm/tags', [CRMController::class, 'tags'])->name('crm.tags.index');
Route::post('/crm/tags', [CRMController::class, 'storeTag'])->name('crm.tags.store');
Route::put('/crm/tags/{tag}', [CRMController::class, 'updateTag'])->name('crm.tags.update');
Route::delete('/crm/tags/{tag}', [CRMController::class, 'destroyTag'])->name('crm.tags.destroy');

// Categories Management
Route::get('/crm/categories', [CRMController::class, 'categories'])->name('crm.categories.index');
Route::get('/crm/categories/{category}', [CRMController::class, 'showCategory'])->name('crm.categories.show');
Route::post('/crm/categories', [CRMController::class, 'storeCategory'])->name('crm.categories.store');
Route::put('/crm/categories/{category}', [CRMController::class, 'updateCategory'])->name('crm.categories.update');

// NEW FEATURES - User Management & RBAC
Route::get('/crm/users', [CRMController::class, 'users'])->name('crm.users.index');
Route::post('/crm/users', [CRMController::class, 'storeUser'])->name('crm.users.store');
Route::get('/crm/users/create', [CRMController::class, 'createUser'])->name('crm.users.create');
Route::get('/crm/users/{user}/edit', [CRMController::class, 'editUser'])->name('crm.users.edit');
Route::put('/crm/users/{user}', [CRMController::class, 'updateUser'])->name('crm.users.update');
Route::delete('/crm/users/{user}', [CRMController::class, 'destroyUser'])->name('crm.users.destroy');

// Roles Management
Route::get('/crm/roles', [CRMController::class, 'roles'])->name('crm.roles.index');
Route::post('/crm/roles', [CRMController::class, 'storeRole'])->name('crm.roles.store');
Route::get('/crm/roles/create', [CRMController::class, 'createRole'])->name('crm.roles.create');
Route::get('/crm/roles/{role}/edit', [CRMController::class, 'editRole'])->name('crm.roles.edit');
Route::put('/crm/roles/{role}', [CRMController::class, 'updateRole'])->name('crm.roles.update');
Route::delete('/crm/roles/{role}', [CRMController::class, 'destroyRole'])->name('crm.roles.destroy');

// Permissions Management
Route::get('/crm/permissions', [CRMController::class, 'permissions'])->name('crm.permissions.index');
Route::post('/crm/permissions', [CRMController::class, 'storePermission'])->name('crm.permissions.store');
Route::get('/crm/permissions/create', [CRMController::class, 'createPermission'])->name('crm.permissions.create');
Route::get('/crm/permissions/{permission}/edit', [CRMController::class, 'editPermission'])->name('crm.permissions.edit');
Route::put('/crm/permissions/{permission}', [CRMController::class, 'updatePermission'])->name('crm.permissions.update');
Route::delete('/crm/permissions/{permission}', [CRMController::class, 'destroyPermission'])->name('crm.permissions.destroy');

// NEW FEATURES - Deals Pipeline
Route::get('/crm/deals', [CRMController::class, 'deals'])->name('crm.deals.index');
Route::post('/crm/deals', [CRMController::class, 'storeDeal'])->name('crm.deals.store');
Route::get('/crm/deals/{deal}', [CRMController::class, 'showDeal'])->name('crm.deals.show');
Route::put('/crm/deals/{deal}', [CRMController::class, 'updateDeal'])->name('crm.deals.update');
Route::delete('/crm/deals/{deal}', [CRMController::class, 'destroyDeal'])->name('crm.deals.destroy');
Route::put('/crm/deals/{deal}/stage', [CRMController::class, 'updateDealStage'])->name('crm.deals.update-stage');

// NEW FEATURES - Surveys
Route::get('/crm/surveys', [CRMController::class, 'surveys'])->name('crm.surveys.index');
Route::post('/crm/surveys', [CRMController::class, 'storeSurvey'])->name('crm.surveys.store');
Route::get('/crm/surveys/{survey}', [CRMController::class, 'showSurvey'])->name('crm.surveys.show');
Route::get('/crm/surveys/{survey}/edit', [CRMController::class, 'editSurvey'])->name('crm.surveys.edit');
Route::put('/crm/surveys/{survey}', [CRMController::class, 'updateSurvey'])->name('crm.surveys.update');
Route::delete('/crm/surveys/{survey}', [CRMController::class, 'destroySurvey'])->name('crm.surveys.destroy');
Route::post('/crm/surveys/{survey}/activate', [CRMController::class, 'activateSurvey'])->name('crm.surveys.activate');
Route::post('/crm/surveys/{survey}/close', [CRMController::class, 'closeSurvey'])->name('crm.surveys.close');

// NEW FEATURES - Audit Logs
Route::get('/crm/audit-logs', [CRMController::class, 'auditLogs'])->name('crm.audit-logs.index');

// NEW FEATURES - Duplicate Detection
Route::get('/crm/duplicates', [CRMController::class, 'duplicates'])->name('crm.duplicates.index');
Route::get('/crm/duplicates/scan', [CRMController::class, 'scanDuplicates'])->name('crm.duplicates.scan');
Route::put('/crm/contacts/merge', [CRMController::class, 'mergeContacts'])->name('crm.contacts.merge');
Route::delete('/crm/categories/{category}', [CRMController::class, 'destroyCategory'])->name('crm.categories.destroy');

// Contact Notes
Route::get('/crm/contacts/{contact}/notes', [CRMController::class, 'contactNotes'])->name('crm.contacts.notes');
Route::post('/crm/contacts/{contact}/notes', [CRMController::class, 'storeContactNote'])->name('crm.contacts.notes.store');
Route::delete('/crm/contacts/{contact}/notes/{note}', [CRMController::class, 'deleteContactNote'])->name('crm.contacts.notes.delete');

// Tasks/Follow-ups
Route::get('/crm/tasks', [CRMController::class, 'tasks'])->name('crm.tasks.index');
Route::post('/crm/tasks', [CRMController::class, 'storeTask'])->name('crm.tasks.store');
Route::put('/crm/tasks/{task}', [CRMController::class, 'updateTask'])->name('crm.tasks.update');
Route::put('/crm/tasks/{task}/status', [CRMController::class, 'updateTaskStatus'])->name('crm.tasks.status');
Route::delete('/crm/tasks/{task}', [CRMController::class, 'destroyTask'])->name('crm.tasks.destroy');

// Chat/Messaging
Route::get('/crm/chat', [\App\Http\Controllers\ChatController::class, 'index'])->name('crm.chat.index');
Route::get('/crm/chat/test-api', [\App\Http\Controllers\ChatController::class, 'testApi'])->name('crm.chat.test-api');
Route::get('/crm/chat/check-new-messages', [\App\Http\Controllers\ChatController::class, 'checkNewMessages'])->name('crm.chat.check-new-messages');
Route::get('/crm/chat/conversations', [\App\Http\Controllers\ChatController::class, 'getConversations'])->name('crm.chat.conversations');
Route::get('/crm/chat/unread-count', [\App\Http\Controllers\ChatController::class, 'getUnreadCount'])->name('crm.chat.unread-count');
Route::get('/crm/chat/sessions', [\App\Http\Controllers\ChatController::class, 'getSessionsList'])->name('crm.chat.sessions');
Route::get('/crm/chat/chats', [\App\Http\Controllers\ChatController::class, 'getChats'])->name('crm.chat.chats');
Route::get('/crm/chat/messages', [\App\Http\Controllers\ChatController::class, 'getChatMessages'])->name('crm.chat.messages');
Route::get('/crm/chat/quick-replies', [\App\Http\Controllers\ChatController::class, 'getQuickReplies'])->name('crm.chat.quick-replies');
Route::get('/crm/chat/contacts', [\App\Http\Controllers\ChatController::class, 'getContacts'])->name('crm.chat.contacts');
Route::post('/crm/chat/create', [\App\Http\Controllers\ChatController::class, 'createFromContact'])->name('crm.chat.create');

// Get conversation by phone number
Route::get('/crm/chat/by-phone/{phone}', [\App\Http\Controllers\ChatController::class, 'getByPhone'])->name('crm.chat.by-phone');

// File upload endpoint
Route::post('/crm/chat/upload', [\App\Http\Controllers\ChatController::class, 'uploadFile'])->name('crm.chat.upload');

// These routes with {conversation} parameter must be after the specific routes
Route::get('/crm/chat/{conversation}', [\App\Http\Controllers\ChatController::class, 'show'])->name('crm.chat.show');
Route::get('/crm/chat/{conversation}/messages', [\App\Http\Controllers\ChatController::class, 'getMessages'])->name('crm.chat.messages');
Route::post('/crm/chat/{conversation}/send', [\App\Http\Controllers\ChatController::class, 'sendMessage'])->name('crm.chat.send');
Route::post('/crm/chat/{conversation}/assign', [\App\Http\Controllers\ChatController::class, 'assign'])->name('crm.chat.assign');
Route::post('/crm/chat/{conversation}/close', [\App\Http\Controllers\ChatController::class, 'close'])->name('crm.chat.close');
Route::post('/crm/chat/{conversation}/read', [\App\Http\Controllers\ChatController::class, 'markRead'])->name('crm.chat.read');

}); // End of auth middleware group

// Webhook for incoming messages (no auth required - external service)
Route::post('/webhook/chat', [\App\Http\Controllers\ChatController::class, 'webhook'])->name('webhook.chat');

// Role API (for AJAX)
Route::get('/crm/roles/{role}/data', [CRMController::class, 'getRoleData'])->name('crm.roles.data');

Route::get('/crm/permissions/{permission}/data', [CRMController::class, 'getPermissionData'])->name('crm.permissions.data');
