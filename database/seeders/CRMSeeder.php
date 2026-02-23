<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Segment;
use App\Models\Tag;
use App\Models\Contact;
use App\Models\Product;
use App\Models\MessageTemplate;
use App\Models\QuickReply;
use App\Models\User;

class CRMSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default segments
        $segments = [
            ['name' => 'VIP', 'slug' => 'vip', 'description' => 'VIP customers with high value', 'color' => '#fbbf24'],
            ['name' => 'Regular', 'slug' => 'regular', 'description' => 'Regular customers', 'color' => '#60a5fa'],
            ['name' => 'New', 'slug' => 'new', 'description' => 'New customers', 'color' => '#34d399'],
            ['name' => 'Inactive', 'slug' => 'inactive', 'description' => 'Inactive customers', 'color' => '#9ca3af'],
            ['name' => 'Loyal', 'slug' => 'loyal', 'description' => 'Loyal repeat customers', 'color' => '#a78bfa'],
        ];

        foreach ($segments as $segment) {
            Segment::create($segment);
        }

        // Create default tags
        $tags = [
            ['name' => 'Hot Lead', 'slug' => 'hot-lead', 'color' => '#ef4444', 'description' => 'Hot sales lead'],
            ['name' => 'Cold Lead', 'slug' => 'cold-lead', 'color' => '#3b82f6', 'description' => 'Cold sales lead'],
            ['name' => 'Customer', 'slug' => 'customer', 'color' => '#22c55e', 'description' => 'Existing customer'],
            ['name' => 'Prospect', 'slug' => 'prospect', 'color' => '#f59e0b', 'description' => 'Potential customer'],
            ['name' => 'Partner', 'slug' => 'partner', 'color' => '#8b5cf6', 'description' => 'Business partner'],
            ['name' => 'Supplier', 'slug' => 'supplier', 'color' => '#06b6d4', 'description' => 'Product supplier'],
            ['name' => 'Priority', 'slug' => 'priority', 'color' => '#ec4899', 'description' => 'Priority contact'],
            ['name' => 'Follow Up', 'slug' => 'follow-up', 'color' => '#f97316', 'description' => 'Needs follow up'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }

        // Create sample contacts
        $contacts = [
            [
                'phone' => '6281234567890',
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@email.com',
                'company' => 'PT Maju Jaya',
                'address' => 'Jl. Sudirman No. 123, Jakarta',
                'status' => 'active',
                'segment_id' => 1, // VIP
            ],
            [
                'phone' => '6281234567891',
                'name' => 'Siti Rahayu',
                'email' => 'siti.rahayu@email.com',
                'company' => 'CV Berkah Abadi',
                'address' => 'Jl. Gatot Subroto No. 45, Bandung',
                'status' => 'active',
                'segment_id' => 2, // Regular
            ],
            [
                'phone' => '6281234567892',
                'name' => 'Ahmad Hidayat',
                'email' => 'ahmad.hidayat@email.com',
                'company' => 'Toko Sejahtera',
                'address' => 'Jl. Pahlawan No. 78, Surabaya',
                'status' => 'active',
                'segment_id' => 3, // New
            ],
            [
                'phone' => '6281234567893',
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@email.com',
                'company' => 'PT Harapan Bangsa',
                'address' => 'Jl. Diponegoro No. 56, Yogyakarta',
                'status' => 'active',
                'segment_id' => 5, // Loyal
            ],
            [
                'phone' => '6281234567894',
                'name' => 'Rudi Hermawan',
                'email' => 'rudi.hermawan@email.com',
                'company' => 'UD Makmur',
                'address' => 'Jl. Ahmad Yani No. 90, Semarang',
                'status' => 'inactive',
                'segment_id' => 4, // Inactive
            ],
            [
                'phone' => '6281234567895',
                'name' => 'Rina Wati',
                'email' => 'rina.wati@email.com',
                'company' => 'PT Sukses Mandiri',
                'address' => 'Jl. Pemuda No. 12, Malang',
                'status' => 'active',
                'segment_id' => 2, // Regular
            ],
            [
                'phone' => '6281234567896',
                'name' => 'Agus Prasetyo',
                'email' => 'agus.prasetyo@email.com',
                'company' => 'CV Prima Utama',
                'address' => 'Jl. Veteran No. 34, Medan',
                'status' => 'active',
                'segment_id' => 1, // VIP
            ],
            [
                'phone' => '6281234567897',
                'name' => 'Linda Kusuma',
                'email' => 'linda.kusuma@email.com',
                'company' => 'PT Nusantara Jaya',
                'address' => 'Jl. Hayam Wuruk No. 67, Denpasar',
                'status' => 'active',
                'segment_id' => 5, // Loyal
            ],
            [
                'phone' => '6281234567898',
                'name' => 'Hendra Wijaya',
                'email' => 'hendra.wijaya@email.com',
                'company' => 'Toko Sentosa',
                'address' => 'Jl. MT Haryono No. 89, Makassar',
                'status' => 'active',
                'segment_id' => 3, // New
            ],
            [
                'phone' => '6281234567899',
                'name' => 'Maya Sari',
                'email' => 'maya.sari@email.com',
                'company' => 'PT Indah Permai',
                'address' => 'Jl. Asia Afrika No. 23, Bogor',
                'status' => 'active',
                'segment_id' => 2, // Regular
            ],
        ];

        foreach ($contacts as $contact) {
            Contact::create($contact);
        }

        // Attach tags to contacts
        $contactTags = [
            1 => [1, 3], // Budi - Hot Lead, Customer
            2 => [3, 4], // Siti - Customer, Prospect
            3 => [4, 8], // Ahmad - Prospect, Follow Up
            4 => [3, 5], // Dewi - Customer, Partner
            5 => [3], // Rudi - Customer
            6 => [3, 7], // Rina - Customer, Priority
            7 => [1, 3, 7], // Agus - Hot Lead, Customer, Priority
            8 => [3, 5], // Linda - Customer, Partner
            9 => [4, 8], // Hendra - Prospect, Follow Up
            10 => [3, 4], // Maya - Customer, Prospect
        ];

        foreach ($contactTags as $contactId => $tagIds) {
            $contact = Contact::find($contactId);
            if ($contact) {
                $contact->tags()->sync($tagIds);
            }
        }

        // Create sample products
        $products = [
            [
                'sku' => 'PRD-001',
                'name' => 'Paket Internet Bulanan 10GB',
                'description' => 'Paket internet bulanan dengan kuota 10GB',
                'price' => 50000,
                'currency' => 'IDR',
                'stock' => 999,
                'category' => 'Internet',
                'is_active' => true,
            ],
            [
                'sku' => 'PRD-002',
                'name' => 'Paket Internet Bulanan 25GB',
                'description' => 'Paket internet bulanan dengan kuota 25GB',
                'price' => 100000,
                'currency' => 'IDR',
                'stock' => 999,
                'category' => 'Internet',
                'is_active' => true,
            ],
            [
                'sku' => 'PRD-003',
                'name' => 'Paket Internet Bulanan 50GB',
                'description' => 'Paket internet bulanan dengan kuota 50GB',
                'price' => 175000,
                'currency' => 'IDR',
                'stock' => 999,
                'category' => 'Internet',
                'is_active' => true,
            ],
            [
                'sku' => 'PRD-004',
                'name' => 'Voucher Game Rp 50.000',
                'description' => 'Voucher game senilai Rp 50.000',
                'price' => 50000,
                'currency' => 'IDR',
                'stock' => 500,
                'category' => 'Game',
                'is_active' => true,
            ],
            [
                'sku' => 'PRD-005',
                'name' => 'Voucher Game Rp 100.000',
                'description' => 'Voucher game senilai Rp 100.000',
                'price' => 100000,
                'currency' => 'IDR',
                'stock' => 500,
                'category' => 'Game',
                'is_active' => true,
            ],
            [
                'sku' => 'PRD-006',
                'name' => 'Token Listrik 20.000',
                'description' => 'Token listrik PLN 20.000',
                'price' => 21500,
                'currency' => 'IDR',
                'stock' => 999,
                'category' => 'PLN',
                'is_active' => true,
            ],
            [
                'sku' => 'PRD-007',
                'name' => 'Token Listrik 50.000',
                'description' => 'Token listrik PLN 50.000',
                'price' => 51500,
                'currency' => 'IDR',
                'stock' => 999,
                'category' => 'PLN',
                'is_active' => true,
            ],
            [
                'sku' => 'PRD-008',
                'name' => 'Token Listrik 100.000',
                'description' => 'Token listrik PLN 100.000',
                'price' => 101500,
                'currency' => 'IDR',
                'stock' => 999,
                'category' => 'PLN',
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // Create message templates
        $templates = [
            [
                'name' => 'Order Confirmation',
                'slug' => 'order-confirmation',
                'type' => 'text',
                'category' => 'order_confirmation',
                'content' => 'Halo {{name}}, terima kasih telah berbelanja!\n\nPesanan Anda #{{order_number}} telah kami terima.\nTotal: {{total}}\n\nKami akan segera memproses pesanan Anda.',
                'variables' => ['name', 'order_number', 'total'],
                'is_approved' => true,
                'approved_at' => now(),
            ],
            [
                'name' => 'Payment Reminder',
                'slug' => 'payment-reminder',
                'type' => 'text',
                'category' => 'payment_reminder',
                'content' => 'Halo {{name}}, ini adalah pengingat pembayaran.\n\nPesanan #{{order_number}} senilai {{total}} belum dibayar.\n\nSilakan lakukan pembayaran sebelum {{deadline}}.',
                'variables' => ['name', 'order_number', 'total', 'deadline'],
                'is_approved' => true,
                'approved_at' => now(),
            ],
            [
                'name' => 'Shipping Notification',
                'slug' => 'shipping-notification',
                'type' => 'text',
                'category' => 'shipping_notification',
                'content' => 'Halo {{name}}, pesanan Anda sedang dalam perjalanan!\n\nNo. Pesanan: {{order_number}}\nNo. Resi: {{tracking_number}}\nKurir: {{courier}}\n\nLacak pesanan Anda di: {{tracking_url}}',
                'variables' => ['name', 'order_number', 'tracking_number', 'courier', 'tracking_url'],
                'is_approved' => true,
                'approved_at' => now(),
            ],
            [
                'name' => 'Welcome Message',
                'slug' => 'welcome-message',
                'type' => 'text',
                'category' => 'greeting',
                'content' => 'Selamat datang {{name}}! 🎉\n\nTerima kasih telah bergabung dengan kami. Kami siap melayani Anda.\n\nKetik HELP untuk melihat menu bantuan.',
                'variables' => ['name'],
                'is_approved' => true,
                'approved_at' => now(),
            ],
            [
                'name' => 'Promo Broadcast',
                'slug' => 'promo-broadcast',
                'type' => 'text',
                'category' => 'marketing',
                'content' => '🔥 PROMO SPESIAL! 🔥\n\n{{promo_title}}\n\n{{promo_description}}\n\nBerlaku sampai {{end_date}}\n\nKlik link berikut untuk info lebih lanjut:\n{{promo_link}}',
                'variables' => ['promo_title', 'promo_description', 'end_date', 'promo_link'],
                'is_approved' => true,
                'approved_at' => now(),
            ],
            [
                'name' => 'Ticket Response',
                'slug' => 'ticket-response',
                'type' => 'text',
                'category' => 'support',
                'content' => 'Halo {{name}},\n\nTiket Anda #{{ticket_number}} telah kami balas.\n\nAnda dapat melihat respons kami di aplikasi atau website.\n\nTerima kasih telah menghubungi kami.',
                'variables' => ['name', 'ticket_number'],
                'is_approved' => true,
                'approved_at' => now(),
            ],
        ];

        foreach ($templates as $template) {
            MessageTemplate::create($template);
        }

        // Create quick replies
        $quickReplies = [
            [
                'name' => 'Greeting',
                'content' => 'Halo! Selamat datang di layanan kami. Ada yang bisa kami bantu?',
                'category' => 'greeting',
            ],
            [
                'name' => 'Business Hours',
                'content' => 'Jam operasional kami:\nSenin - Jumat: 08:00 - 17:00\nSabtu: 08:00 - 12:00\nMinggu & Hari Libur: Tutup',
                'category' => 'info',
            ],
            [
                'name' => 'Payment Info',
                'content' => 'Informasi Pembayaran:\n\nBank BCA: 1234567890\na.n. PT Contoh Perusahaan\n\nBank Mandiri: 0987654321\na.n. PT Contoh Perusahaan\n\nKonfirmasi pembayaran silakan hubungi CS.',
                'category' => 'payment',
            ],
            [
                'name' => 'Shipping Info',
                'content' => 'Informasi Pengiriman:\n\n- JNE REG: 2-3 hari\n- JNE YES: 1 hari\n- J&T: 2-3 hari\n- SiCepat: 2-3 hari\n\nGratis ongkir untuk pembelian di atas Rp 200.000',
                'category' => 'shipping',
            ],
            [
                'name' => 'Thank You',
                'content' => 'Terima kasih telah menghubungi kami! Jika ada pertanyaan lain, jangan ragu untuk bertanya. 😊',
                'category' => 'closing',
            ],
            [
                'name' => 'Wait Response',
                'content' => 'Mohon tunggu sebentar, kami sedang memproses permintaan Anda.',
                'category' => 'response',
            ],
            [
                'name' => 'Transfer Confirmation',
                'content' => 'Mohon kirimkan bukti transfer Anda untuk kami verifikasi. Pastikan foto bukti transfer terlihat jelas.',
                'category' => 'payment',
            ],
            [
                'name' => 'Order Status',
                'content' => 'Untuk mengecek status pesanan, silakan berikan nomor pesanan Anda. Contoh: ORD-20240101-0001',
                'category' => 'order',
            ],
        ];

        foreach ($quickReplies as $reply) {
            QuickReply::create($reply);
        }

        $this->command->info('CRM data seeded successfully!');
        $this->command->info('- 5 Segments');
        $this->command->info('- 8 Tags');
        $this->command->info('- 10 Contacts');
        $this->command->info('- 8 Products');
        $this->command->info('- 6 Message Templates');
        $this->command->info('- 8 Quick Replies');
    }
}
