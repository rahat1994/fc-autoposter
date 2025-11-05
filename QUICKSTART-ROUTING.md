# Quick Start - Laravel-Style Routing

Get started with the FC Autoposter routing system in 5 minutes!

## 1. Your First Route

Open `routes/api.php` and add:

```php
$router->get('/hello', function($request) {
    return Response::success('Hello from my API!');
});
```

**Test it:**
```bash
curl http://your-site.com/wp-json/fc-autoposter/v1/hello
```

**Response:**
```json
{
  "success": true,
  "message": "Hello from my API!",
  "data": null
}
```

## 2. Route with Parameters

```php
$router->get('/greet/{name}', function($request) {
    $name = $request->param('name');
    return Response::success("Hello, $name!");
});
```

**Test it:**
```bash
curl http://your-site.com/wp-json/fc-autoposter/v1/greet/John
```

## 3. Create a Controller

Create `includes/Controllers/TaskController.php`:

```php
<?php
namespace FCAutoposter\Controllers;

use FCAutoposter\Routing\Response;

class TaskController extends Controller {
    
    public function index($request) {
        // Get all tasks (example using WordPress options)
        $tasks = get_option('fc_tasks', []);
        return Response::success('Tasks retrieved', $tasks);
    }
    
    public function store($request) {
        // Validate
        $validation = $this->validate($request, [
            'title' => 'required|string|min:3',
            'description' => 'required|string'
        ]);
        
        if ($validation) {
            return $validation;
        }
        
        // Create task
        $tasks = get_option('fc_tasks', []);
        $task = [
            'id' => uniqid(),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'completed' => false,
            'created_at' => current_time('mysql')
        ];
        
        $tasks[] = $task;
        update_option('fc_tasks', $tasks);
        
        return Response::created($task, 'Task created successfully');
    }
}
```

## 4. Register Controller Routes

In `routes/api.php`:

```php
// Protected routes
$router->group(['prefix' => 'tasks', 'middleware' => ['auth']], function($router) {
    $router->get('/', 'TaskController@index');
    $router->post('/', 'TaskController@store');
});
```

**Test it:**
```bash
# Get tasks
curl http://your-site.com/wp-json/fc-autoposter/v1/tasks \
  -H "X-WP-Nonce: YOUR_NONCE"

# Create task
curl -X POST http://your-site.com/wp-json/fc-autoposter/v1/tasks \
  -H "Content-Type: application/json" \
  -H "X-WP-Nonce: YOUR_NONCE" \
  -d '{"title":"My Task","description":"Task description"}'
```

## 5. Use in Vue.js

In your Vue component:

```vue
<template>
  <div>
    <h2>Tasks</h2>
    <ul>
      <li v-for="task in tasks" :key="task.id">
        {{ task.title }}
      </li>
    </ul>
    <button @click="loadTasks">Refresh</button>
  </div>
</template>

<script>
export default {
  data() {
    return {
      tasks: []
    }
  },
  
  mounted() {
    this.loadTasks()
  },
  
  methods: {
    async loadTasks() {
      const response = await fetch('/wp-json/fc-autoposter/v1/tasks', {
        headers: {
          'X-WP-Nonce': window.fcAutoposterAdmin.nonce
        }
      })
      
      const data = await response.json()
      if (data.success) {
        this.tasks = data.data
      }
    }
  }
}
</script>
```

## Common Patterns

### GET with Query Parameters
```php
$router->get('/search', function($request) {
    $query = $request->query('q');
    $page = $request->query('page', 1);
    
    // Do search...
    
    return Response::success('Search results', $results);
});

// Access: GET /search?q=test&page=2
```

### POST with JSON Body
```php
$router->post('/create', function($request) {
    $title = $request->input('title');
    $content = $request->input('content');
    
    // Create resource...
    
    return Response::created($resource);
});
```

### PUT/PATCH for Updates
```php
$router->put('/update/{id}', function($request) {
    $id = $request->param('id');
    $data = $request->only(['title', 'content']);
    
    // Update resource...
    
    return Response::success('Updated successfully');
});
```

### DELETE for Removal
```php
$router->delete('/delete/{id}', function($request) {
    $id = $request->param('id');
    
    // Delete resource...
    
    return Response::success('Deleted successfully');
});
```

### RESTful Resource (All CRUD Routes)
```php
// One line creates all 5 routes!
$router->resource('posts', 'PostController');

// Creates:
// GET    /posts           -> PostController@index
// POST   /posts           -> PostController@store
// GET    /posts/{id}      -> PostController@show
// PUT    /posts/{id}      -> PostController@update
// DELETE /posts/{id}      -> PostController@destroy
```

## Validation Rules

Available in controllers via `$this->validate()`:

```php
$validation = $this->validate($request, [
    'title' => 'required|string|min:3|max:255',
    'email' => 'required|email',
    'age' => 'required|integer|min:18',
    'status' => 'in:active,inactive,pending',
    'url' => 'url',
    'tags' => 'array'
]);
```

## Response Types

```php
// Success (200)
Response::success('Message', $data);

// Created (201)
Response::created($data, 'Created successfully');

// Error (400)
Response::error('Error message');

// Not Found (404)
Response::notFound('Resource not found');

// Unauthorized (401)
Response::unauthorized('Please login');

// Forbidden (403)
Response::forbidden('Access denied');

// Validation Error (422)
Response::validationError($errors);

// Custom
Response::json($data, 201)->header('X-Custom', 'value');
```

## Middleware

```php
// Auth - requires login
$router->get('/profile', 'UserController@profile')
    ->middleware('auth');

// Admin - requires manage_options capability
$router->get('/settings', 'SettingsController@index')
    ->middleware(['auth', 'admin']);

// Apply to group
$router->group(['middleware' => 'auth'], function($router) {
    // All routes here require authentication
});
```

## Getting WordPress Nonce

For authenticated requests from JavaScript:

```php
// In your plugin file
wp_localize_script('your-script', 'fcAutoposterAdmin', [
    'nonce' => wp_create_nonce('wp_rest'),
    'restUrl' => rest_url('fc-autoposter/v1/')
]);
```

Then in JavaScript:
```javascript
fetch(url, {
    headers: {
        'X-WP-Nonce': window.fcAutoposterAdmin.nonce
    }
})
```

## Next Steps

1. Read the full [ROUTING.md](ROUTING.md) documentation
2. Check [EXAMPLE-USAGE.md](EXAMPLE-USAGE.md) for complete examples
3. Look at `routes/api.php` for more examples
4. Explore `includes/Controllers/ExampleController.php` for controller patterns

## Need Help?

- Check if WordPress REST API is working: visit `/wp-json/`
- Enable WordPress debug mode to see errors
- Check browser console for JavaScript errors
- Verify nonce is being sent correctly in requests

## API Endpoints

All routes are available at:
```
http://your-site.com/wp-json/fc-autoposter/v1/{your-route}
```

Happy coding! 🚀
