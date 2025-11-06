<?php
/**
 * Example Controller
 * 
 * Demonstrates how to use the routing system
 */

namespace FCAutoposter\Controllers;

use FCAutoposter\Routing\Response;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ExampleController extends Controller {
    
    /**
     * Display a listing of the resource
     * GET /examples
     */
    public function index($request) {
        // Example: Get all posts
        $posts = get_posts([
            'post_type' => 'post',
            'posts_per_page' => 10,
            'post_status' => 'publish'
        ]);
        
        $data = array_map(function($post) {
            return [
                'id' => $post->ID,
                'title' => $post->post_title,
                'content' => $post->post_content,
                'date' => $post->post_date
            ];
        }, $posts);
        
        return Response::success('Posts retrieved successfully', $data);
    }
    
    /**
     * Store a newly created resource
     * POST /examples
     */
    public function store($request) {
        // Validate input
        $validation = $this->validate($request, [
            'title' => 'required|string|min:3|max:255',
            'content' => 'required|string',
            'status' => 'in:publish,draft'
        ]);
        
        if ($validation) {
            return $validation;
        }
        
        // Create post
        $post_id = wp_insert_post([
            'post_title' => $request->input('title'),
            'post_content' => $request->input('content'),
            'post_status' => $request->input('status', 'draft'),
            'post_type' => 'post'
        ]);
        
        if (is_wp_error($post_id)) {
            return Response::error('Failed to create post', $post_id->get_error_message());
        }
        
        $post = get_post($post_id);
        
        return Response::created([
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'status' => $post->post_status,
            'date' => $post->post_date
        ], 'Post created successfully');
    }
    
    /**
     * Display the specified resource
     * GET /examples/{id}
     */
    public function show($request) {
        $id = $request->param('id');
        
        $post = get_post($id);
        
        if (!$post) {
            return Response::notFound('Post not found');
        }
        
        return Response::success('Post retrieved successfully', [
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'excerpt' => $post->post_excerpt,
            'status' => $post->post_status,
            'date' => $post->post_date,
            'modified' => $post->post_modified
        ]);
    }
    
    /**
     * Update the specified resource
     * PUT/PATCH /examples/{id}
     */
    public function update($request) {
        $id = $request->param('id');
        
        // Check if post exists
        $post = get_post($id);
        if (!$post) {
            return Response::notFound('Post not found');
        }
        
        // Validate input
        $validation = $this->validate($request, [
            'title' => 'string|min:3|max:255',
            'content' => 'string',
            'status' => 'in:publish,draft,pending'
        ]);
        
        if ($validation) {
            return $validation;
        }
        
        // Update post
        $update_data = ['ID' => $id];
        
        if ($request->has('title')) {
            $update_data['post_title'] = $request->input('title');
        }
        
        if ($request->has('content')) {
            $update_data['post_content'] = $request->input('content');
        }
        
        if ($request->has('status')) {
            $update_data['post_status'] = $request->input('status');
        }
        
        $result = wp_update_post($update_data);
        
        if (is_wp_error($result)) {
            return Response::error('Failed to update post', $result->get_error_message());
        }
        
        $updated_post = get_post($id);
        
        return Response::success('Post updated successfully', [
            'id' => $updated_post->ID,
            'title' => $updated_post->post_title,
            'content' => $updated_post->post_content,
            'status' => $updated_post->post_status,
            'modified' => $updated_post->post_modified
        ]);
    }
    
    /**
     * Remove the specified resource
     * DELETE /examples/{id}
     */
    public function destroy($request) {
        $id = $request->param('id');
        
        // Check if post exists
        $post = get_post($id);
        if (!$post) {
            return Response::notFound('Post not found');
        }
        
        // Delete post
        $result = wp_delete_post($id, true);
        
        if (!$result) {
            return Response::error('Failed to delete post');
        }
        
        return Response::success('Post deleted successfully');
    }
}
