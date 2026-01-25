<?php

use Core\Router;

$router = new Router();

// Site and health check routes
$router->get('/', 'SiteController@index');
$router->get('/health', 'SiteController@health');

// Site information routes
$router->group(['prefix' => 'site'], function ($router) {
    $router->get('/info', 'SiteController@info');
    $router->get('/endpoints', 'SiteController@endpoints');
});

// Authentication routes (public)
$router->group(['prefix' => 'auth'], function ($router) {
    $router->post('/register', 'AuthController@register')->middleware('RateLimitMiddleware');
    $router->post('/login', 'AuthController@login')->middleware('RateLimitMiddleware');
    $router->post('/refresh', 'AuthController@refresh');
    $router->post('/logout', 'AuthController@logout');
    $router->post('/forgot-password', 'AuthController@forgotPassword')->middleware('RateLimitMiddleware');
    $router->post('/reset-password', 'AuthController@resetPassword')->middleware('RateLimitMiddleware');
    $router->get('/me', 'AuthController@me')->middleware('AuthMiddleware');
});

// User routes (protected)
$router->group(['prefix' => 'users', 'middleware' => ['AuthMiddleware']], function ($router) {
    $router->get('/', 'UserController@index');
    $router->get('/all', 'UserController@all');
    $router->get('/{id}', 'UserController@show');
    $router->post('/', 'UserController@store');
    $router->put('/{id}', 'UserController@update');
    $router->delete('/{id}', 'UserController@destroy');
});



// comments routes
$router->group(['prefix' => 'comments'], function ($router) {
    $router->get('/', 'CommentController@index');
    $router->get('/all', 'CommentController@all');
    $router->get('/{id}', 'CommentController@show');
    $router->post('/', 'CommentController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'CommentController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'CommentController@destroy')->middleware('AuthMiddleware');
});


// post_tags routes
$router->group(['prefix' => 'post_tags'], function ($router) {
    $router->get('/', 'PostTagController@index');
    $router->get('/all', 'PostTagController@all');
    $router->get('/{id}', 'PostTagController@show');
    $router->post('/', 'PostTagController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'PostTagController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'PostTagController@destroy')->middleware('AuthMiddleware');
});


// posts routes
$router->group(['prefix' => 'posts'], function ($router) {
    $router->get('/', 'PostController@index');
    $router->get('/all', 'PostController@all');
    $router->get('/{id}', 'PostController@show');
    $router->post('/', 'PostController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'PostController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'PostController@destroy')->middleware('AuthMiddleware');
});


// tags routes
$router->group(['prefix' => 'tags'], function ($router) {
    $router->get('/', 'TagController@index');
    $router->get('/all', 'TagController@all');
    $router->get('/{id}', 'TagController@show');
    $router->post('/', 'TagController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'TagController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'TagController@destroy')->middleware('AuthMiddleware');
});


// activity_log routes
$router->group(['prefix' => 'activity_log'], function ($router) {
    $router->get('/', 'ActivityLogController@index');
    $router->get('/all', 'ActivityLogController@all');
    $router->get('/{id}', 'ActivityLogController@show');
    $router->post('/', 'ActivityLogController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'ActivityLogController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'ActivityLogController@destroy')->middleware('AuthMiddleware');
});


// article routes
$router->group(['prefix' => 'article'], function ($router) {
    $router->get('/', 'ArticleController@index');
    $router->get('/all', 'ArticleController@all');
    $router->get('/{id}', 'ArticleController@show');
    $router->post('/', 'ArticleController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'ArticleController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'ArticleController@destroy')->middleware('AuthMiddleware');
});


// article_class routes
$router->group(['prefix' => 'article_class'], function ($router) {
    $router->get('/', 'ArticleClaController@index');
    $router->get('/all', 'ArticleClaController@all');
    $router->get('/{id}', 'ArticleClaController@show');
    $router->post('/', 'ArticleClaController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'ArticleClaController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'ArticleClaController@destroy')->middleware('AuthMiddleware');
});


// article_comment routes
$router->group(['prefix' => 'article_comment'], function ($router) {
    $router->get('/', 'ArticleCommentController@index');
    $router->get('/all', 'ArticleCommentController@all');
    $router->get('/{id}', 'ArticleCommentController@show');
    $router->post('/', 'ArticleCommentController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'ArticleCommentController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'ArticleCommentController@destroy')->middleware('AuthMiddleware');
});


// article_comment_like routes
$router->group(['prefix' => 'article_comment_like'], function ($router) {
    $router->get('/', 'ArticleCommentLikeController@index');
    $router->get('/all', 'ArticleCommentLikeController@all');
    $router->get('/{id}', 'ArticleCommentLikeController@show');
    $router->post('/', 'ArticleCommentLikeController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'ArticleCommentLikeController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'ArticleCommentLikeController@destroy')->middleware('AuthMiddleware');
});


// article_like routes
$router->group(['prefix' => 'article_like'], function ($router) {
    $router->get('/', 'ArticleLikeController@index');
    $router->get('/all', 'ArticleLikeController@all');
    $router->get('/{id}', 'ArticleLikeController@show');
    $router->post('/', 'ArticleLikeController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'ArticleLikeController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'ArticleLikeController@destroy')->middleware('AuthMiddleware');
});


// asc_import_log routes
$router->group(['prefix' => 'asc_import_log'], function ($router) {
    $router->get('/', 'AscImportLogController@index');
    $router->get('/all', 'AscImportLogController@all');
    $router->get('/{id}', 'AscImportLogController@show');
    $router->post('/', 'AscImportLogController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'AscImportLogController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'AscImportLogController@destroy')->middleware('AuthMiddleware');
});


// asc_mapping routes
$router->group(['prefix' => 'asc_mapping'], function ($router) {
    $router->get('/', 'AscMappingController@index');
    $router->get('/all', 'AscMappingController@all');
    $router->get('/{id}', 'AscMappingController@show');
    $router->post('/', 'AscMappingController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'AscMappingController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'AscMappingController@destroy')->middleware('AuthMiddleware');
});


// asset routes
$router->group(['prefix' => 'asset'], function ($router) {
    $router->get('/', 'AssetController@index');
    $router->get('/all', 'AssetController@all');
    $router->get('/{id}', 'AssetController@show');
    $router->post('/', 'AssetController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'AssetController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'AssetController@destroy')->middleware('AuthMiddleware');
});


// asset_borrowing routes
$router->group(['prefix' => 'asset_borrowing'], function ($router) {
    $router->get('/', 'AssetBorrowingController@index');
    $router->get('/all', 'AssetBorrowingController@all');
    $router->get('/{id}', 'AssetBorrowingController@show');
    $router->post('/', 'AssetBorrowingController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'AssetBorrowingController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'AssetBorrowingController@destroy')->middleware('AuthMiddleware');
});


// assignment routes
$router->group(['prefix' => 'assignment'], function ($router) {
    $router->get('/', 'AssignmentController@index');
    $router->get('/all', 'AssignmentController@all');
    $router->get('/{id}', 'AssignmentController@show');
    $router->post('/', 'AssignmentController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'AssignmentController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'AssignmentController@destroy')->middleware('AuthMiddleware');
});


// assignment_class routes
$router->group(['prefix' => 'assignment_class'], function ($router) {
    $router->get('/', 'AssignmentClaController@index');
    $router->get('/all', 'AssignmentClaController@all');
    $router->get('/{id}', 'AssignmentClaController@show');
    $router->post('/', 'AssignmentClaController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'AssignmentClaController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'AssignmentClaController@destroy')->middleware('AuthMiddleware');
});


// assignment_result routes
$router->group(['prefix' => 'assignment_result'], function ($router) {
    $router->get('/', 'AssignmentResultController@index');
    $router->get('/all', 'AssignmentResultController@all');
    $router->get('/{id}', 'AssignmentResultController@show');
    $router->post('/', 'AssignmentResultController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'AssignmentResultController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'AssignmentResultController@destroy')->middleware('AuthMiddleware');
});


// attendance_daily_student routes
$router->group(['prefix' => 'attendance_daily_student'], function ($router) {
    $router->get('/', 'AttendanceDailyStudentController@index');
    $router->get('/all', 'AttendanceDailyStudentController@all');
    $router->get('/{id}', 'AttendanceDailyStudentController@show');
    $router->post('/', 'AttendanceDailyStudentController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'AttendanceDailyStudentController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'AttendanceDailyStudentController@destroy')->middleware('AuthMiddleware');
});


// attendance_student routes
$router->group(['prefix' => 'attendance_student'], function ($router) {
    $router->get('/', 'AttendanceStudentController@index');
    $router->get('/all', 'AttendanceStudentController@all');
    $router->get('/{id}', 'AttendanceStudentController@show');
    $router->post('/', 'AttendanceStudentController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'AttendanceStudentController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'AttendanceStudentController@destroy')->middleware('AuthMiddleware');
});


// attendance_teacher routes
$router->group(['prefix' => 'attendance_teacher'], function ($router) {
    $router->get('/', 'AttendanceTeacherController@index');
    $router->get('/all', 'AttendanceTeacherController@all');
    $router->get('/{id}', 'AttendanceTeacherController@show');
    $router->post('/', 'AttendanceTeacherController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'AttendanceTeacherController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'AttendanceTeacherController@destroy')->middleware('AuthMiddleware');
});


// billing routes
$router->group(['prefix' => 'billing'], function ($router) {
    $router->get('/', 'BillingController@index');
    $router->get('/all', 'BillingController@all');
    $router->get('/{id}', 'BillingController@show');
    $router->post('/', 'BillingController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'BillingController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'BillingController@destroy')->middleware('AuthMiddleware');
});


// book routes
$router->group(['prefix' => 'book'], function ($router) {
    $router->get('/', 'BookController@index');
    $router->get('/all', 'BookController@all');
    $router->get('/{id}', 'BookController@show');
    $router->post('/', 'BookController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'BookController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'BookController@destroy')->middleware('AuthMiddleware');
});


// book_borrowing routes
$router->group(['prefix' => 'book_borrowing'], function ($router) {
    $router->get('/', 'BookBorrowingController@index');
    $router->get('/all', 'BookBorrowingController@all');
    $router->get('/{id}', 'BookBorrowingController@show');
    $router->post('/', 'BookBorrowingController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'BookBorrowingController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'BookBorrowingController@destroy')->middleware('AuthMiddleware');
});


// class_semester routes
$router->group(['prefix' => 'class_semester'], function ($router) {
    $router->get('/', 'ClassSemesterController@index');
    $router->get('/all', 'ClassSemesterController@all');
    $router->get('/{id}', 'ClassSemesterController@show');
    $router->post('/', 'ClassSemesterController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'ClassSemesterController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'ClassSemesterController@destroy')->middleware('AuthMiddleware');
});


// classroom routes
$router->group(['prefix' => 'classroom'], function ($router) {
    $router->get('/', 'ClassroomController@index');
    $router->get('/all', 'ClassroomController@all');
    $router->get('/{id}', 'ClassroomController@show');
    $router->post('/', 'ClassroomController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'ClassroomController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'ClassroomController@destroy')->middleware('AuthMiddleware');
});


// counseling_session routes
$router->group(['prefix' => 'counseling_session'], function ($router) {
    $router->get('/', 'CounselingSessionController@index');
    $router->get('/all', 'CounselingSessionController@all');
    $router->get('/{id}', 'CounselingSessionController@show');
    $router->post('/', 'CounselingSessionController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'CounselingSessionController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'CounselingSessionController@destroy')->middleware('AuthMiddleware');
});


// department routes
$router->group(['prefix' => 'department'], function ($router) {
    $router->get('/', 'DepartmentController@index');
    $router->get('/all', 'DepartmentController@all');
    $router->get('/{id}', 'DepartmentController@show');
    $router->post('/', 'DepartmentController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'DepartmentController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'DepartmentController@destroy')->middleware('AuthMiddleware');
});


// department_semester routes
$router->group(['prefix' => 'department_semester'], function ($router) {
    $router->get('/', 'DepartmentSemesterController@index');
    $router->get('/all', 'DepartmentSemesterController@all');
    $router->get('/{id}', 'DepartmentSemesterController@show');
    $router->post('/', 'DepartmentSemesterController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'DepartmentSemesterController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'DepartmentSemesterController@destroy')->middleware('AuthMiddleware');
});


// exam routes
$router->group(['prefix' => 'exam'], function ($router) {
    $router->get('/', 'ExamController@index');
    $router->get('/all', 'ExamController@all');
    $router->get('/{id}', 'ExamController@show');
    $router->post('/', 'ExamController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'ExamController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'ExamController@destroy')->middleware('AuthMiddleware');
});


// exam_class routes
$router->group(['prefix' => 'exam_class'], function ($router) {
    $router->get('/', 'ExamClaController@index');
    $router->get('/all', 'ExamClaController@all');
    $router->get('/{id}', 'ExamClaController@show');
    $router->post('/', 'ExamClaController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'ExamClaController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'ExamClaController@destroy')->middleware('AuthMiddleware');
});


// exam_class_user routes
$router->group(['prefix' => 'exam_class_user'], function ($router) {
    $router->get('/', 'ExamClassUserController@index');
    $router->get('/all', 'ExamClassUserController@all');
    $router->get('/{id}', 'ExamClassUserController@show');
    $router->post('/', 'ExamClassUserController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'ExamClassUserController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'ExamClassUserController@destroy')->middleware('AuthMiddleware');
});


// exam_result routes
$router->group(['prefix' => 'exam_result'], function ($router) {
    $router->get('/', 'ExamResultController@index');
    $router->get('/all', 'ExamResultController@all');
    $router->get('/{id}', 'ExamResultController@show');
    $router->post('/', 'ExamResultController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'ExamResultController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'ExamResultController@destroy')->middleware('AuthMiddleware');
});


// exam_result_answer routes
$router->group(['prefix' => 'exam_result_answer'], function ($router) {
    $router->get('/', 'ExamResultAnswerController@index');
    $router->get('/all', 'ExamResultAnswerController@all');
    $router->get('/{id}', 'ExamResultAnswerController@show');
    $router->post('/', 'ExamResultAnswerController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'ExamResultAnswerController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'ExamResultAnswerController@destroy')->middleware('AuthMiddleware');
});


// exercise routes
$router->group(['prefix' => 'exercise'], function ($router) {
    $router->get('/', 'ExerciseController@index');
    $router->get('/all', 'ExerciseController@all');
    $router->get('/{id}', 'ExerciseController@show');
    $router->post('/', 'ExerciseController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'ExerciseController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'ExerciseController@destroy')->middleware('AuthMiddleware');
});


// exercise_comment routes
$router->group(['prefix' => 'exercise_comment'], function ($router) {
    $router->get('/', 'ExerciseCommentController@index');
    $router->get('/all', 'ExerciseCommentController@all');
    $router->get('/{id}', 'ExerciseCommentController@show');
    $router->post('/', 'ExerciseCommentController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'ExerciseCommentController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'ExerciseCommentController@destroy')->middleware('AuthMiddleware');
});


// exercise_group routes
$router->group(['prefix' => 'exercise_group'], function ($router) {
    $router->get('/', 'ExerciseGroupController@index');
    $router->get('/all', 'ExerciseGroupController@all');
    $router->get('/{id}', 'ExerciseGroupController@show');
    $router->post('/', 'ExerciseGroupController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'ExerciseGroupController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'ExerciseGroupController@destroy')->middleware('AuthMiddleware');
});


// financial_transaction routes
$router->group(['prefix' => 'financial_transaction'], function ($router) {
    $router->get('/', 'FinancialTransactionController@index');
    $router->get('/all', 'FinancialTransactionController@all');
    $router->get('/{id}', 'FinancialTransactionController@show');
    $router->post('/', 'FinancialTransactionController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'FinancialTransactionController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'FinancialTransactionController@destroy')->middleware('AuthMiddleware');
});


// grade_level routes
$router->group(['prefix' => 'grade_level'], function ($router) {
    $router->get('/', 'GradeLevelController@index');
    $router->get('/all', 'GradeLevelController@all');
    $router->get('/{id}', 'GradeLevelController@show');
    $router->post('/', 'GradeLevelController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'GradeLevelController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'GradeLevelController@destroy')->middleware('AuthMiddleware');
});


// lesson_score_student routes
$router->group(['prefix' => 'lesson_score_student'], function ($router) {
    $router->get('/', 'LessonScoreStudentController@index');
    $router->get('/all', 'LessonScoreStudentController@all');
    $router->get('/{id}', 'LessonScoreStudentController@show');
    $router->post('/', 'LessonScoreStudentController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'LessonScoreStudentController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'LessonScoreStudentController@destroy')->middleware('AuthMiddleware');
});


// lesson_session routes
$router->group(['prefix' => 'lesson_session'], function ($router) {
    $router->get('/', 'LessonSessionController@index');
    $router->get('/all', 'LessonSessionController@all');
    $router->get('/{id}', 'LessonSessionController@show');
    $router->post('/', 'LessonSessionController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'LessonSessionController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'LessonSessionController@destroy')->middleware('AuthMiddleware');
});


// migration routes
$router->group(['prefix' => 'migration'], function ($router) {
    $router->get('/', 'MigrationController@index');
    $router->get('/all', 'MigrationController@all');
    $router->get('/{id}', 'MigrationController@show');
    $router->post('/', 'MigrationController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'MigrationController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'MigrationController@destroy')->middleware('AuthMiddleware');
});


// notification routes
$router->group(['prefix' => 'notification'], function ($router) {
    $router->get('/', 'NotificationController@index');
    $router->get('/all', 'NotificationController@all');
    $router->get('/{id}', 'NotificationController@show');
    $router->post('/', 'NotificationController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'NotificationController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'NotificationController@destroy')->middleware('AuthMiddleware');
});


// payment routes
$router->group(['prefix' => 'payment'], function ($router) {
    $router->get('/', 'PaymentController@index');
    $router->get('/all', 'PaymentController@all');
    $router->get('/{id}', 'PaymentController@show');
    $router->post('/', 'PaymentController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'PaymentController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'PaymentController@destroy')->middleware('AuthMiddleware');
});


// period routes
$router->group(['prefix' => 'period'], function ($router) {
    $router->get('/', 'PeriodController@index');
    $router->get('/all', 'PeriodController@all');
    $router->get('/{id}', 'PeriodController@show');
    $router->post('/', 'PeriodController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'PeriodController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'PeriodController@destroy')->middleware('AuthMiddleware');
});


// question routes
$router->group(['prefix' => 'question'], function ($router) {
    $router->get('/', 'QuestionController@index');
    $router->get('/all', 'QuestionController@all');
    $router->get('/{id}', 'QuestionController@show');
    $router->post('/', 'QuestionController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'QuestionController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'QuestionController@destroy')->middleware('AuthMiddleware');
});


// question_answer routes
$router->group(['prefix' => 'question_answer'], function ($router) {
    $router->get('/', 'QuestionAnswerController@index');
    $router->get('/all', 'QuestionAnswerController@all');
    $router->get('/{id}', 'QuestionAnswerController@show');
    $router->post('/', 'QuestionAnswerController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'QuestionAnswerController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'QuestionAnswerController@destroy')->middleware('AuthMiddleware');
});


// question_bank routes
$router->group(['prefix' => 'question_bank'], function ($router) {
    $router->get('/', 'QuestionBankController@index');
    $router->get('/all', 'QuestionBankController@all');
    $router->get('/{id}', 'QuestionBankController@show');
    $router->post('/', 'QuestionBankController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'QuestionBankController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'QuestionBankController@destroy')->middleware('AuthMiddleware');
});


// role routes
$router->group(['prefix' => 'role'], function ($router) {
    $router->get('/', 'RoleController@index');
    $router->get('/all', 'RoleController@all');
    $router->get('/{id}', 'RoleController@show');
    $router->post('/', 'RoleController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'RoleController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'RoleController@destroy')->middleware('AuthMiddleware');
});


// school_year routes
$router->group(['prefix' => 'school_year'], function ($router) {
    $router->get('/', 'SchoolYearController@index');
    $router->get('/all', 'SchoolYearController@all');
    $router->get('/{id}', 'SchoolYearController@show');
    $router->post('/', 'SchoolYearController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'SchoolYearController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'SchoolYearController@destroy')->middleware('AuthMiddleware');
});


// semester routes
$router->group(['prefix' => 'semester'], function ($router) {
    $router->get('/', 'SemesterController@index');
    $router->get('/all', 'SemesterController@all');
    $router->get('/{id}', 'SemesterController@show');
    $router->post('/', 'SemesterController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'SemesterController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'SemesterController@destroy')->middleware('AuthMiddleware');
});


// staff routes
$router->group(['prefix' => 'staff'], function ($router) {
    $router->get('/', 'StaffController@index');
    $router->get('/all', 'StaffController@all');
    $router->get('/{id}', 'StaffController@show');
    $router->post('/', 'StaffController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'StaffController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'StaffController@destroy')->middleware('AuthMiddleware');
});


// staff_upload routes
$router->group(['prefix' => 'staff_upload'], function ($router) {
    $router->get('/', 'StaffUploadController@index');
    $router->get('/all', 'StaffUploadController@all');
    $router->get('/{id}', 'StaffUploadController@show');
    $router->post('/', 'StaffUploadController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'StaffUploadController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'StaffUploadController@destroy')->middleware('AuthMiddleware');
});


// staff_upload_result routes
$router->group(['prefix' => 'staff_upload_result'], function ($router) {
    $router->get('/', 'StaffUploadResultController@index');
    $router->get('/all', 'StaffUploadResultController@all');
    $router->get('/{id}', 'StaffUploadResultController@show');
    $router->post('/', 'StaffUploadResultController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'StaffUploadResultController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'StaffUploadResultController@destroy')->middleware('AuthMiddleware');
});


// staff_upload_result_detail routes
$router->group(['prefix' => 'staff_upload_result_detail'], function ($router) {
    $router->get('/', 'StaffUploadResultDetailController@index');
    $router->get('/all', 'StaffUploadResultDetailController@all');
    $router->get('/{id}', 'StaffUploadResultDetailController@show');
    $router->post('/', 'StaffUploadResultDetailController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'StaffUploadResultDetailController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'StaffUploadResultDetailController@destroy')->middleware('AuthMiddleware');
});


// status_type routes
$router->group(['prefix' => 'status_type'], function ($router) {
    $router->get('/', 'StatusTypeController@index');
    $router->get('/all', 'StatusTypeController@all');
    $router->get('/{id}', 'StatusTypeController@show');
    $router->post('/', 'StatusTypeController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'StatusTypeController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'StatusTypeController@destroy')->middleware('AuthMiddleware');
});


// student routes
$router->group(['prefix' => 'student'], function ($router) {
    $router->get('/', 'StudentController@index');
    $router->get('/all', 'StudentController@all');
    $router->get('/{id}', 'StudentController@show');
    $router->post('/', 'StudentController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'StudentController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'StudentController@destroy')->middleware('AuthMiddleware');
});


// student_class routes
$router->group(['prefix' => 'student_class'], function ($router) {
    $router->get('/', 'StudentClaController@index');
    $router->get('/all', 'StudentClaController@all');
    $router->get('/{id}', 'StudentClaController@show');
    $router->post('/', 'StudentClaController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'StudentClaController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'StudentClaController@destroy')->middleware('AuthMiddleware');
});


// student_class_history routes
$router->group(['prefix' => 'student_class_history'], function ($router) {
    $router->get('/', 'StudentClassHistoryController@index');
    $router->get('/all', 'StudentClassHistoryController@all');
    $router->get('/{id}', 'StudentClassHistoryController@show');
    $router->post('/', 'StudentClassHistoryController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'StudentClassHistoryController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'StudentClassHistoryController@destroy')->middleware('AuthMiddleware');
});


// student_upload routes
$router->group(['prefix' => 'student_upload'], function ($router) {
    $router->get('/', 'StudentUploadController@index');
    $router->get('/all', 'StudentUploadController@all');
    $router->get('/{id}', 'StudentUploadController@show');
    $router->post('/', 'StudentUploadController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'StudentUploadController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'StudentUploadController@destroy')->middleware('AuthMiddleware');
});


// student_upload_result routes
$router->group(['prefix' => 'student_upload_result'], function ($router) {
    $router->get('/', 'StudentUploadResultController@index');
    $router->get('/all', 'StudentUploadResultController@all');
    $router->get('/{id}', 'StudentUploadResultController@show');
    $router->post('/', 'StudentUploadResultController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'StudentUploadResultController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'StudentUploadResultController@destroy')->middleware('AuthMiddleware');
});


// student_upload_result_detail routes
$router->group(['prefix' => 'student_upload_result_detail'], function ($router) {
    $router->get('/', 'StudentUploadResultDetailController@index');
    $router->get('/all', 'StudentUploadResultDetailController@all');
    $router->get('/{id}', 'StudentUploadResultDetailController@show');
    $router->post('/', 'StudentUploadResultDetailController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'StudentUploadResultDetailController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'StudentUploadResultDetailController@destroy')->middleware('AuthMiddleware');
});


// student_violation routes
$router->group(['prefix' => 'student_violation'], function ($router) {
    $router->get('/', 'StudentViolationController@index');
    $router->get('/all', 'StudentViolationController@all');
    $router->get('/{id}', 'StudentViolationController@show');
    $router->post('/', 'StudentViolationController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'StudentViolationController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'StudentViolationController@destroy')->middleware('AuthMiddleware');
});


// student_violation_type routes
$router->group(['prefix' => 'student_violation_type'], function ($router) {
    $router->get('/', 'StudentViolationTypeController@index');
    $router->get('/all', 'StudentViolationTypeController@all');
    $router->get('/{id}', 'StudentViolationTypeController@show');
    $router->post('/', 'StudentViolationTypeController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'StudentViolationTypeController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'StudentViolationTypeController@destroy')->middleware('AuthMiddleware');
});


// subject routes
$router->group(['prefix' => 'subject'], function ($router) {
    $router->get('/', 'SubjectController@index');
    $router->get('/all', 'SubjectController@all');
    $router->get('/{id}', 'SubjectController@show');
    $router->post('/', 'SubjectController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'SubjectController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'SubjectController@destroy')->middleware('AuthMiddleware');
});


// teacher routes
$router->group(['prefix' => 'teacher'], function ($router) {
    $router->get('/', 'TeacherController@index');
    $router->get('/all', 'TeacherController@all');
    $router->get('/{id}', 'TeacherController@show');
    $router->post('/', 'TeacherController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'TeacherController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'TeacherController@destroy')->middleware('AuthMiddleware');
});


// teacher_upload routes
$router->group(['prefix' => 'teacher_upload'], function ($router) {
    $router->get('/', 'TeacherUploadController@index');
    $router->get('/all', 'TeacherUploadController@all');
    $router->get('/{id}', 'TeacherUploadController@show');
    $router->post('/', 'TeacherUploadController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'TeacherUploadController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'TeacherUploadController@destroy')->middleware('AuthMiddleware');
});


// teacher_upload_result routes
$router->group(['prefix' => 'teacher_upload_result'], function ($router) {
    $router->get('/', 'TeacherUploadResultController@index');
    $router->get('/all', 'TeacherUploadResultController@all');
    $router->get('/{id}', 'TeacherUploadResultController@show');
    $router->post('/', 'TeacherUploadResultController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'TeacherUploadResultController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'TeacherUploadResultController@destroy')->middleware('AuthMiddleware');
});


// teacher_upload_result_detail routes
$router->group(['prefix' => 'teacher_upload_result_detail'], function ($router) {
    $router->get('/', 'TeacherUploadResultDetailController@index');
    $router->get('/all', 'TeacherUploadResultDetailController@all');
    $router->get('/{id}', 'TeacherUploadResultDetailController@show');
    $router->post('/', 'TeacherUploadResultDetailController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'TeacherUploadResultDetailController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'TeacherUploadResultDetailController@destroy')->middleware('AuthMiddleware');
});


// teaching_schedule routes
$router->group(['prefix' => 'teaching_schedule'], function ($router) {
    $router->get('/', 'TeachingScheduleController@index');
    $router->get('/all', 'TeachingScheduleController@all');
    $router->get('/{id}', 'TeachingScheduleController@show');
    $router->post('/', 'TeachingScheduleController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'TeachingScheduleController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'TeachingScheduleController@destroy')->middleware('AuthMiddleware');
});


// user_parent_student routes
$router->group(['prefix' => 'user_parent_student'], function ($router) {
    $router->get('/', 'UserParentStudentController@index');
    $router->get('/all', 'UserParentStudentController@all');
    $router->get('/{id}', 'UserParentStudentController@show');
    $router->post('/', 'UserParentStudentController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'UserParentStudentController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'UserParentStudentController@destroy')->middleware('AuthMiddleware');
});


// user_read_article routes
$router->group(['prefix' => 'user_read_article'], function ($router) {
    $router->get('/', 'UserReadArticleController@index');
    $router->get('/all', 'UserReadArticleController@all');
    $router->get('/{id}', 'UserReadArticleController@show');
    $router->post('/', 'UserReadArticleController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'UserReadArticleController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'UserReadArticleController@destroy')->middleware('AuthMiddleware');
});


// user_role routes
$router->group(['prefix' => 'user_role'], function ($router) {
    $router->get('/', 'UserRoleController@index');
    $router->get('/all', 'UserRoleController@all');
    $router->get('/{id}', 'UserRoleController@show');
    $router->post('/', 'UserRoleController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'UserRoleController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'UserRoleController@destroy')->middleware('AuthMiddleware');
});


// violation_counseling routes
$router->group(['prefix' => 'violation_counseling'], function ($router) {
    $router->get('/', 'ViolationCounselingController@index');
    $router->get('/all', 'ViolationCounselingController@all');
    $router->get('/{id}', 'ViolationCounselingController@show');
    $router->post('/', 'ViolationCounselingController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'ViolationCounselingController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'ViolationCounselingController@destroy')->middleware('AuthMiddleware');
});


// questionbanks routes
$router->group(['prefix' => 'questionbanks'], function ($router) {
    $router->get('/', 'QuestionbankController@index');
    $router->get('/all', 'QuestionbankController@all');
    $router->get('/{id}', 'QuestionbankController@show');
    $router->post('/', 'QuestionbankController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'QuestionbankController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'QuestionbankController@destroy')->middleware('AuthMiddleware');
});


// jobs routes
$router->group(['prefix' => 'jobs'], function($router) {
    $router->get('/', 'JobController@index');
    $router->get('/all', 'JobController@all');
    $router->get('/{id}', 'JobController@show');
    $router->post('/', 'JobController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'JobController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'JobController@destroy')->middleware('AuthMiddleware');
});

return $router;
