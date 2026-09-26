<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_can_view_logs_to_outsiders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCanViewLogsToOutsidersTable extends Migration
{
    public function up()
    {
        Schema::table('outsiders', function (Blueprint $table) {
            $table->boolean('can_view_logs')->default(false)->after('notif_enabled');
        });
    }

    public function down()
    {
        Schema::table('outsiders', function (Blueprint $table) {
            $table->dropColumn('can_view_logs');
        });
    }
}
