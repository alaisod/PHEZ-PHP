<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStorePhotoToMembers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('members', [
            'store_photo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'geo_location',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('members', 'store_photo');
    }
}
