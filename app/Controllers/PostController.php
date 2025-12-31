<?php
/**
 * Post Controller
 * 
 * Handles post-related API endpoints
 */

namespace FCAutoposter\Controllers;

use FCAutoposter\Models\Post;
use FCAutoposter\Routing\Response;
use FCAutoposter\Routing\Request;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class PostController {
    
    /**
     * Get all posts
     */
    public function index(Request $request) {
        try {
            $page = (int) $request->query('page', 1);
            $per_page = (int) $request->query('per_page', 10);
            
            $result = Post::getWithDetails($page, $per_page);
            error_log('Post::getWithDetails result keys: ' . implode(', ', array_keys($result)));
            if (isset($result['meta'])) {
                error_log('Meta keys: ' . implode(', ', array_keys($result['meta'])));
            } else {
                error_log('Meta key missing!');
            }
            
            return Response::success('Posts retrieved successfully', $result);
            
        } catch (\Exception $e) {
            error_log('FC Autoposter Post Controller Error: ' . $e->getMessage());
            return Response::error('Failed to retrieve posts', $e->getMessage(), 500);
        }
    }
    
    /**
     * Delete a post
     */
    public function destroy(Request $request) {
        global $wpdb;
        
        try {
            $id = $request->param('id');
            
            $post = Post::find($id);
            
            if (!$post) {
                return Response::error('Post not found', null, 404);
            }
            
            // Get the fcom_post_id before deleting
            $fcom_post_id = $post->fcom_post_id;
            
            // Delete from our pivot table
            $success = $post->delete();
            
            if (!$success) {
                return Response::error('Failed to delete post link', null, 500);
            }
            
            // Delete from wp_fcom_posts table as requested
            if ($fcom_post_id) {
                $fcom_posts_table = $wpdb->prefix . 'fcom_posts';
                $wpdb->delete($fcom_posts_table, ['id' => $fcom_post_id]);
            }
            
            return Response::success('Post deleted successfully');
            
        } catch (\Exception $e) {
            error_log('FC Autoposter Post Controller Error: ' . $e->getMessage());
            return Response::error('Failed to delete post', $e->getMessage(), 500);
        }
    }
}
