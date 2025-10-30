#!/usr/bin/env node

/**
 * CORS Testing Utility
 * Test if the Vite dev server is accessible from your WordPress domain
 */

import https from 'https';
import http from 'http';

const DEV_SERVER_URL = 'http://localhost:5173';

function testCors(wpDomain) {
    console.log(`🧪 Testing CORS from ${wpDomain} to ${DEV_SERVER_URL}...`);
    
    const url = new URL(DEV_SERVER_URL);
    const client = url.protocol === 'https:' ? https : http;
    
    const options = {
        hostname: url.hostname,
        port: url.port,
        path: '/',
        method: 'GET',
        headers: {
            'Origin': wpDomain,
            'User-Agent': 'FC-Autoposter-CORS-Test/1.0'
        }
    };
    
    const req = client.request(options, (res) => {
        console.log(`📊 Status Code: ${res.statusCode}`);
        console.log('📋 Response Headers:');
        
        Object.entries(res.headers).forEach(([key, value]) => {
            if (key.toLowerCase().includes('cors') || key.toLowerCase().includes('origin') || key.toLowerCase().includes('access-control')) {
                console.log(`   ${key}: ${value}`);
            }
        });
        
        if (res.headers['access-control-allow-origin']) {
            if (res.headers['access-control-allow-origin'] === '*' || 
                res.headers['access-control-allow-origin'] === wpDomain) {
                console.log('✅ CORS configured correctly!');
            } else {
                console.log(`❌ CORS origin mismatch. Expected: ${wpDomain}, Got: ${res.headers['access-control-allow-origin']}`);
            }
        } else {
            console.log('⚠️  No CORS headers found. This might cause issues.');
        }
    });
    
    req.on('error', (error) => {
        console.error('❌ Connection failed:', error.message);
        console.log('💡 Make sure the Vite dev server is running with: npm run dev');
    });
    
    req.setTimeout(5000, () => {
        console.error('❌ Request timed out');
        req.destroy();
    });
    
    req.end();
}

// Usage examples
const testDomains = [
    'http://testing-ground.test',
    'https://testing-ground.test',
    'http://testing_ground.test',
    'https://testing_ground.test',
    'http://localhost:8080', // Common WordPress dev port
];

const wpDomain = process.argv[2];

if (wpDomain) {
    testCors(wpDomain);
} else {
    console.log('🔧 CORS Testing Utility');
    console.log('Usage: node test-cors.js <your-wordpress-domain>');
    console.log('\nExample:');
    console.log('  node scripts/test-cors.js http://testing-ground.test');
    console.log('\nCommon domains to test:');
    testDomains.forEach(domain => console.log(`  ${domain}`));
}