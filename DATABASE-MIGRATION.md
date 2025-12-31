# FC Autoposter Database Migration System

This document explains how to use the database migration system in the FC Autoposter plugin.

## Overview

The migration system provides a structured way to manage database schema changes and updates. It automatically runs migrations when the plugin is activated or when new migrations are detected.

## Migration Structure

### Base Migration Class

All migrations extend the `FCAutoposter\Database\Migration` base class, which provides common functionality:

- `up()` - Creates/modifies database structures
- `down()` - Reverses the migration (for rollbacks)
- `shouldRun()` - Determines if the migration needs to run
- `postMigration()` - Actions to perform after migration completes

### Migration Location

Migrations are stored in: `/database/migrations/`

### Migration Naming

Migration files should be named using PascalCase to match their class names:
- File: `CreateFcFaAgentsTable.php`
- Class: `CreateFcFaAgentsTable`

## Agents Table Migration

The agents table migration (`CreateFcFaAgentsTable`) creates the main table for storing AI agent configurations with the following columns:

```sql
CREATE TABLE wp_fc_fa_agents (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    description text,
    type varchar(100) NOT NULL,
    model varchar(100) NOT NULL,
    status enum('active', 'inactive') NOT NULL DEFAULT 'active',
    system_prompt longtext NOT NULL,
    web_search tinyint(1) NOT NULL DEFAULT 0,
    file_processing tinyint(1) NOT NULL DEFAULT 0,
    create_user tinyint(1) NOT NULL DEFAULT 0,
    user_id bigint(20) unsigned NULL,
    username varchar(100) NULL,
    user_email varchar(100) NULL,
    interactions bigint(20) unsigned NOT NULL DEFAULT 0,
    settings longtext NULL COMMENT 'JSON encoded settings',
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_agent_type (type),
    KEY idx_agent_status (status),
    KEY idx_agent_user_id (user_id),
    KEY idx_agent_created_at (created_at),
    UNIQUE KEY uk_agent_name (name)
);
```

## How Migrations Work

### Automatic Execution

1. **Plugin Activation**: Migrations run when the plugin is first activated
2. **Plugin Load**: Migrations check if they need to run on each `plugins_loaded` hook
3. **Version Check**: Migrations track their version to avoid re-running

### Migration Manager

The `MigrationManager` class handles:
- Loading all migration classes
- Checking which migrations need to run
- Executing migrations in sequence
- Handling errors and logging
- Tracking migration status

### Migration Flow

1. Migration Manager loads all migration classes
2. For each migration, calls `shouldRun()` method
3. If migration should run, calls `up()` method
4. If successful, calls `postMigration()` method
5. Logs results and updates version tracking

## Using the Agent Model

After migration, you can use the `Agent` model to interact with the database:

```php
// Create a new agent
$agent = Agent::create([
    'name' => 'Content Writer Bot',
    'description' => 'AI agent for creating content',
    'type' => 'content-writer',
    'model' => 'gpt-4',
    'system_prompt' => 'You are a helpful content writer...',
    'web_search' => true,
    'file_processing' => false
]);

// Find agents
$agent = Agent::find(1);
$agents = Agent::all();
$active_agents = Agent::active();
$content_agents = Agent::byType('content-writer');

// Update agent
$agent->update(['status' => 'inactive']);

// Delete agent
$agent->delete();

// Get statistics
$stats = Agent::getStats();
```

## API Endpoints

The migration also enables REST API endpoints for managing agents:

- `GET /wp-json/fc-autoposter/v1/agents` - List agents
- `POST /wp-json/fc-autoposter/v1/agents` - Create agent
- `GET /wp-json/fc-autoposter/v1/agents/{id}` - Get agent
- `PUT /wp-json/fc-autoposter/v1/agents/{id}` - Update agent
- `DELETE /wp-json/fc-autoposter/v1/agents/{id}` - Delete agent
- `POST /wp-json/fc-autoposter/v1/agents/{id}/toggle-status` - Toggle status

## Debugging

### Debug Mode

When `WP_DEBUG` is enabled, you get:
- Additional admin menu: "Database Status" 
- Migration status display
- Table structure information
- Agent statistics

### Test Script

A test script is available at `/test-migration.php` (WordPress root) for manual testing.

### Error Logging

All migration activities are logged to the WordPress error log with prefix "FC Autoposter:".

## Creating New Migrations

1. Create a new PHP file in `/database/migrations/`
2. Extend the `Migration` base class
3. Implement required methods
4. Add the class name to `MigrationManager::loadMigrations()`
5. Regenerate autoloader: `composer dump-autoload -o`

Example:

```php
<?php
namespace FCAutoposter\Database\Migrations;

use FCAutoposter\Database\Migration;

class CreateMyNewTable extends Migration {
    protected $version = '1.0.0';
    protected $table = 'fc_fa_my_table';
    
    public function up() {
        global $wpdb;
        $table_name = $this->getTableName();
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        return true;
    }
    
    public function down() {
        global $wpdb;
        $table_name = $this->getTableName();
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        return true;
    }
}
```

## Troubleshooting

### Migration Not Running

1. Check if class exists: `class_exists('FCAutoposter\\Database\\Migrations\\YourMigration')`
2. Verify autoloader: `composer dump-autoload -o`
3. Check WordPress error logs
4. Ensure migration is added to `MigrationManager::loadMigrations()`

### Table Creation Issues

1. Check database permissions
2. Verify WordPress table prefix
3. Check for SQL syntax errors
4. Ensure `dbDelta()` requirements are met

### Class Loading Issues

1. Verify PSR-4 autoloading is configured correctly
2. Check file and class naming conventions
3. Regenerate composer autoloader

## Security Notes

- All migrations require admin privileges
- Database operations use WordPress's built-in functions
- Input sanitization is handled by WordPress and custom validation
- Table prefixes are automatically applied for multi-site compatibility