<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMembersAndPeople extends Migration
{
    public function up()
    {
        // Create 'people' table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'person_name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'telephone' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => true,
            ],
            'line_id' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
            ],
            'line_display_name' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('line_id');
        $this->forge->createTable('people');

        // Create 'members' table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'member_code' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'shop_name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'shop_telephone' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'address' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'geo_location' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
            ],
            'contact_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('member_code');
        $this->forge->addForeignKey('contact_id', 'people', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('members');

        
    }

    public function down()
    {
        $this->forge->dropTable('members');
        $this->forge->dropTable('people');
    }
}
