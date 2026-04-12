<?php

use Wibiesana\Padi\Core\Router;

$router = new Router();

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here you can register API routes for your application. These routes are
| loaded by the Router class and automatically support middleware,
| automatic response formatting, and exception handling.
|
*/

// ============================================================================
// SITE & HEALTH CHECK ROUTES
// ============================================================================
// Public routes for site information and health monitoring

$router->get('/', 'SiteController@index');
$router->get('/health', 'SiteController@health');

// Site information routes
$router->group(['prefix' => 'site'], function ($router) {
    $router->get('/info', 'SiteController@info');
    $router->get('/endpoints', 'SiteController@endpoints');
});

// File upload routes
$router->post('/file/upload-question-image', 'FileController@uploadQuestionImage')->middleware('AuthMiddleware');
$router->get('/file/show/{path*}', 'FileController@serve');

// ============================================================================
// AUTHENTICATION ROUTES (PUBLIC)
// ============================================================================
// User registration, login, password reset, and token management

$router->group(['prefix' => 'auth'], function ($router) {
    // User registration & login (rate limited)
    $router->post('/register', 'AuthController@register')->middleware('RateLimitMiddleware');
    $router->post('/login', 'AuthController@login')->middleware('RateLimitMiddleware');

    // Token management
    $router->post('/refresh', 'AuthController@refresh');
    $router->post('/logout', 'AuthController@logout');

    // Password recovery (rate limited)
    $router->post('/forgot-password', 'AuthController@forgotPassword')->middleware('RateLimitMiddleware');
    $router->post('/reset-password', 'AuthController@resetPassword')->middleware('RateLimitMiddleware');

    // Get current user info (protected)
    $router->get('/me', 'AuthController@me')->middleware('AuthMiddleware');

    // Change password (protected)
    $router->post('/change-password', 'AuthController@changePassword')->middleware('AuthMiddleware');
});

// ============================================================================
// DASHBOARD ROUTES (PROTECTED)
// ============================================================================
// Dashboard statistics and analytics
$router->group(['prefix' => 'dashboard', 'middleware' => ['AuthMiddleware']], function ($router) {
    $router->get('/attendance-stats', 'DashboardController@getAttendanceStats');
});

// Profile management routes
$router->group(['prefix' => 'user', 'middleware' => ['AuthMiddleware']], function ($router) {
    $router->post('/update-profile', 'AuthController@updateProfile');
});



// ============================================================================
// USERS MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for users - requires authentication
$router->group(['prefix' => 'users', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->get('/', 'UserController@index');           // List users with pagination
    $router->get('/all', 'UserController@all');         // Get all users
    $router->get('/{id}', 'UserController@show');
    $router->post('/', 'UserController@store');         // Create new user
    $router->put('/{id}', 'UserController@update');     // Update user
    $router->delete('/{id}', 'UserController@destroy'); // Delete user
});




// ============================================================================
// ACTIVITY LOG ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for activity-log data
$router->group(['prefix' => 'activity-log'], function ($router) {
    // List & view operations
    $router->get('/', 'ActivityLogController@index');           // List activity-log with pagination
    $router->get('/all', 'ActivityLogController@all');         // Get all activity-log
    $router->get('/{id}', 'ActivityLogController@show');       // Get specific item
});

// ============================================================================
// ACTIVITY LOG MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for activity-log - requires authentication
$router->group(['prefix' => 'activity-log', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ActivityLogController@store');         // Create new item
    $router->put('/{id}', 'ActivityLogController@update');     // Update item
    $router->delete('/{id}', 'ActivityLogController@destroy'); // Delete item
});



// ============================================================================
// ARTICLE ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for article data
$router->group(['prefix' => 'article'], function ($router) {
    // List & view operations
    $router->get('/', 'ArticleController@index');           // List article with pagination
    $router->get('/all', 'ArticleController@all');         // Get all article
    $router->get('/{id}', 'ArticleController@show');       // Get specific item
});

// ============================================================================
// ARTICLE MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for article - requires authentication
$router->group(['prefix' => 'article', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ArticleController@store');         // Create new item
    $router->put('/{id}', 'ArticleController@update');     // Update item
    $router->delete('/{id}', 'ArticleController@destroy'); // Delete item
});



// ============================================================================
// ARTICLE CLASS ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for article-class data
$router->group(['prefix' => 'article-class'], function ($router) {
    // List & view operations
    $router->get('/', 'ArticleClassController@index');           // List article-class with pagination
    $router->get('/all', 'ArticleClassController@all');         // Get all article-class
    $router->get('/{id}', 'ArticleClassController@show');       // Get specific item
});

// ============================================================================
// ARTICLE CLASS MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for article-class - requires authentication
$router->group(['prefix' => 'article-class', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ArticleClassController@store');         // Create new item
    $router->put('/{id}', 'ArticleClassController@update');     // Update item
    $router->delete('/{id}', 'ArticleClassController@destroy'); // Delete item
});



// ============================================================================
// ARTICLE COMMENT ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for article-comment data
$router->group(['prefix' => 'article-comment'], function ($router) {
    // List & view operations
    $router->get('/', 'ArticleCommentController@index');           // List article-comment with pagination
    $router->get('/all', 'ArticleCommentController@all');         // Get all article-comment
    $router->get('/{id}', 'ArticleCommentController@show');       // Get specific item
});

// ============================================================================
// ARTICLE COMMENT MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for article-comment - requires authentication
$router->group(['prefix' => 'article-comment', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ArticleCommentController@store');         // Create new item
    $router->put('/{id}', 'ArticleCommentController@update');     // Update item
    $router->delete('/{id}', 'ArticleCommentController@destroy'); // Delete item
});



// ============================================================================
// ARTICLE COMMENT LIKE ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for article-comment-like data
$router->group(['prefix' => 'article-comment-like'], function ($router) {
    // List & view operations
    $router->get('/', 'ArticleCommentLikeController@index');           // List article-comment-like with pagination
    $router->get('/all', 'ArticleCommentLikeController@all');         // Get all article-comment-like
    $router->get('/{id}', 'ArticleCommentLikeController@show');       // Get specific item
});

// ============================================================================
// ARTICLE COMMENT LIKE MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for article-comment-like - requires authentication
$router->group(['prefix' => 'article-comment-like', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ArticleCommentLikeController@store');         // Create new item
    $router->put('/{id}', 'ArticleCommentLikeController@update');     // Update item
    $router->delete('/{id}', 'ArticleCommentLikeController@destroy'); // Delete item
});



// ============================================================================
// ARTICLE LIKE ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for article-like data
$router->group(['prefix' => 'article-like'], function ($router) {
    // List & view operations
    $router->get('/', 'ArticleLikeController@index');           // List article-like with pagination
    $router->get('/all', 'ArticleLikeController@all');         // Get all article-like
    $router->get('/{id}', 'ArticleLikeController@show');       // Get specific item
});

// ============================================================================
// ARTICLE LIKE MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for article-like - requires authentication
$router->group(['prefix' => 'article-like', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ArticleLikeController@store');         // Create new item
    $router->put('/{id}', 'ArticleLikeController@update');     // Update item
    $router->delete('/{id}', 'ArticleLikeController@destroy'); // Delete item
});



// ============================================================================
// ASC IMPORT LOG ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for asc-import-log data
$router->group(['prefix' => 'asc-import-log'], function ($router) {
    // List & view operations
    $router->get('/', 'AscImportLogController@index');           // List asc-import-log with pagination
    $router->get('/all', 'AscImportLogController@all');         // Get all asc-import-log
    $router->get('/{id}', 'AscImportLogController@show');       // Get specific item
});

// ============================================================================
// ASC IMPORT LOG MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for asc-import-log - requires authentication
$router->group(['prefix' => 'asc-import-log', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'AscImportLogController@store');         // Create new item
    $router->put('/{id}', 'AscImportLogController@update');     // Update item
    $router->delete('/{id}', 'AscImportLogController@destroy'); // Delete item
});



// ============================================================================
// ASC MAPPING ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for asc-mapping data
$router->group(['prefix' => 'asc-mapping'], function ($router) {
    // List & view operations
    $router->get('/', 'AscMappingController@index');           // List asc-mapping with pagination
    $router->get('/all', 'AscMappingController@all');         // Get all asc-mapping
    $router->get('/{id}', 'AscMappingController@show');       // Get specific item
});

// ============================================================================
// ASC MAPPING MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for asc-mapping - requires authentication
$router->group(['prefix' => 'asc-mapping', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'AscMappingController@store');         // Create new item
    $router->put('/{id}', 'AscMappingController@update');     // Update item
    $router->delete('/{id}', 'AscMappingController@destroy'); // Delete item
});



// ============================================================================
// DATA CENTER ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for helper data (options, selects, etc)
$router->group(['prefix' => 'data-center'], function ($router) {
    $router->get('/{type}', 'DataCenterController@index');
});

// ============================================================================
// ASSET ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for asset data
$router->group(['prefix' => 'asset'], function ($router) {
    // List & view operations
    $router->get('/', 'AssetController@index');           // List asset with pagination
    $router->get('/all', 'AssetController@all');         // Get all asset
    $router->get('/{id}', 'AssetController@show');       // Get specific item
});

// ============================================================================
// ASSET MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for asset - requires authentication
$router->group(['prefix' => 'asset', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'AssetController@store');         // Create new item
    $router->put('/{id}', 'AssetController@update');     // Update item
    $router->delete('/{id}', 'AssetController@destroy'); // Delete item
});



// ============================================================================
// ASSET BORROWING ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for asset-borrowing data
$router->group(['prefix' => 'asset-borrowing'], function ($router) {
    // List & view operations
    $router->get('/', 'AssetBorrowingController@index');           // List asset-borrowing with pagination
    $router->get('/all', 'AssetBorrowingController@all');         // Get all asset-borrowing
    $router->get('/{id}', 'AssetBorrowingController@show');       // Get specific item
});

// ============================================================================
// ASSET BORROWING MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for asset-borrowing - requires authentication
$router->group(['prefix' => 'asset-borrowing', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'AssetBorrowingController@store');         // Create new item
    $router->put('/{id}', 'AssetBorrowingController@update');     // Update item
    $router->delete('/{id}', 'AssetBorrowingController@destroy'); // Delete item
});



// ============================================================================
// ASSIGNMENT ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for assignment data
$router->group(['prefix' => 'assignment'], function ($router) {
    // List & view operations
    $router->get('/', 'AssignmentController@index');           // List assignment with pagination
    $router->get('/all', 'AssignmentController@all');         // Get all assignment
    $router->get('/{id}', 'AssignmentController@show');       // Get specific item
});

// ============================================================================
// ASSIGNMENT MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for assignment - requires authentication
$router->group(['prefix' => 'assignment', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'AssignmentController@store');         // Create new item
    $router->put('/{id}', 'AssignmentController@update');     // Update item
    $router->delete('/{id}', 'AssignmentController@destroy'); // Delete item
});



// ============================================================================
// ASSIGNMENT CLASS ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for assignment-class data
$router->group(['prefix' => 'assignment-class'], function ($router) {
    // List & view operations
    $router->get('/', 'AssignmentClassController@index');           // List assignment-class with pagination
    $router->get('/all', 'AssignmentClassController@all');         // Get all assignment-class
    $router->get('/{id}', 'AssignmentClassController@show');       // Get specific item
});

// ============================================================================
// ASSIGNMENT CLASS MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for assignment-class - requires authentication
$router->group(['prefix' => 'assignment-class', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'AssignmentClassController@store');         // Create new item
    $router->put('/{id}', 'AssignmentClassController@update');     // Update item
    $router->delete('/{id}', 'AssignmentClassController@destroy'); // Delete item
});



// ============================================================================
// ASSIGNMENT RESULT ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for assignment-result data
$router->group(['prefix' => 'assignment-result'], function ($router) {
    // List & view operations
    $router->get('/my-upload', 'AssignmentResultController@myUpload')->middleware('AuthMiddleware');
    $router->get('/teacher-class-summary', 'AssignmentResultController@teacherClassSummary')->middleware('AuthMiddleware');
    $router->get('/assignment/{id}', 'AssignmentResultController@submissionsByAssignment');
    $router->get('/', 'AssignmentResultController@index');           // List assignment-result with pagination
    $router->get('/all', 'AssignmentResultController@all');         // Get all assignment-result
    $router->get('/{id}', 'AssignmentResultController@show');       // Get specific item
});

// ============================================================================
// ASSIGNMENT RESULT MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for assignment-result - requires authentication
$router->group(['prefix' => 'assignment-result', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/update', 'AssignmentResultController@update');     // Special case for custom frontend update
    $router->put('/give-score/{id}', 'AssignmentResultController@giveScore');
    $router->post('/', 'AssignmentResultController@store');         // Create new item
    $router->put('/{id}', 'AssignmentResultController@update');     // Update item
    $router->delete('/{id}', 'AssignmentResultController@destroy'); // Delete item
});



// ============================================================================
// ATTENDANCE DAILY STUDENT ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for attendance-daily-student data
$router->group(['prefix' => 'attendance-daily-student'], function ($router) {
    // List & view operations
    $router->get('/', 'AttendanceDailyStudentController@index');           // List attendance-daily-student with pagination
    $router->get('/all', 'AttendanceDailyStudentController@all');         // Get all attendance-daily-student
    $router->get('/{id}', 'AttendanceDailyStudentController@show');       // Get specific item
});

// ============================================================================
// ATTENDANCE DAILY STUDENT MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for attendance-daily-student - requires authentication
$router->group(['prefix' => 'attendance-daily-student', 'middleware' => ['AuthMiddleware']], function ($router) {
    $router->post('/batch', 'AttendanceDailyStudentController@batchStore');
    // Modification operations
    $router->post('/', 'AttendanceDailyStudentController@store');         // Create new item
    $router->put('/{id}', 'AttendanceDailyStudentController@update');     // Update item
    $router->delete('/{id}', 'AttendanceDailyStudentController@destroy'); // Delete item
});

// ============================================================================
// ATTENDANCE DAILY TEACHER ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for attendance-daily-teacher data
$router->group(['prefix' => 'attendance-daily-teacher'], function ($router) {
    // List & view operations
    $router->get('/', 'AttendanceDailyTeacherController@index');           // List attendance-daily-teacher with pagination
    $router->get('/all', 'AttendanceDailyTeacherController@all');         // Get all attendance-daily-teacher
    $router->get('/{id}', 'AttendanceDailyTeacherController@show');       // Get specific item
});

// ============================================================================
// ATTENDANCE DAILY TEACHER MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for attendance-daily-teacher - requires authentication
$router->group(['prefix' => 'attendance-daily-teacher', 'middleware' => ['AuthMiddleware']], function ($router) {
    $router->post('/batch', 'AttendanceDailyTeacherController@batchStore');
    // Modification operations
    $router->post('/', 'AttendanceDailyTeacherController@store');         // Create new item
    $router->put('/{id}', 'AttendanceDailyTeacherController@update');     // Update item
    $router->delete('/{id}', 'AttendanceDailyTeacherController@destroy'); // Delete item
});



// ============================================================================
// ATTENDANCE STUDENT ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for attendance-student data
$router->group(['prefix' => 'attendance-student'], function ($router) {
    // List & view operations
    $router->get('/', 'AttendanceStudentController@index');           // List attendance-student with pagination
    $router->get('/all', 'AttendanceStudentController@all');         // Get all attendance-student
    $router->get('/summary', 'AttendanceStudentController@getSummary'); // Get attendance summary
    $router->get('/session/{id}', 'AttendanceStudentController@getBySession'); // Get by lesson session
    $router->get('/{id}', 'AttendanceStudentController@show');       // Get specific item
});

// ============================================================================
// ATTENDANCE STUDENT MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for attendance-student - requires authentication
$router->group(['prefix' => 'attendance-student', 'middleware' => ['AuthMiddleware']], function ($router) {
    $router->post('/batch', 'AttendanceStudentController@batchStore');
    // Modification operations
    $router->post('/', 'AttendanceStudentController@store');         // Create new item
    $router->put('/{id}', 'AttendanceStudentController@update');     // Update item
    $router->delete('/{id}', 'AttendanceStudentController@destroy'); // Delete item
});



// ============================================================================
// ATTENDANCE TEACHER ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for attendance-teacher data
$router->group(['prefix' => 'attendance-teacher'], function ($router) {
    // List & view operations
    $router->get('/', 'AttendanceTeacherController@index');           // List attendance-teacher with pagination
    $router->get('/all', 'AttendanceTeacherController@all');         // Get all attendance-teacher
    $router->get('/{id}', 'AttendanceTeacherController@show');       // Get specific item
});

// ============================================================================
// ATTENDANCE TEACHER MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for attendance-teacher - requires authentication
$router->group(['prefix' => 'attendance-teacher', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'AttendanceTeacherController@store');         // Create new item
    $router->put('/{id}', 'AttendanceTeacherController@update');     // Update item
    $router->delete('/{id}', 'AttendanceTeacherController@destroy'); // Delete item
});



// ============================================================================
// BILLING ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for billing data
$router->group(['prefix' => 'billing'], function ($router) {
    // List & view operations
    $router->get('/', 'BillingController@index');           // List billing with pagination
    $router->get('/all', 'BillingController@all');         // Get all billing
    $router->get('/{id}', 'BillingController@show');       // Get specific item
});

// ============================================================================
// BILLING MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for billing - requires authentication
$router->group(['prefix' => 'billing', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'BillingController@store');         // Create new item
    $router->put('/{id}', 'BillingController@update');     // Update item
    $router->delete('/{id}', 'BillingController@destroy'); // Delete item
});



// ============================================================================
// BOOK ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for book data
$router->group(['prefix' => 'book'], function ($router) {
    // List & view operations
    $router->get('/', 'BookController@index');           // List book with pagination
    $router->get('/all', 'BookController@all');         // Get all book
    $router->get('/{id}', 'BookController@show');       // Get specific item
});

// ============================================================================
// BOOK MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for book - requires authentication
$router->group(['prefix' => 'book', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'BookController@store');         // Create new item
    $router->put('/{id}', 'BookController@update');     // Update item
    $router->delete('/{id}', 'BookController@destroy'); // Delete item
});



// ============================================================================
// BOOK BORROWING ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for book-borrowing data
$router->group(['prefix' => 'book-borrowing'], function ($router) {
    // List & view operations
    $router->get('/', 'BookBorrowingController@index');           // List book-borrowing with pagination
    $router->get('/all', 'BookBorrowingController@all');         // Get all book-borrowing
    $router->get('/{id}', 'BookBorrowingController@show');       // Get specific item
});

// ============================================================================
// BOOK BORROWING MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for book-borrowing - requires authentication
$router->group(['prefix' => 'book-borrowing', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'BookBorrowingController@store');         // Create new item
    $router->put('/{id}', 'BookBorrowingController@update');     // Update item
    $router->delete('/{id}', 'BookBorrowingController@destroy'); // Delete item
});



// ============================================================================
// CLASS SEMESTER ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for class-semester data
$router->group(['prefix' => 'class-semester'], function ($router) {
    // List & view operations
    $router->get('/', 'ClassSemesterController@index');           // List class-semester with pagination
    $router->get('/all', 'ClassSemesterController@all');         // Get all class-semester
    $router->get('/{id}', 'ClassSemesterController@show');       // Get specific item
});

// ============================================================================
// CLASS SEMESTER MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for class-semester - requires authentication
$router->group(['prefix' => 'class-semester', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ClassSemesterController@store');         // Create new item
    $router->put('/{id}', 'ClassSemesterController@update');     // Update item
    $router->delete('/{id}', 'ClassSemesterController@destroy'); // Delete item
});



// ============================================================================
// CLASSROOM ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for classroom data
$router->group(['prefix' => 'classroom'], function ($router) {
    // List & view operations
    $router->get('/teacher-classes', 'ClassroomController@teacherClasses');
    $router->get('/result-summary', 'ClassroomController@resultSummary')->middleware('AuthMiddleware');
    $router->get('/', 'ClassroomController@index');           // List classroom with pagination
    $router->get('/all', 'ClassroomController@all');         // Get all classroom
    $router->get('/{id}', 'ClassroomController@show');       // Get specific item
});

// ============================================================================
// CLASSROOM MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for classroom - requires authentication
$router->group(['prefix' => 'classroom', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ClassroomController@store');         // Create new item
    $router->put('/{id}', 'ClassroomController@update');     // Update item
    $router->delete('/{id}', 'ClassroomController@destroy'); // Delete item
});



// ============================================================================
// COUNSELING SESSION ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for counseling-session data
$router->group(['prefix' => 'counseling-session'], function ($router) {
    // List & view operations
    $router->get('/', 'CounselingSessionController@index');           // List counseling-session with pagination
    $router->get('/all', 'CounselingSessionController@all');         // Get all counseling-session
    $router->get('/{id}', 'CounselingSessionController@show');       // Get specific item
});

// ============================================================================
// COUNSELING SESSION MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for counseling-session - requires authentication
$router->group(['prefix' => 'counseling-session', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'CounselingSessionController@store');         // Create new item
    $router->put('/{id}', 'CounselingSessionController@update');     // Update item
    $router->delete('/{id}', 'CounselingSessionController@destroy'); // Delete item
});



// ============================================================================
// DEPARTMENT ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for department data
$router->group(['prefix' => 'department'], function ($router) {
    // List & view operations
    $router->get('/', 'DepartmentController@index');           // List department with pagination
    $router->get('/all', 'DepartmentController@all');         // Get all department
    $router->get('/{id}', 'DepartmentController@show');       // Get specific item
});

// ============================================================================
// DEPARTMENT MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for department - requires authentication
$router->group(['prefix' => 'department', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'DepartmentController@store');         // Create new item
    $router->put('/{id}', 'DepartmentController@update');     // Update item
    $router->delete('/{id}', 'DepartmentController@destroy'); // Delete item
});



// ============================================================================
// DEPARTMENT SEMESTER ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for department-semester data
$router->group(['prefix' => 'department-semester'], function ($router) {
    // List & view operations
    $router->get('/', 'DepartmentSemesterController@index');           // List department-semester with pagination
    $router->get('/all', 'DepartmentSemesterController@all');         // Get all department-semester
    $router->get('/{id}', 'DepartmentSemesterController@show');       // Get specific item
});

// ============================================================================
// DEPARTMENT SEMESTER MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for department-semester - requires authentication
$router->group(['prefix' => 'department-semester', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'DepartmentSemesterController@store');         // Create new item
    $router->put('/{id}', 'DepartmentSemesterController@update');     // Update item
    $router->delete('/{id}', 'DepartmentSemesterController@destroy'); // Delete item
});



// ============================================================================
// EXAM EVENT ROUTES (PROTECTED)
// ============================================================================
$router->group(['prefix' => 'exam-event', 'middleware' => ['AuthMiddleware']], function ($router) {
    $router->get('/', 'ExamEventController@index');
    $router->get('/all', 'ExamEventController@all');
    $router->post('/', 'ExamEventController@store');
    $router->get('/{id}', 'ExamEventController@show');
    $router->get('/{id}/student-cards', 'ExamEventController@getStudentCards');
    $router->get('/{id}/monitoring', 'ExamEventController@getMonitoring');
    $router->post('/unlock/{resultId}', 'ExamEventController@unlockStudent');
    $router->post('/lock/{resultId}', 'ExamEventController@lockStudent');
    $router->post('/finish/{resultId}', 'ExamEventController@finishStudent');
    $router->post('/add-time/{resultId}', 'ExamEventController@addTime');
    $router->post('/resume/{resultId}', 'ExamEventController@resumeStudent');
    $router->post('/batch-action', 'ExamEventController@batchAction');
    $router->put('/{id}', 'ExamEventController@update');
    $router->delete('/{id}', 'ExamEventController@destroy');
});

// ============================================================================
// EXAM ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
$router->group(['prefix' => 'exam'], function ($router) {
    $router->get('/', 'ExamController@index');
    $router->get('/all', 'ExamController@all');
    $router->get('/card/student', 'ExamController@getStudentCardData');
    $router->get('/my-appointments', 'ExamController@getMyAppointments');
    $router->get('/available-today', 'ExamController@getAvailableToday');
    $router->get('/student-detail/{id}', 'ExamController@getStudentExamDetail');
    $router->get('/{id}', 'ExamController@show');
});

// ============================================================================
// EXAM MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
$router->group(['prefix' => 'exam', 'middleware' => ['AuthMiddleware']], function ($router) {
    $router->post('/start', 'ExamController@startExam');
    $router->post('/save-answer', 'ExamController@saveAnswer');
    $router->post('/finish', 'ExamController@finishExam');
    $router->post('/lock', 'ExamController@lockExam');
    $router->post('/', 'ExamController@store');
    $router->put('/{id}', 'ExamController@update');
    $router->delete('/{id}', 'ExamController@destroy');
});

// ============================================================================
// EXAM REPORT ROUTES (BERITA ACARA)
// ============================================================================
$router->group(['prefix' => 'exam-report', 'middleware' => ['AuthMiddleware']], function ($router) {
    $router->get('/', 'ExamReportController@index');
    $router->post('/', 'ExamReportController@store');
    $router->get('/detail', 'ExamReportController@getByExamClass');
});



// ============================================================================
// EXAM CLASS ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for exam-class data
$router->group(['prefix' => 'exam-class'], function ($router) {
    // List & view operations
    $router->get('/', 'ExamClassController@index');           // List exam-class with pagination
    $router->get('/all', 'ExamClassController@all');         // Get all exam-class
    $router->get('/{id}', 'ExamClassController@show');       // Get specific item
});

// ============================================================================
// EXAM CLASS MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for exam-class - requires authentication
$router->group(['prefix' => 'exam-class', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ExamClassController@store');         // Create new item
    $router->put('/{id}', 'ExamClassController@update');     // Update item
    $router->delete('/{id}', 'ExamClassController@destroy'); // Delete item
});



// ============================================================================
// EXAM CLASS USER ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for exam-class-user data
$router->group(['prefix' => 'exam-class-user'], function ($router) {
    // List & view operations
    $router->get('/', 'ExamClassUserController@index');           // List exam-class-user with pagination
    $router->get('/all', 'ExamClassUserController@all');         // Get all exam-class-user
    $router->get('/{id}', 'ExamClassUserController@show');       // Get specific item
});

// ============================================================================
// EXAM CLASS USER MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for exam-class-user - requires authentication
$router->group(['prefix' => 'exam-class-user', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ExamClassUserController@store');         // Create new item
    $router->put('/{id}', 'ExamClassUserController@update');     // Update item
    $router->delete('/{id}', 'ExamClassUserController@destroy'); // Delete item
});

// ============================================================================
// EXAM RESULT ROUTES
// ============================================================================
$router->group(['prefix' => 'exam-result', 'middleware' => ['AuthMiddleware']], function ($router) {
    $router->get('/my-result', 'ExamResultController@myResults');
    $router->get('/teacher-class-summary', 'ExamResultController@teacherClassSummary');
    $router->get('/', 'ExamResultController@index');
    $router->get('/all', 'ExamResultController@all');
    $router->get('/{id}', 'ExamResultController@show');
    $router->post('/', 'ExamResultController@store');
    $router->put('/{id}', 'ExamResultController@update');
    $router->delete('/{id}', 'ExamResultController@destroy');
});



// ============================================================================
// EXAM RESULT ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for exam-result data
$router->group(['prefix' => 'exam-result'], function ($router) {
    // List & view operations
    $router->get('/', 'ExamResultController@index');           // List exam-result with pagination
    $router->get('/all', 'ExamResultController@all');         // Get all exam-result
    $router->get('/{id}', 'ExamResultController@show');       // Get specific item
});

// ============================================================================
// EXAM RESULT MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for exam-result - requires authentication
$router->group(['prefix' => 'exam-result', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ExamResultController@store');         // Create new item
    $router->put('/{id}', 'ExamResultController@update');     // Update item
    $router->delete('/{id}', 'ExamResultController@destroy'); // Delete item
});



// ============================================================================
// EXAM RESULT ANSWER ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for exam-result-answer data
$router->group(['prefix' => 'exam-result-answer'], function ($router) {
    // List & view operations
    $router->get('/', 'ExamResultAnswerController@index');           // List exam-result-answer with pagination
    $router->get('/all', 'ExamResultAnswerController@all');         // Get all exam-result-answer
    $router->get('/{id}', 'ExamResultAnswerController@show');       // Get specific item
});

// ============================================================================
// EXAM RESULT ANSWER MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for exam-result-answer - requires authentication
$router->group(['prefix' => 'exam-result-answer', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ExamResultAnswerController@store');         // Create new item
    $router->put('/{id}', 'ExamResultAnswerController@update');     // Update item
    $router->delete('/{id}', 'ExamResultAnswerController@destroy'); // Delete item
});



// ============================================================================
// EXERCISE ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for exercise data
$router->group(['prefix' => 'exercise'], function ($router) {
    // List & view operations
    $router->get('/', 'ExerciseController@index');           // List exercise with pagination
    $router->get('/all', 'ExerciseController@all');         // Get all exercise
    $router->get('/{id}', 'ExerciseController@show');       // Get specific item
});

// ============================================================================
// EXERCISE MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for exercise - requires authentication
$router->group(['prefix' => 'exercise', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ExerciseController@store');         // Create new item
    $router->put('/{id}', 'ExerciseController@update');     // Update item
    $router->delete('/{id}', 'ExerciseController@destroy'); // Delete item
});



// ============================================================================
// EXERCISE COMMENT ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for exercise-comment data
$router->group(['prefix' => 'exercise-comment'], function ($router) {
    // List & view operations
    $router->get('/', 'ExerciseCommentController@index');           // List exercise-comment with pagination
    $router->get('/all', 'ExerciseCommentController@all');         // Get all exercise-comment
    $router->get('/{id}', 'ExerciseCommentController@show');       // Get specific item
});

// ============================================================================
// EXERCISE COMMENT MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for exercise-comment - requires authentication
$router->group(['prefix' => 'exercise-comment', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ExerciseCommentController@store');         // Create new item
    $router->put('/{id}', 'ExerciseCommentController@update');     // Update item
    $router->delete('/{id}', 'ExerciseCommentController@destroy'); // Delete item
});



// ============================================================================
// EXERCISE GROUP ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for exercise-group data
$router->group(['prefix' => 'exercise-group'], function ($router) {
    // List & view operations
    $router->get('/', 'ExerciseGroupController@index');           // List exercise-group with pagination
    $router->get('/all', 'ExerciseGroupController@all');         // Get all exercise-group
    $router->get('/{id}', 'ExerciseGroupController@show');       // Get specific item
});

// ============================================================================
// EXERCISE GROUP MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for exercise-group - requires authentication
$router->group(['prefix' => 'exercise-group', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ExerciseGroupController@store');         // Create new item
    $router->put('/{id}', 'ExerciseGroupController@update');     // Update item
    $router->delete('/{id}', 'ExerciseGroupController@destroy'); // Delete item
});



// ============================================================================
// FINANCIAL TRANSACTION ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for financial-transaction data
$router->group(['prefix' => 'financial-transaction'], function ($router) {
    // List & view operations
    $router->get('/', 'FinancialTransactionController@index');           // List financial-transaction with pagination
    $router->get('/all', 'FinancialTransactionController@all');         // Get all financial-transaction
    $router->get('/{id}', 'FinancialTransactionController@show');       // Get specific item
});

// ============================================================================
// FINANCIAL TRANSACTION MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for financial-transaction - requires authentication
$router->group(['prefix' => 'financial-transaction', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'FinancialTransactionController@store');         // Create new item
    $router->put('/{id}', 'FinancialTransactionController@update');     // Update item
    $router->delete('/{id}', 'FinancialTransactionController@destroy'); // Delete item
});



// ============================================================================
// GRADE LEVEL ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for grade-level data
$router->group(['prefix' => 'grade-level'], function ($router) {
    // List & view operations
    $router->get('/', 'GradeLevelController@index');           // List grade-level with pagination
    $router->get('/all', 'GradeLevelController@all');         // Get all grade-level
    $router->get('/{id}', 'GradeLevelController@show');       // Get specific item
});

// ============================================================================
// GRADE LEVEL MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for grade-level - requires authentication
$router->group(['prefix' => 'grade-level', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'GradeLevelController@store');         // Create new item
    $router->put('/{id}', 'GradeLevelController@update');     // Update item
    $router->delete('/{id}', 'GradeLevelController@destroy'); // Delete item
});



// ============================================================================
// LESSON SCORE STUDENT ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for lesson-score-student data
$router->group(['prefix' => 'lesson-score-student'], function ($router) {
    // List & view operations
    $router->get('/', 'LessonScoreStudentController@index');           // List lesson-score-student with pagination
    $router->get('/all', 'LessonScoreStudentController@all');         // Get all lesson-score-student
    $router->get('/{id}', 'LessonScoreStudentController@show');       // Get specific item
});

// ============================================================================
// LESSON SCORE STUDENT MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for lesson-score-student - requires authentication
$router->group(['prefix' => 'lesson-score-student', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'LessonScoreStudentController@store');         // Create new item
    $router->put('/{id}', 'LessonScoreStudentController@update');     // Update item
    $router->delete('/{id}', 'LessonScoreStudentController@destroy'); // Delete item
});



// ============================================================================
// LESSON SESSION ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for lesson-session data
$router->group(['prefix' => 'lesson-session'], function ($router) {
    // List & view operations
    $router->get('/teaching-schedule', 'LessonSessionController@teachingSchedule'); // New custom route must come BEFORE /{id} if id is dynamic, though here it's specific
    $router->get('/', 'LessonSessionController@index');           // List lesson-session with pagination
    $router->get('/all', 'LessonSessionController@all');         // Get all lesson-session
    $router->get('/{id}', 'LessonSessionController@show');       // Get specific item
});

// ============================================================================
// LESSON SESSION MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for lesson-session - requires authentication
$router->group(['prefix' => 'lesson-session', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'LessonSessionController@store');         // Create new item
    $router->put('/{id}', 'LessonSessionController@update');     // Update item
    $router->delete('/{id}', 'LessonSessionController@destroy'); // Delete item
});



// ============================================================================
// MIGRATION ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for migration data
$router->group(['prefix' => 'migration'], function ($router) {
    // List & view operations
    $router->get('/', 'MigrationController@index');           // List migration with pagination
    $router->get('/all', 'MigrationController@all');         // Get all migration
    $router->get('/{id}', 'MigrationController@show');       // Get specific item
});

// ============================================================================
// MIGRATION MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for migration - requires authentication
$router->group(['prefix' => 'migration', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'MigrationController@store');         // Create new item
    $router->put('/{id}', 'MigrationController@update');     // Update item
    $router->delete('/{id}', 'MigrationController@destroy'); // Delete item
});



// ============================================================================
// NOTIFICATION ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for notification data
$router->group(['prefix' => 'notification'], function ($router) {
    // List & view operations
    $router->get('/', 'NotificationController@index');           // List notification with pagination
    $router->get('/all', 'NotificationController@all');         // Get all notification
    $router->get('/{id}', 'NotificationController@show');       // Get specific item
});

// ============================================================================
// NOTIFICATION MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for notification - requires authentication
$router->group(['prefix' => 'notification', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'NotificationController@store');         // Create new item
    $router->put('/{id}', 'NotificationController@update');     // Update item
    $router->delete('/{id}', 'NotificationController@destroy'); // Delete item
});



// ============================================================================
// PAYMENT ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for payment data
$router->group(['prefix' => 'payment'], function ($router) {
    // List & view operations
    $router->get('/', 'PaymentController@index');           // List payment with pagination
    $router->get('/all', 'PaymentController@all');         // Get all payment
    $router->get('/{id}', 'PaymentController@show');       // Get specific item
});

// ============================================================================
// PAYMENT MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for payment - requires authentication
$router->group(['prefix' => 'payment', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'PaymentController@store');         // Create new item
    $router->put('/{id}', 'PaymentController@update');     // Update item
    $router->delete('/{id}', 'PaymentController@destroy'); // Delete item
});



// ============================================================================
// PERIOD ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for period data
$router->group(['prefix' => 'period'], function ($router) {
    // List & view operations
    $router->get('/', 'PeriodController@index');           // List period with pagination
    $router->get('/all', 'PeriodController@all');         // Get all period
    $router->get('/{id}', 'PeriodController@show');       // Get specific item
});

// ============================================================================
// PERIOD MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for period - requires authentication
$router->group(['prefix' => 'period', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'PeriodController@store');         // Create new item
    $router->put('/{id}', 'PeriodController@update');     // Update item
    $router->delete('/{id}', 'PeriodController@destroy'); // Delete item
});



// ============================================================================
// QUESTION ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
$router->group(['prefix' => 'question'], function ($router) {
    $router->get('/', 'QuestionController@index');
    $router->get('/all', 'QuestionController@all');
    $router->get('/{id}', 'QuestionController@show');
});

// ============================================================================
// QUESTION MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
$router->group(['prefix' => 'question', 'middleware' => ['AuthMiddleware']], function ($router) {
    $router->post('/batch', 'QuestionController@batchStore');
    $router->post('/', 'QuestionController@store');
    $router->put('/{id}', 'QuestionController@update');
    $router->delete('/{id}', 'QuestionController@destroy');
});



// ============================================================================
// QUESTION ANSWER ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for question-answer data
$router->group(['prefix' => 'question-answer'], function ($router) {
    // List & view operations
    $router->get('/', 'QuestionAnswerController@index');           // List question-answer with pagination
    $router->get('/all', 'QuestionAnswerController@all');         // Get all question-answer
    $router->get('/{id}', 'QuestionAnswerController@show');       // Get specific item
});

// ============================================================================
// QUESTION ANSWER MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for question-answer - requires authentication
$router->group(['prefix' => 'question-answer', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'QuestionAnswerController@store');         // Create new item
    $router->put('/{id}', 'QuestionAnswerController@update');     // Update item
    $router->delete('/{id}', 'QuestionAnswerController@destroy'); // Delete item
});



// ============================================================================
// QUESTION BANK ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
$router->group(['prefix' => 'question-bank'], function ($router) {
    $router->get('/', 'QuestionBankController@index');
    $router->get('/all', 'QuestionBankController@all');
    $router->get('/{id}', 'QuestionBankController@show');
});

// ============================================================================
// QUESTION BANK MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
$router->group(['prefix' => 'question-bank', 'middleware' => ['AuthMiddleware']], function ($router) {
    $router->post('/', 'QuestionBankController@store');
    $router->put('/{id}', 'QuestionBankController@update');
    $router->delete('/{id}', 'QuestionBankController@destroy');
});



// ============================================================================
// ROLE ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for role data
$router->group(['prefix' => 'role'], function ($router) {
    // List & view operations
    $router->get('/', 'RoleController@index');           // List role with pagination
    $router->get('/all', 'RoleController@all');         // Get all role
    $router->get('/{id}', 'RoleController@show');       // Get specific item
});

// ============================================================================
// ROLE MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for role - requires authentication
$router->group(['prefix' => 'role', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'RoleController@store');         // Create new item
    $router->put('/{id}', 'RoleController@update');     // Update item
    $router->delete('/{id}', 'RoleController@destroy'); // Delete item
});



// ============================================================================
// SCHOOL YEAR ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for school-year data
$router->group(['prefix' => 'school-year'], function ($router) {
    // List & view operations
    $router->get('/', 'SchoolYearController@index');           // List school-year with pagination
    $router->get('/all', 'SchoolYearController@all');         // Get all school-year
    $router->get('/{id}', 'SchoolYearController@show');       // Get specific item
});

// ============================================================================
// SCHOOL YEAR MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for school-year - requires authentication
$router->group(['prefix' => 'school-year', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->put('/set-active/{id}', 'SchoolYearController@setActive');
    $router->post('/', 'SchoolYearController@store');         // Create new item
    $router->put('/{id}', 'SchoolYearController@update');     // Update item
    $router->delete('/{id}', 'SchoolYearController@destroy'); // Delete item
});



// ============================================================================
// SEMESTER ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for semester data
$router->group(['prefix' => 'semester'], function ($router) {
    // List & view operations
    $router->get('/', 'SemesterController@index');           // List semester with pagination
    $router->get('/all', 'SemesterController@all');         // Get all semester
    $router->get('/{id}', 'SemesterController@show');       // Get specific item
});

// ============================================================================
// SEMESTER MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for semester - requires authentication
$router->group(['prefix' => 'semester', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->put('/set-active/{id}', 'SemesterController@setActive');
    $router->post('/', 'SemesterController@store');         // Create new item
    $router->put('/{id}', 'SemesterController@update');     // Update item
    $router->delete('/{id}', 'SemesterController@destroy'); // Delete item
});



// ============================================================================
// STAFF ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for staff data
$router->group(['prefix' => 'staff'], function ($router) {
    // List & view operations
    $router->get('/', 'StaffController@index');           // List staff with pagination
    $router->get('/all', 'StaffController@all');         // Get all staff
    $router->get('/not-active-staff', 'StaffController@getInactive'); // Get inactive staff
    $router->get('/{id}', 'StaffController@show');       // Get specific item
});

// ============================================================================
// STAFF MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for staff - requires authentication
$router->group(['prefix' => 'staff', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/check-uniqueness', 'StaffController@checkUniqueness');
    $router->post('/batch-create', 'StaffController@batchCreate');
    $router->post('/', 'StaffController@store');         // Create new item
    $router->put('/{id}', 'StaffController@update');     // Update item
    $router->delete('/{id}', 'StaffController@destroy'); // Delete item
});



// ============================================================================
// STAFF UPLOAD ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for staff-upload data
$router->group(['prefix' => 'staff-upload'], function ($router) {
    // List & view operations
    $router->get('/', 'StaffUploadController@index');           // List staff-upload with pagination
    $router->get('/all', 'StaffUploadController@all');         // Get all staff-upload
    $router->get('/{id}', 'StaffUploadController@show');       // Get specific item
});

// ============================================================================
// STAFF UPLOAD MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for staff-upload - requires authentication
$router->group(['prefix' => 'staff-upload', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'StaffUploadController@store');         // Create new item
    $router->put('/{id}', 'StaffUploadController@update');     // Update item
    $router->delete('/{id}', 'StaffUploadController@destroy'); // Delete item
});



// ============================================================================
// STAFF UPLOAD RESULT ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for staff-upload-result data
$router->group(['prefix' => 'staff-upload-result'], function ($router) {
    // List & view operations
    $router->get('/', 'StaffUploadResultController@index');           // List staff-upload-result with pagination
    $router->get('/all', 'StaffUploadResultController@all');         // Get all staff-upload-result
    $router->get('/{id}', 'StaffUploadResultController@show');       // Get specific item
});

// ============================================================================
// STAFF UPLOAD RESULT MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for staff-upload-result - requires authentication
$router->group(['prefix' => 'staff-upload-result', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'StaffUploadResultController@store');         // Create new item
    $router->put('/{id}', 'StaffUploadResultController@update');     // Update item
    $router->delete('/{id}', 'StaffUploadResultController@destroy'); // Delete item
});



// ============================================================================
// STAFF UPLOAD RESULT DETAIL ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for staff-upload-result-detail data
$router->group(['prefix' => 'staff-upload-result-detail'], function ($router) {
    // List & view operations
    $router->get('/', 'StaffUploadResultDetailController@index');           // List staff-upload-result-detail with pagination
    $router->get('/all', 'StaffUploadResultDetailController@all');         // Get all staff-upload-result-detail
    $router->get('/{id}', 'StaffUploadResultDetailController@show');       // Get specific item
});

// ============================================================================
// STAFF UPLOAD RESULT DETAIL MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for staff-upload-result-detail - requires authentication
$router->group(['prefix' => 'staff-upload-result-detail', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'StaffUploadResultDetailController@store');         // Create new item
    $router->put('/{id}', 'StaffUploadResultDetailController@update');     // Update item
    $router->delete('/{id}', 'StaffUploadResultDetailController@destroy'); // Delete item
});



// ============================================================================
// STATUS TYPE ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for status-type data
$router->group(['prefix' => 'status-type'], function ($router) {
    // List & view operations
    $router->get('/', 'StatusTypeController@index');           // List status-type with pagination
    $router->get('/all', 'StatusTypeController@all');         // Get all status-type
    $router->get('/{id}', 'StatusTypeController@show');       // Get specific item
});

// ============================================================================
// STATUS TYPE MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for status-type - requires authentication
$router->group(['prefix' => 'status-type', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'StatusTypeController@store');         // Create new item
    $router->put('/{id}', 'StatusTypeController@update');     // Update item
    $router->delete('/{id}', 'StatusTypeController@destroy'); // Delete item
});



// ============================================================================
// STUDENT ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for student data
$router->group(['prefix' => 'student'], function ($router) {
    // List & view operations
    $router->get('/', 'StudentController@index');           // List student with pagination
    $router->get('/all', 'StudentController@all');         // Get all student
    $router->get('/not-active-student', 'StudentController@getInactive'); // Get inactive students
    $router->get('/{id}', 'StudentController@show');       // Get specific item
});

// ============================================================================
// STUDENT MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for student - requires authentication
$router->group(['prefix' => 'student', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/check-uniqueness', 'StudentController@checkUniqueness');
    $router->post('/batch-create', 'StudentController@batchCreate');
    $router->post('/', 'StudentController@store');         // Create new item
    $router->put('/{id}', 'StudentController@update');     // Update item
    $router->delete('/{id}', 'StudentController@destroy'); // Delete item
});



// ============================================================================
// STUDENT CLASS ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for student-class data
$router->group(['prefix' => 'student-class'], function ($router) {
    $router->get('/class/{classId}', 'StudentClassController@getByClass');
    // List & view operations
    $router->get('/', 'StudentClassController@index');           // List student-class with pagination
    $router->get('/all', 'StudentClassController@all');         // Get all student-class
    $router->get('/{id}', 'StudentClassController@show');       // Get specific item
});

// ============================================================================
// STUDENT CLASS MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for student-class - requires authentication
$router->group(['prefix' => 'student-class', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'StudentClassController@store');         // Create new item
    $router->put('/{id}', 'StudentClassController@update');     // Update item
    $router->delete('/{id}', 'StudentClassController@destroy'); // Delete item
});



// ============================================================================
// STUDENT CLASS HISTORY ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for student-class-history data
$router->group(['prefix' => 'student-class-history'], function ($router) {
    // List & view operations
    $router->get('/', 'StudentClassHistoryController@index');           // List student-class-history with pagination
    $router->get('/all', 'StudentClassHistoryController@all');         // Get all student-class-history
    $router->get('/{id}', 'StudentClassHistoryController@show');       // Get specific item
});

// ============================================================================
// STUDENT CLASS HISTORY MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for student-class-history - requires authentication
$router->group(['prefix' => 'student-class-history', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'StudentClassHistoryController@store');         // Create new item
    $router->put('/{id}', 'StudentClassHistoryController@update');     // Update item
    $router->delete('/{id}', 'StudentClassHistoryController@destroy'); // Delete item
});



// ============================================================================
// STUDENT UPLOAD ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for student-upload data
$router->group(['prefix' => 'student-upload'], function ($router) {
    // List & view operations
    $router->get('/', 'StudentUploadController@index');           // List student-upload with pagination
    $router->get('/all', 'StudentUploadController@all');         // Get all student-upload
    $router->get('/{id}', 'StudentUploadController@show');       // Get specific item
});

// ============================================================================
// STUDENT UPLOAD MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for student-upload - requires authentication
$router->group(['prefix' => 'student-upload', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'StudentUploadController@store');         // Create new item
    $router->put('/{id}', 'StudentUploadController@update');     // Update item
    $router->delete('/{id}', 'StudentUploadController@destroy'); // Delete item
});



// ============================================================================
// STUDENT UPLOAD RESULT ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for student-upload-result data
$router->group(['prefix' => 'student-upload-result'], function ($router) {
    // List & view operations
    $router->get('/', 'StudentUploadResultController@index');           // List student-upload-result with pagination
    $router->get('/all', 'StudentUploadResultController@all');         // Get all student-upload-result
    $router->get('/{id}', 'StudentUploadResultController@show');       // Get specific item
});

// ============================================================================
// STUDENT UPLOAD RESULT MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for student-upload-result - requires authentication
$router->group(['prefix' => 'student-upload-result', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->get('/{id}/download', 'StudentUploadResultController@download');
    $router->post('/', 'StudentUploadResultController@store');         // Create new item
    $router->put('/{id}', 'StudentUploadResultController@update');     // Update item
    $router->delete('/{id}', 'StudentUploadResultController@destroy'); // Delete item
});



// ============================================================================
// STUDENT UPLOAD RESULT DETAIL ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for student-upload-result-detail data
$router->group(['prefix' => 'student-upload-result-detail'], function ($router) {
    // List & view operations
    $router->get('/', 'StudentUploadResultDetailController@index');           // List student-upload-result-detail with pagination
    $router->get('/all', 'StudentUploadResultDetailController@all');         // Get all student-upload-result-detail
    $router->get('/{id}', 'StudentUploadResultDetailController@show');       // Get specific item
});

// ============================================================================
// STUDENT UPLOAD RESULT DETAIL MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for student-upload-result-detail - requires authentication
$router->group(['prefix' => 'student-upload-result-detail', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->get('/{id}/download', 'StudentUploadResultDetailController@download');
    $router->post('/', 'StudentUploadResultDetailController@store');         // Create new item
    $router->put('/{id}', 'StudentUploadResultDetailController@update');     // Update item
    $router->delete('/{id}', 'StudentUploadResultDetailController@destroy'); // Delete item
});



// ============================================================================
// STUDENT VIOLATION ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for student-violation data
$router->group(['prefix' => 'student-violation'], function ($router) {
    // List & view operations
    $router->get('/', 'StudentViolationController@index');           // List student-violation with pagination
    $router->get('/all', 'StudentViolationController@all');         // Get all student-violation
    $router->get('/{id}', 'StudentViolationController@show');       // Get specific item
});

// ============================================================================
// STUDENT VIOLATION MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for student-violation - requires authentication
$router->group(['prefix' => 'student-violation', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'StudentViolationController@store');         // Create new item
    $router->put('/{id}', 'StudentViolationController@update');     // Update item
    $router->delete('/{id}', 'StudentViolationController@destroy'); // Delete item
});



// ============================================================================
// STUDENT VIOLATION TYPE ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for student-violation-type data
$router->group(['prefix' => 'student-violation-type'], function ($router) {
    // List & view operations
    $router->get('/', 'StudentViolationTypeController@index');           // List student-violation-type with pagination
    $router->get('/all', 'StudentViolationTypeController@all');         // Get all student-violation-type
    $router->get('/{id}', 'StudentViolationTypeController@show');       // Get specific item
});

// ============================================================================
// STUDENT VIOLATION TYPE MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for student-violation-type - requires authentication
$router->group(['prefix' => 'student-violation-type', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'StudentViolationTypeController@store');         // Create new item
    $router->put('/{id}', 'StudentViolationTypeController@update');     // Update item
    $router->delete('/{id}', 'StudentViolationTypeController@destroy'); // Delete item
});



// ============================================================================
// SUBJECT ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for subject data
$router->group(['prefix' => 'subject'], function ($router) {
    // List & view operations
    $router->get('/', 'SubjectController@index');           // List subject with pagination
    $router->get('/all', 'SubjectController@all');         // Get all subject
    $router->get('/{id}', 'SubjectController@show');       // Get specific item
});

// ============================================================================
// SUBJECT MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for subject - requires authentication
$router->group(['prefix' => 'subject', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'SubjectController@store');         // Create new item
    $router->put('/{id}', 'SubjectController@update');     // Update item
    $router->delete('/{id}', 'SubjectController@destroy'); // Delete item
});



// ============================================================================
// TEACHER ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for teacher data
$router->group(['prefix' => 'teacher'], function ($router) {
    // List & view operations
    $router->get('/', 'TeacherController@index');           // List teacher with pagination
    $router->get('/all', 'TeacherController@all');         // Get all teacher
    $router->get('/not-active-teacher', 'TeacherController@getInactive'); // Get inactive teachers
    $router->get('/{id}', 'TeacherController@show');       // Get specific item
});

// ============================================================================
// TEACHER MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for teacher - requires authentication
$router->group(['prefix' => 'teacher', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/check-uniqueness', 'TeacherController@checkUniqueness');
    $router->post('/batch-create', 'TeacherController@batchCreate');
    $router->post('/', 'TeacherController@store');         // Create new item
    $router->put('/{id}', 'TeacherController@update');     // Update item
    $router->delete('/{id}', 'TeacherController@destroy'); // Delete item
});



// ============================================================================
// TEACHER UPLOAD ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for teacher-upload data
$router->group(['prefix' => 'teacher-upload'], function ($router) {
    // List & view operations
    $router->get('/', 'TeacherUploadController@index');           // List teacher-upload with pagination
    $router->get('/all', 'TeacherUploadController@all');         // Get all teacher-upload
    $router->get('/{id}', 'TeacherUploadController@show');       // Get specific item
});

// ============================================================================
// TEACHER UPLOAD MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for teacher-upload - requires authentication
$router->group(['prefix' => 'teacher-upload', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'TeacherUploadController@store');         // Create new item
    $router->put('/{id}', 'TeacherUploadController@update');     // Update item
    $router->delete('/{id}', 'TeacherUploadController@destroy'); // Delete item
});



// ============================================================================
// TEACHER UPLOAD RESULT ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for teacher-upload-result data
$router->group(['prefix' => 'teacher-upload-result'], function ($router) {
    // List & view operations
    $router->get('/', 'TeacherUploadResultController@index');           // List teacher-upload-result with pagination
    $router->get('/all', 'TeacherUploadResultController@all');         // Get all teacher-upload-result
    $router->get('/{id}/download', 'TeacherUploadResultController@download');
    $router->get('/{id}', 'TeacherUploadResultController@show');       // Get specific item
});

// ============================================================================
// TEACHER UPLOAD RESULT MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for teacher-upload-result - requires authentication
$router->group(['prefix' => 'teacher-upload-result', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'TeacherUploadResultController@store');         // Create new item
    $router->put('/{id}', 'TeacherUploadResultController@update');     // Update item
    $router->delete('/{id}', 'TeacherUploadResultController@destroy'); // Delete item
});



// ============================================================================
// TEACHER UPLOAD RESULT DETAIL ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for teacher-upload-result-detail data
$router->group(['prefix' => 'teacher-upload-result-detail'], function ($router) {
    // List & view operations
    $router->get('/', 'TeacherUploadResultDetailController@index');           // List teacher-upload-result-detail with pagination
    $router->get('/all', 'TeacherUploadResultDetailController@all');         // Get all teacher-upload-result-detail
    $router->get('/{id}', 'TeacherUploadResultDetailController@show');       // Get specific item
});

// ============================================================================
// TEACHER UPLOAD RESULT DETAIL MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for teacher-upload-result-detail - requires authentication
$router->group(['prefix' => 'teacher-upload-result-detail', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'TeacherUploadResultDetailController@store');         // Create new item
    $router->put('/{id}', 'TeacherUploadResultDetailController@update');     // Update item
    $router->delete('/{id}', 'TeacherUploadResultDetailController@destroy'); // Delete item
});



// ============================================================================
// Public read access for teaching-schedule data
$router->group(['prefix' => 'teaching-schedule'], function ($router) {
    // List & view operations
    $router->get('/', 'TeachingScheduleController@index');           // List teaching-schedule with pagination
    $router->get('/all', 'TeachingScheduleController@all');         // Get all teaching-schedule
    // Custom timetable routes
    $router->get('/get-timetable-by-teacher', 'TeachingScheduleController@getTimetableByTeacher');
    $router->get('/get-timetable-all-teachers', 'TeachingScheduleController@getTimetableAllTeachers');
    $router->get('/get-timetable-by-classroom', 'TeachingScheduleController@getTimetableByClassroom');
    $router->get('/get-timetable-all-classrooms', 'TeachingScheduleController@getTimetableAllClassrooms');
    $router->get('/check-conflict', 'TeachingScheduleController@checkConflict');
    $router->get('/today', 'TeachingScheduleController@getTodaySchedule');
    $router->get('/{id}', 'TeachingScheduleController@show');       // Get specific item
});

// ============================================================================
// TEACHING SCHEDULE MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for teaching-schedule - requires authentication
$router->group(['prefix' => 'teaching-schedule', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/parse-xml-metadata', 'TeachingScheduleController@parseXmlMetadata');
    $router->post('/import-store', 'TeachingScheduleController@importStore');
    $router->post('/', 'TeachingScheduleController@store');         // Create new item
    $router->put('/{id}', 'TeachingScheduleController@update');     // Update item
    $router->delete('/{id}', 'TeachingScheduleController@destroy'); // Delete item
});



// ============================================================================
// USER PARENT STUDENT ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for user-parent-student data
$router->group(['prefix' => 'user-parent-student'], function ($router) {
    // List & view operations
    $router->get('/', 'UserParentStudentController@index');           // List user-parent-student with pagination
    $router->get('/all', 'UserParentStudentController@all');         // Get all user-parent-student
    $router->get('/{id}', 'UserParentStudentController@show');       // Get specific item
});

// ============================================================================
// USER PARENT STUDENT MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for user-parent-student - requires authentication
$router->group(['prefix' => 'user-parent-student', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'UserParentStudentController@store');         // Create new item
    $router->put('/{id}', 'UserParentStudentController@update');     // Update item
    $router->delete('/{id}', 'UserParentStudentController@destroy'); // Delete item
});



// ============================================================================
// USER READ ARTICLE ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for user-read-article data
$router->group(['prefix' => 'user-read-article'], function ($router) {
    // List & view operations
    $router->get('/', 'UserReadArticleController@index');           // List user-read-article with pagination
    $router->get('/all', 'UserReadArticleController@all');         // Get all user-read-article
    $router->get('/{id}', 'UserReadArticleController@show');       // Get specific item
});

// ============================================================================
// USER READ ARTICLE MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for user-read-article - requires authentication
$router->group(['prefix' => 'user-read-article', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'UserReadArticleController@store');         // Create new item
    $router->put('/{id}', 'UserReadArticleController@update');     // Update item
    $router->delete('/{id}', 'UserReadArticleController@destroy'); // Delete item
});



// ============================================================================
// USER ROLE ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for user-role data
$router->group(['prefix' => 'user-role'], function ($router) {
    // List & view operations
    $router->get('/', 'UserRoleController@index');           // List user-role with pagination
    $router->get('/all', 'UserRoleController@all');         // Get all user-role
    $router->get('/{id}', 'UserRoleController@show');       // Get specific item
});

// ============================================================================
// USER ROLE MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for user-role - requires authentication
$router->group(['prefix' => 'user-role', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'UserRoleController@store');         // Create new item
    $router->put('/{id}', 'UserRoleController@update');     // Update item
    $router->delete('/{id}', 'UserRoleController@destroy'); // Delete item
});



// ============================================================================
// VIOLATION COUNSELING ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for violation-counseling data
$router->group(['prefix' => 'violation-counseling'], function ($router) {
    // List & view operations
    $router->get('/', 'ViolationCounselingController@index');           // List violation-counseling with pagination
    $router->get('/all', 'ViolationCounselingController@all');         // Get all violation-counseling
    $router->get('/{id}', 'ViolationCounselingController@show');       // Get specific item
});

// ============================================================================
// VIOLATION COUNSELING MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for violation-counseling - requires authentication
$router->group(['prefix' => 'violation-counseling', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ViolationCounselingController@store');         // Create new item
    $router->put('/{id}', 'ViolationCounselingController@update');     // Update item
    $router->delete('/{id}', 'ViolationCounselingController@destroy'); // Delete item
});



// ============================================================================
// ============================================================================
// SETTING ROUTES (PUBLIC & PROTECTED)
// ============================================================================

// Public read access for global settings
$router->group(['prefix' => 'setting'], function ($router) {
    $router->get('/', 'SettingController@index');
    $router->get('/{id}', 'SettingController@show');
});

// Protected modification operations
$router->group(['prefix' => 'setting', 'middleware' => ['AuthMiddleware']], function ($router) {
    $router->post('/', 'SettingController@store');
    $router->put('/{id}', 'SettingController@update');
    $router->delete('/{id}', 'SettingController@destroy');
});


// ============================================================================
// ATTENDANCE DAILY TEACHER ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for attendance-daily-teacher data
$router->group(['prefix' => 'attendance-daily-teacher'], function ($router) {
    // List & view operations
    $router->get('/', 'AttendanceDailyTeacherController@index');           // List attendance-daily-teacher with pagination
    $router->get('/all', 'AttendanceDailyTeacherController@all');         // Get all attendance-daily-teacher
    $router->get('/{id}', 'AttendanceDailyTeacherController@show');       // Get specific item
});

// ============================================================================
// ATTENDANCE DAILY TEACHER MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for attendance-daily-teacher - requires authentication
$router->group(['prefix' => 'attendance-daily-teacher', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'AttendanceDailyTeacherController@store');         // Create new item
    $router->put('/{id}', 'AttendanceDailyTeacherController@update');     // Update item
    $router->delete('/{id}', 'AttendanceDailyTeacherController@destroy'); // Delete item
});



// ============================================================================
// SETTINGS ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for settings data
$router->group(['prefix' => 'settings'], function ($router) {
    // List & view operations
    $router->get('/', 'SettingController@index');           // List settings with pagination
    $router->get('/all', 'SettingController@all');         // Get all settings
    $router->get('/{id}', 'SettingController@show');       // Get specific item
});

// ============================================================================
// SETTINGS MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for settings - requires authentication
$router->group(['prefix' => 'settings', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'SettingController@store');         // Create new item
    $router->put('/{id}', 'SettingController@update');     // Update item
    $router->delete('/{id}', 'SettingController@destroy'); // Delete item
});



// ============================================================================
// CALENDAR ACADEMICS ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for calendar-academics data
$router->group(['prefix' => 'calendar-academics'], function ($router) {
    // List & view operations
    $router->get('/', 'CalendarAcademicController@index');           // List calendar-academics with pagination
    $router->get('/all', 'CalendarAcademicController@all');         // Get all calendar-academics
    $router->get('/{id}', 'CalendarAcademicController@show');       // Get specific item
});

// ============================================================================
// CALENDAR ACADEMICS MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for calendar-academics - requires authentication
$router->group(['prefix' => 'calendar-academics', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'CalendarAcademicController@store');         // Create new item
    $router->put('/{id}', 'CalendarAcademicController@update');     // Update item
    $router->delete('/{id}', 'CalendarAcademicController@destroy'); // Delete item
});




// ============================================================================
// EXAM EXAMINERS ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for exam-examiners data
$router->group(['prefix' => 'exam-examiners'], function ($router) {
    // List & view operations
    $router->get('/', 'ExamExaminerController@index');           // List exam-examiners with pagination
    $router->get('/all', 'ExamExaminerController@all');         // Get all exam-examiners
    $router->get('/{id}', 'ExamExaminerController@show');       // Get specific item
});

// ============================================================================
// EXAM EXAMINERS MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for exam-examiners - requires authentication
$router->group(['prefix' => 'exam-examiners', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ExamExaminerController@store');         // Create new item
    $router->put('/{id}', 'ExamExaminerController@update');     // Update item
    $router->delete('/{id}', 'ExamExaminerController@destroy'); // Delete item
});



// ============================================================================
// EXAM REPORTS ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for exam-reports data
$router->group(['prefix' => 'exam-reports'], function ($router) {
    // List & view operations
    $router->get('/', 'ExamReportController@index');           // List exam-reports with pagination
    $router->get('/all', 'ExamReportController@all');         // Get all exam-reports
    $router->get('/{id}', 'ExamReportController@show');       // Get specific item
});

// ============================================================================
// EXAM REPORTS MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for exam-reports - requires authentication
$router->group(['prefix' => 'exam-reports', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ExamReportController@store');         // Create new item
    $router->put('/{id}', 'ExamReportController@update');     // Update item
    $router->delete('/{id}', 'ExamReportController@destroy'); // Delete item
});



// ============================================================================
// EXAM SUPERVISORS ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for exam-supervisors data
$router->group(['prefix' => 'exam-supervisors'], function ($router) {
    // List & view operations
    $router->get('/', 'ExamSupervisorController@index');           // List exam-supervisors with pagination
    $router->get('/all', 'ExamSupervisorController@all');         // Get all exam-supervisors
    $router->get('/{id}', 'ExamSupervisorController@show');       // Get specific item
});

// ============================================================================
// EXAM SUPERVISORS MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for exam-supervisors - requires authentication
$router->group(['prefix' => 'exam-supervisors', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ExamSupervisorController@store');         // Create new item
    $router->put('/{id}', 'ExamSupervisorController@update');     // Update item
    $router->delete('/{id}', 'ExamSupervisorController@destroy'); // Delete item
});



// ============================================================================
// CLASSROOM MEMBER ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for classroom-member data
$router->group(['prefix' => 'classroom-member'], function ($router) {
    // List & view operations
    $router->get('/', 'ClassroomMemberController@index');           // List classroom-member with pagination
    $router->get('/all', 'ClassroomMemberController@all');         // Get all classroom-member
    $router->get('/unassigned', 'ClassroomMemberController@getUnassignedStudents');
    $router->get('/{id}', 'ClassroomMemberController@show');       // Get specific item
});

// ============================================================================
// CLASSROOM MEMBER MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for classroom-member - requires authentication
$router->group(['prefix' => 'classroom-member', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ClassroomMemberController@store');         // Create new item
    $router->post('/sync', 'ClassroomMemberController@syncMembers');
    $router->put('/{id}', 'ClassroomMemberController@update');     // Update item
    $router->delete('/{id}', 'ClassroomMemberController@destroy'); // Delete item
});



// ============================================================================
// EXAM EVENTS ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for exam-events data
$router->group(['prefix' => 'exam-events'], function ($router) {
    // List & view operations
    $router->get('/', 'ExamEventController@index');           // List exam-events with pagination
    $router->get('/all', 'ExamEventController@all');         // Get all exam-events
    $router->get('/{id}', 'ExamEventController@show');       // Get specific item
});

// ============================================================================
// EXAM EVENTS MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for exam-events - requires authentication
$router->group(['prefix' => 'exam-events', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ExamEventController@store');         // Create new item
    $router->put('/{id}', 'ExamEventController@update');     // Update item
    $router->delete('/{id}', 'ExamEventController@destroy'); // Delete item
});



// ============================================================================
// VIOLATION ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for violation data
$router->group(['prefix' => 'violation'], function ($router) {
    // List & view operations
    $router->get('/', 'ViolationController@index');           // List violation with pagination
    $router->get('/all', 'ViolationController@all');         // Get all violation
    $router->get('/{id}', 'ViolationController@show');       // Get specific item
});

// ============================================================================
// VIOLATION MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for violation - requires authentication
$router->group(['prefix' => 'violation', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ViolationController@store');         // Create new item
    $router->put('/{id}', 'ViolationController@update');     // Update item
    $router->delete('/{id}', 'ViolationController@destroy'); // Delete item
});



// ============================================================================
// VIOLATION COUNSELING SESSION ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for violation-counseling-session data
$router->group(['prefix' => 'violation-counseling-session'], function ($router) {
    // List & view operations
    $router->get('/', 'ViolationCounselingSessionController@index');           // List violation-counseling-session with pagination
    $router->get('/all', 'ViolationCounselingSessionController@all');         // Get all violation-counseling-session
    $router->get('/{id}', 'ViolationCounselingSessionController@show');       // Get specific item
});

// ============================================================================
// VIOLATION COUNSELING SESSION MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for violation-counseling-session - requires authentication
$router->group(['prefix' => 'violation-counseling-session', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ViolationCounselingSessionController@store');         // Create new item
    $router->put('/{id}', 'ViolationCounselingSessionController@update');     // Update item
    $router->delete('/{id}', 'ViolationCounselingSessionController@destroy'); // Delete item
});



// ============================================================================
// VIOLATION TYPE ROUTES - READ OPERATIONS (PUBLIC)
// ============================================================================
// Public read access for violation-type data
$router->group(['prefix' => 'violation-type'], function ($router) {
    // List & view operations
    $router->get('/', 'ViolationTypeController@index');           // List violation-type with pagination
    $router->get('/all', 'ViolationTypeController@all');         // Get all violation-type
    $router->get('/{id}', 'ViolationTypeController@show');       // Get specific item
});

// ============================================================================
// VIOLATION TYPE MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Modification operations for violation-type - requires authentication
$router->group(['prefix' => 'violation-type', 'middleware' => ['AuthMiddleware']], function ($router) {
    // Modification operations
    $router->post('/', 'ViolationTypeController@store');         // Create new item
    $router->put('/{id}', 'ViolationTypeController@update');     // Update item
    $router->delete('/{id}', 'ViolationTypeController@destroy'); // Delete item
});

// ============================================================================
// UTILS ROUTES (PROTECTED)
// ============================================================================
$router->post('/utils/render-content', 'UtilsController@renderContent')->middleware('AuthMiddleware');


return $router;
