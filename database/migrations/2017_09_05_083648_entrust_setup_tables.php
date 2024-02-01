<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class EntrustSetupTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        DB::connection('accounts')->beginTransaction();

        Schema::connection('accounts')->create('client_types', function(Blueprint $table) {
            $table->increments('id', true);
            $table->string('type')->unique();
            $table->timestamps();
        });

        Schema::connection('accounts')->create('clients', function(Blueprint $table) {
            $table->increments('id', true);
            $table->string('company_name')->nullable();
            $table->integer('type')->unsigned();
            $table->foreign('type')->references('id')->on('client_types')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->integer('super_admin')->unsigned()->nullable();
            $table->string('company_slug')->unique();
            $table->boolean('activated');
            $table->timestamps();
        });

		Schema::connection('accounts')->create('subsellusers', function(Blueprint $table) {
					$table->increments('id', true);
					$table->string('subseller_username')->unique()->nullable();
					$table->string('email')->unique();
					$table->string('password', 32);
					$table->string('salt');
					$table->boolean('activated');
					$table->timestamps();
				});

        Schema::connection('accounts')->create('users', function(Blueprint $table) {
            $table->increments('id', true);
            $table->integer('client_id')->unsigned()->nullable();
            $table->foreign('client_id')->references('id')->on('clients')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->string('username')->unique()->nullable();
            $table->string('email')->unique();
            $table->string('password', 32);
            $table->string('salt');
            $table->boolean('activated');
            $table->timestamps();            
        });

        Schema::connection('accounts')->table('clients', function(Blueprint $table){
            $table->foreign('super_admin')->references('id')->on('users')
                    ->onUpdate('cascade')->onDelete('cascade');
        });

        // create table for roles
        Schema::connection('accounts')->create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('client_id')->unsigned();
            $table->foreign('client_id')->references('id')->on('clients')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Schema::connection('accounts')->create('role_user', function (Blueprint $table) {
        //     $table->integer('role_id')->unsigned();
        //     $table->integer('user_id')->unsigned();

        //     $table->foreign('role_id')->references('id')->on('roles')
        //         ->onUpdate('cascade')->onDelete('cascade');
        //     $table->foreign('user_id')->references('id')->on('users')
        //         ->onUpdate('cascade')->onDelete('cascade');

        //     $table->primary(['user_id', 'role_id']);
        // });

        // Create table for storing permissions
        Schema::connection('accounts')->create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });        

        // Create table for associating permissions to roles (Many-to-Many)
        Schema::connection('accounts')->create('permission_role', function (Blueprint $table) {
            $table->integer('role_id')->unsigned();
            $table->integer('permission_id')->unsigned();

            $table->foreign('role_id')->references('id')->on('roles')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['permission_id', 'role_id']);
        });

        // Create table for associating permissions to users (Many-to-Many)
        Schema::connection('accounts')->create('role_user', function (Blueprint $table) {
            $table->integer('role_id')->unsigned();
            $table->integer('user_id')->unsigned();

            $table->foreign('role_id')->references('id')->on('roles')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['role_id', 'user_id']);
        });

        DB::connection('accounts')->commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::connection('accounts')->table('clients', function (Blueprint $table){
            $table->dropForeign('clients_super_admin_foreign');
        });
        Schema::connection('accounts')->table('permission_role', function (Blueprint $table){
            $table->dropForeign('permission_role_permission_id_foreign');
        });
        Schema::connection('accounts')->table('permission_role', function (Blueprint $table){
            $table->dropForeign('permission_role_role_id_foreign');
        });
        Schema::connection('accounts')->table('role_user', function (Blueprint $table){
            $table->dropForeign('role_user_role_id_foreign');
        });
        Schema::connection('accounts')->table('role_user', function (Blueprint $table){
            $table->dropForeign('role_user_user_id_foreign');
        });
        Schema::connection('accounts')->table('users', function (Blueprint $table){
            $table->dropForeign('users_client_id_foreign');
        });
        Schema::drop('permission_user');
        Schema::drop('role_permission');
        Schema::drop('permissions');
        Schema::drop('roles');
        Schema::drop('users');
        Schema::drop('clients');
        Schema::drop('subsellusers');
    }
}