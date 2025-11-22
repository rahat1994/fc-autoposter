<?php

use FCAutoposter\Database\Migration;

class AddAgentIdToContentInstructionsTable extends Migration {
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'fc_fa_content_instructions';
        
        // Add agent_id column if it doesn't exist
        if (!$this->columnExists($table_name, 'agent_id')) {
            $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN agent_id bigint(20) unsigned NULL AFTER id");
        }

        // Add fcom_space_id column if it doesn't exist
        if (!$this->columnExists($table_name, 'fcom_space_id')) {
            $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN fcom_space_id bigint(20) unsigned NULL AFTER agent_id");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'fc_fa_content_instructions';
        
        // Drop agent_id column
        if ($this->columnExists($table_name, 'agent_id')) {
            $wpdb->query("ALTER TABLE {$table_name} DROP COLUMN agent_id");
        }

        // Drop fcom_space_id column
        if ($this->columnExists($table_name, 'fcom_space_id')) {
            $wpdb->query("ALTER TABLE {$table_name} DROP COLUMN fcom_space_id");
        }
    }

    /**
     * Check if a column exists in a table.
     *
     * @param string $table_name
     * @param string $column_name
     * @return bool
     */
    private function columnExists($table_name, $column_name) {
        global $wpdb;
        $row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$table_name}' AND column_name = '{$column_name}'");
        return !empty($row);
    }
}
