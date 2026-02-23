<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Chatbot;

class ChatbotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chatbots = [
            [
                'name' => 'Main Menu Bot',
                'description' => 'Main chatbot for handling customer inquiries',
                'status' => 'active',
                'keywords' => ['menu', 'utama', 'main', 'start', 'mulai'],
                'flows' => [
                    [
                        'id' => 'start',
                        'type' => 'message',
                        'content' => 'Selamat datang di *Layanan Kami*! 🎉\n\nSilakan pilih menu:\n1. Cek Pesanan\n2. Beli Produk\n3. Bantuan\n4. Hubungi CS\n\nKetik nomor atau nama menu.',
                        'next' => 'menu_selection',
                    ],
                    [
                        'id' => 'menu_selection',
                        'type' => 'condition',
                        'conditions' => [
                            [
                                'match' => ['1', 'pesanan', 'cek pesanan', 'order'],
                                'next' => 'check_order',
                            ],
                            [
                                'match' => ['2', 'beli', 'produk', 'product'],
                                'next' => 'buy_product',
                            ],
                            [
                                'match' => ['3', 'bantuan', 'help'],
                                'next' => 'help_menu',
                            ],
                            [
                                'match' => ['4', 'cs', 'customer service', 'hubungi'],
                                'next' => 'contact_cs',
                            ],
                        ],
                        'default' => 'invalid_input',
                    ],
                    [
                        'id' => 'check_order',
                        'type' => 'message',
                        'content' => '📦 CEK PESANAN\n\nSilakan ketik nomor pesanan Anda.\nContoh: ORD-20240101-0001',
                        'next' => 'wait_order_number',
                    ],
                    [
                        'id' => 'wait_order_number',
                        'type' => 'input',
                        'variable' => 'order_number',
                        'next' => 'process_order_check',
                    ],
                    [
                        'id' => 'process_order_check',
                        'type' => 'api_call',
                        'endpoint' => '/api/internal/check-order',
                        'method' => 'POST',
                        'params' => ['order_number' => '{{order_number}}'],
                        'success_next' => 'show_order_status',
                        'error_next' => 'order_not_found',
                    ],
                    [
                        'id' => 'show_order_status',
                        'type' => 'message',
                        'content' => '📦 Status Pesanan: {{order_number}}\n\nStatus: {{order_status}}\nTotal: {{order_total}}\n\n{{order_details}}',
                        'next' => 'end',
                    ],
                    [
                        'id' => 'order_not_found',
                        'type' => 'message',
                        'content' => '❌ Maaf, pesanan tidak ditemukan.\n\nPastikan nomor pesanan benar atau hubungi CS untuk bantuan.',
                        'next' => 'start',
                    ],
                    [
                        'id' => 'buy_product',
                        'type' => 'message',
                        'content' => '🛒 BELI PRODUK\n\nPilih kategori:\n1. Paket Internet\n2. Voucher Game\n3. Token Listrik\n\nKetik nomor kategori.',
                        'next' => 'category_selection',
                    ],
                    [
                        'id' => 'category_selection',
                        'type' => 'condition',
                        'conditions' => [
                            [
                                'match' => ['1', 'internet', 'paket internet'],
                                'next' => 'internet_products',
                            ],
                            [
                                'match' => ['2', 'game', 'voucher game'],
                                'next' => 'game_products',
                            ],
                            [
                                'match' => ['3', 'pln', 'token listrik', 'listrik'],
                                'next' => 'pln_products',
                            ],
                        ],
                        'default' => 'invalid_input',
                    ],
                    [
                        'id' => 'internet_products',
                        'type' => 'message',
                        'content' => '📡 PAKET INTERNET\n\n1. 10GB - Rp 50.000\n2. 25GB - Rp 100.000\n3. 50GB - Rp 175.000\n\nKetik nomor produk untuk membeli.',
                        'next' => 'process_purchase',
                    ],
                    [
                        'id' => 'game_products',
                        'type' => 'message',
                        'content' => '🎮 VOUCHER GAME\n\n1. Rp 50.000\n2. Rp 100.000\n\nKetik nomor produk untuk membeli.',
                        'next' => 'process_purchase',
                    ],
                    [
                        'id' => 'pln_products',
                        'type' => 'message',
                        'content' => '⚡ TOKEN LISTRIK\n\n1. 20.000 - Rp 21.500\n2. 50.000 - Rp 51.500\n3. 100.000 - Rp 101.500\n\nKetik nomor produk untuk membeli.',
                        'next' => 'process_purchase',
                    ],
                    [
                        'id' => 'process_purchase',
                        'type' => 'message',
                        'content' => '✅ Produk dipilih!\n\nSilakan ketik nomor tujuan:\n- Untuk paket internet: nomor HP\n- Untuk voucher game: ID Game\n- Untuk token listrik: nomor meter PLN',
                        'next' => 'wait_destination',
                    ],
                    [
                        'id' => 'wait_destination',
                        'type' => 'input',
                        'variable' => 'destination_number',
                        'next' => 'confirm_purchase',
                    ],
                    [
                        'id' => 'confirm_purchase',
                        'type' => 'message',
                        'content' => '📋 KONFIRMASI PEMBELIAN\n\nProduk: {{product_name}}\nNomor Tujuan: {{destination_number}}\nHarga: {{product_price}}\n\nKetik KONFIRMASI untuk melanjutkan atau BATAL untuk membatalkan.',
                        'next' => 'wait_confirmation',
                    ],
                    [
                        'id' => 'wait_confirmation',
                        'type' => 'condition',
                        'conditions' => [
                            [
                                'match' => ['konfirmasi', 'confirm', 'ya', 'yes'],
                                'next' => 'create_order',
                            ],
                            [
                                'match' => ['batal', 'cancel', 'tidak', 'no'],
                                'next' => 'cancel_order',
                            ],
                        ],
                        'default' => 'invalid_input',
                    ],
                    [
                        'id' => 'create_order',
                        'type' => 'api_call',
                        'endpoint' => '/api/internal/create-order',
                        'method' => 'POST',
                        'params' => [
                            'product_id' => '{{product_id}}',
                            'destination' => '{{destination_number}}',
                        ],
                        'success_next' => 'order_created',
                        'error_next' => 'order_error',
                    ],
                    [
                        'id' => 'order_created',
                        'type' => 'message',
                        'content' => '✅ PESANAN BERHASIL!\n\nNomor Pesanan: {{new_order_number}}\n\nSilakan lakukan pembayaran dalam 24 jam.\n\nKetik BAYAR untuk melihat informasi pembayaran.',
                        'next' => 'end',
                    ],
                    [
                        'id' => 'order_error',
                        'type' => 'message',
                        'content' => '❌ Terjadi kesalahan saat membuat pesanan.\n\nSilakan coba lagi atau hubungi CS.',
                        'next' => 'start',
                    ],
                    [
                        'id' => 'cancel_order',
                        'type' => 'message',
                        'content' => '❌ Pesanan dibatalkan.\n\nKetik MENU untuk kembali ke menu utama.',
                        'next' => 'start',
                    ],
                    [
                        'id' => 'help_menu',
                        'type' => 'message',
                        'content' => '❓ BANTUAN\n\n1. Cara Pembelian\n2. Cara Pembayaran\n3. Kebijakan Refund\n4. FAQ\n\nKetik nomor untuk informasi lebih lanjut.',
                        'next' => 'help_selection',
                    ],
                    [
                        'id' => 'help_selection',
                        'type' => 'condition',
                        'conditions' => [
                            [
                                'match' => ['1'],
                                'next' => 'help_purchase',
                            ],
                            [
                                'match' => ['2'],
                                'next' => 'help_payment',
                            ],
                            [
                                'match' => ['3'],
                                'next' => 'help_refund',
                            ],
                            [
                                'match' => ['4'],
                                'next' => 'help_faq',
                            ],
                        ],
                        'default' => 'invalid_input',
                    ],
                    [
                        'id' => 'help_purchase',
                        'type' => 'message',
                        'content' => '📖 CARA PEMBELIAN\n\n1. Ketik MENU untuk melihat produk\n2. Pilih kategori produk\n3. Pilih produk yang diinginkan\n4. Masukkan nomor tujuan\n5. Konfirmasi pembelian\n6. Lakukan pembayaran\n\nKetik MENU untuk mulai berbelanja.',
                        'next' => 'start',
                    ],
                    [
                        'id' => 'help_payment',
                        'type' => 'message',
                        'content' => '💳 CARA PEMBAYARAN\n\nTransfer ke rekening berikut:\n\nBank BCA: 1234567890\na.n. PT Contoh Perusahaan\n\nBank Mandiri: 0987654321\na.n. PT Contoh Perusahaan\n\nSetelah transfer, kirim bukti pembayaran ke CS kami.',
                        'next' => 'start',
                    ],
                    [
                        'id' => 'help_refund',
                        'type' => 'message',
                        'content' => '🔄 KEBIJAKAN REFUND\n\n- Refund tersedia dalam 7 hari\n- Produk yang sudah digunakan tidak dapat di-refund\n- Proses refund 3-5 hari kerja\n\nHubungi CS untuk pengajuan refund.',
                        'next' => 'start',
                    ],
                    [
                        'id' => 'help_faq',
                        'type' => 'message',
                        'content' => '❓ FAQ\n\nQ: Berapa lama proses pengiriman?\nA: Produk digital langsung dikirim dalam 5 menit.\n\nQ: Bagaimana jika produk tidak masuk?\nA: Hubungi CS dengan bukti pembelian.\n\nQ: Apakah tersedia 24 jam?\nA: Ya, layanan kami tersedia 24 jam.\n\nKetik MENU untuk kembali.',
                        'next' => 'start',
                    ],
                    [
                        'id' => 'contact_cs',
                        'type' => 'message',
                        'content' => '👨‍💼 CUSTOMER SERVICE\n\nAnda akan dihubungkan dengan CS kami.\n\nJam operasional:\nSenin - Jumat: 08:00 - 17:00\nSabtu: 08:00 - 12:00\n\nDi luar jam operasional, pesan Anda akan dibalas keesokan harinya.',
                        'next' => 'transfer_to_cs',
                    ],
                    [
                        'id' => 'transfer_to_cs',
                        'type' => 'transfer',
                        'transfer_to' => 'human',
                        'message' => 'Customer meminta bantuan CS.',
                    ],
                    [
                        'id' => 'invalid_input',
                        'type' => 'message',
                        'content' => '❌ Maaf, pilihan tidak valid.\n\nSilakan ketik MENU untuk kembali ke menu utama.',
                        'next' => 'start',
                    ],
                    [
                        'id' => 'end',
                        'type' => 'end',
                        'message' => 'Terima kasih telah menggunakan layanan kami! 😊\n\nKetik MENU untuk kembali ke menu utama.',
                    ],
                ],
                'default_response' => [
                    'message' => 'Maaf, saya tidak mengerti. Ketik MENU untuk melihat pilihan yang tersedia.',
                ],
                'fallback_response' => [
                    'message' => 'Maaf, terjadi kesalahan. Silakan coba lagi atau hubungi CS.',
                ],
                'handover_enabled' => true,
            ],
            [
                'name' => 'Order Status Bot',
                'description' => 'Quick order status checker',
                'status' => 'active',
                'keywords' => ['order', 'pesanan', 'status', 'cek'],
                'flows' => [
                    [
                        'id' => 'start',
                        'type' => 'message',
                        'content' => '📦 CEK STATUS PESANAN\n\nSilakan ketik nomor pesanan Anda:',
                        'next' => 'wait_order',
                    ],
                    [
                        'id' => 'wait_order',
                        'type' => 'input',
                        'variable' => 'order_number',
                        'next' => 'check_status',
                    ],
                    [
                        'id' => 'check_status',
                        'type' => 'api_call',
                        'endpoint' => '/api/internal/check-order',
                        'method' => 'POST',
                        'params' => ['order_number' => '{{order_number}}'],
                        'success_next' => 'show_result',
                        'error_next' => 'not_found',
                    ],
                    [
                        'id' => 'show_result',
                        'type' => 'message',
                        'content' => '📦 Status: {{status}}\n\n{{details}}',
                        'next' => 'end',
                    ],
                    [
                        'id' => 'not_found',
                        'type' => 'message',
                        'content' => '❌ Pesanan tidak ditemukan. Periksa kembali nomor pesanan Anda.',
                        'next' => 'start',
                    ],
                    [
                        'id' => 'end',
                        'type' => 'end',
                    ],
                ],
                'default_response' => [
                    'message' => 'Ketik nomor pesanan Anda untuk mengecek status.',
                ],
                'fallback_response' => [
                    'message' => 'Terjadi kesalahan. Silakan coba lagi.',
                ],
                'handover_enabled' => true,
            ],
            [
                'name' => 'Promo Bot',
                'description' => 'Handle promo inquiries and claims',
                'status' => 'active',
                'keywords' => ['promo', 'diskon', 'discount', 'offer', 'penawaran'],
                'flows' => [
                    [
                        'id' => 'start',
                        'type' => 'message',
                        'content' => '🔥 PROMO SPESIAL! 🔥\n\n1. Diskon 20% semua paket internet\n2. Cashback 10% voucher game\n3. Gratis ongkir token listrik\n\nKetik nomor promo untuk klaim:',
                        'next' => 'promo_select',
                    ],
                    [
                        'id' => 'promo_select',
                        'type' => 'condition',
                        'conditions' => [
                            [
                                'match' => ['1'],
                                'next' => 'promo_internet',
                            ],
                            [
                                'match' => ['2'],
                                'next' => 'promo_game',
                            ],
                            [
                                'match' => ['3'],
                                'next' => 'promo_pln',
                            ],
                        ],
                        'default' => 'invalid',
                    ],
                    [
                        'id' => 'promo_internet',
                        'type' => 'message',
                        'content' => '📡 PROMO INTERNET 20%\n\nKode: INTERNET20\nBerlaku hingga: 31 Januari 2025\n\nKetik KODE untuk menggunakan promo ini.',
                        'next' => 'apply_code',
                    ],
                    [
                        'id' => 'promo_game',
                        'type' => 'message',
                        'content' => '🎮 PROMO VOUCHER GAME 10%\n\nKode: GAME10\nBerlaku hingga: 31 Januari 2025\n\nKetik KODE untuk menggunakan promo ini.',
                        'next' => 'apply_code',
                    ],
                    [
                        'id' => 'promo_pln',
                        'type' => 'message',
                        'content' => '⚡ PROMO TOKEN LISTRIK\n\nGratis ongkir untuk pembelian token listrik!\nOtomatis diterapkan saat checkout.',
                        'next' => 'end',
                    ],
                    [
                        'id' => 'apply_code',
                        'type' => 'message',
                        'content' => '✅ Kode promo telah disalin!\n\nSilakan lakukan pembelian dan gunakan kode saat checkout.',
                        'next' => 'end',
                    ],
                    [
                        'id' => 'invalid',
                        'type' => 'message',
                        'content' => '❌ Pilihan tidak valid. Ketik MENU untuk melihat promo.',
                        'next' => 'start',
                    ],
                    [
                        'id' => 'end',
                        'type' => 'end',
                    ],
                ],
                'default_response' => [
                    'message' => 'Ketik PROMO untuk melihat promo yang tersedia.',
                ],
                'fallback_response' => [
                    'message' => 'Promo tidak tersedia saat ini.',
                ],
                'handover_enabled' => true,
            ],
        ];

        foreach ($chatbots as $chatbot) {
            Chatbot::create($chatbot);
        }

        $this->command->info('Chatbots seeded successfully! (3 chatbots)');
    }
}
