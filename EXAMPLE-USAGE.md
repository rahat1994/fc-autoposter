# Example Usage - Laravel-Style Routing in WordPress

This guide provides practical examples of using the routing system in FC Autoposter.

## Table of Contents
- [Basic Examples](#basic-examples)
- [Building a Posts API](#building-a-posts-api)
- [User Management API](#user-management-api)
- [File Upload API](#file-upload-api)
- [Integration with Vue.js](#integration-with-vuejs)

## Basic Examples

### 1. Simple Hello World

```php
// routes/api.php
$router->get('/hello-world', function($request) {
    return Response::success('Hello, World!');
});

// Access: GET /wp-json/fc-autoposter/v1/hello-world
// Response: {"success": true, "message": "Hello, World!", "data": null}
```

### 2. Echo Parameters

```php
$router->get('/echo/{message}', function($request) {
    $message = $request->param('message');
    return Response::json([
        'echo' => $message,
        'timestamp' => current_time('mysql')
    ]);
});

// Access: GET /wp-json/fc-autoposter/v1/echo/test
// Response: {"echo": "test", "timestamp": "2024-01-01 12:00:00"}
```

## Building a Posts API

### Step 1: Create the Controller

Create `includes/Controllers/PostController.php`:

```php
<?php
namespace FCAutoposter\Controllers;

use FCAutoposter\Routing\Response;

class PostController extends Controller {
    
    /**
     * Get all posts
     */
    public function index($request) {
        // Get query parameters
        $per_page = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        
        // Get posts
        $args = [
            'post_type' => 'post',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'post_status' => 'publish'
        ];
        
        $query = new \WP_Query($args);
        
        $posts = array_map(function($post) {
            return [
                'id' => $post->ID,
                'title' => $post->post_title,
                'excerpt' => $post->post_excerpt,
                'date' => $post->post_date,
                'author' => get_the_author_meta('display_name', $post->post_author),
                'link' => get_permalink($post->ID)
            ];
        }, $query->posts);
        
        return Response::success('Posts retrieved successfully', [
            'posts' => $posts,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'current_page' => $page
        ]);
    }
    
    /**
     * Get single post
     */
    public function show($request) {
        $id = $request->param('id');
        $post = get_post($id);
        
        if (!$post || $post->post_status !== 'publish') {
            return Response::notFound('Post not found');
        }
        
        return Response::success('Post retrieved', [
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => apply_filters('the_content', $post->post_content),
            'excerpt' => $post->post_excerpt,
            'date' => $post->post_date,
            'author' => [
                'id' => $post->post_author,
                'name' => get_the_author_meta('display_name', $post->post_author)
            ],
            'categories' => wp_get_post_categories($post->ID, ['fields' => 'names']),
            'tags' => wp_get_post_tags($post->ID, ['fields' => 'names'])
        ]);
    }
    
    /**
     * Create new post
     */
    public function store($request) {
        // Check permissions
        if ($auth = $this->authorize('publish_posts')) {
            return $auth;
        }
        
        // Validate input
        $validation = $this->validate($request, [
            'title' => 'required|string|min:3|max:255',
            'content' => 'required|string',
            'status' => 'in:publish,draft,pending'
        ]);
        
        if ($validation) {
            return $validation;
        }
        
        // Create post
        $post_data = [
            'post_title' => sanitize_text_field($request->input('title')),
            'post_content' => wp_kses_post($request->input('content')),
            'post_status' => $request->input('status', 'draft'),
            'post_author' => $this->getCurrentUserId(),
            'post_type' => 'post'
        ];
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return Response::error('Failed to create post', $post_id->get_error_message());
        }
        
        // Add categories if provided
        if ($request->has('categories')) {
            wp_set_post_categories($post_id, $request->input('categories'));
        }
        
        // Add tags if provided
        if ($request->has('tags')) {
            wp_set_post_tags($post_id, $request->input('tags'));
        }
        
        $post = get_post($post_id);
        
        return Response::created([
            'id' => $post->ID,
            'title' => $post->post_title,
            'status' => $post->post_status,
            'link' => get_permalink($post->ID)
        ], 'Post created successfully');
    }
    
    /**
     * Update post
     */
    public function update($request) {
        $id = $request->param('id');
        
        // Check if post exists
        $post = get_post($id);
        if (!$post) {
            return Response::notFound('Post not found');
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $id)) {
            return Response::forbidden('You cannot edit this post');
        }
        
        // Validate
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
            $update_data['post_title'] = sanitize_text_field($request->input('title'));
        }
        
        if ($request->has('content')) {
            $update_data['post_content'] = wp_kses_post($request->input('content'));
        }
        
        if ($request->has('status')) {
            $update_data['post_status'] = $request->input('status');
        }
        
        wp_update_post($update_data);
        
        $updated_post = get_post($id);
        
        return Response::success('Post updated successfully', [
            'id' => $updated_post->ID,
            'title' => $updated_post->post_title,
            'status' => $updated_post->post_status
        ]);
    }
    
    /**
     * Delete post
     */
    public function destroy($request) {
        $id = $request->param('id');
        
        $post = get_post($id);
        if (!$post) {
            return Response::notFound('Post not found');
        }
        
        // Check permissions
        if (!current_user_can('delete_post', $id)) {
            return Response::forbidden('You cannot delete this post');
        }
        
        wp_delete_post($id, true);
        
        return Response::success('Post deleted successfully');
    }
}
```

### Step 2: Register Routes

In `routes/api.php`:

```php
// Protected routes for authenticated users with publish_posts capability
$router->group(['prefix' => 'posts', 'middleware' => ['auth', 'admin']], function($router) {
    $router->get('/', 'PostController@index')->name('posts.index');
    $router->post('/', 'PostController@store')->name('posts.store');
    $router->get('/{id}', 'PostController@show')->name('posts.show');
    $router->put('/{id}', 'PostController@update')->name('posts.update');
    $router->delete('/{id}', 'PostController@destroy')->name('posts.destroy');
});

// Or use resource routing (same as above)
// $router->resource('posts', 'PostController');
```

### Step 3: Test the API

```bash
# List posts
curl http://your-site.com/wp-json/fc-autoposter/v1/posts

# Get specific post
curl http://your-site.com/wp-json/fc-autoposter/v1/posts/123

# Create post (requires authentication)
curl -X POST http://your-site.com/wp-json/fc-autoposter/v1/posts \
  -H "Content-Type: application/json" \
  -H "X-WP-Nonce: YOUR_NONCE" \
  -d '{"title":"New Post","content":"Post content","status":"publish"}'

# Update post
curl -X PUT http://your-site.com/wp-json/fc-autoposter/v1/posts/123 \
  -H "Content-Type: application/json" \
  -H "X-WP-Nonce: YOUR_NONCE" \
  -d '{"title":"Updated Title"}'

# Delete post
curl -X DELETE http://your-site.com/wp-json/fc-autoposter/v1/posts/123 \
  -H "X-WP-Nonce: YOUR_NONCE"
```

## User Management API

```php
// includes/Controllers/UserController.php
namespace FCAutoposter\Controllers;

use FCAutoposter\Routing\Response;

class UserController extends Controller {
    
    public function profile($request) {
        if (!$this->isLoggedIn()) {
            return Response::unauthorized('Please log in');
        }
        
        $user = wp_get_current_user();
        
        return Response::success('User profile', [
            'id' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'display_name' => $user->display_name,
            'roles' => $user->roles,
            'registered' => $user->user_registered
        ]);
    }
    
    public function updateProfile($request) {
        if (!$this->isLoggedIn()) {
            return Response::unauthorized();
        }
        
        $validation = $this->validate($request, [
            'display_name' => 'string|max:255',
            'email' => 'email'
        ]);
        
        if ($validation) {
            return $validation;
        }
        
        $user_id = $this->getCurrentUserId();
        $update_data = ['ID' => $user_id];
        
        if ($request->has('display_name')) {
            $update_data['display_name'] = $request->input('display_name');
        }
        
        if ($request->has('email')) {
            $update_data['user_email'] = $request->input('email');
        }
        
        wp_update_user($update_data);
        
        return Response::success('Profile updated successfully');
    }
}

// routes/api.php
$router->group(['middleware' => 'auth'], function($router) {
    $router->get('/me', 'UserController@profile')->name('user.profile');
    $router->put('/me', 'UserController@updateProfile')->name('user.update');
});
```

## File Upload API

```php
// includes/Controllers/MediaController.php
namespace FCAutoposter\Controllers;

use FCAutoposter\Routing\Response;

class MediaController extends Controller {
    
    public function upload($request) {
        // Check permissions
        if ($auth = $this->authorize('upload_files')) {
            return $auth;
        }
        
        // Check if file was uploaded
        if (empty($_FILES['file'])) {
            return Response::error('No file uploaded');
        }
        
        // WordPress file upload
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        $file = $_FILES['file'];
        
        // Upload file
        $upload = wp_handle_upload($file, ['test_form' => false]);
        
        if (isset($upload['error'])) {
            return Response::error('Upload failed', $upload['error']);
        }
        
        // Create attachment
        $attachment = [
            'post_mime_type' => $upload['type'],
            'post_title' => sanitize_file_name($file['name']),
            'post_content' => '',
            'post_status' => 'inherit'
        ];
        
        $attachment_id = wp_insert_attachment($attachment, $upload['file']);
        
        // Generate metadata
        $metadata = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $metadata);
        
        return Response::created([
            'id' => $attachment_id,
            'url' => $upload['url'],
            'type' => $upload['type']
        ], 'File uploaded successfully');
    }
}

// routes/api.php
$router->group(['prefix' => 'media', 'middleware' => ['auth', 'admin']], function($router) {
    $router->post('/upload', 'MediaController@upload')->name('media.upload');
});
```

## Integration with Vue.js

### In your Vue component:

```vue
<template>
  <div>
    <h2>Posts</h2>
    <div v-for="post in posts" :key="post.id">
      <h3>{{ post.title }}</h3>
      <p>{{ post.excerpt }}</p>
      <button @click="deletePost(post.id)">Delete</button>
    </div>
    
    <form @submit.prevent="createPost">
      <input v-model="newPost.title" placeholder="Title" required>
      <textarea v-model="newPost.content" placeholder="Content" required></textarea>
      <button type="submit">Create Post</button>
    </form>
  </div>
</template>

<script>
export default {
  data() {
    return {
      posts: [],
      newPost: {
        title: '',
        content: ''
      }
    }
  },
  
  mounted() {
    this.fetchPosts()
  },
  
  methods: {
    async fetchPosts() {
      try {
        const response = await fetch('/wp-json/fc-autoposter/v1/posts', {
          headers: {
            'X-WP-Nonce': window.fcAutoposterAdmin.nonce
          }
        })
        
        const data = await response.json()
        
        if (data.success) {
          this.posts = data.data.posts
        }
      } catch (error) {
        console.error('Failed to fetch posts:', error)
      }
    },
    
    async createPost() {
      try {
        const response = await fetch('/wp-json/fc-autoposter/v1/posts', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': window.fcAutoposterAdmin.nonce
          },
          body: JSON.stringify({
            title: this.newPost.title,
            content: this.newPost.content,
            status: 'publish'
          })
        })
        
        const data = await response.json()
        
        if (data.success) {
          alert('Post created successfully!')
          this.newPost.title = ''
          this.newPost.content = ''
          this.fetchPosts()
        } else {
          alert('Failed to create post: ' + data.message)
        }
      } catch (error) {
        console.error('Failed to create post:', error)
      }
    },
    
    async deletePost(id) {
      if (!confirm('Are you sure you want to delete this post?')) {
        return
      }
      
      try {
        const response = await fetch(`/wp-json/fc-autoposter/v1/posts/${id}`, {
          method: 'DELETE',
          headers: {
            'X-WP-Nonce': window.fcAutoposterAdmin.nonce
          }
        })
        
        const data = await response.json()
        
        if (data.success) {
          this.fetchPosts()
        } else {
          alert('Failed to delete post: ' + data.message)
        }
      } catch (error) {
        console.error('Failed to delete post:', error)
      }
    }
  }
}
</script>
```

### Providing Nonce to Vue App

In your main plugin file, ensure nonce is passed to JavaScript:

```php
// In fc-autoposter.php, add to the enqueue function
wp_localize_script('fc-autoposter-app', 'fcAutoposterAdmin', [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('wp_rest'),
    'restUrl' => rest_url('fc-autoposter/v1/')
]);
```

## Tips for Production

1. **Always validate input** - Never trust user input
2. **Use nonces** - Protect against CSRF attacks
3. **Check permissions** - Verify user capabilities
4. **Sanitize output** - Use WordPress sanitization functions
5. **Handle errors gracefully** - Return meaningful error messages
6. **Log errors** - Use `error_log()` for debugging
7. **Rate limiting** - Consider implementing rate limiting for public endpoints
8. **Cache responses** - Use WordPress transients for expensive queries

## More Examples

Check the `routes/api.php` file for more examples including:
- Public endpoints
- Authentication middleware
- Admin-only routes
- Route grouping
- Named routes
