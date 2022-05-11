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
        $this->schema->create('greattree_file', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('uuid');
            $table->string('name', 80)->comment('檔案名稱');
            $table->string('basename', 200)->comment('檔案實際名稱');
            $table->unsignedBigInteger('size')->comment('檔案大小');
            $table->string('mime_type', 36)->comment('檔案類型');
            $table->string('extension', 12)->comment('副檔名');
            $table->string('disk', 32)->comment('檔案庫');
            $table->string('path')->comment('檔案位置(相對)');
            $table->unsignedBigInteger('created_user')->comment('建立人');
            $table->dateTime('created_at')->nullable();
            $table->softDeletes();

            $table->comment = '檔案訊息';
        });
        $this->schema->create('greattree_file_relationships', function (Blueprint $table) {
            $table->string('file_id', 36)->comment('檔案PK');
            $table->string('model_name_type', 120)->comment('對應模組');
            $table->string('item_id', 36)->comment('對應資料id');
            $table->string('item_column', 50)->comment('對應資料返回欄位欄位');

            $table->foreign('file_id')
                ->references('id')
                ->on('greattree_file')
                ->onDelete('cascade');

            $table->comment = '檔案關聯訊息';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('greattree_file_relationships');
        $this->schema->dropIfExists('greattree_file');
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
