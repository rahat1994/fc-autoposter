#!/usr/bin/env node

/**
 * Local Domain Detection Helper
 * This script helps configure the correct local domain for CORS
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

function detectLocalDomain() {
    const cwd = process.cwd();
    
    // Get project directory name (common pattern for local dev)
    const projectPath = path.resolve(cwd, '../../..');
    const projectName = path.basename(projectPath);
    
    console.log('🔍 Detecting local development environment...');
    console.log(`📁 Project path: ${projectPath}`);
    console.log(`📛 Project name: ${projectName}`);
    
    // Common local development patterns
    const possibleDomains = [
        `${projectName}.test`,
        `${projectName}.local`, 
        `${projectName}.dev`,
        'localhost',
        '127.0.0.1'
    ];
    
    console.log('\n🌐 Possible local domains:');
    possibleDomains.forEach((domain, index) => {
        console.log(`  ${index + 1}. http://${domain}`);
        console.log(`     https://${domain}`);
    });
    
    console.log('\n📝 To configure your local domain:');
    console.log('1. Update vite.config.js ALLOWED_ORIGINS array');
    console.log('2. Add your domain to the list');
    console.log('3. Restart the dev server');
    
    console.log('\n💡 Example vite.config.js configuration:');
    console.log('const ALLOWED_ORIGINS = [');
    possibleDomains.forEach(domain => {
        console.log(`  'http://${domain}',`);
        console.log(`  'https://${domain}',`);
    });
    console.log(']');
    
    return possibleDomains;
}

function updateViteConfig(domains) {
    const viteConfigPath = path.join(__dirname, '..', 'vite.config.js');
    
    try {
        let viteConfig = fs.readFileSync(viteConfigPath, 'utf8');
        
        // Create new ALLOWED_ORIGINS array
        const originsArray = domains.flatMap(domain => [
            `  'http://${domain}',`,
            `  'https://${domain}',`
        ]);
        
        const newOrigins = `const ALLOWED_ORIGINS = [\n${originsArray.join('\n')}\n  // Add your specific local domain here\n]`;
        
        // Replace the existing ALLOWED_ORIGINS
        const updatedConfig = viteConfig.replace(
            /const ALLOWED_ORIGINS = \[[^\]]+\]/s,
            newOrigins
        );
        
        fs.writeFileSync(viteConfigPath, updatedConfig);
        console.log('\n✅ Updated vite.config.js with detected domains');
        
    } catch (error) {
        console.error('❌ Error updating vite.config.js:', error.message);
    }
}

// Main execution
const domains = detectLocalDomain();

// Check if user wants to auto-update
const autoUpdate = process.argv[2] === '--update';
if (autoUpdate) {
    updateViteConfig(domains);
}