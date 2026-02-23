<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\Campaign;
use App\Models\MessageTemplate;
use App\Models\Interaction;
use App\Models\User;
use App\Models\Segment;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class CRMDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding CRM data...');

        // Get first user
        $user = User::first();

        // Get or create contacts
        $contacts = $this->seedContacts();
        $this->command->info('Created ' . count($contacts) . ' contacts');

        // Seed orders
        $this->seedOrders($contacts);
        $this->command->info('Created orders');

        // Seed tickets
        $this->seedTickets($contacts, $user);
        $this->command->info('Created tickets');

        // Seed message templates
        $templates = $this->seedMessageTemplates($user);
        $this->command->info('Created message templates');

        // Seed campaigns
        $this->seedCampaigns($contacts, $templates, $user);
        $this->command->info('Created campaigns');

        // Seed interactions
        $this->seedInteractions($contacts, $user);
        $this->command->info('Created interactions');

        $this->command->info('CRM data seeding completed!');
    }

    protected function seedContacts()
    {
        $contacts = [];
        $segment = Segment::first();
        $tags = Tag::all();

        $sampleContacts = [
            ['phone' => '6281234567001', 'name' => 'Ahmad Fauzi', 'email' => 'ahmad@example.com', 'company' => 'PT Maju Jaya'],
            ['phone' => '6281234567002', 'name' => 'Siti Rahayu', 'email' => 'siti@example.com', 'company' => 'CV Berkah Sejahtera'],
            ['phone' => '6281234567003', 'name' => 'Budi Santoso', 'email' => 'budi@example.com', 'company' => 'Toko Elektronik Budi'],
            ['phone' => '6281234567004', 'name' => 'Dewi Lestari', 'email' => 'dewi@example.com', 'company' => 'PT Sumber Rejeki'],
            ['phone' => '6281234567005', 'name' => 'Hendra Wijaya', 'email' => 'hendra@example.com', 'company' => 'UD Hendra Trading'],
            ['phone' => '6281234567006', 'name' => 'Maya Putri', 'email' => 'maya@example.com', 'company' => 'Maya Fashion'],
            ['phone' => '6281234567007', 'name' => 'Rudi Hermawan', 'email' => 'rudi@example.com', 'company' => 'Rudi Motor'],
            ['phone' => '6281234567008', 'name' => 'Lisa Amalia', 'email' => 'lisa@example.com', 'company' => 'Lisa Beauty'],
            ['phone' => '6281234567009', 'name' => 'Joko Prasetyo', 'email' => 'joko@example.com', 'company' => 'Joko Computer'],
            ['phone' => '6281234567010', 'name' => 'Nina Kartika', 'email' => 'nina@example.com', 'company' => 'Nina Bakery'],
        ];

        foreach ($sampleContacts as $contactData) {
            $contact = Contact::firstOrCreate(
                ['phone' => $contactData['phone']],
                array_merge($contactData, [
                    'segment_id' => $segment ? $segment->id : null,
                    'status' => 'active',
                    'address' => 'Jl. Merdeka No. ' . rand(1, 100) . ', Jakarta',
                    'last_contacted_at' => now()->subDays(rand(0, 30)),
                ])
            );

            // Assign random tags
            if ($tags->count() > 0 && rand(0, 1)) {
                $randomTags = $tags->random(rand(1, min(2, $tags->count())))->pluck('id')->toArray();
                $contact->tags()->sync($randomTags);
            }

            $contacts[] = $contact;
        }

        return $contacts;
    }

    protected function seedOrders($contacts)
    {
        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        $items = [
            ['name' => 'Smartphone Xiaomi', 'price' => 2500000, 'quantity' => 1],
            ['name' => 'Laptop Asus', 'price' => 5500000, 'quantity' => 1],
            ['name' => 'Headphone Sony', 'price' => 450000, 'quantity' => 2],
            ['name' => 'Keyboard Mechanical', 'price' => 350000, 'quantity' => 1],
            ['name' => 'Mouse Wireless', 'price' => 150000, 'quantity' => 2],
            ['name' => 'Monitor LG 24 inch', 'price' => 1800000, 'quantity' => 1],
            ['name' => 'Webcam Logitech', 'price' => 650000, 'quantity' => 1],
            ['name' => 'Flashdisk 32GB', 'price' => 75000, 'quantity' => 3],
        ];

        foreach ($contacts as $index => $contact) {
            // Create 1-3 orders per contact
            $numOrders = rand(1, 3);

            for ($i = 0; $i < $numOrders; $i++) {
                $orderItems = [];
                $totalAmount = 0;

                // Random items for this order
                $numItems = rand(1, 3);
                $selectedItems = array_rand($items, $numItems);

                if (!is_array($selectedItems)) {
                    $selectedItems = [$selectedItems];
                }

                foreach ($selectedItems as $key) {
                    $item = $items[$key];
                    $qty = $item['quantity'];
                    $orderItems[] = [
                        'name' => $item['name'],
                        'price' => $item['price'],
                        'quantity' => $qty,
                    ];
                    $totalAmount += $item['price'] * $qty;
                }

                $status = $statuses[array_rand($statuses)];
                $orderedAt = now()->subDays(rand(1, 60));

                Order::firstOrCreate(
                    ['order_number' => 'ORD-' . date('Ymd', $orderedAt->timestamp) . '-' . str_pad($index . $i + 1, 4, '0', STR_PAD_LEFT)],
                    [
                        'contact_id' => $contact->id,
                        'status' => $status,
                        'total_amount' => $totalAmount,
                        'currency' => 'IDR',
                        'items' => $orderItems,
                        'shipping_address' => $contact->address,
                        'shipping_method' => rand(0, 1) ? 'JNE' : 'Pos Indonesia',
                        'ordered_at' => $orderedAt,
                        'confirmed_at' => $status !== 'pending' ? $orderedAt->addHours(rand(1, 24)) : null,
                        'shipped_at' => in_array($status, ['shipped', 'delivered']) ? $orderedAt->addDays(rand(2, 5)) : null,
                        'delivered_at' => $status === 'delivered' ? $orderedAt->addDays(rand(5, 10)) : null,
                    ]
                );
            }
        }
    }

    protected function seedTickets($contacts, $user)
    {
        $subjects = [
            'Permintaan informasi produk',
            'Keluhan pengiriman tertunda',
            'Permintaan refund',
            'Pertanyaan tentang garansi',
            'Complain tentang kualitas produk',
            'Permintaan diskon khusus',
            'Bantuan teknis produk',
            'Konfirmasi pesanan',
            'Permintaan katalog produk',
            'Kerjasama bisnis',
        ];

        $statuses = ['open', 'in_progress', 'waiting_customer', 'resolved', 'closed'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $categories = ['general', 'support', 'complaint', 'sales', 'feedback'];

        foreach ($contacts as $index => $contact) {
            // Create 1-2 tickets per contact
            $numTickets = rand(1, 2);

            for ($i = 0; $i < $numTickets; $i++) {
                $status = $statuses[array_rand($statuses)];
                $createdAt = now()->subDays(rand(1, 30));

                $ticket = Ticket::firstOrCreate(
                    ['ticket_number' => 'TKT-' . date('Ymd', $createdAt->timestamp) . '-' . str_pad($index . $i + 1, 4, '0', STR_PAD_LEFT)],
                    [
                        'contact_id' => $contact->id,
                        'assigned_to' => $user ? $user->id : null,
                        'subject' => $subjects[array_rand($subjects)],
                        'description' => 'Ini adalah deskripsi ticket untuk sample data. customer menyampaikan keluhan atau pertanyaan melalui WhatsApp.',
                        'status' => $status,
                        'priority' => $priorities[array_rand($priorities)],
                        'category' => $categories[array_rand($categories)],
                        'first_response_at' => $status !== 'open' ? $createdAt->addHours(rand(1, 8)) : null,
                        'resolved_at' => in_array($status, ['resolved', 'closed']) ? $createdAt->addDays(rand(1, 5)) : null,
                        'closed_at' => $status === 'closed' ? $createdAt->addDays(rand(5, 10)) : null,
                        'response_time' => rand(15, 180),
                    ]
                );

                // Create initial message
                TicketMessage::firstOrCreate(
                    ['ticket_id' => $ticket->id],
                    [
                        'contact_id' => $contact->id,
                        'message' => 'Hi, saya ingin bertanya tentang produk ini...',
                        'is_from_customer' => true,
                        'created_at' => $createdAt,
                    ]
                );

                // Add response if ticket is not open
                if ($status !== 'open') {
                    TicketMessage::firstOrCreate(
                        [],
                        [
                            'ticket_id' => $ticket->id,
                            'user_id' => $user ? $user->id : null,
                            'message' => 'Terima kasih telah menghubungi kami. Kami akan segera memproses permintaan Anda.',
                            'is_from_customer' => false,
                            'created_at' => $createdAt->addHours(rand(1, 8)),
                        ]
                    );
                }
            }
        }
    }

    protected function seedMessageTemplates($user)
    {
        $templates = [
            [
                'name' => 'Welcome Message',
                'slug' => 'welcome-message',
                'type' => 'text',
                'category' => 'general',
                'content' => 'Halo {{name}}! Selamat datang di layanan WhatsApp kami. Ada yang bisa kami bantu?',
                'variables' => json_encode(['name']),
                'is_approved' => true,
                'approved_at' => now(),
            ],
            [
                'name' => 'Order Confirmation',
                'slug' => 'order-confirmation',
                'type' => 'text',
                'category' => 'order_confirmation',
                'content' => 'Terima kasih atas pesanan Anda! No. Pesanan: {{order_number}}. Total: Rp {{total_amount}}. Kami akan segera memproses pesanan Anda.',
                'variables' => json_encode(['order_number', 'total_amount']),
                'is_approved' => true,
                'approved_at' => now(),
            ],
            [
                'name' => 'Shipping Notification',
                'slug' => 'shipping-notification',
                'type' => 'text',
                'category' => 'shipping_notification',
                'content' => 'Pesanan Anda telah dikirim! No. Resi: {{tracking_number}}. Estimasi tiba: {{estimated_arrival}}. Terima kasih telah berbelanja.',
                'variables' => json_encode(['tracking_number', 'estimated_arrival']),
                'is_approved' => true,
                'approved_at' => now(),
            ],
            [
                'name' => 'Payment Reminder',
                'slug' => 'payment-reminder',
                'type' => 'text',
                'category' => 'payment_reminder',
                'content' => 'Halo {{name}}, pesanan Anda dengan No. {{order_number}} belum dibayar. Silakan lakukan pembayaran dalam 2x24 jam. Terima kasih.',
                'variables' => json_encode(['name', 'order_number']),
                'is_approved' => true,
                'approved_at' => now(),
            ],
            [
                'name' => 'Promo Announcement',
                'slug' => 'promo-announcement',
                'type' => 'text',
                'category' => 'promotion',
                'content' => '🔥 PROMO SPESIAL! Dapatkan diskon {{discount}}% untuk semua produk. Gunakan kode: {{code}}. Berlaku hingga {{end_date}}. Jangan lewatkan!',
                'variables' => json_encode(['discount', 'code', 'end_date']),
                'is_approved' => true,
                'approved_at' => now(),
            ],
        ];

        $createdTemplates = [];
        foreach ($templates as $templateData) {
            $template = MessageTemplate::firstOrCreate(
                ['slug' => $templateData['slug']],
                array_merge($templateData, [
                    'created_by' => $user ? $user->id : null,
                ])
            );
            $createdTemplates[] = $template;
        }

        return $createdTemplates;
    }

    protected function seedCampaigns($contacts, $templates, $user)
    {
        $campaigns = [
            [
                'name' => 'Promo Ramadan 2024',
                'slug' => 'promo-ramadan-2024',
                'type' => 'broadcast',
                'status' => 'completed',
                'total_recipients' => count($contacts),
                'sent_count' => count($contacts) - 2,
                'delivered_count' => count($contacts) - 3,
                'read_count' => count($contacts) - 5,
                'replied_count' => rand(5, 15),
                'failed_count' => 2,
            ],
            [
                'name' => 'New Product Launch',
                'slug' => 'new-product-launch',
                'type' => 'broadcast',
                'status' => 'running',
                'total_recipients' => count($contacts),
                'sent_count' => rand(1, count($contacts)),
                'delivered_count' => rand(1, count($contacts) - 1),
                'read_count' => rand(1, count($contacts) - 2),
                'replied_count' => rand(1, 10),
                'failed_count' => rand(0, 2),
            ],
            [
                'name' => 'Customer Feedback Survey',
                'slug' => 'customer-feedback-survey',
                'type' => 'broadcast',
                'status' => 'scheduled',
                'scheduled_at' => now()->addDays(7),
                'total_recipients' => count($contacts),
                'sent_count' => 0,
                'delivered_count' => 0,
                'read_count' => 0,
                'replied_count' => 0,
                'failed_count' => 0,
            ],
            [
                'name' => 'Welcome New Customers',
                'slug' => 'welcome-new-customers',
                'type' => 'trigger',
                'status' => 'running',
                'total_recipients' => 0,
                'sent_count' => 0,
                'delivered_count' => 0,
                'read_count' => 0,
                'replied_count' => 0,
                'failed_count' => 0,
            ],
        ];

        foreach ($campaigns as $campaignData) {
            $template = $templates[array_rand($templates)];

            Campaign::firstOrCreate(
                ['slug' => $campaignData['slug']],
                array_merge($campaignData, [
                    'description' => 'Campaign untuk ' . strtolower($campaignData['name']),
                    'template_id' => $template->id,
                    'target_segments' => [1],
                    'target_tags' => ['VIP', 'New'],
                    'created_by' => $user ? $user->id : null,
                    'started_at' => $campaignData['status'] === 'running' ? now()->subDays(2) : null,
                    'completed_at' => $campaignData['status'] === 'completed' ? now()->subDays(1) : null,
                ])
            );
        }
    }

    protected function seedInteractions($contacts, $user)
    {
        $directions = ['inbound', 'outbound'];
        $channels = ['whatsapp'];
        $types = ['text', 'image', 'document'];
        $statuses = ['pending', 'sent', 'delivered', 'read', 'failed'];

        $messages = [
            'Hi, saya mau tanya produk ini available ga?',
            'Thank you for your response!',
            'Apakah bisa COD?',
            'Okay, saya tertarik untuk membeli',
            'Berapa lama pengiriman ke Surabaya?',
            'Produk sudah sampai, terima kasih!',
            'Bisa minta discount ga?',
            'Saya ingin komplain tentang pesanan',
            'Terima kasih atas informasinya',
            'Apa kabar? Saya ingin order lagi',
        ];

        $responses = [
            'Halo! Terima kasih telah menghubungi. Produk tersedia, ada yang bisa kami bantu?',
            'Tentu, kami siap membantu. Ada pertanyaan lain?',
            'Ya, tersedia. Silakan pilih produk yang diinginkan.',
            'Terima kasih telah memesan. Pesanan akan kami proses.',
            'Pengiriman ke Surabaya memakan waktu 2-3 hari kerja.',
            'Mohon maaf atas ketidaknyamanan. Kami akan segera mengatasi masalah ini.',
        ];

        foreach ($contacts as $contact) {
            // Create 3-8 interactions per contact
            $numInteractions = rand(3, 8);

            for ($i = 0; $i < $numInteractions; $i++) {
                $direction = $directions[array_rand($directions)];
                $type = $types[array_rand($types)];
                $status = $statuses[array_rand($statuses)];
                $createdAt = now()->subDays(rand(0, 30))->subHours(rand(0, 23));

                $content = $direction === 'inbound' 
                    ? $messages[array_rand($messages)] 
                    : $responses[array_rand($responses)];

                Interaction::firstOrCreate(
                    [],
                    [
                        'contact_id' => $contact->id,
                        'direction' => $direction,
                        'channel' => $channels[array_rand($channels)],
                        'type' => $type,
                        'content' => $content,
                        'status' => $status,
                        'user_id' => $direction === 'outbound' ? $user ? $user->id : null : null,
                        'is_automated' => rand(0, 1) ? true : false,
                        'is_from_bot' => rand(0, 1) ? true : false,
                        'sent_at' => $direction === 'outbound' ? $createdAt : null,
                        'delivered_at' => in_array($status, ['delivered', 'read']) ? $createdAt->addMinutes(rand(1, 30)) : null,
                        'read_at' => $status === 'read' ? $createdAt->addHours(rand(1, 6)) : null,
                        'created_at' => $createdAt,
                    ]
                );
            }
        }
    }
}
