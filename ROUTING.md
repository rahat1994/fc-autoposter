# Laravel-Style PHP Routing System

This WordPress plugin includes a Laravel-inspired routing system that provides a clean, expressive way to define API endpoints and handle HTTP requests.

## Features

- 🚀 **Laravel-style route definitions** - Familiar syntax for developers coming from Laravel
- 🛣️ **RESTful routing** - Support for GET, POST, PUT, PATCH, DELETE methods
- 🎯 **Route parameters** - Dynamic URL segments with parameter extraction
- 🔐 **Middleware support** - Built-in authentication, authorization, and custom middleware
- 🎨 **Route grouping** - Group routes with shared attributes (prefix, middleware)
- 🏗️ **Controller classes** - Organize your code with MVC pattern
- ✅ **Input validation** - Built-in validation helpers
- 📦 **RESTful resources** - Generate CRUD routes automatically

## Directory Structure

```
fc-autoposter/
├── includes/
│   ├── Routing/
│   │   ├── Router.php                 # Main router class
│   │   ├── Route.php                  # Route definition
│   │   ├── Request.php                # HTTP request handler
│   │   ├── Response.php               # HTTP response handler
│   │   └── RouteServiceProvider.php   # WordPress integration
│   ├── Controllers/
│   │   ├── Controller.php             # Base controller
│   │   └── ExampleController.php      # Example controller
│   ├── Middleware/
│   │   ├── Middleware.php             # Base middleware
│   │   ├── AuthMiddleware.php         # Authentication
│   │   ├── AdminMiddleware.php        # Admin authorization
│   │   └── NonceMiddleware.php        # Nonce verification
│   └── autoloader.php                 # PSR-4 autoloader
└── routes/
    └── api.php                        # API route definitions
```

## Quick Start

### 1. Defining Routes

Routes are defined in `routes/api.php`. All routes are automatically prefixed with `fc-autoposter/v1`.

```php
// Simple GET route
$router->get('/hello', function($request) {
    return Response::success('Hello World!');
});

// Route with parameters
$router->get('/users/{id}', function($request) {
    $id = $request->param('id');
    return Response::success('User ID: ' . $id);
});

// Controller action
$router->get('/posts', 'PostController@index');
```

### 2. HTTP Methods

```php
$router->get('/resource', $action);       // GET
$router->post('/resource', $action);      // POST
$router->put('/resource', $action);       // PUT
$router->patch('/resource', $action);     // PATCH
$router->delete('/resource', $action);    // DELETE
$router->any('/resource', $action);       // Any method
$router->match(['GET', 'POST'], '/resource', $action); // Specific methods
```

### 3. Route Parameters

```php
// Required parameter
$router->get('/users/{id}', function($request) {
    $id = $request->param('id');
    return Response::json(['user_id' => $id]);
});

// Multiple parameters
$router->get('/posts/{postId}/comments/{commentId}', function($request) {
    $postId = $request->param('postId');
    $commentId = $request->param('commentId');
    return Response::json([
        'post' => $postId,
        'comment' => $commentId
    ]);
});
```

### 4. Route Groups

Group routes with shared attributes:

```php
// With prefix
$router->group(['prefix' => 'admin'], function($router) {
    $router->get('/dashboard', 'AdminController@dashboard');
    // Route: /admin/dashboard
});

// With middleware
$router->group(['middleware' => 'auth'], function($router) {
    $router->get('/profile', 'UserController@profile');
});

// Multiple attributes
$router->group([
    'prefix' => 'api',
    'middleware' => ['auth', 'admin']
], function($router) {
    $router->get('/settings', 'SettingsController@index');
    // Route: /api/settings
});
```

### 5. RESTful Resources

Generate all CRUD routes automatically:

```php
$router->resource('posts', 'PostController');
```

This creates:
- `GET /posts` → `PostController@index`
- `POST /posts` → `PostController@store`
- `GET /posts/{id}` → `PostController@show`
- `PUT /posts/{id}` → `PostController@update`
- `DELETE /posts/{id}` → `PostController@destroy`

### 6. Named Routes

```php
$router->get('/profile', 'UserController@show')->name('user.profile');

// Generate URL
$url = $router->url('user.profile');
```

## Creating Controllers

### Basic Controller

Create a controller in `includes/Controllers/`:

```php
<?php
namespace FCAutoposter\Controllers;

use FCAutoposter\Routing\Response;

class PostController extends Controller {
    
    public function index($request) {
        // Get all posts
        $posts = get_posts(['post_type' => 'post']);
        
        return Response::success('Posts retrieved', $posts);
    }
    
    public function show($request) {
        $id = $request->param('id');
        $post = get_post($id);
        
        if (!$post) {
            return Response::notFound('Post not found');
        }
        
        return Response::success('Post found', $post);
    }
}
```

### Controller with Validation

```php
public function store($request) {
    // Validate input
    $validation = $this->validate($request, [
        'title' => 'required|string|min:3|max:255',
        'content' => 'required|string',
        'email' => 'email',
        'status' => 'in:publish,draft,pending'
    ]);
    
    if ($validation) {
        return $validation; // Returns validation error response
    }
    
    // Create resource
    // ...
    
    return Response::created($data);
}
```

### Available Validation Rules

- `required` - Field must be present and not empty
- `string` - Must be a string
- `numeric` - Must be numeric
- `integer` - Must be an integer
- `email` - Must be a valid email
- `url` - Must be a valid URL
- `array` - Must be an array
- `min:n` - Minimum length/value
- `max:n` - Maximum length/value
- `in:val1,val2` - Must be one of the specified values

## Working with Requests

The `Request` object provides access to all request data:

```php
public function store($request) {
    // Get single input
    $title = $request->input('title');
    $title = $request->input('title', 'Default Title'); // With default
    
    // Get all input
    $all = $request->all();
    
    // Get specific inputs
    $data = $request->only(['title', 'content']);
    $data = $request->except(['password']);
    
    // Query parameters
    $page = $request->query('page', 1);
    
    // Route parameters
    $id = $request->param('id');
    
    // Headers
    $token = $request->header('Authorization');
    
    // Check if input exists
    if ($request->has('title')) {
        // ...
    }
    
    // HTTP method
    $method = $request->method(); // GET, POST, etc.
}
```

## Response Types

The `Response` class provides various response helpers:

```php
// Success response (200)
return Response::success('Operation successful', $data);

// Created (201)
return Response::created($data, 'Resource created');

// No content (204)
return Response::noContent();

// Error response (400)
return Response::error('Something went wrong');

// Not found (404)
return Response::notFound('Resource not found');

// Unauthorized (401)
return Response::unauthorized('Please login');

// Forbidden (403)
return Response::forbidden('Access denied');

// Validation error (422)
return Response::validationError($errors, 'Validation failed');

// Custom JSON response
return Response::json($data, 200);

// Custom response
return Response::json($data, 201)
    ->header('X-Custom-Header', 'value');
```

## Middleware

### Using Middleware

Apply middleware to routes or groups:

```php
// Single middleware
$router->get('/admin', 'AdminController@index')->middleware('auth');

// Multiple middleware
$router->get('/settings', 'SettingsController@index')
    ->middleware(['auth', 'admin']);

// Group middleware
$router->group(['middleware' => ['auth', 'admin']], function($router) {
    // All routes here use both middleware
});
```

### Built-in Middleware

- `auth` - Requires user to be logged in
- `admin` - Requires user to have `manage_options` capability
- `nonce` - Verifies WordPress nonce for security

### Creating Custom Middleware

Create a middleware class in `includes/Middleware/`:

```php
<?php
namespace FCAutoposter\Middleware;

use FCAutoposter\Routing\Response;

class CustomMiddleware extends Middleware {
    
    public function handle($request, $next) {
        // Check condition
        if (!$this->checkSomething()) {
            return Response::forbidden('Access denied');
        }
        
        // Continue to next middleware/controller
        return $next($request);
    }
    
    private function checkSomething() {
        // Your logic here
        return true;
    }
}
```

Register in `RouteServiceProvider.php`:

```php
$this->router->registerMiddleware('custom', 'FCAutoposter\\Middleware\\CustomMiddleware');
```

Use in routes:

```php
$router->get('/protected', 'Controller@method')->middleware('custom');
```

## Authorization in Controllers

Use built-in authorization helpers:

```php
public function update($request) {
    // Check if user has capability
    $auth = $this->authorize('edit_posts');
    if ($auth) {
        return $auth; // Returns 403 response
    }
    
    // Or manual checks
    if (!$this->userCan('manage_options')) {
        return Response::forbidden();
    }
    
    if (!$this->isLoggedIn()) {
        return Response::unauthorized();
    }
    
    // Get current user
    $userId = $this->getCurrentUserId();
    
    // Continue with action...
}
```

## Testing the API

### Using cURL

```bash
# GET request
curl http://your-site.com/wp-json/fc-autoposter/v1/hello

# POST request with JSON
curl -X POST http://your-site.com/wp-json/fc-autoposter/v1/posts \
  -H "Content-Type: application/json" \
  -d '{"title":"Test Post","content":"Post content"}'

# With authentication (nonce)
curl http://your-site.com/wp-json/fc-autoposter/v1/profile \
  -H "X-WP-Nonce: YOUR_NONCE_HERE"
```

### Using JavaScript (from WordPress admin)

```javascript
// In your Vue.js or JavaScript code
const response = await fetch('/wp-json/fc-autoposter/v1/posts', {
  method: 'GET',
  headers: {
    'Content-Type': 'application/json',
    'X-WP-Nonce': wpApiSettings.nonce // WordPress provides this
  }
});

const data = await response.json();
console.log(data);
```

### From Vue Components

```javascript
// In Vue component
async fetchPosts() {
  try {
    const response = await fetch('/wp-json/fc-autoposter/v1/posts', {
      headers: {
        'X-WP-Nonce': window.fcAutoposterAdmin.nonce
      }
    });
    
    if (!response.ok) {
      throw new Error('Failed to fetch posts');
    }
    
    const data = await response.json();
    this.posts = data.data;
  } catch (error) {
    console.error('Error:', error);
  }
}
```

## Example Routes

See `routes/api.php` for complete examples:

- Simple closures
- Controller actions
- Route parameters
- Route groups
- RESTful resources
- Public and protected routes

## Best Practices

1. **Use Controllers** - Keep route closures simple, move logic to controllers
2. **Validate Input** - Always validate user input
3. **Use Middleware** - Apply authentication and authorization via middleware
4. **Return Consistent Responses** - Use the Response helpers
5. **Name Your Routes** - Makes generating URLs easier
6. **Group Related Routes** - Use route groups for organization
7. **Check Permissions** - Always verify user has required capabilities
8. **Handle Errors** - Use try-catch and return appropriate error responses

## Accessing the API

All routes are accessible via the WordPress REST API at:

```
http://your-site.com/wp-json/fc-autoposter/v1/{route}
```

Example:
- `http://your-site.com/wp-json/fc-autoposter/v1/hello`
- `http://your-site.com/wp-json/fc-autoposter/v1/posts`
- `http://your-site.com/wp-json/fc-autoposter/v1/posts/123`

## Getting WordPress Nonce

For authenticated requests, get the nonce from:

```php
// In PHP
$nonce = wp_create_nonce('wp_rest');

// Pass to JavaScript
wp_localize_script('your-script', 'wpApiSettings', [
    'nonce' => wp_create_nonce('wp_rest')
]);
```

## Troubleshooting

### Routes not working

1. Check that permalinks are enabled (Settings > Permalinks)
2. Verify the route file `routes/api.php` exists
3. Check for PHP errors in WordPress debug log
4. Ensure autoloader is loaded correctly

### 403 Forbidden

1. Check middleware requirements (auth, admin)
2. Verify user has required capabilities
3. Check WordPress nonce is being sent correctly

### 404 Not Found

1. Verify route is registered in `routes/api.php`
2. Check URL matches route pattern exactly
3. Ensure WordPress REST API is working: visit `/wp-json/`

## Additional Resources

- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)
- [Laravel Routing Documentation](https://laravel.com/docs/routing)
- [PSR-4 Autoloading](https://www.php-fig.org/psr/psr-4/)
