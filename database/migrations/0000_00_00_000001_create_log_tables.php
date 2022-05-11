<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The database schema.
     *
     * @var \Illuminate\Database\Schema\Builder
     */
    protected $schema;
    /**
     * Create a new migration instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->schema = Schema::connection($this->getConnection());
    }
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // exception 記錄
        $table_exception_logs = config('greattree.logger.database.tables.exception_logs');
        if (!$this->schema->hasTable($table_exception_logs)) {
            $this->schema->create($table_exception_logs, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->text('message');
                $table->longText('context');
                $table->string('channel', 32)->index();
                $table->unsignedSmallInteger('level')->index();
                $table->string('level_name', 20);
                $table->longText('formatted');
                $table->text('file_position')->comment('錯誤訊息位置');
                $table->text('extra');
                $table->string('remote_addr')->nullable()->comment('來源IP');
                $table->dateTime('created_at')->nullable();
                $table->engine = 'MyISAM';
            });
        }

        //API REQUEST 記錄
        $table_request_logs = config('greattree.logger.database.tables.request_logs');
        if (!$this->schema->hasTable($table_request_logs)) {
            $this->schema->create($table_request_logs, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->char('request_id', 36)->nullable();
                $table->longText('message');
                $table->longText('context');
                $table->string('channel', 32)->index();
                $table->unsignedSmallInteger('level')->index();
                $table->string('level_name', 20);
                $table->string('extra');
                $table->longText('formatted');
                $table->string('remote_addr')->nullable()->comment('來源IP');
                $table->text('user_agent')->nullable();
                $table->dateTime('created_at')->nullable();
                $table->engine = 'MyISAM';
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropTable(config('greattree.logger.database.tables.request_logs'));
        $this->dropTable(config('greattree.logger.database.tables.exception_logs'));
    }


    private function dropTable($table)
    {
        if ($this->schema->hasTable($table)) {
            $this->schema->dropIfExists($table);
        }
    }
    /**
     * Get the migration connection name.
     *
     * @return string|null
     */
    public function getConnection()
    {
        return config('greattree.logger.database.connection');
    }
};
