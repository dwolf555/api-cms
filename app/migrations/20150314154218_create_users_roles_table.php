<?php

use Phinx\Migration\AbstractMigration;

class CreateUsersRolesTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */
    
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->table('users_roles')
            ->addColumn('user_id', 'integer')
            ->addColumn('role_id', 'integer')
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'cascade'])
            ->addForeignKey('role_id', 'roles', 'id', ['delete' => 'cascade'])
            ->save();

    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('users_roles');
    }
}