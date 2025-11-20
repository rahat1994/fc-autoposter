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
            
            if ($type) {
                $agents = Agent::byType($type);
            } elseif ($status) {
                $agents = Agent::all($status);
            } else {
                $agents = Agent::all();
            }
            
            // Convert to arrays
            $agents_data = array_map(function($agent) {
                return $agent->toArray();
            }, $agents);
            
            return Response::success('Agents retrieved successfully', $agents_data);
            
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
            
            // Create agent
            $agent = Agent::create($data);
            
            if (!$agent) {
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