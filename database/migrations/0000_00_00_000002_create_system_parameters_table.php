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
    // parameters 參數 記錄
    $this->schema->create('system_parameters', function (Blueprint $table) {
      $table->increments('id');
      $table->string('combine_code', 232)->default('main')->unique('code_unique')->comment('scope / group / slug');
      $table->string('scope', 48)->default('main')->comment('作用域');
      $table->string('group_code', 48)->index('group_code_index')->comment('群組名稱');
      $table->string('code', 128)->nullable()->comment('參數 KEY值');
      $table->text('value')->nullable()->comment('數值');
      $table->unsignedSmallInteger('sort')->default(0)->comment('排序');
      $table->boolean('is_useful')->default(1);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    $this->schema->dropIfExists('system_parameters');
  }

  /**
   * Get the migration connection name.
   *
   * @return string|null
   */
  public function getConnection()
  {
    return config('greattree.system.database.connection');
  }
};
