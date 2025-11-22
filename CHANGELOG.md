# Changelog

All notable changes to FC Autoposter will be documented in this file.

## [1.1.0] - 2024-01-01

### Added - Laravel-Style PHP Routing System

#### Core Routing Components
- **Router Class** (`includes/Routing/Router.php`)
  - Support for GET, POST, PUT, PATCH, DELETE HTTP methods
  - Route grouping with shared attributes (prefix, middleware)
  - RESTful resource routing (automatic CRUD route generation)
  - Named routes for URL generation
  - Route parameter extraction and pattern matching

- **Route Class** (`includes/Routing/Route.php`)
  - Individual route definitions
  - Dynamic parameter extraction from URLs
  - Regex pattern matching for flexible routing
  - Middleware support per route

- **Request Class** (`includes/Routing/Request.php`)
  - Clean interface for accessing request data
  - Support for query parameters, body data, and route parameters
  - Header access and manipulation
  - Helper methods: `input()`, `query()`, `param()`, `only()`, `except()`
  - JSON request body parsing

- **Response Class** (`includes/Routing/Response.php`)
  - Standardized JSON response format
  - Helper methods for common responses:
    - `success()` - 200 OK responses
    - `created()` - 201 Created responses
    - `error()` - 400 Bad Request responses
    - `notFound()` - 404 Not Found responses
    - `unauthorized()` - 401 Unauthorized responses
    - `forbidden()` - 403 Forbidden responses
    - `validationError()` - 422 Unprocessable Entity responses
  - Custom headers and status codes support

#### MVC Architecture
- **Base Controller** (`includes/Controllers/Controller.php`)
  - Input validation helpers with multiple rules
  - Authorization helpers (`authorize()`, `userCan()`, `isLoggedIn()`)
  - WordPress user integration
  - Validation rules: required, string, numeric, email, min, max, in, url, array

- **Example Controller** (`includes/Controllers/ExampleController.php`)
  - Complete CRUD operations example
  - Demonstrates validation and authorization
  - Shows best practices for WordPress integration

#### Middleware System
- **Base Middleware** (`includes/Middleware/Middleware.php`)
  - Abstract class for all middleware
  - Pipeline pattern support

- **AuthMiddleware** (`includes/Middleware/AuthMiddleware.php`)
  - Ensures user is logged in
  - Returns 401 if not authenticated

- **AdminMiddleware** (`includes/Middleware/AdminMiddleware.php`)
  - Requires `manage_options` capability
  - Returns 403 if user lacks permissions

- **NonceMiddleware** (`includes/Middleware/NonceMiddleware.php`)
  - WordPress nonce verification
  - CSRF protection for state-changing operations

#### Integration
- **RouteServiceProvider** (`includes/Routing/RouteServiceProvider.php`)
  - Integrates routing system with WordPress REST API
  - Automatically registers routes under `fc-autoposter/v1` namespace
  - Handles middleware pipeline execution
  - Converts custom Request/Response to WordPress format

- **PSR-4 Autoloader** (`includes/autoloader.php`)
  - Automatic class loading
  - Supports namespaced classes
  - No manual requires needed

#### Configuration
- **API Routes File** (`routes/api.php`)
  - Centralized route definitions
  - Example routes included:
    - Hello world endpoint
    - Test endpoint with timestamp
    - Parameterized routes
    - Protected routes with middleware
    - RESTful resource examples
    - Public status endpoints
    - User profile endpoints

#### Documentation
- **ROUTING.md** - Complete routing system documentation
  - All features explained in detail
  - API reference for all classes
  - Middleware creation guide
  - Testing and troubleshooting section

- **EXAMPLE-USAGE.md** - Practical examples
  - Building a complete Posts API
  - User management API
  - File upload API
  - Vue.js integration examples
  - Production tips and best practices

- **QUICKSTART-ROUTING.md** - Quick start guide
  - Get started in 5 minutes
  - Common patterns and recipes
  - Validation rules reference
  - Response types reference

- **Updated README.md**
  - Added routing system to features list
  - Updated plugin structure diagram
  - Added quick API usage example
  - Link to routing documentation

### Changed
- Modified `fc-autoposter.php` to bootstrap routing system
- Added autoloader initialization on plugin load
- Registered route service provider with WordPress

### Technical Details
- **Namespace**: `FCAutoposter\`
- **API Namespace**: `fc-autoposter/v1`
- **PHP Version**: 7.4+
- **WordPress Version**: 5.0+
- **PSR Standards**: PSR-4 autoloading

### Routes Available
All routes accessible at: `http://your-site.com/wp-json/fc-autoposter/v1/{route}`

Example routes included:
- `GET /hello` - Simple hello world
- `GET /test` - Test endpoint with data
- `GET /greet/{name}` - Parameterized route
- `GET /posts` - List posts (protected)
- `POST /posts` - Create post (protected)
- `GET /posts/{id}` - Get single post (protected)
- `PUT /posts/{id}` - Update post (protected)
- `DELETE /posts/{id}` - Delete post (protected)
- `GET /public/status` - Public status check
- `GET /profile` - User profile (auth required)

## [1.0.0] - Previous Release

### Initial Features
- WordPress plugin with admin panel
- Vue 3 for reactive UI components
- Vite for fast development and optimized builds
- Hot Module Replacement (HMR) in development mode
