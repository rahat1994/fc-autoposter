<?php
/**
 * Migration Manager
 * 
 * Handles running and managing database migrations
 */

namespace FCAutoposter\Database;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class MigrationManager {
    
    /**
     * @var array
     */
    protected $migrations = [];
    
    /**
     * @var string
     */
    protected $migrations_path;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->migrations_path = FC_AUTOPOSTER_PLUGIN_DIR . 'database/migrations/';
        $this->loadMigrations();
    }
    
    /**
     * Load all migration classes
     */
    protected function loadMigrations() {
        // Register known migrations directly
        $migration_classes = [
            'FCAutoposter\\Database\\Migrations\\CreateFcFaAgentsTable',
        ];
        
        foreach ($migration_classes as $class_name) {
            if (class_exists($class_name)) {
                $this->migrations[] = new $class_name();
            }
        }
    }
    
    /**
     * Convert filename to class name
     * 
     * @param string $filename
     * @return string
     */
    protected function filenameToClassName($filename) {
        // Convert snake_case to PascalCase
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $filename)));
    }
    
    /**
     * Run all pending migrations
     * 
     * @return array
     */
    public function runMigrations() {
        $results = [];
        
        foreach ($this->migrations as $migration) {
            $result = $this->runMigration($migration);
            $results[$migration->getName()] = $result;
        }
        
        return $results;
    }
    
    /**
     * Run a specific migration
     * 
     * @param Migration $migration
     * @return array
     */
    protected function runMigration(Migration $migration) {
        $result = [
            'name' => $migration->getName(),
            'version' => $migration->getVersion(),
            'status' => 'skipped',
            'message' => 'Migration already applied',
            'error' => null
        ];
        
        try {
            // Check if migration should run
            if (!$migration->shouldRun()) {
                return $result;
            }
            
            // Run the migration
            $success = $migration->up();
            
            if ($success) {
                // Run post-migration actions
                $migration->postMigration();
                
                $result['status'] = 'success';
                $result['message'] = 'Migration completed successfully';
                
                // Log success
                error_log("FC Autoposter: Migration completed - {$migration->getName()}");
                
            } else {
                $result['status'] = 'failed';
                $result['message'] = 'Migration returned false';
                
                error_log("FC Autoposter: Migration failed - {$migration->getName()}");
            }
            
        } catch (\Exception $e) {
            $result['status'] = 'error';
            $result['message'] = 'Migration threw an exception';
            $result['error'] = $e->getMessage();
            
            error_log("FC Autoposter: Migration error - {$migration->getName()}: " . $e->getMessage());
        }
        
        return $result;
    }
    
    /**
     * Rollback a specific migration
     * 
     * @param string $migration_name
     * @return array
     */
    public function rollbackMigration($migration_name) {
        $migration = $this->findMigration($migration_name);
        
        if (!$migration) {
            return [
                'status' => 'error',
                'message' => 'Migration not found'
            ];
        }
        
        try {
            $success = $migration->down();
            
            if ($success) {
                return [
                    'status' => 'success',
                    'message' => 'Migration rolled back successfully'
                ];
            } else {
                return [
                    'status' => 'failed',
                    'message' => 'Migration rollback returned false'
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Rollback threw an exception',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Find a migration by name
     * 
     * @param string $name
     * @return Migration|null
     */
    protected function findMigration($name) {
        foreach ($this->migrations as $migration) {
            if ($migration->getName() === $name) {
                return $migration;
            }
        }
        
        return null;
    }
    
    /**
     * Get all migrations
     * 
     * @return array
     */
    public function getMigrations() {
        return $this->migrations;
    }
    
    /**
     * Get migration status
     * 
     * @return array
     */
    public function getStatus() {
        $status = [];
        
        foreach ($this->migrations as $migration) {
            $status[] = [
                'name' => $migration->getName(),
                'version' => $migration->getVersion(),
                'should_run' => $migration->shouldRun(),
                'class' => get_class($migration)
            ];
        }
        
        return $status;
    }
    
    /**
     * Check if any migrations are pending
     * 
     * @return bool
     */
    public function hasPendingMigrations() {
        foreach ($this->migrations as $migration) {
            if ($migration->shouldRun()) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get pending migrations count
     * 
     * @return int
     */
    public function getPendingMigrationsCount() {
        $count = 0;
        
        foreach ($this->migrations as $migration) {
            if ($migration->shouldRun()) {
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Force run all migrations (ignore shouldRun check)
     * 
     * @return array
     */
    public function forceRunMigrations() {
        $results = [];
        
        foreach ($this->migrations as $migration) {
            try {
                $success = $migration->up();
                $migration->postMigration();
                
                $results[$migration->getName()] = [
                    'status' => $success ? 'success' : 'failed',
                    'message' => $success ? 'Forced migration completed' : 'Forced migration failed'
                ];
                
            } catch (\Exception $e) {
                $results[$migration->getName()] = [
                    'status' => 'error',
                    'message' => 'Forced migration threw exception',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
}