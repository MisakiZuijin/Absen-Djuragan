<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ensures all shift and core system columns exist across environments (Local / Staging / Production)
     * without throwing duplicate column errors if already present.
     */
    public function up(): void
    {
        // 1. Table: shifts
        if (Schema::hasTable('shifts')) {
            Schema::table('shifts', function (Blueprint $table) {
                if (!Schema::hasColumn('shifts', 'type')) {
                    $table->string('type')->nullable();
                }
                if (!Schema::hasColumn('shifts', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }
                if (!Schema::hasColumn('shifts', 'is_gps_active')) {
                    $table->boolean('is_gps_active')->default(true);
                }
                if (!Schema::hasColumn('shifts', 'is_friday_break_active')) {
                    $table->boolean('is_friday_break_active')->default(false);
                }
                if (!Schema::hasColumn('shifts', 'friday_start_break_time')) {
                    $table->time('friday_start_break_time')->nullable();
                }
                if (!Schema::hasColumn('shifts', 'friday_end_break_time')) {
                    $table->time('friday_end_break_time')->nullable();
                }
                if (!Schema::hasColumn('shifts', 'friday_break_time_in_minute')) {
                    $table->integer('friday_break_time_in_minute')->default(0);
                }
                if (!Schema::hasColumn('shifts', 'is_split_shift')) {
                    $table->boolean('is_split_shift')->default(false);
                }
                if (!Schema::hasColumn('shifts', 'split_wfh_start')) {
                    $table->time('split_wfh_start')->nullable();
                }
                if (!Schema::hasColumn('shifts', 'split_wfh_end')) {
                    $table->time('split_wfh_end')->nullable();
                }
                if (!Schema::hasColumn('shifts', 'split_wfo_start')) {
                    $table->time('split_wfo_start')->nullable();
                }
                if (!Schema::hasColumn('shifts', 'split_wfo_end')) {
                    $table->time('split_wfo_end')->nullable();
                }
                if (!Schema::hasColumn('shifts', 'is_rolling')) {
                    $table->boolean('is_rolling')->default(false);
                }
            });

            // Pastikan panjang kolom name pada shifts minimal varchar(100)
            try {
                DB::statement("ALTER TABLE shifts MODIFY COLUMN name VARCHAR(100) NOT NULL");
            } catch (\Throwable $e) {
                // Abaikan jika database engine tidak mendukung ALTER TABLE MODIFY langsung
            }
        }

        // 2. Table: offices
        if (Schema::hasTable('offices')) {
            Schema::table('offices', function (Blueprint $table) {
                if (!Schema::hasColumn('offices', 'sop_url')) {
                    $table->text('sop_url')->nullable();
                }
                if (!Schema::hasColumn('offices', 'rules_url')) {
                    $table->text('rules_url')->nullable();
                }
                if (!Schema::hasColumn('offices', 'rules_description')) {
                    $table->text('rules_description')->nullable();
                }
                if (!Schema::hasColumn('offices', 'piket_url')) {
                    $table->text('piket_url')->nullable();
                }
                if (!Schema::hasColumn('offices', 'piket_description')) {
                    $table->text('piket_description')->nullable();
                }
                if (!Schema::hasColumn('offices', 'piket_max_per_week')) {
                    $table->integer('piket_max_per_week')->default(1);
                }
                if (!Schema::hasColumn('offices', 'is_hybrid_enabled')) {
                    $table->boolean('is_hybrid_enabled')->default(false);
                }
            });
        }

        // 3. Table: divisions
        if (Schema::hasTable('divisions')) {
            Schema::table('divisions', function (Blueprint $table) {
                if (!Schema::hasColumn('divisions', 'meet_url')) {
                    $table->text('meet_url')->nullable();
                }
            });
        }

        // 4. Table: hand_raises
        if (Schema::hasTable('hand_raises')) {
            Schema::table('hand_raises', function (Blueprint $table) {
                if (!Schema::hasColumn('hand_raises', 'meet_url')) {
                    $table->text('meet_url')->nullable();
                }
                if (!Schema::hasColumn('hand_raises', 'project_id')) {
                    $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null');
                }
            });
        }

        // 5. Table: broadcasts
        if (Schema::hasTable('broadcasts')) {
            Schema::table('broadcasts', function (Blueprint $table) {
                if (!Schema::hasColumn('broadcasts', 'broadcast_type')) {
                    $table->string('broadcast_type')->default('all');
                }
                if (!Schema::hasColumn('broadcasts', 'category')) {
                    $table->enum('category', ['announcement', 'scheduled_broadcast'])->default('announcement');
                }
                if (!Schema::hasColumn('broadcasts', 'scheduled_at')) {
                    $table->timestamp('scheduled_at')->nullable();
                }
                if (!Schema::hasColumn('broadcasts', 'requires_report')) {
                    $table->boolean('requires_report')->default(false);
                }
                if (!Schema::hasColumn('broadcasts', 'report_question')) {
                    $table->text('report_question')->nullable();
                }
                if (!Schema::hasColumn('broadcasts', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                }
            });
        }

        // 6. Table: attendances
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (!Schema::hasColumn('attendances', 'adjusted_end_time')) {
                    $table->time('adjusted_end_time')->nullable();
                }
                if (!Schema::hasColumn('attendances', 'checkout_notes')) {
                    $table->text('checkout_notes')->nullable();
                }
                if (!Schema::hasColumn('attendances', 'keterangan')) {
                    $table->text('keterangan')->nullable();
                }
                if (!Schema::hasColumn('attendances', 'permit_type')) {
                    $table->string('permit_type')->nullable();
                }
                if (!Schema::hasColumn('attendances', 'prayer_duration')) {
                    $table->integer('prayer_duration')->default(0)->nullable();
                }
            });
        }

        // 7. Table: late_absences
        if (Schema::hasTable('late_absences')) {
            Schema::table('late_absences', function (Blueprint $table) {
                if (!Schema::hasColumn('late_absences', 'original_absen_time')) {
                    $table->timestamp('original_absen_time')->nullable();
                }
                if (!Schema::hasColumn('late_absences', 'original_end_time')) {
                    $table->string('original_end_time')->nullable();
                }
            });
        }

        // 8. Table: intern_accounts
        if (Schema::hasTable('intern_accounts')) {
            Schema::table('intern_accounts', function (Blueprint $table) {
                if (!Schema::hasColumn('intern_accounts', 'spreadsheet_url')) {
                    $table->string('spreadsheet_url', 500)->nullable();
                }
                if (!Schema::hasColumn('intern_accounts', 'enabled_platforms')) {
                    $table->json('enabled_platforms')->nullable();
                }
            });
        }

        // 9. Table: change_time_registrations
        if (Schema::hasTable('change_time_registrations')) {
            Schema::table('change_time_registrations', function (Blueprint $table) {
                if (!Schema::hasColumn('change_time_registrations', 'target_schedule_ids')) {
                    $table->json('target_schedule_ids')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('shifts')) {
            Schema::table('shifts', function (Blueprint $table) {
                if (Schema::hasColumn('shifts', 'is_gps_active')) {
                    $table->dropColumn('is_gps_active');
                }
            });
        }
    }
};
