public function up()
{
    Schema::table('items', function (Blueprint $table) {
        $table->integer('stock')->default(0);
    });
}

public function down()
{
    Schema::table('items', function (Blueprint $table) {
        $table->dropColumn('stock');
    });
}
