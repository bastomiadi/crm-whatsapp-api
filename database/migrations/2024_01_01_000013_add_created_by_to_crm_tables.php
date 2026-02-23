<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add created_by to contacts (if not exists)
        if (!Schema::hasColumn('contacts', 'created_by')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('notes');
            });
        }

        // Add created_by to orders (if not exists)
        if (!Schema::hasColumn('orders', 'created_by')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('notes');
            });
        }

        // Add created_by to tickets (if not exists)
        if (!Schema::hasColumn('tickets', 'created_by')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('assigned_to');
            });
        }

        // Add created_by to segments (if not exists)
        if (!Schema::hasColumn('segments', 'created_by')) {
            Schema::table('segments', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('description');
            });
        }

        // Add created_by to tags (if not exists)
        if (!Schema::hasColumn('tags', 'created_by')) {
            Schema::table('tags', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('description');
            });
        }

        // Add created_by to products (if not exists)
        if (!Schema::hasColumn('products', 'created_by')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('description');
            });
        }

        // Add created_by to campaigns (if not exists)
        if (!Schema::hasColumn('campaigns', 'created_by')) {
            Schema::table('campaigns', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('ends_at');
            });
        }

        // Add created_by to surveys (if not exists)
        if (!Schema::hasColumn('surveys', 'created_by')) {
            Schema::table('surveys', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('ends_at');
            });
        }

        // Add created_by to deals (if not exists)
        if (!Schema::hasColumn('deals', 'created_by')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('notes');
            });
        }

        // Add created_by to categories (if not exists)
        if (!Schema::hasColumn('categories', 'created_by')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('description');
            });
        }

        // Add created_by to message_templates (if not exists)
        if (!Schema::hasColumn('message_templates', 'created_by')) {
            Schema::table('message_templates', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('buttons');
            });
        }

        // Add created_by to automations (if not exists)
        if (!Schema::hasColumn('automations', 'created_by')) {
            Schema::table('automations', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('actions');
            });
        }

        // Add created_by to chatbots (if not exists)
        if (!Schema::hasColumn('chatbots', 'created_by')) {
            Schema::table('chatbots', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('working_hours');
            });
        }

        // Add created_by to quick_replies (if not exists)
        if (!Schema::hasColumn('quick_replies', 'created_by')) {
            Schema::table('quick_replies', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('category');
            });
        }
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            if (Schema::hasColumn('contacts', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });

        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });

        Schema::table('segments', function (Blueprint $table) {
            if (Schema::hasColumn('segments', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });

        Schema::table('tags', function (Blueprint $table) {
            if (Schema::hasColumn('tags', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });

        Schema::table('campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('campaigns', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });

        Schema::table('surveys', function (Blueprint $table) {
            if (Schema::hasColumn('surveys', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });

        Schema::table('deals', function (Blueprint $table) {
            if (Schema::hasColumn('deals', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
};
