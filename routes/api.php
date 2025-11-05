<?php
/**
 * API Routes
 * 
 * Define your API routes here using Laravel-style syntax
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

use FCAutoposter\Routing\Router;

/**
 * @var Router $router
 */

// Example: Simple routes with closures
$router->get('/hello', function($request) {
    return \FCAutoposter\Routing\Response::success('Hello from FC Autoposter API!');
})->name('hello');

$router->get('/test', function($request) {
    return \FCAutoposter\Routing\Response::json([
        'message' => 'API is working!',
        'timestamp' => current_time('mysql'),
        'user_id' => get_current_user_id()
    ]);
})->name('test');

// Example: Route with parameters
$router->get('/greet/{name}', function($request) {
    $name = $request->param('name');
    return \FCAutoposter\Routing\Response::success("Hello, {$name}!");
})->name('greet');

// Example: Route groups with prefix and middleware
$router->group(['prefix' => 'posts', 'middleware' => ['auth', 'admin']], function($router) {
    
    // GET /posts
    $router->get('/', 'ExampleController@index')->name('posts.index');
    
    // POST /posts
    $router->post('/', 'ExampleController@store')->name('posts.store');
    
    // GET /posts/{id}
    $router->get('/{id}', 'ExampleController@show')->name('posts.show');
    
    // PUT /posts/{id}
    $router->put('/{id}', 'ExampleController@update')->name('posts.update');
    
    // PATCH /posts/{id}
    $router->patch('/{id}', 'ExampleController@update')->name('posts.patch');
    
    // DELETE /posts/{id}
    $router->delete('/{id}', 'ExampleController@destroy')->name('posts.destroy');
});

// Example: RESTful resource route (creates all CRUD routes automatically)
// This is equivalent to the group above
// $router->resource('posts', 'ExampleController');

// Example: Public routes (no authentication required)
$router->group(['prefix' => 'public'], function($router) {
    
    $router->get('/status', function($request) {
        return \FCAutoposter\Routing\Response::success('Service is running', [
            'version' => FC_AUTOPOSTER_VERSION,
            'php_version' => PHP_VERSION,
            'wordpress_version' => get_bloginfo('version')
        ]);
    })->name('public.status');
    
    $router->get('/ping', function($request) {
        return \FCAutoposter\Routing\Response::json(['pong' => true]);
    })->name('public.ping');
});

// Example: Routes with authentication only
$router->group(['middleware' => 'auth'], function($router) {
    
    $router->get('/profile', function($request) {
        $user = wp_get_current_user();
        
        return \FCAutoposter\Routing\Response::success('User profile', [
            'id' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'display_name' => $user->display_name,
            'roles' => $user->roles
        ]);
    })->name('profile');
    
    $router->post('/logout', function($request) {
        wp_logout();
        return \FCAutoposter\Routing\Response::success('Logged out successfully');
    })->name('logout');
});

// Add your custom routes below this line
// Example:
// $router->get('/my-endpoint', 'MyController@myMethod')->name('my.route');
