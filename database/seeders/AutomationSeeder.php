<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Automation;

class AutomationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $automations = [
            [
                'name' => 'Welcome New Contact',
                'description' => 'Send welcome message when new contact is added',
                'trigger_type' => 'contact_created',
                'trigger_config' => [
                    'segment_id' => null, // All segments
                ],
                'actions' => [
                    [
                        'type' => 'send_message',
                        'config' => [
                            'template_id' => 4, // Welcome Message template
                            'use_template' => true,
                        ],
                    ],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Order Confirmation',
                'description' => 'Send confirmation when order is placed',
                'trigger_type' => 'order_created',
                'trigger_config' => [
                    'status' => 'pending',
                ],
                'actions' => [
                    [
                        'type' => 'send_message',
                        'config' => [
                            'template_id' => 1, // Order Confirmation template
                            'use_template' => true,
                        ],
                    ],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Shipping Notification',
                'description' => 'Send notification when order is shipped',
                'trigger_type' => 'order_status_changed',
                'trigger_config' => [
                    'status' => 'shipped',
                ],
                'actions' => [
                    [
                        'type' => 'send_message',
                        'config' => [
                            'template_id' => 3, // Shipping Notification template
                            'use_template' => true,
                        ],
                    ],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Payment Reminder',
                'description' => 'Send payment reminder for pending orders',
                'trigger_type' => 'scheduled',
                'trigger_config' => [
                    'schedule' => 'daily',
                    'time' => '10:00',
                    'conditions' => [
                        'order_status' => 'pending',
                        'hours_since_created' => 24,
                    ],
                ],
                'actions' => [
                    [
                        'type' => 'send_message',
                        'config' => [
                            'template_id' => 2, // Payment Reminder template
                            'use_template' => true,
                        ],
                    ],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Ticket Auto Reply',
                'description' => 'Send auto reply when new ticket is created',
                'trigger_type' => 'ticket_created',
                'trigger_config' => [],
                'actions' => [
                    [
                        'type' => 'send_message',
                        'config' => [
                            'message' => 'Halo {{name}}, terima kasih telah menghubungi kami. Tiket Anda #{{ticket_number}} telah kami terima dan akan segera ditindaklanjuti oleh tim kami.',
                        ],
                    ],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Ticket Status Update',
                'description' => 'Send notification when ticket status changes',
                'trigger_type' => 'ticket_status_changed',
                'trigger_config' => [
                    'status' => 'resolved',
                ],
                'actions' => [
                    [
                        'type' => 'send_message',
                        'config' => [
                            'message' => 'Halo {{name}}, tiket Anda #{{ticket_number}} telah diselesaikan. Terima kasih telah menghubungi kami!',
                        ],
                    ],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Keyword: Product Info',
                'description' => 'Auto reply when customer asks about products',
                'trigger_type' => 'keyword_detected',
                'trigger_config' => [
                    'keywords' => ['produk', 'product', 'barang', 'katalog', 'catalog'],
                    'match_type' => 'contains',
                ],
                'actions' => [
                    [
                        'type' => 'send_message',
                        'config' => [
                            'message' => 'Berikut adalah daftar produk kami:\n\n1. Paket Internet 10GB - Rp 50.000\n2. Paket Internet 25GB - Rp 100.000\n3. Paket Internet 50GB - Rp 175.000\n4. Voucher Game Rp 50.000\n5. Voucher Game Rp 100.000\n6. Token Listrik 20.000\n7. Token Listrik 50.000\n8. Token Listrik 100.000\n\nKetik nomor produk untuk informasi lebih lanjut.',
                        ],
                    ],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Keyword: Price List',
                'description' => 'Auto reply when customer asks about prices',
                'trigger_type' => 'keyword_detected',
                'trigger_config' => [
                    'keywords' => ['harga', 'price', 'biaya', 'tarif', 'cost'],
                    'match_type' => 'contains',
                ],
                'actions' => [
                    [
                        'type' => 'send_message',
                        'config' => [
                            'message' => 'Untuk informasi harga lengkap, silakan ketik:\n\n- PRODUK untuk melihat daftar produk\n- INTERNET untuk paket internet\n- GAME untuk voucher game\n- PLN untuk token listrik\n\nAtau hubungi CS kami untuk penawaran khusus!',
                        ],
                    ],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Keyword: Help Menu',
                'description' => 'Show help menu when customer types help',
                'trigger_type' => 'keyword_detected',
                'trigger_config' => [
                    'keywords' => ['help', 'bantuan', 'menu', '?'],
                    'match_type' => 'exact',
                ],
                'actions' => [
                    [
                        'type' => 'send_message',
                        'config' => [
                            'message' => '📋 MENU UTAMA\n\nKetik perintah berikut:\n\n1. PRODUK - Lihat daftar produk\n2. HARGA - Lihat daftar harga\n3. PESANAN - Cek status pesanan\n4. TIKET - Buat tiket bantuan\n5. CS - Hubungi customer service\n\nAtau ketik pertanyaan Anda langsung.',
                        ],
                    ],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Inactive Customer Follow-up',
                'description' => 'Send follow-up to inactive customers',
                'trigger_type' => 'scheduled',
                'trigger_config' => [
                    'schedule' => 'weekly',
                    'day' => 'monday',
                    'time' => '09:00',
                    'conditions' => [
                        'segment_id' => 4, // Inactive segment
                        'days_since_last_interaction' => 30,
                    ],
                ],
                'actions' => [
                    [
                        'type' => 'send_message',
                        'config' => [
                            'message' => 'Halo {{name}}, kami merindukan Anda! 🥰\n\nAda promo spesial untuk Anda:\n- Diskon 20% untuk semua produk\n- Berlaku hingga akhir bulan\n\nKetik PROMO untuk info lebih lanjut.',
                        ],
                    ],
                ],
                'is_active' => true,
            ],
        ];

        foreach ($automations as $automation) {
            Automation::create($automation);
        }

        $this->command->info('Automations seeded successfully! (10 automations)');
    }
}
