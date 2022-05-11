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
        $this->schema->create('greattree_file_logs', function (Blueprint $table) {
            $table->string('file_id', 36)->index()->comment('檔案PK');
            $table->string('action', 16)->comment('檔案操作');
            $table->string('message')->comment('檔案說明');
            $table->unsignedBigInteger('size')->comment('檔案大小');
            $table->unsignedBigInteger('created_user')->comment('建立人');
            $table->dateTime('created_at')->nullable();
            $table->comment = '檔案LOG';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('greattree_file_logs');
    }
    /**
     * Get the migration connection name.
     *
     * @return string|null
     */
    public function getConnection()
    {
        return config('greattree.storage.database.connection');
    }
};
