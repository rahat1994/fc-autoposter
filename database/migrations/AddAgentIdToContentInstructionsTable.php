<?php

namespace FCAutoposter\Database\Migrations;

use FCAutoposter\Database\Migration;

class AddAgentIdToContentInstructionsTable extends Migration {
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $result1 = $this->addColumn('agent_id', 'bigint(20) unsigned NULL', 'fc_fa_content_instructions', 'id');
        $result2 = $this->addColumn('fcom_space_id', 'bigint(20) unsigned NULL', 'fc_fa_content_instructions', 'agent_id');
        
        return $result1 && $result2;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        $result1 = $this->dropColumn('agent_id', 'fc_fa_content_instructions');
        $result2 = $this->dropColumn('fcom_space_id', 'fc_fa_content_instructions');
        
        return $result1 && $result2;
    }
}
