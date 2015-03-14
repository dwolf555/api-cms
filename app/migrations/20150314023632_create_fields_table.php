<?php

use Phinx\Migration\AbstractMigration;

class CreateFieldsTable extends AbstractMigration
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
        $this->table('fields')
            ->addColumn('entity_id', 'integer')
            ->addColumn('name', 'string')
            ->addColumn('type', 'string')
            ->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('entity_id', 'entities', 'id')
            ->addIndex(['entity_id', 'name'], ['unique' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('fields');
    }
}