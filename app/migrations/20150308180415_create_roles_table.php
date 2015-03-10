<?php

use Phinx\Migration\AbstractMigration;

class CreateRolesTable extends AbstractMigration
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
        $this->table('roles')
            ->addColumn('name', 'string')
            ->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex('name', ['unique' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('roles');
    }
}