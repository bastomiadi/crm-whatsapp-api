<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\Campaign;
use App\Models\MessageTemplate;
use App\Models\Automation;
use App\Models\Chatbot;
use App\Models\ChatbotSession;
use App\Models\QuickReply;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\Task;
use App\Models\Tag;
use App\Models\Segment;
use App\Models\Product;
use App\Models\Category;
use App\Models\Deal;
use App\Models\Interaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class AgentDataSeeder extends Seeder
{
    /**
     * Run the database seeds for agent user.
     */
    public function run(): void
    {
        $this->command->info('Seeding agent data for testing...');

        // Get agent user
        $agent = User::where('email', 'agent@agent.com')->first();
        
        if (!$agent) {
            $this->command->error('Agent user not found! Please run php artisan db:seed first.');
            return;
        }

        $this->command->info('Creating data for agent user: ' . $agent->name);

        // Seed agent's products first
        $this->seedAgentProducts($agent);
        $this->command->info('Created agent products');

        // Seed agent's categories
        $this->seedAgentCategories($agent);
        $this->command->info('Created agent categories');

        // Seed agent's tags
        $this->seedAgentTags($agent);
        $this->command->info('Created agent tags');

        // Seed agent's segments
        $this->seedAgentSegments($agent);
        $this->command->info('Created agent segments');

        // Get segment and tags
        $segment = Segment::first();
        $tags = Tag::all();
        $categories = Category::all();

        // Seed agent's contacts
        $agentContacts = $this->seedAgentContacts($agent, $segment, $tags);
        $this->command->info('Created ' . count($agentContacts) . ' agent contacts');

        // Seed agent's orders
        $this->seedAgentOrders($agentContacts, $agent);
        $this->command->info('Created agent orders');

        // Seed agent's tickets
        $this->seedAgentTickets($agentContacts, $agent);
        $this->command->info('Created agent tickets');

        // Seed agent's message templates
        $this->seedAgentMessageTemplates($agent);
        $this->command->info('Created agent message templates');

        // Seed agent's campaigns
        $this->seedAgentCampaigns($agentContacts, $agent);
        $this->command->info('Created agent campaigns');

        // Seed agent's automations
        $this->seedAgentAutomations($agent);
        $this->command->info('Created agent automations');

        // Seed agent's chatbots
        $this->seedAgentChatbots($agent);
        $this->command->info('Created agent chatbots');

        // Seed agent's chatbot sessions
        $this->seedAgentChatbotSessions($agent);
        $this->command->info('Created agent chatbot sessions');

        // Seed agent's quick replies
        $this->seedAgentQuickReplies($agent);
        $this->command->info('Created agent quick replies');

        // Seed agent's surveys
        $this->seedAgentSurveys($agent);
        $this->command->info('Created agent surveys');

        // Seed agent's tasks
        $this->seedAgentTasks($agentContacts, $agent);
        $this->command->info('Created agent tasks');

        // Seed agent's deals
        $this->seedAgentDeals($agentContacts, $agent);
        $this->command->info('Created agent deals');

        // Seed agent's interactions
        $this->seedAgentInteractions($agentContacts, $agent);
        $this->command->info('Created agent interactions');

        $this->command->info('Agent data seeding completed!');
    }

    protected function seedAgentProducts($agent)
    {
        $products = [
            ['name' => 'Agent Product Alpha', 'sku' => 'AGN-ALPHA-001', 'price' => 150000],
            ['name' => 'Agent Product Beta', 'sku' => 'AGN-BETA-002', 'price' => 250000],
            ['name' => 'Agent Product Gamma', 'sku' => 'AGN-GAMMA-003', 'price' => 350000],
            ['name' => 'Agent Product Delta', 'sku' => 'AGN-DELTA-004', 'price' => 450000],
            ['name' => 'Agent Product Epsilon', 'sku' => 'AGN-EPSILON-005', 'price' => 550000],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['sku' => $product['sku']],
                [
                    'name' => $product['name'],
                    'description' => 'Product created by agent user',
                    'price' => $product['price'],
                    'stock' => rand(10, 100),
                    'is_active' => true,
                    'currency' => 'IDR',
                    'created_by' => $agent->id,
                ]
            );
        }
    }

    protected function seedAgentCategories($agent)
    {
        $categories = [
            ['name' => 'Agent Category A', 'slug' => 'agent-cat-a-' . time()],
            ['name' => 'Agent Category B', 'slug' => 'agent-cat-b-' . time()],
            ['name' => 'Agent Category C', 'slug' => 'agent-cat-c-' . time()],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => 'Category created by agent user',
                    'created_by' => $agent->id,
                ]
            );
        }
    }

    protected function seedAgentTags($agent)
    {
        $tags = [
            ['name' => 'Agent Tag 1', 'slug' => 'agent-tag-1-' . time(), 'color' => '#FF5733'],
            ['name' => 'Agent Tag 2', 'slug' => 'agent-tag-2-' . time(), 'color' => '#33FF57'],
            ['name' => 'Agent Tag 3', 'slug' => 'agent-tag-3-' . time(), 'color' => '#3357FF'],
            ['name' => 'Agent Tag 4', 'slug' => 'agent-tag-4-' . time(), 'color' => '#FF33F5'],
        ];

        foreach ($tags as $tag) {
            Tag::firstOrCreate(
                ['slug' => $tag['slug']],
                [
                    'name' => $tag['name'],
                    'color' => $tag['color'],
                    'created_by' => $agent->id,
                ]
            );
        }
    }

    protected function seedAgentSegments($agent)
    {
        $segments = [
            ['name' => 'Agent Segment VIP', 'slug' => 'agent-seg-vip-' . time()],
            ['name' => 'Agent Segment Regular', 'slug' => 'agent-seg-regular-' . time()],
            ['name' => 'Agent Segment New', 'slug' => 'agent-seg-new-' . time()],
        ];

        foreach ($segments as $segment) {
            Segment::firstOrCreate(
                ['slug' => $segment['slug']],
                [
                    'name' => $segment['name'],
                    'description' => 'Segment created by agent user',
                    'criteria' => ['field' => 'created_at', 'operator' => '>', 'value' => '2024-01-01'],
                    'is_dynamic' => true,
                    'color' => '#' . dechex(rand(0, 16777215)),
                ]
            );
        }
    }

    protected function seedAgentContacts($agent, $segment, $tags)
    {
        $contacts = [];
        
        $agentContactData = [
            ['phone' => '6289876543001', 'name' => 'Agen Satu', 'email' => 'agensatu@test.com', 'company' => 'PT Agen Jaya'],
            ['phone' => '6289876543002', 'name' => 'Agen Dua', 'email' => 'agendua@test.com', 'company' => 'CV Agen Maju'],
            ['phone' => '6289876543003', 'name' => 'Agen Tiga', 'email' => 'agentiga@test.com', 'company' => 'Toko Agen Bersama'],
            ['phone' => '6289876543004', 'name' => 'Agen Empat', 'email' => 'agenempat@test.com', 'company' => 'UD Agen Sejahtera'],
            ['phone' => '6289876543005', 'name' => 'Agen Lima', 'email' => 'agenlima@test.com', 'company' => 'Agen Lima Corp'],
        ];

        foreach ($agentContactData as $data) {
            $contact = Contact::firstOrCreate(
                ['phone' => $data['phone']],
                array_merge($data, [
                    'segment_id' => $segment ? $segment->id : null,
                    'status' => 'active',
                    'address' => 'Jl. Agent No. ' . rand(1, 50) . ', Bandung',
                    'last_contacted_at' => now()->subDays(rand(0, 30)),
                    'created_by' => $agent->id,
                ])
            );

            if ($tags->count() > 0 && rand(0, 1)) {
                $randomTags = $tags->random(rand(1, min(2, $tags->count())))->pluck('id')->toArray();
                $contact->tags()->sync($randomTags);
            }

            $contacts[] = $contact;
        }

        return $contacts;
    }

    protected function seedAgentOrders($contacts, $agent)
    {
        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

        foreach ($contacts as $contact) {
            $numOrders = rand(1, 3);
            
            for ($i = 0; $i < $numOrders; $i++) {
                $orderNumber = 'AGN-' . $agent->id . '-' . uniqid();
                Order::firstOrCreate(
                    [
                        'order_number' => $orderNumber,
                    ],
                    [
                        'contact_id' => $contact->id,
                        'status' => $statuses[array_rand($statuses)],
                        'total_amount' => rand(100000, 5000000),
                        'currency' => 'IDR',
                        'items' => [
                            ['name' => 'Product ' . rand(1, 10), 'qty' => rand(1, 5), 'price' => rand(50000, 500000)]
                        ],
                        'created_by' => $agent->id,
                    ]
                );
            }
        }
    }

    protected function seedAgentTickets($contacts, $agent)
    {
        $statuses = ['open', 'in_progress', 'waiting_customer', 'resolved', 'closed'];
        $priorities = ['low', 'medium', 'high', 'urgent'];

        foreach ($contacts as $contact) {
            $numTickets = rand(1, 2);
            
            for ($i = 0; $i < $numTickets; $i++) {
                $ticketNumber = 'TKT-AGN-' . $agent->id . '-' . rand(1000, 9999);
                Ticket::firstOrCreate(
                    [
                        'ticket_number' => $ticketNumber,
                    ],
                    [
                        'contact_id' => $contact->id,
                        'subject' => 'Agent Ticket ' . rand(1000, 9999),
                        'description' => 'Ticket created by agent user for testing',
                        'status' => $statuses[array_rand($statuses)],
                        'priority' => $priorities[array_rand($priorities)],
                        'category' => 'support',
                        'assigned_to' => $agent->id,
                        'created_by' => $agent->id,
                    ]
                );
            }
        }
    }

    protected function seedAgentMessageTemplates($agent)
    {
        $templates = [
            ['name' => 'Agent Greeting', 'content' => 'Hello! This is agent greeting message.', 'slug' => 'agent-greeting'],
            ['name' => 'Agent Follow Up', 'content' => 'Hi, just following up on our previous conversation.', 'slug' => 'agent-follow-up'],
            ['name' => 'Agent Thank You', 'content' => 'Thank you for contacting us!', 'slug' => 'agent-thank-you'],
            ['name' => 'Agent Goodbye', 'content' => 'Thank you, have a great day!', 'slug' => 'agent-goodbye'],
        ];

        foreach ($templates as $template) {
            MessageTemplate::firstOrCreate(
                ['slug' => $template['slug']],
                [
                    'name' => $template['name'],
                    'content' => $template['content'],
                    'category' => 'Agent',
                    'created_by' => $agent->id,
                ]
            );
        }
    }

    protected function seedAgentCampaigns($contacts, $agent)
    {
        $campaigns = [
            ['name' => 'Agent Promo January', 'slug' => 'agent-promo-jan-' . time(), 'status' => 'completed', 'description' => 'January promo campaign'],
            ['name' => 'Agent Promo February', 'slug' => 'agent-promo-feb-' . time(), 'status' => 'running', 'description' => 'February promo campaign'],
            ['name' => 'Agent Promo March', 'slug' => 'agent-promo-mar-' . time(), 'status' => 'draft', 'description' => 'March promo campaign'],
        ];

        foreach ($campaigns as $campaign) {
            Campaign::firstOrCreate(
                ['slug' => $campaign['slug']],
                [
                    'name' => $campaign['name'],
                    'description' => $campaign['description'],
                    'type' => 'broadcast',
                    'status' => $campaign['status'],
                    'scheduled_at' => now()->addDays(rand(1, 30)),
                    'created_by' => $agent->id,
                ]
            );
        }
    }

    protected function seedAgentAutomations($agent)
    {
        $automations = [
            ['name' => 'Agent Welcome Flow', 'trigger_type' => 'contact_created'],
            ['name' => 'Agent Follow Up Flow', 'trigger_type' => 'contact_tagged'],
            ['name' => 'Agent Reminder Flow', 'trigger_type' => 'scheduled'],
        ];

        foreach ($automations as $automation) {
            Automation::firstOrCreate(
                ['name' => $automation['name']],
                [
                    'description' => 'Automation created by agent user',
                    'trigger_type' => $automation['trigger_type'],
                    'trigger_config' => [],
                    'conditions' => [],
                    'actions' => [
                        ['action' => 'send_message', 'delay' => 0],
                        ['action' => 'wait', 'delay' => 3600],
                        ['action' => 'send_message', 'delay' => 0],
                    ],
                    'is_active' => true,
                    'created_by' => $agent->id,
                ]
            );
        }
    }

    protected function seedAgentChatbots($agent)
    {
        $chatbots = [
            ['name' => 'Agent Sales Bot', 'description' => 'Sales chatbot for agent'],
            ['name' => 'Agent Support Bot', 'description' => 'Support chatbot for agent'],
            ['name' => 'Agent FAQ Bot', 'description' => 'FAQ chatbot for agent'],
        ];

        foreach ($chatbots as $chatbot) {
            Chatbot::firstOrCreate(
                ['name' => $chatbot['name']],
                [
                    'description' => $chatbot['description'],
                    'status' => 'active',
                    'default_response' => ['Hello! How can I help you?'],
                    'fallback_response' => ['Sorry, I did not understand.'],
                    'keywords' => [],
                    'flows' => [],
                    'working_hours' => [],
                    'created_by' => $agent->id,
                ]
            );
        }
    }

    protected function seedAgentQuickReplies($agent)
    {
        $replies = [
            ['name' => 'Agent Hello', 'content' => 'Hello! How can I help you? (Agent)'],
            ['name' => 'Agent Thanks', 'content' => 'Thank you for your patience! (Agent)'],
            ['name' => 'Agent Sorry', 'content' => 'I apologize for the inconvenience. (Agent)'],
            ['name' => 'Agent Bye', 'content' => 'Goodbye! Have a nice day! (Agent)'],
            ['name' => 'Agent Info', 'content' => 'Let me check that information for you. (Agent)'],
            ['name' => 'Agent Wait', 'content' => 'Please wait a moment while I check. (Agent)'],
            ['name' => 'Agent Transfer', 'content' => 'I will transfer you to our specialist. (Agent)'],
            ['name' => 'Agent Resolve', 'content' => 'Your issue has been resolved. (Agent)'],
        ];

        foreach ($replies as $reply) {
            QuickReply::firstOrCreate(
                ['name' => $reply['name']],
                array_merge($reply, [
                    'category' => 'Agent',
                    'created_by' => $agent->id,
                ])
            );
        }
    }

    protected function seedAgentSurveys($agent)
    {
        $surveys = [
            ['title' => 'Agent NPS Survey', 'type' => 'nps', 'status' => 'active'],
            ['title' => 'Agent Satisfaction Survey', 'type' => 'satisfaction', 'status' => 'active'],
            ['title' => 'Agent Feedback Survey', 'type' => 'feedback', 'status' => 'draft'],
        ];

        foreach ($surveys as $survey) {
            $createdSurvey = Survey::firstOrCreate(
                ['title' => $survey['title']],
                array_merge($survey, [
                    'description' => 'Survey created by agent user for testing',
                    'created_by' => $agent->id,
                ])
            );

            // Add some responses
            if ($survey['status'] === 'active' && rand(0, 1)) {
                $contacts = Contact::where('created_by', $agent->id)->limit(3)->get();
                foreach ($contacts as $contact) {
                    SurveyResponse::firstOrCreate(
                        [
                            'survey_id' => $createdSurvey->id,
                            'contact_id' => $contact->id,
                        ],
                        [
                            'answers' => ['q1' => rand(1, 5)],
                            'nps_score' => rand(1, 10),
                            'satisfaction_score' => rand(1, 5),
                        ]
                    );
                }
            }
        }
    }

    protected function seedAgentTasks($contacts, $agent)
    {
        $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        $priorities = ['low', 'medium', 'high', 'urgent'];

        $taskNames = [
            'Follow up with client',
            'Send proposal',
            'Schedule meeting',
            'Prepare invoice',
            'Make call',
            'Send email',
            'Update CRM',
            'Review contract',
        ];

        foreach ($contacts as $contact) {
            $numTasks = rand(1, 3);
            
            for ($i = 0; $i < $numTasks; $i++) {
                Task::firstOrCreate(
                    [
                        'contact_id' => $contact->id,
                        'title' => $taskNames[array_rand($taskNames)] . ' - ' . $contact->name,
                    ],
                    [
                        'description' => 'Task created by agent user',
                        'status' => $statuses[array_rand($statuses)],
                        'priority' => $priorities[array_rand($priorities)],
                        'due_date' => now()->addDays(rand(1, 14)),
                        'assigned_to' => $agent->id,
                        'created_by' => $agent->id,
                    ]
                );
            }
        }
    }

    protected function seedAgentDeals($contacts, $agent)
    {
        $stages = ['lead', 'qualified', 'proposal', 'negotiation', 'closed_won', 'closed_lost'];

        $dealNames = [
            'Agent Sales Deal',
            'Agent Service Deal',
            'Agent Product Deal',
            'Agent Partnership Deal',
            'Agent Renewal Deal',
        ];

        foreach ($contacts as $contact) {
            $numDeals = rand(1, 2);
            
            for ($i = 0; $i < $numDeals; $i++) {
                Deal::firstOrCreate(
                    [
                        'contact_id' => $contact->id,
                        'title' => $dealNames[array_rand($dealNames)] . ' - ' . $contact->name,
                    ],
                    [
                        'stage' => $stages[array_rand($stages)],
                        'value' => rand(1000000, 50000000),
                        'probability' => rand(10, 100),
                        'assigned_to' => $agent->id,
                    ]
                );
            }
        }
    }

    protected function seedAgentInteractions($contacts, $agent)
    {
        $directions = ['inbound', 'outbound'];
        $channels = ['whatsapp', 'email', 'sms', 'webchat'];
        $types = ['text', 'image', 'document', 'audio', 'video'];
        $statuses = ['pending', 'sent', 'delivered', 'read', 'failed'];
        
        $messageTemplates = [
            'Hi {{name}}, thank you for contacting us!',
            'Hello! How can I help you today?',
            'Thank you for your message. We will respond shortly.',
            'Your inquiry has been received. Our team will contact you soon.',
            'Welcome to our service! Let us know how we can assist you.',
        ];

        foreach ($contacts as $contact) {
            // Create 3-8 interactions per contact
            $numInteractions = rand(3, 8);
            
            for ($i = 0; $i < $numInteractions; $i++) {
                $direction = $directions[array_rand($directions)];
                $channel = $channels[array_rand($channels)];
                $type = $types[array_rand($types)];
                $status = $statuses[array_rand($statuses)];
                
                // Generate message content
                $content = str_replace('{{name}}', $contact->name, $messageTemplates[array_rand($messageTemplates)]);
                if ($direction === 'inbound') {
                    $content = 'Customer: ' . $content;
                } else {
                    $content = 'Agent: ' . $content;
                }
                
                $sentAt = now()->subDays(rand(0, 30))->subHours(rand(0, 23));
                
                Interaction::firstOrCreate(
                    [
                        'contact_id' => $contact->id,
                        'direction' => $direction,
                        'channel' => $channel,
                        'type' => $type,
                        'content' => $content,
                    ],
                    [
                        'status' => $status,
                        'user_id' => $direction === 'outbound' ? $agent->id : null,
                        'is_automated' => rand(0, 1) == 1,
                        'is_from_bot' => rand(0, 1) == 1 && $direction === 'inbound',
                        'sent_at' => $sentAt,
                        'delivered_at' => in_array($status, ['delivered', 'read']) ? $sentAt->addMinutes(rand(1, 5)) : null,
                        'read_at' => $status === 'read' ? $sentAt->addMinutes(rand(5, 30)) : null,
                    ]
                );
            }
        }
    }

    protected function seedAgentChatbotSessions($agent)
    {
        // Get agent's chatbots
        $chatbots = Chatbot::where('created_by', $agent->id)->get();
        
        if ($chatbots->isEmpty()) {
            $this->command->info('No agent chatbots found, skipping sessions');
            return;
        }

        // Get agent's contacts
        $contacts = Contact::where('created_by', $agent->id)->get();
        
        if ($contacts->isEmpty()) {
            $this->command->info('No agent contacts found, skipping sessions');
            return;
        }

        $statuses = ['active', 'completed', 'expired'];
        $nodeIds = ['start', 'menu', 'promo', 'order', 'support', 'end'];

        foreach ($chatbots as $chatbot) {
            // Create 3-10 sessions per chatbot
            $numSessions = rand(3, 10);
            
            for ($i = 0; $i < $numSessions; $i++) {
                $contact = $contacts->random();
                $status = $statuses[array_rand($statuses)];
                $currentNode = $nodeIds[array_rand($nodeIds)];
                $sessionId = \Illuminate\Support\Str::uuid()->toString();
                
                $createdAt = now()->subDays(rand(0, 30));
                
                ChatbotSession::firstOrCreate(
                    [
                        'session_id' => $sessionId,
                    ],
                    [
                        'chatbot_id' => $chatbot->id,
                        'contact_id' => $contact->id,
                        'current_node' => $currentNode,
                        'context' => [
                            'name' => $contact->name,
                            'phone' => $contact->phone,
                        ],
                        'history' => [
                            ['type' => 'user', 'message' => 'Hello', 'timestamp' => $createdAt->toIso8601String()],
                            ['type' => 'bot', 'message' => 'Welcome! How can I help?', 'timestamp' => $createdAt->addSeconds(2)->toIso8601String()],
                        ],
                        'status' => $status,
                        'expires_at' => $status === 'active' ? now()->addHours(24) : ($status === 'expired' ? $createdAt->subHours(1) : null),
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]
                );
            }
        }
    }
}
