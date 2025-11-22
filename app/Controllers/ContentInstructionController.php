<?php
/**
 * Content Instruction Controller
 * 
 * Handles content instruction API endpoints
 */

namespace FCAutoposter\Controllers;

use FCAutoposter\Models\ContentInstruction;
use FCAutoposter\Routing\Response;
use FCAutoposter\Routing\Request;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ContentInstructionController {
    
    /**
     * Get all instructions
     */
    public function index(Request $request) {
        try {
            $status = $request->query('status');
            $page = (int) $request->query('page', 1);
            $per_page = (int) $request->query('per_page', 10);
            
            $where = [];
            if ($status) {
                $where['status'] = $status;
            }
            
            $result = ContentInstruction::paginate($page, $per_page, $where);
            
            // Convert objects to arrays
            $result['data'] = array_map(function($instruction) {
                return $instruction->toArray();
            }, $result['data']);
            
            return Response::success('Instructions retrieved successfully', $result);
            
        } catch (\Exception $e) {
            error_log('FC Autoposter ContentInstruction Controller Error: ' . $e->getMessage());
            return Response::error('Failed to retrieve instructions', $e->getMessage(), 500);
        }
    }
    
    /**
     * Get a specific instruction
     */
    public function show(Request $request) {
        try {
            $id = $request->param('id');
            
            $instruction = ContentInstruction::find($id);
            
            if (!$instruction) {
                return Response::error('Instruction not found', null, 404);
            }
            
            return Response::success('Instruction retrieved successfully', $instruction->toArray());
            
        } catch (\Exception $e) {
            error_log('FC Autoposter ContentInstruction Controller Error: ' . $e->getMessage());
            return Response::error('Failed to retrieve instruction', $e->getMessage(), 500);
        }
    }

    public function fcom_spaces(Request $request){
        try {
            global $wpdb;

            $fcom_spaces = $wpdb->get_results("SELECT id,title FROM {$wpdb->prefix}fcom_spaces");

            return Response::success('FCom Spaces retrieved successfully', $fcom_spaces);   
        } catch (\Exception $e) {
            error_log('FC Autoposter ContentInstruction Controller Error: ' . $e->getMessage());
            return Response::error('Failed to retrieve instruction', $e->getMessage(), 500);
        }
    }
    
    /**
     * Create a new instruction
     */
    public function store(Request $request) {
        try {
            // Get request data
            $data = $request->all();
            
            // Validate required fields
            if (empty($data['instruction'])) {
                return Response::error("Field 'instruction' is required", null, 400);
            }
            
            // Set defaults
            $data['status'] = 'pending';
            $data['attempts'] = 0;
            
            // Create instruction
            $instruction = ContentInstruction::create($data);
            
            if (!$instruction) {
                return Response::error('Failed to create instruction', null, 500);
            }
            
            return Response::success('Instruction created successfully', $instruction->toArray(), 201);
            
        } catch (\Exception $e) {
            error_log('FC Autoposter ContentInstruction Controller Error: ' . $e->getMessage());
            return Response::error('Failed to create instruction', $e->getMessage(), 500);
        }
    }
    
    /**
     * Update an instruction
     */
    public function update(Request $request) {
        try {
            $id = $request->param('id');
            $data = $request->all();
            
            $instruction = ContentInstruction::find($id);
            
            if (!$instruction) {
                return Response::error('Instruction not found', null, 404);
            }
            
            $success = $instruction->update($data);
            
            if (!$success) {
                return Response::error('Failed to update instruction', null, 500);
            }
            
            return Response::success('Instruction updated successfully', $instruction->toArray());
            
        } catch (\Exception $e) {
            error_log('FC Autoposter ContentInstruction Controller Error: ' . $e->getMessage());
            return Response::error('Failed to update instruction', $e->getMessage(), 500);
        }
    }
    
    /**
     * Delete an instruction
     */
    public function destroy(Request $request) {
        try {
            $id = $request->param('id');
            
            $instruction = ContentInstruction::find($id);
            
            if (!$instruction) {
                return Response::error('Instruction not found', null, 404);
            }
            
            $success = $instruction->delete();
            
            if (!$success) {
                return Response::error('Failed to delete instruction', null, 500);
            }
            
            return Response::success('Instruction deleted successfully');
            
        } catch (\Exception $e) {
            error_log('FC Autoposter ContentInstruction Controller Error: ' . $e->getMessage());
            return Response::error('Failed to delete instruction', $e->getMessage(), 500);
        }
    }
    
    /**
     * Retry a failed instruction
     */
    public function retry(Request $request) {
        try {
            $id = $request->param('id');
            
            $instruction = ContentInstruction::find($id);
            
            if (!$instruction) {
                return Response::error('Instruction not found', null, 404);
            }
            
            $success = $instruction->update([
                'status' => 'pending',
                'error_message' => null
            ]);
            
            if (!$success) {
                return Response::error('Failed to retry instruction', null, 500);
            }
            
            return Response::success('Instruction queued for retry', $instruction->toArray());
            
        } catch (\Exception $e) {
            error_log('FC Autoposter ContentInstruction Controller Error: ' . $e->getMessage());
            return Response::error('Failed to retry instruction', $e->getMessage(), 500);
        }
    }
}
