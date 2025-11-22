<?php
/**
 * Agent Controller
 * 
 * Handles agent-related API endpoints
 */

namespace FCAutoposter\Controllers;

use FCAutoposter\Models\Agent;
use FCAutoposter\Routing\Response;
use FCAutoposter\Routing\Request;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class AgentController {
    
    /**
     * Get all agents
     */
    public function index(Request $request) {
        try {
            $status = $request->query('status');
            $type = $request->query('type');
            $page = (int) $request->query('page', 1);
            $per_page = (int) $request->query('per_page', 5);
            
            $where = [];
            if ($status) {
                $where['status'] = $status;
            }
            if ($type) {
                $where['type'] = $type;
            }
            
            $result = Agent::paginate($page, $per_page, $where);
            
            // Convert objects to arrays
            $result['data'] = array_map(function($agent) {
                return $agent->toArray();
            }, $result['data']);
            
            return Response::success('Agents retrieved successfully', $result);
            
        } catch (\Exception $e) {
            error_log('FC Autoposter Agent Controller Error: ' . $e->getMessage());
            return Response::error('Failed to retrieve agents', $e->getMessage(), 500);
        }
    }
    
    /**
     * Get a specific agent
     */
    public function show(Request $request) {
        try {
            $id = $request->param('id');
            
            $agent = Agent::find($id);
            
            if (!$agent) {
                return Response::error('Agent not found', null, 404);
            }
            
            return Response::success('Agent retrieved successfully', $agent->toArray());
            
        } catch (\Exception $e) {
            error_log('FC Autoposter Agent Controller Error: ' . $e->getMessage());
            return Response::error('Failed to retrieve agent', $e->getMessage(), 500);
        }
    }
    
    /**
     * Create a new agent
     */
    public function store(Request $request) {
        try {
            // Get request data
            $data = $request->all();
            
            // Validate required fields
            $required_fields = ['name', 'type', 'model', 'system_prompt'];
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    return Response::error("Field '{$field}' is required", null, 400);
                }
            }
            
            // Check if agent name already exists
            $existing_agent = Agent::findByName($data['name']);
            if ($existing_agent) {
                return Response::error('Agent with this name already exists', null, 400);
            }
            
            // Handle user creation if requested
            $user_id = null;
            if (!empty($data['create_user']) && $data['create_user'] == 1) {
                // Validate username and email
                if (empty($data['username'])) {
                    return Response::error('Username is required when creating a user', null, 400);
                }
                if (empty($data['user_email'])) {
                    return Response::error('Email is required when creating a user', null, 400);
                }
                
                // Check if username already exists
                if (username_exists($data['username'])) {
                    return Response::error('Username already exists', null, 400);
                }
                
                // Check if email already exists
                if (email_exists($data['user_email'])) {
                    return Response::error('Email already exists', null, 400);
                }
                
                // Validate email format
                if (!is_email($data['user_email'])) {
                    return Response::error('Invalid email address', null, 400);
                }
                
                // Create WordPress user
                $user_data = [
                    'user_login' => sanitize_user($data['username']),
                    'user_email' => sanitize_email($data['user_email']),
                    'user_pass' => wp_generate_password(12, true, true),
                    'display_name' => $data['name'], // Use agent name as display name
                    'role' => 'author', // Default role for agent users
                    'description' => 'AI Agent: ' . $data['name']
                ];
                
                $user_id = wp_insert_user($user_data);
                
                // Check for errors
                if (is_wp_error($user_id)) {
                    return Response::error('Failed to create WordPress user: ' . $user_id->get_error_message(), null, 500);
                }
                
                // Store the user_id in the data
                $data['user_id'] = $user_id;
            }
            
            // Create agent
            $agent = Agent::create($data);
            
            if (!$agent) {
                // If agent creation failed but user was created, delete the user
                if ($user_id) {
                    wp_delete_user($user_id);
                }
                return Response::error('Failed to create agent', null, 500);
            }
            
            return Response::success('Agent created successfully', $agent->toArray(), 201);
            
        } catch (\Exception $e) {
            error_log('FC Autoposter Agent Controller Error: ' . $e->getMessage());
            return Response::error('Failed to create agent', $e->getMessage(), 500);
        }
    }
    
    /**
     * Update an agent
     */
    public function update(Request $request) {
        try {
            $id = $request->param('id');
            $data = $request->all();
            
            $agent = Agent::find($id);
            
            if (!$agent) {
                return Response::error('Agent not found', null, 404);
            }
            
            // Check if name is being changed and if it conflicts
            if (isset($data['name']) && $data['name'] !== $agent->name) {
                $existing_agent = Agent::findByName($data['name']);
                if ($existing_agent && $existing_agent->id !== $agent->id) {
                    return Response::error('Agent with this name already exists', null, 400);
                }
            }
            
            $success = $agent->update($data);
            
            if (!$success) {
                return Response::error('Failed to update agent', null, 500);
            }
            
            return Response::success('Agent updated successfully', $agent->toArray());
            
        } catch (\Exception $e) {
            error_log('FC Autoposter Agent Controller Error: ' . $e->getMessage());
            return Response::error('Failed to update agent', $e->getMessage(), 500);
        }
    }
    
    /**
     * Delete an agent
     */
    public function destroy(Request $request) {
        try {
            $id = $request->param('id');
            
            $agent = Agent::find($id);
            
            if (!$agent) {
                return Response::error('Agent not found', null, 404);
            }
            
            $success = $agent->delete();
            
            if (!$success) {
                return Response::error('Failed to delete agent', null, 500);
            }
            
            return Response::success('Agent deleted successfully');
            
        } catch (\Exception $e) {
            error_log('FC Autoposter Agent Controller Error: ' . $e->getMessage());
            return Response::error('Failed to delete agent', $e->getMessage(), 500);
        }
    }
    
    /**
     * Toggle agent status
     */
    public function toggleStatus(Request $request) {
        try {
            $id = $request->param('id');
            
            $agent = Agent::find($id);
            
            if (!$agent) {
                return Response::error('Agent not found', null, 404);
            }
            
            $new_status = $agent->status === 'active' ? 'inactive' : 'active';
            $success = $agent->update(['status' => $new_status]);
            
            if (!$success) {
                return Response::error('Failed to toggle agent status', null, 500);
            }
            
            return Response::success('Agent status updated successfully', $agent->toArray());
            
        } catch (\Exception $e) {
            error_log('FC Autoposter Agent Controller Error: ' . $e->getMessage());
            return Response::error('Failed to toggle agent status', $e->getMessage(), 500);
        }
    }
    
    /**
     * Get agent statistics
     */
    public function stats(Request $request) {
        try {
            $stats = Agent::getStats();
            
            return Response::success('Agent statistics retrieved successfully', $stats);
            
        } catch (\Exception $e) {
            error_log('FC Autoposter Agent Controller Error: ' . $e->getMessage());
            return Response::error('Failed to retrieve agent statistics', $e->getMessage(), 500);
        }
    }
    
    /**
     * Increment agent interactions
     */
    public function incrementInteractions(Request $request) {
        try {
            $id = $request->param('id');
            
            $agent = Agent::find($id);
            
            if (!$agent) {
                return Response::error('Agent not found', null, 404);
            }
            
            $success = $agent->incrementInteractions();
            
            if (!$success) {
                return Response::error('Failed to increment interactions', null, 500);
            }
            
            return Response::success('Interactions incremented successfully', [
                'interactions' => $agent->interactions
            ]);
            
        } catch (\Exception $e) {
            error_log('FC Autoposter Agent Controller Error: ' . $e->getMessage());
            return Response::error('Failed to increment interactions', $e->getMessage(), 500);
        }
    }
}