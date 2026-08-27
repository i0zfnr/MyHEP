<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AiHelperController as AdminAiHelperController;
use App\Http\Controllers\Admin\ActiveVisitorController;
use App\Http\Controllers\Admin\BugReportController as AdminBugReportController;
use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\Admin\FoodBankController as AdminFoodBankController;
use App\Http\Controllers\Admin\GuardManagementController;
use App\Http\Controllers\Admin\LaptopController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\MovementController as AdminMovementController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\ProgramOperationController;
use App\Http\Controllers\Admin\ProgramCertificateController;
use App\Http\Controllers\Admin\ProgramParticipationPointController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\ScholarshipController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentDocumentController as AdminStudentDocumentController;
use App\Http\Controllers\Admin\StudentInsuranceController;
use App\Http\Controllers\Admin\StudentScholarshipStatusController;
use App\Http\Controllers\Admin\StaffManagementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BugReportController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NotificationFeedController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\ProgramActivityController as StudentProgramActivityController;
use App\Http\Controllers\Student\AiHelperController as StudentAiHelperController;
use App\Http\Controllers\Student\DocumentController as StudentDocumentController;
use App\Http\Controllers\Student\FoodBankController as StudentFoodBankController;
use App\Http\Controllers\Student\MovementController as StudentMovementController;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Controllers\Student\ScholarshipStatusController;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/system-overview/live', [HomeController::class, 'live'])->name('system-overview.live');
Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');
Route::post('/theme', [SettingController::class, 'updateTheme'])->name('theme.update');

Route::get('/report-problem', [BugReportController::class, 'create'])->name('bug-reports.create');
Route::post('/report-problem', [BugReportController::class, 'store'])
    ->middleware('throttle:6,10')
    ->name('bug-reports.store');

// Login Routes
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.submit');
Route::get('/password/forgot', [LoginController::class, 'forgotForm'])->name('password.forgot');
Route::post('/password/forgot', [LoginController::class, 'sendResetCode'])->name('password.forgot.send');
Route::get('/password/verify', [LoginController::class, 'verifyForm'])->name('password.verify');
Route::post('/password/verify', [LoginController::class, 'verifyCode'])->name('password.verify.check');
Route::get('/password/reset', [LoginController::class, 'resetForm'])->name('password.reset');
Route::post('/password/reset', [LoginController::class, 'resetPassword'])->name('password.reset.update');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
Route::get('/settings', [SettingController::class, 'show'])
    ->middleware('auth.session.any')
    ->name('settings.show');
Route::post('/settings', [SettingController::class, 'update'])
    ->middleware('auth.session.any')
    ->name('settings.update');
Route::post('/settings/role-mode', [SettingController::class, 'updateRoleMode'])
    ->middleware('auth.session.any')
    ->name('settings.role-mode.update');
Route::delete('/settings/sessions', [SettingController::class, 'destroyOtherSessions'])
    ->middleware('auth.session.any')
    ->name('settings.sessions.destroy-others');
Route::delete('/settings/sessions/{publicId}', [SettingController::class, 'destroySession'])
    ->middleware('auth.session.any')
    ->name('settings.sessions.destroy');
Route::get('/notifications/feed', NotificationFeedController::class)
    ->middleware('auth.session.any')
    ->name('notifications.feed');
Route::post('/push/subscribe', [PushSubscriptionController::class, 'store'])
    ->middleware('auth.session.any')
    ->name('push.subscribe');
Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'destroy'])
    ->middleware('auth.session.any')
    ->name('push.unsubscribe');

Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])
    ->middleware('auth.session:student')
    ->name('student.dashboard');
Route::get('/student/programs', [StudentProgramActivityController::class, 'index'])->middleware('auth.session:student')->name('student.programs.index');
Route::get('/student/programs/attendance-qr', [StudentProgramActivityController::class, 'attendanceQrAccess'])->middleware('auth.session:student')->name('student.programs.attendance-qr.index');
Route::get('/student/programs/{program}/attendance-qr', [StudentProgramActivityController::class, 'attendanceQrPresenter'])->middleware('auth.session:student')->name('student.programs.attendance-qr.presenter');
Route::get('/student/programs/{program}/attendance-qr/live-token', [StudentProgramActivityController::class, 'attendanceQrLiveToken'])->middleware('auth.session:student')->name('student.programs.attendance-qr.live-token');
Route::get('/student/programs/{program}', [StudentProgramActivityController::class, 'show'])->middleware('auth.session:student')->name('student.programs.show');
Route::post('/student/programs/{program}/attendance', [StudentProgramActivityController::class, 'store'])->middleware(['auth.session:student', 'throttle:10,1'])->name('student.programs.attendance.store');
Route::post('/student/programs/{program}/quick-scan', [StudentProgramActivityController::class, 'quickScanAttendance'])->middleware('auth.session:student')->name('student.programs.quick-scan');
Route::get('/student/programs/{program}/survey', [StudentProgramActivityController::class, 'survey'])->middleware('auth.session:student')->name('student.programs.survey');
Route::post('/student/programs/{program}/survey', [StudentProgramActivityController::class, 'storeSurvey'])->middleware(['auth.session:student', 'throttle:15,1'])->name('student.programs.survey.store');
Route::get('/student/certificates/{certificate}/download', [StudentProgramActivityController::class, 'downloadCertificate'])->middleware('auth.session:student')->name('student.certificates.download');
Route::get('/student/scholarship-status', [ScholarshipStatusController::class, 'edit'])
    ->middleware('auth.session:student')
    ->name('student.scholarship-status.form');
Route::post('/student/scholarship-status', [ScholarshipStatusController::class, 'update'])
    ->middleware('auth.session:student')
    ->name('student.scholarship-status.submit');
Route::get('/student/profile', [ProfileController::class, 'show'])
    ->middleware('auth.session:student')
    ->name('student.profile');
Route::post('/student/profile', [ProfileController::class, 'update'])
    ->middleware('auth.session:student')
    ->name('student.profile.update');
Route::post('/student/profile/password', [ProfileController::class, 'updatePassword'])
    ->middleware('auth.session:student')
    ->name('student.profile.password.update');
Route::get('/student/ai-helper', [StudentAiHelperController::class, 'index'])
    ->middleware(['auth.session:student', 'feature.enabled:student_ai_helper'])
    ->name('student.ai-helper.index');
Route::get('/student/ai-helper/conversations/{conversation}', [StudentAiHelperController::class, 'conversation'])
    ->middleware(['auth.session:student', 'feature.enabled:student_ai_helper'])
    ->name('student.ai-helper.conversations.show');
Route::patch('/student/ai-helper/conversations/{conversation}', [StudentAiHelperController::class, 'renameConversation'])
    ->middleware(['auth.session:student', 'feature.enabled:student_ai_helper'])
    ->name('student.ai-helper.conversations.rename');
Route::delete('/student/ai-helper/conversations/{conversation}', [StudentAiHelperController::class, 'deleteConversation'])
    ->middleware(['auth.session:student', 'feature.enabled:student_ai_helper'])
    ->name('student.ai-helper.conversations.delete');
Route::delete('/student/ai-helper/conversations', [StudentAiHelperController::class, 'deleteAllConversations'])
    ->middleware(['auth.session:student', 'feature.enabled:student_ai_helper'])
    ->name('student.ai-helper.conversations.delete-all');
Route::post('/student/ai-helper', [StudentAiHelperController::class, 'ask'])
    ->middleware(['auth.session:student', 'feature.enabled:student_ai_helper', 'throttle:20,1'])
    ->name('student.ai-helper.ask');
Route::get('/student/movements', [StudentMovementController::class, 'index'])
    ->middleware('auth.session:student')
    ->name('student.movements.index');
Route::get('/student/movements/scan', [StudentMovementController::class, 'scan'])
    ->middleware('auth.session:student')
    ->name('student.movements.scan');
Route::post('/student/movements', [StudentMovementController::class, 'store'])
    ->middleware('auth.session:student')
    ->name('student.movements.store');
Route::get('/student/documents', [StudentDocumentController::class, 'index'])
    ->middleware(['auth.session:student', 'feature.enabled:document_centre'])
    ->name('student.documents.index');
Route::post('/student/documents', [StudentDocumentController::class, 'store'])
    ->middleware(['auth.session:student', 'feature.enabled:document_centre', 'throttle:10,1'])
    ->name('student.documents.store');
Route::get('/student/documents/{id}/download', [StudentDocumentController::class, 'download'])
    ->middleware(['auth.session:student', 'feature.enabled:document_centre'])
    ->name('student.documents.download');

Route::get('/student/foodbank', [StudentFoodBankController::class, 'index'])
    ->middleware('auth.session:student')
    ->name('student.foodbank.index');
Route::get('/student/foodbank/claim', [StudentFoodBankController::class, 'claimView'])
    ->middleware('auth.session:student')
    ->name('student.foodbank.claim');
Route::post('/student/foodbank/quick-scan', [StudentFoodBankController::class, 'quickScan'])
    ->middleware('auth.session:student')
    ->name('student.foodbank.quick_scan');

Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware('auth.session:admin')
    ->name('admin.dashboard');
Route::get('/admin/program-participation-points', [ProgramParticipationPointController::class, 'index'])
    ->middleware(['auth.session:admin', 'admin.scope:discipline'])
    ->name('admin.program-participation-points.index');
Route::prefix('/admin/programs')->middleware('auth.session:admin')->name('admin.programs.')->group(function (): void {
    Route::get('/', [ProgramController::class, 'index'])->name('index');
    Route::get('/create', [ProgramController::class, 'create'])->name('create');
    Route::post('/', [ProgramController::class, 'store'])->name('store');
    Route::get('/{program}', [ProgramController::class, 'show'])->name('show');
    Route::get('/{program}/edit', [ProgramController::class, 'edit'])->name('edit');
    Route::put('/{program}', [ProgramController::class, 'update'])->name('update');
    Route::delete('/{program}', [ProgramController::class, 'destroy'])->name('destroy');
    Route::get('/{program}/paperworks/{paperwork}/download', [ProgramController::class, 'download'])->name('paperworks.download');

    Route::get('/{program}/operations', [ProgramOperationController::class, 'operations'])->name('operations');
    Route::get('/{program}/questionnaire', [ProgramOperationController::class, 'questionnaire'])->name('questionnaire');
    Route::post('/{program}/ai-questionnaire', [ProgramOperationController::class, 'generateAiQuestionnaire'])->name('ai-questionnaire');
    Route::post('/{program}/survey/save', [ProgramOperationController::class, 'saveSurvey'])->name('survey.save');
    Route::post('/{program}/survey/publish', [ProgramOperationController::class, 'publishSurvey'])->name('survey.publish');
    Route::post('/{program}/survey/publish-mode', [ProgramOperationController::class, 'publishSurveyMode'])->name('survey.publish-mode');
    Route::post('/{program}/survey/close', [ProgramOperationController::class, 'closeSurvey'])->name('survey.close');
    Route::put('/{program}/questionnaire-setting', [ProgramOperationController::class, 'updateQuestionnaireSetting'])->name('questionnaire-setting.update');
    Route::post('/{program}/attendance/open', [ProgramOperationController::class, 'openAttendance'])->name('attendance.open');
    Route::post('/{program}/attendance/close', [ProgramOperationController::class, 'closeAttendance'])->name('attendance.close');
    Route::post('/{program}/attendance/toggle', [ProgramOperationController::class, 'openAttendance'])->name('attendance.toggle');
    Route::get('/{program}/student-page-permissions', fn (int $program) => redirect()->route('admin.programs.operations', $program))
        ->middleware('admin.scope:program_access.manage')
        ->name('student-page-permissions.index');
    Route::post('/{program}/student-page-permissions', [ProgramOperationController::class, 'grantStudentPagePermission'])
        ->middleware('admin.scope:program_access.manage')
        ->name('student-page-permissions.store');
    Route::delete('/{program}/student-page-permissions/{permission}', [ProgramOperationController::class, 'revokeStudentPagePermission'])
        ->middleware('admin.scope:program_access.manage')
        ->name('student-page-permissions.destroy');
    Route::post('/{program}/report/generate', [ProgramOperationController::class, 'generateReport'])->name('report.generate');
    Route::get('/{program}/report/download/{format}', [ProgramOperationController::class, 'downloadReport'])->name('report.download');
    Route::post('/{program}/report/upload-edited', [ProgramOperationController::class, 'uploadEditedReport'])->name('report.upload-edited');
    Route::post('/{program}/report/submit', [ProgramOperationController::class, 'submitReport'])->name('report.submit');
    Route::post('/{program}/report/review', [ProgramOperationController::class, 'reviewReport'])->name('report.review');
    Route::get('/{program}/presenter', [ProgramOperationController::class, 'presenter'])->name('presenter');
    Route::get('/{program}/live-token', [ProgramOperationController::class, 'liveToken'])->name('live-token');
    Route::post('/{program}/certificates/generate', [ProgramCertificateController::class, 'generate'])->name('certificates.generate');
    Route::post('/{program}/certificates/generate-test', [ProgramCertificateController::class, 'generateTest'])->name('certificates.generate-test');
    Route::delete('/{program}/certificates', [ProgramCertificateController::class, 'destroyForProgram'])->name('certificates.destroy-all');
});
Route::get('/admin/program-certificates', [ProgramCertificateController::class, 'index'])->middleware('auth.session:admin')->name('admin.program-certificates.index');
Route::get('/admin/program-certificates/{certificate}/download', [ProgramCertificateController::class, 'download'])->middleware('auth.session:admin')->name('admin.program-certificates.download');
Route::get('/admin/program-certificate-templates', [ProgramCertificateController::class, 'templates'])->middleware('auth.session:admin')->name('admin.program-certificate-templates.index');
Route::post('/admin/program-certificate-templates/analyze', [ProgramCertificateController::class, 'analyzeTemplate'])->middleware(['auth.session:admin', 'throttle:5,1'])->name('admin.program-certificate-templates.analyze');
Route::post('/admin/program-certificate-templates', [ProgramCertificateController::class, 'storeTemplate'])->middleware('auth.session:admin')->name('admin.program-certificate-templates.store');
Route::get('/admin/program-certificate-templates/{template}/preview', [ProgramCertificateController::class, 'previewTemplate'])->middleware('auth.session:admin')->name('admin.program-certificate-templates.preview');

Route::get('/programs/{program}/qr-checkin', [ProgramOperationController::class, 'publicCheckin'])->name('public.programs.qr_checkin');
Route::post('/programs/{program}/qr-checkin', [ProgramOperationController::class, 'storePublicCheckin'])->name('public.programs.qr_checkin.store');
Route::get('/laptop-borrow/{token}', [LaptopController::class, 'borrowForm'])
    ->whereUuid('token')
    ->name('laptops.borrow');
Route::post('/laptop-borrow/{token}/staff-check', [LaptopController::class, 'checkStaff'])
    ->whereUuid('token')
    ->middleware('throttle:30,1')
    ->name('laptops.borrow.staff-check');
Route::post('/laptop-borrow/{token}', [LaptopController::class, 'borrow'])
    ->whereUuid('token')
    ->middleware('throttle:10,1')
    ->name('laptops.borrow.store');
Route::get('/admin/laptops', [LaptopController::class, 'index'])
    ->middleware(['auth.session:admin', 'admin.scope:laptops.manage'])
    ->name('admin.laptops.index');
Route::get('/admin/laptops/scan', [LaptopController::class, 'scan'])
    ->middleware(['auth.session:admin', 'admin.scope:laptops.use'])
    ->name('admin.laptops.scan');
Route::post('/admin/laptops/scan', [LaptopController::class, 'processScan'])
    ->middleware(['auth.session:admin', 'admin.scope:laptops.use', 'throttle:30,1'])
    ->name('admin.laptops.scan.process');
Route::get('/admin/laptops/print', [LaptopController::class, 'print'])
    ->middleware(['auth.session:admin', 'admin.scope:laptops.manage'])
    ->name('admin.laptops.print');
Route::get('/admin/profile', [AdminProfileController::class, 'show'])
    ->middleware('auth.session:admin')
    ->name('admin.profile');
Route::post('/admin/profile/photo', [AdminProfileController::class, 'updatePhoto'])
    ->middleware('auth.session:admin')
    ->name('admin.profile.photo');
Route::put('/admin/profile/password', [AdminProfileController::class, 'updatePassword'])
    ->middleware(['auth.session:admin', 'throttle:6,1'])
    ->name('admin.profile.password');
Route::get('/admin/documents', [AdminStudentDocumentController::class, 'index'])
    ->middleware(['auth.session:admin', 'admin.scope:documents'])
    ->name('admin.documents.index');
Route::get('/admin/documents/{id}/download', [AdminStudentDocumentController::class, 'download'])
    ->middleware(['auth.session:admin', 'admin.scope:documents'])
    ->name('admin.documents.download');
Route::patch('/admin/documents/{id}/review', [AdminStudentDocumentController::class, 'review'])
    ->middleware(['auth.session:admin', 'admin.scope:documents'])
    ->name('admin.documents.review');
Route::get('/admin/insurance', [StudentInsuranceController::class, 'index'])
    ->middleware(['auth.session:admin', 'admin.scope:insurance'])
    ->name('admin.insurance.index');
Route::get('/admin/insurance/export', [StudentInsuranceController::class, 'export'])
    ->middleware(['auth.session:admin', 'admin.scope:insurance'])
    ->name('admin.insurance.export');
Route::get('/admin/insurance/receipt/{id}/download', [StudentInsuranceController::class, 'downloadReceipt'])
    ->middleware(['auth.session:admin', 'admin.scope:insurance'])
    ->name('admin.insurance.download-receipt');
Route::patch('/admin/insurance/receipt/{id}/review', [StudentInsuranceController::class, 'reviewReceipt'])
    ->middleware(['auth.session:admin', 'admin.scope:insurance'])
    ->name('admin.insurance.review');
Route::post('/admin/insurance/broadcast-notice', [StudentInsuranceController::class, 'broadcastNotice'])
    ->middleware(['auth.session:admin', 'admin.scope:insurance', 'throttle:6,1'])
    ->name('admin.insurance.broadcast-notice');
Route::get('/admin/system-monitoring/live', [AdminDashboardController::class, 'live'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.system-monitoring.live');
Route::get('/admin/reports/monthly', [AdminReportController::class, 'monthly'])
    ->middleware(['auth.session:admin', 'admin.scope:reports'])
    ->name('admin.reports.monthly');
Route::get('/admin/student-scholarship-status', [StudentScholarshipStatusController::class, 'index'])
    ->middleware(['auth.session:admin', 'admin.scope:scholarship'])
    ->name('admin.student-scholarship-status.index');
Route::get('/admin/student-scholarship-status/documents/{id}/download', [StudentScholarshipStatusController::class, 'downloadOfferLetter'])
    ->middleware(['auth.session:admin', 'admin.scope:scholarship'])
    ->name('admin.student-scholarship-status.documents.download');
Route::get('/admin/foodbank', [AdminFoodBankController::class, 'index'])
    ->middleware(['auth.session:admin', 'admin.scope:foodbank'])
    ->name('admin.foodbank.index');
Route::get('/admin/foodbank/export', [AdminFoodBankController::class, 'export'])
    ->middleware(['auth.session:admin', 'admin.scope:foodbank'])
    ->name('admin.foodbank.export');
Route::get('/admin/foodbank/qr', [AdminFoodBankController::class, 'printQr'])
    ->middleware(['auth.session:admin', 'admin.scope:foodbank'])
    ->name('admin.foodbank.qr');
Route::delete('/admin/foodbank/{id}', [AdminFoodBankController::class, 'destroy'])
    ->middleware(['auth.session:admin', 'admin.scope:foodbank'])
    ->name('admin.foodbank.destroy');
Route::get('/admin/ai-helper', [AdminAiHelperController::class, 'index'])
    ->middleware(['auth.session:admin', 'admin.scope:backoffice', 'feature.enabled:admin_ai_helper,system_admin'])
    ->name('admin.ai-helper.index');
Route::get('/admin/ai-helper/conversations/{conversation}', [AdminAiHelperController::class, 'conversation'])
    ->middleware(['auth.session:admin', 'admin.scope:backoffice', 'feature.enabled:admin_ai_helper,system_admin'])
    ->name('admin.ai-helper.conversations.show');
Route::patch('/admin/ai-helper/conversations/{conversation}', [AdminAiHelperController::class, 'renameConversation'])
    ->middleware(['auth.session:admin', 'admin.scope:backoffice', 'feature.enabled:admin_ai_helper,system_admin'])
    ->name('admin.ai-helper.conversations.rename');
Route::patch('/admin/ai-helper/conversations/{conversation}/messages/{message}', [AdminAiHelperController::class, 'updateMessage'])
    ->middleware(['auth.session:admin', 'admin.scope:backoffice', 'feature.enabled:admin_ai_helper,system_admin'])
    ->name('admin.ai-helper.messages.update');
Route::delete('/admin/ai-helper/conversations/{conversation}', [AdminAiHelperController::class, 'deleteConversation'])
    ->middleware(['auth.session:admin', 'admin.scope:backoffice', 'feature.enabled:admin_ai_helper,system_admin'])
    ->name('admin.ai-helper.conversations.delete');
Route::delete('/admin/ai-helper/conversations', [AdminAiHelperController::class, 'deleteAllConversations'])
    ->middleware(['auth.session:admin', 'admin.scope:backoffice', 'feature.enabled:admin_ai_helper,system_admin'])
    ->name('admin.ai-helper.conversations.delete-all');
Route::post('/admin/ai-helper', [AdminAiHelperController::class, 'ask'])
    ->middleware(['auth.session:admin', 'admin.scope:backoffice', 'feature.enabled:admin_ai_helper,system_admin'])
    ->name('admin.ai-helper.ask');
Route::post('/admin/ai-helper/paperwork/generate', [AdminAiHelperController::class, 'generatePaperwork'])
    ->middleware(['auth.session:admin', 'admin.scope:backoffice', 'feature.enabled:admin_ai_helper,system_admin'])
    ->name('admin.ai-helper.paperwork.generate');
Route::get('/admin/ai-helper/paperwork/{id}/download/{format}', [AdminAiHelperController::class, 'downloadPaperwork'])
    ->middleware(['auth.session:admin', 'admin.scope:backoffice', 'feature.enabled:admin_ai_helper,system_admin'])
    ->name('admin.ai-helper.paperwork.download');
Route::delete('/admin/ai-helper/paperwork/{id}', [AdminAiHelperController::class, 'deletePaperwork'])
    ->middleware(['auth.session:admin', 'admin.scope:backoffice', 'feature.enabled:admin_ai_helper,system_admin'])
    ->name('admin.ai-helper.paperwork.delete');
Route::get('/admin/ai-helper/paperwork/history', [AdminAiHelperController::class, 'paperworkHistory'])
    ->middleware(['auth.session:admin', 'admin.scope:backoffice', 'feature.enabled:admin_ai_helper,system_admin'])
    ->name('admin.ai-helper.paperwork.history');
Route::get('/admin/ai-helper/reports/history', [AdminAiHelperController::class, 'reportHistory'])
    ->middleware(['auth.session:admin', 'admin.scope:backoffice', 'feature.enabled:admin_ai_helper,system_admin'])
    ->name('admin.ai-helper.reports.history');
Route::prefix('/lecturer/ai-helper')->middleware(['auth.session:admin', 'admin.scope:reports', 'feature.enabled:lecturer_ai_helper'])->name('lecturer.ai-helper.')->group(function (): void {
    Route::get('/', [AdminAiHelperController::class, 'index'])->name('index');
    Route::get('/conversations/{conversation}', [AdminAiHelperController::class, 'conversation'])->name('conversations.show');
    Route::patch('/conversations/{conversation}', [AdminAiHelperController::class, 'renameConversation'])->name('conversations.rename');
    Route::patch('/conversations/{conversation}/messages/{message}', [AdminAiHelperController::class, 'updateMessage'])->name('messages.update');
    Route::delete('/conversations/{conversation}', [AdminAiHelperController::class, 'deleteConversation'])->name('conversations.delete');
    Route::delete('/conversations', [AdminAiHelperController::class, 'deleteAllConversations'])->name('conversations.delete-all');
    Route::post('/', [AdminAiHelperController::class, 'ask'])->middleware('throttle:20,1')->name('ask');
    Route::post('/paperwork/generate', [AdminAiHelperController::class, 'generatePaperwork'])->name('paperwork.generate');
    Route::get('/paperwork/{id}/download/{format}', [AdminAiHelperController::class, 'downloadPaperwork'])->name('paperwork.download');
    Route::delete('/paperwork/{id}', [AdminAiHelperController::class, 'deletePaperwork'])->name('paperwork.delete');
    Route::get('/paperwork/history', [AdminAiHelperController::class, 'paperworkHistory'])->name('paperwork.history');
    Route::get('/reports/history', [AdminAiHelperController::class, 'reportHistory'])->name('reports.history');
});
Route::get('/admin/movements', [AdminMovementController::class, 'index'])
    ->middleware(['auth.session:admin', 'admin.scope:movement'])
    ->name('admin.movements.index');
Route::get('/admin/movements/export', [AdminMovementController::class, 'export'])
    ->middleware(['auth.session:admin', 'admin.scope:movement'])
    ->name('admin.movements.export');
Route::get('/admin/movements/outside', [AdminMovementController::class, 'outside'])
    ->middleware(['auth.session:admin', 'admin.scope:movement'])
    ->name('admin.movements.outside');
Route::get('/admin/movements/violations', [AdminMovementController::class, 'violations'])
    ->middleware(['auth.session:admin', 'admin.scope:movement'])
    ->name('admin.movements.violations');
Route::get('/admin/movements/qr', [AdminMovementController::class, 'qr'])
    ->middleware(['auth.session:admin', 'admin.scope:movement'])
    ->name('admin.movements.qr');
Route::get('/admin/movements/qr/status', [AdminMovementController::class, 'qrStatus'])
    ->middleware(['auth.session:admin', 'admin.scope:movement'])
    ->name('admin.movements.qr.status');
Route::get('/admin/movements/qr/display', [AdminMovementController::class, 'qrDisplay'])
    ->middleware(['auth.session:admin', 'admin.scope:movement'])
    ->name('admin.movements.qr.display');
Route::post('/admin/movements/qr', [AdminMovementController::class, 'updateQr'])
    ->middleware(['auth.session:admin', 'admin.scope:movement'])
    ->name('admin.movements.qr.update');
Route::get('/admin/movements/settings', [AdminMovementController::class, 'settings'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.movements.settings');
Route::post('/admin/movements/settings', [AdminMovementController::class, 'updateSettings'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.movements.settings.update');

Route::get('/admin/admin-users', [AdminUserController::class, 'index'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.admin-users.index');

Route::get('/admin/maintenance', [MaintenanceController::class, 'index'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.maintenance.index');

Route::post('/admin/maintenance', [MaintenanceController::class, 'update'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.maintenance.update');
Route::post('/admin/maintenance/push/test', [MaintenanceController::class, 'testPush'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.maintenance.push.test');
Route::post('/admin/maintenance/email/test', [MaintenanceController::class, 'testEmail'])
    ->middleware(['auth.session:admin', 'admin.scope:system', 'throttle:5,10'])
    ->name('admin.maintenance.email.test');
Route::post('/admin/maintenance/push/broadcast', [MaintenanceController::class, 'broadcastMaintenance'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.maintenance.push.broadcast');
Route::get('/admin/features', [FeatureController::class, 'index'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.features.index');
Route::patch('/admin/features/{feature}', [FeatureController::class, 'update'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.features.update');
Route::patch('/admin/system-settings/session-lifetime', [FeatureController::class, 'updateSessionLifetime'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.system-settings.session-lifetime.update');

Route::get('/admin/admin-users/create', [AdminUserController::class, 'create'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.admin-users.create');
Route::post('/admin/admin-users', [AdminUserController::class, 'store'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.admin-users.store');
Route::get('/admin/admin-users/{id}/edit', [AdminUserController::class, 'edit'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.admin-users.edit');
Route::put('/admin/admin-users/{id}', [AdminUserController::class, 'update'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.admin-users.update');
Route::post('/admin/admin-users/{id}/reset-password', [AdminUserController::class, 'resetPassword'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.admin-users.reset-password');
Route::delete('/admin/admin-users/{id}', [AdminUserController::class, 'destroy'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.admin-users.destroy');

Route::prefix('/admin/staff')->middleware(['auth.session:admin', 'admin.scope:staff.manage'])->name('admin.staff.')->group(function (): void {
    Route::get('/', [StaffManagementController::class, 'index'])->name('index');
    Route::get('/create', [StaffManagementController::class, 'create'])->name('create');
    Route::post('/', [StaffManagementController::class, 'store'])->name('store');
    Route::post('/import', [StaffManagementController::class, 'import'])->name('import');
    Route::get('/{id}/edit', [StaffManagementController::class, 'edit'])->name('edit');
    Route::put('/{id}', [StaffManagementController::class, 'update'])->name('update');
    Route::post('/{id}/reset-password', [StaffManagementController::class, 'resetPassword'])->name('reset-password');
    Route::delete('/{id}', [StaffManagementController::class, 'destroy'])->name('destroy');
});

Route::prefix('/admin/guards')->middleware(['auth.session:admin', 'admin.scope:guards.manage', 'lecturer.page:guard_management'])->name('admin.guards.')->group(function (): void {
    Route::get('/', [GuardManagementController::class, 'index'])->name('index');
    Route::get('/create', [GuardManagementController::class, 'create'])->name('create');
    Route::post('/', [GuardManagementController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [GuardManagementController::class, 'edit'])->name('edit');
    Route::put('/{id}', [GuardManagementController::class, 'update'])->name('update');
    Route::post('/{id}/reset-password', [GuardManagementController::class, 'resetPassword'])->name('reset-password');
    Route::delete('/{id}', [GuardManagementController::class, 'destroy'])->name('destroy');
});
Route::get('/admin/bug-reports', [AdminBugReportController::class, 'index'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.bug-reports.index');
Route::put('/admin/bug-reports/{id}', [AdminBugReportController::class, 'update'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.bug-reports.update');
Route::delete('/admin/bug-reports/{id}', [AdminBugReportController::class, 'destroy'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.bug-reports.destroy');
Route::get('/admin/active-visitors', [ActiveVisitorController::class, 'index'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.active-visitors.index');
Route::delete('/admin/active-visitors', [ActiveVisitorController::class, 'clear'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.active-visitors.clear');
Route::delete('/admin/active-visitors/{id}', [ActiveVisitorController::class, 'destroy'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.active-visitors.destroy');

Route::get('/admin/students', [StudentController::class, 'index'])
    ->middleware(['auth.session:admin', 'admin.scope:students.list'])
    ->name('admin.students.index');
Route::get('/admin/students/search', [StudentController::class, 'search'])
    ->middleware(['auth.session:admin', 'admin.scope:students.lookup'])
    ->name('admin.students.search');
Route::get('/admin/students/export', [StudentController::class, 'export'])
    ->middleware(['auth.session:admin', 'admin.scope:students.export'])
    ->name('admin.students.export');
Route::get('/admin/students/create', [StudentController::class, 'create'])
    ->middleware(['auth.session:admin', 'admin.scope:students.manage'])
    ->name('admin.students.create');
Route::post('/admin/students', [StudentController::class, 'store'])
    ->middleware(['auth.session:admin', 'admin.scope:students.manage'])
    ->name('admin.students.store');
Route::post('/admin/students/import', [StudentController::class, 'import'])
    ->middleware(['auth.session:admin', 'admin.scope:students.manage'])
    ->name('admin.students.import');
Route::get('/admin/students/{id}', [StudentController::class, 'show'])
    ->middleware(['auth.session:admin', 'admin.scope:students.sensitive'])
    ->name('admin.students.show');
Route::get('/admin/students/{id}/edit', [StudentController::class, 'edit'])
    ->middleware(['auth.session:admin', 'admin.scope:students.manage'])
    ->name('admin.students.edit');
Route::put('/admin/students/{id}', [StudentController::class, 'update'])
    ->middleware(['auth.session:admin', 'admin.scope:students.manage'])
    ->name('admin.students.update');
Route::delete('/admin/students/{id}', [StudentController::class, 'destroy'])
    ->middleware(['auth.session:admin', 'admin.scope:students.manage'])
    ->name('admin.students.destroy');
Route::delete('/admin/students', [StudentController::class, 'destroyAll'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.students.destroy-all');
Route::delete('/admin/students/photos/delete-all', [StudentController::class, 'destroyAllPhotos'])
    ->middleware(['auth.session:admin', 'admin.scope:students.manage'])
    ->name('admin.students.photos.destroy-all');
Route::delete('/admin/students/{id}/photo', [StudentController::class, 'rejectPhoto'])
    ->middleware(['auth.session:admin', 'admin.scope:students.manage'])
    ->name('admin.students.photos.reject');
Route::patch('/admin/students/{id}/photo/approve', [StudentController::class, 'approvePhoto'])
    ->middleware(['auth.session:admin', 'admin.scope:students.manage'])
    ->name('admin.students.photos.approve');
Route::post('/admin/students/{id}/reset-password', [StudentController::class, 'resetPassword'])
    ->middleware(['auth.session:admin', 'admin.scope:students.manage'])
    ->name('admin.students.reset-password');

Route::get('/admin/scholarships', [ScholarshipController::class, 'index'])
    ->middleware(['auth.session:admin', 'admin.scope:scholarship'])
    ->name('admin.scholarships.index');
Route::get('/admin/welfare', [ScholarshipController::class, 'welfare'])
    ->middleware(['auth.session:admin', 'admin.scope:scholarship'])
    ->name('admin.welfare.index');
Route::get('/admin/scholarships/export', [ScholarshipController::class, 'export'])
    ->middleware(['auth.session:admin', 'admin.scope:scholarship'])
    ->name('admin.scholarships.export');
Route::get('/admin/scholarships/b40-tvet', [ScholarshipController::class, 'b40Tvet'])
    ->middleware(['auth.session:admin', 'admin.scope:scholarship'])
    ->name('admin.scholarships.b40-tvet');
Route::post('/admin/scholarships/b40-tvet/import', [ScholarshipController::class, 'importB40Tvet'])
    ->middleware(['auth.session:admin', 'admin.scope:scholarship'])
    ->name('admin.scholarships.b40-tvet.import');
Route::get('/admin/scholarships/b40-tvet/export', [ScholarshipController::class, 'exportB40Tvet'])
    ->middleware(['auth.session:admin', 'admin.scope:scholarship'])
    ->name('admin.scholarships.b40-tvet.export');
Route::get('/admin/scholarships/create', [ScholarshipController::class, 'create'])
    ->middleware(['auth.session:admin', 'admin.scope:scholarship'])
    ->name('admin.scholarships.create');
Route::post('/admin/scholarships', [ScholarshipController::class, 'store'])
    ->middleware(['auth.session:admin', 'admin.scope:scholarship'])
    ->name('admin.scholarships.store');
Route::get('/admin/scholarships/{id}/edit', [ScholarshipController::class, 'edit'])
    ->middleware(['auth.session:admin', 'admin.scope:scholarship'])
    ->name('admin.scholarships.edit');
Route::put('/admin/scholarships/{id}', [ScholarshipController::class, 'update'])
    ->middleware(['auth.session:admin', 'admin.scope:scholarship'])
    ->name('admin.scholarships.update');
Route::delete('/admin/scholarships/{id}', [ScholarshipController::class, 'destroy'])
    ->middleware(['auth.session:admin', 'admin.scope:scholarship'])
    ->name('admin.scholarships.destroy');

Route::get('/admin/foodbank', [AdminFoodBankController::class, 'index'])
    ->middleware(['auth.session:admin', 'admin.scope:scholarship'])
    ->name('admin.foodbank.index');
Route::get('/admin/foodbank/export', [AdminFoodBankController::class, 'export'])
    ->middleware(['auth.session:admin', 'admin.scope:scholarship'])
    ->name('admin.foodbank.export');
Route::get('/admin/foodbank/qr', [AdminFoodBankController::class, 'qr'])
    ->middleware(['auth.session:admin', 'admin.scope:scholarship'])
    ->name('admin.foodbank.qr');
Route::delete('/admin/foodbank/{id}', [AdminFoodBankController::class, 'destroy'])
    ->middleware(['auth.session:admin', 'admin.scope:scholarship'])
    ->name('admin.foodbank.destroy');

Route::get('/admin/scholarship-announcements', function (Request $request) {
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:200'],
        'type' => ['nullable', Rule::in(['scholarship', 'welfare', 'general'])],
    ]);

    $query = DB::table('scholarship_announcements')
        ->join('admins', 'admins.id', '=', 'scholarship_announcements.admin_id')
        ->select(
            'scholarship_announcements.id',
            'scholarship_announcements.title',
            'scholarship_announcements.body',
            'scholarship_announcements.type',
            'scholarship_announcements.link_url',
            'scholarship_announcements.link_label',
            'scholarship_announcements.poster_image',
            'scholarship_announcements.contact_email',
            'scholarship_announcements.contact_phone',
            'scholarship_announcements.created_at',
            'admins.full_name as admin_name'
        );


    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('scholarship_announcements.title', 'like', "%{$q}%")
                ->orWhere('scholarship_announcements.body', 'like', "%{$q}%");
        });
    }

    if (!empty($filters['type'])) {
        $query->where('scholarship_announcements.type', $filters['type']);
    }

    $announcements = $query
        ->orderByDesc('scholarship_announcements.created_at')
        ->paginate(12)
        ->withQueryString();

    return view('admin.scholarship_announcements.index', compact('announcements', 'filters'));
})->middleware(['auth.session:admin', 'admin.scope:scholarship'])->name('admin.scholarship-announcements.index');

Route::get('/admin/scholarship-announcements/export', function (Request $request) {
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:200'],
        'type' => ['nullable', Rule::in(['scholarship', 'welfare', 'general'])],
    ]);

    $query = DB::table('scholarship_announcements')
        ->join('admins', 'admins.id', '=', 'scholarship_announcements.admin_id')
        ->select(
            'scholarship_announcements.id',
            'scholarship_announcements.title',
            'scholarship_announcements.body',
            'scholarship_announcements.type',
            'scholarship_announcements.link_url',
            'scholarship_announcements.link_label',
            'scholarship_announcements.created_at',
            'admins.full_name as admin_name'
        );

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('scholarship_announcements.title', 'like', "%{$q}%")
                ->orWhere('scholarship_announcements.body', 'like', "%{$q}%");
        });
    }

    if (!empty($filters['type'])) {
        $query->where('scholarship_announcements.type', $filters['type']);
    }

    $rows = $query
        ->orderByDesc('scholarship_announcements.created_at')
        ->get()
        ->map(fn ($item) => [
            $item->id,
            $item->title,
            $item->type,
            $item->body,
            $item->link_url ?? '',
            $item->link_label ?? '',
            $item->admin_name,
            $item->created_at,
        ]);

    return downloadCsv(
        'scholarship_announcements_' . now()->format('Ymd_His') . '.csv',
        ['ID', 'Tajuk', 'Jenis', 'Penerangan', 'Link URL', 'Link Label', 'Dicipta Oleh', 'Tarikh'],
        $rows
    );
})->middleware(['auth.session:admin', 'admin.scope:scholarship'])->name('admin.scholarship-announcements.export');

Route::get('/admin/scholarship-announcements/create', function () {
    return view('admin.scholarship_announcements.create');
})->middleware(['auth.session:admin', 'admin.scope:scholarship'])->name('admin.scholarship-announcements.create');

Route::post('/admin/scholarship-announcements', function (Request $request) {
    $validated = $request->validate([
        'title'         => ['required', 'string', 'max:200'],
        'body'          => ['required', 'string'],
        'type'          => ['required', Rule::in(['scholarship', 'welfare', 'general'])],
        'link_url'      => ['nullable', 'url', 'max:500'],
        'link_label'    => ['nullable', 'string', 'max:100'],
        'contact_email' => ['nullable', 'email', 'max:200'],
        'contact_phone' => ['nullable', 'string', 'max:30'],
        'poster_image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
    ]);

    $posterPath = null;
    if ($request->hasFile('poster_image')) {
        $posterPath = $request->file('poster_image')->store('scholarship-posters', 'public');
    }

    $announcementId = DB::table('scholarship_announcements')->insertGetId([
        'admin_id'      => session('auth_user.id'),
        'title'         => $validated['title'],
        'body'          => $validated['body'],
        'type'          => $validated['type'],
        'link_url'      => $validated['link_url'] ?? null,
        'link_label'    => $validated['link_label'] ?? null,
        'poster_image'  => $posterPath,
        'contact_email' => $validated['contact_email'] ?? null,
        'contact_phone' => $validated['contact_phone'] ?? null,
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);

    myhepSendPushToAllStudents([
        'category' => 'scholarship',
        'title' => 'New scholarship announcement',
        'body' => Str::limit($validated['title'], 90),
        'url' => route('student.scholarships.announcements'),
        'tag' => 'scholarship-announcement-' . $announcementId,
    ]);

    return redirect()->route('admin.scholarship-announcements.index')
        ->with('success', __('messages.scholarship_announcement_added'));
})->middleware(['auth.session:admin', 'admin.scope:scholarship'])->name('admin.scholarship-announcements.store');

Route::get('/admin/scholarship-announcements/{id}/edit', function (int $id) {
    $announcement = DB::table('scholarship_announcements')->where('id', $id)->first();
    if (!$announcement) {
        return redirect()->route('admin.scholarship-announcements.index')
            ->withErrors(['announcement' => 'Pengumuman tidak dijumpai.']);
    }

    return view('admin.scholarship_announcements.edit', compact('announcement'));
})->middleware(['auth.session:admin', 'admin.scope:scholarship'])->name('admin.scholarship-announcements.edit');

Route::put('/admin/scholarship-announcements/{id}', function (Request $request, int $id) {
    $announcement = DB::table('scholarship_announcements')->where('id', $id)->first();
    if (!$announcement) {
        return redirect()->route('admin.scholarship-announcements.index')
            ->withErrors(['announcement' => 'Pengumuman tidak dijumpai.']);
    }

    $validated = $request->validate([
        'title'         => ['required', 'string', 'max:200'],
        'body'          => ['required', 'string'],
        'type'          => ['required', Rule::in(['scholarship', 'welfare', 'general'])],
        'link_url'      => ['nullable', 'url', 'max:500'],
        'link_label'    => ['nullable', 'string', 'max:100'],
        'contact_email' => ['nullable', 'email', 'max:200'],
        'contact_phone' => ['nullable', 'string', 'max:30'],
        'poster_image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        'remove_poster' => ['nullable', 'boolean'],
    ]);

    $posterPath = $announcement->poster_image;

    if ($request->boolean('remove_poster')) {
        if ($posterPath) {
            Storage::disk('public')->delete($posterPath);
        }
        $posterPath = null;
    }

    if ($request->hasFile('poster_image')) {
        if ($posterPath) {
            Storage::disk('public')->delete($posterPath);
        }
        $posterPath = $request->file('poster_image')->store('scholarship-posters', 'public');
    }

    DB::table('scholarship_announcements')
        ->where('id', $id)
        ->update([
            'title'         => $validated['title'],
            'body'          => $validated['body'],
            'type'          => $validated['type'],
            'link_url'      => $validated['link_url'] ?? null,
            'link_label'    => $validated['link_label'] ?? null,
            'poster_image'  => $posterPath,
            'contact_email' => $validated['contact_email'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'updated_at'    => now(),
        ]);

    return redirect()->route('admin.scholarship-announcements.index')
        ->with('success', __('messages.scholarship_announcement_updated'));
})->middleware(['auth.session:admin', 'admin.scope:scholarship'])->name('admin.scholarship-announcements.update');

Route::delete('/admin/scholarship-announcements/{id}', function (int $id) {
    $announcement = DB::table('scholarship_announcements')->where('id', $id)->first();
    if (!$announcement) {
        return redirect()->route('admin.scholarship-announcements.index')
            ->withErrors(['announcement' => 'Pengumuman tidak dijumpai.']);
    }
    if ($announcement->poster_image) {
        Storage::disk('public')->delete($announcement->poster_image);
    }
    DB::table('scholarship_announcements')->where('id', $id)->delete();
    auditLog('scholarship_announcements.delete', 'scholarship_announcements', $id, 'Padam pengumuman scholarship');

    return redirect()->route('admin.scholarship-announcements.index')
        ->with('success', __('messages.scholarship_announcement_deleted'));
})->middleware(['auth.session:admin', 'admin.scope:scholarship'])->name('admin.scholarship-announcements.destroy');

Route::get('/admin/discipline-announcements', function (Request $request) {
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:200'],
    ]);

    $query = DB::table('discipline_announcements')
        ->join('admins', 'admins.id', '=', 'discipline_announcements.admin_id')
        ->select(
            'discipline_announcements.id',
            'discipline_announcements.title',
            'discipline_announcements.body',
            'discipline_announcements.created_at',
            'admins.full_name as admin_name'
        );

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('discipline_announcements.title', 'like', "%{$q}%")
                ->orWhere('discipline_announcements.body', 'like', "%{$q}%");
        });
    }

    $announcements = $query
        ->orderByDesc('discipline_announcements.created_at')
        ->paginate(12)
        ->withQueryString();

    return view('admin.discipline_announcements.index', compact('announcements', 'filters'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.discipline-announcements.index');

Route::get('/admin/discipline-announcements/export', function (Request $request) {
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:200'],
    ]);

    $query = DB::table('discipline_announcements')
        ->join('admins', 'admins.id', '=', 'discipline_announcements.admin_id')
        ->select(
            'discipline_announcements.id',
            'discipline_announcements.title',
            'discipline_announcements.body',
            'discipline_announcements.created_at',
            'admins.full_name as admin_name'
        );

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('discipline_announcements.title', 'like', "%{$q}%")
                ->orWhere('discipline_announcements.body', 'like', "%{$q}%");
        });
    }

    $rows = $query
        ->orderByDesc('discipline_announcements.created_at')
        ->get()
        ->map(fn ($item) => [
            $item->id,
            $item->title,
            $item->body,
            $item->admin_name,
            $item->created_at,
        ]);

    return downloadCsv(
        'discipline_announcements_' . now()->format('Ymd_His') . '.csv',
        ['ID', 'Tajuk', 'Penerangan', 'Dicipta Oleh', 'Tarikh'],
        $rows
    );
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.discipline-announcements.export');

Route::get('/admin/discipline-announcements/create', function () {
    return view('admin.discipline_announcements.create');
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.discipline-announcements.create');

Route::post('/admin/discipline-announcements', function (Request $request) {
    $validated = $request->validate([
        'title' => ['required', 'string', 'max:200'],
        'body' => ['required', 'string'],
    ]);

    $announcementId = DB::table('discipline_announcements')->insertGetId([
        'admin_id' => session('auth_user.id'),
        'title' => $validated['title'],
        'body' => $validated['body'],
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    myhepSendPushToAllStudents([
        'category' => 'discipline',
        'title' => 'New discipline announcement',
        'body' => Str::limit($validated['title'], 90),
        'url' => route('student.discipline-announcements.index'),
        'tag' => 'discipline-announcement-' . $announcementId,
    ]);

    return redirect()->route('admin.discipline-announcements.index')
        ->with('success', __('messages.discipline_announcement_added'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.discipline-announcements.store');

Route::get('/admin/discipline-announcements/{id}/edit', function (int $id) {
    $announcement = DB::table('discipline_announcements')->where('id', $id)->first();
    if (!$announcement) {
        return redirect()->route('admin.discipline-announcements.index')
            ->withErrors(['announcement' => 'Pengumuman tidak dijumpai.']);
    }

    return view('admin.discipline_announcements.edit', compact('announcement'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.discipline-announcements.edit');

Route::put('/admin/discipline-announcements/{id}', function (Request $request, int $id) {
    $announcement = DB::table('discipline_announcements')->where('id', $id)->first();
    if (!$announcement) {
        return redirect()->route('admin.discipline-announcements.index')
            ->withErrors(['announcement' => 'Pengumuman tidak dijumpai.']);
    }

    $validated = $request->validate([
        'title' => ['required', 'string', 'max:200'],
        'body' => ['required', 'string'],
    ]);

    DB::table('discipline_announcements')
        ->where('id', $id)
        ->update([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'updated_at' => now(),
        ]);

    return redirect()->route('admin.discipline-announcements.index')
        ->with('success', __('messages.discipline_announcement_updated'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.discipline-announcements.update');

Route::delete('/admin/discipline-announcements/{id}', function (int $id) {
    $deleted = DB::table('discipline_announcements')->where('id', $id)->delete();
    if (!$deleted) {
        return redirect()->route('admin.discipline-announcements.index')
            ->withErrors(['announcement' => 'Pengumuman tidak dijumpai.']);
    }
    auditLog('discipline_announcements.delete', 'discipline_announcements', $id, 'Padam pengumuman disiplin');

    return redirect()->route('admin.discipline-announcements.index')
        ->with('success', __('messages.discipline_announcement_deleted'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.discipline-announcements.destroy');

Route::get('/admin/rules', function (Request $request) {
    $categories = ruleCategoryOptions();
    $categoryIds = $categories->pluck('id')->map(fn ($id) => (int) $id)->all();

    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:150'],
        'category_id' => ['nullable', Rule::in($categoryIds)],
    ]);

    $query = DB::table('rules')
        ->join('rule_categories', 'rule_categories.id', '=', 'rules.category_id')
        ->leftJoin('admins', 'admins.id', '=', 'rules.updated_by')
        ->select(
            'rules.id',
            'rules.title',
            'rules.category_id',
            'rule_categories.name as category_name',
            'rules.description',
            'rules.updated_at',
            'admins.full_name as updated_by_name'
        );

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('rules.title', 'like', "%{$q}%")
                ->orWhere('rules.description', 'like', "%{$q}%");
        });
    }

    if (!empty($filters['category_id'])) {
        $query->where('rules.category_id', (int) $filters['category_id']);
    }

    $rules = $query
        ->orderBy('rule_categories.name')
        ->orderBy('rules.title')
        ->paginate(15)
        ->withQueryString();

    return view('admin.rules.index', compact('rules', 'filters', 'categories'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.rules.index');

Route::get('/admin/rules/export', function (Request $request) {
    $categories = ruleCategoryOptions();
    $categoryIds = $categories->pluck('id')->map(fn ($id) => (int) $id)->all();

    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:150'],
        'category_id' => ['nullable', Rule::in($categoryIds)],
    ]);

    $query = DB::table('rules')
        ->join('rule_categories', 'rule_categories.id', '=', 'rules.category_id')
        ->leftJoin('admins', 'admins.id', '=', 'rules.updated_by')
        ->select(
            'rules.id',
            'rules.title',
            'rule_categories.name as category_name',
            'rules.description',
            'admins.full_name as updated_by_name',
            'rules.updated_at'
        );

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('rules.title', 'like', "%{$q}%")
                ->orWhere('rules.description', 'like', "%{$q}%");
        });
    }

    if (!empty($filters['category_id'])) {
        $query->where('rules.category_id', (int) $filters['category_id']);
    }

    $rows = $query
        ->orderBy('rule_categories.name')
        ->orderBy('rules.title')
        ->get()
        ->map(fn ($rule) => [
            $rule->id,
            $rule->title,
            $rule->category_name,
            $rule->description,
            $rule->updated_by_name ?? '',
            $rule->updated_at,
        ]);

    return downloadCsv(
        'rules_' . now()->format('Ymd_His') . '.csv',
        ['ID', 'Tajuk', 'Kategori', 'Penerangan', 'Kemaskini Oleh', 'Tarikh Kemaskini'],
        $rows
    );
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.rules.export');

Route::get('/admin/rules/create', function () {
    $categories = ruleCategoryOptions();
    return view('admin.rules.create', compact('categories'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.rules.create');

Route::post('/admin/rules', function (Request $request) {
    $categories = ruleCategoryOptions();
    $categoryIds = $categories->pluck('id')->map(fn ($id) => (int) $id)->all();
    $validated = $request->validate([
        'title' => ['required', 'string', 'max:200'],
        'category_id' => ['required', Rule::in($categoryIds)],
        'description' => ['required', 'string'],
    ]);

    $selectedCategory = $categories->firstWhere('id', (int) $validated['category_id']);

    DB::table('rules')->insert([
        'title' => $validated['title'],
        'category' => $selectedCategory?->name ?? 'General',
        'category_id' => $validated['category_id'],
        'description' => $validated['description'],
        'updated_by' => session('auth_user.id'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()->route('admin.rules.index')
        ->with('success', __('messages.rule_added'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.rules.store');

Route::get('/admin/rules/{id}/edit', function (int $id) {
    $rule = DB::table('rules')->where('id', $id)->first();
    if (!$rule) {
        return redirect()->route('admin.rules.index')
            ->withErrors(['rule' => 'Peraturan tidak dijumpai.']);
    }

    $categories = ruleCategoryOptions();
    return view('admin.rules.edit', compact('rule', 'categories'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.rules.edit');

Route::put('/admin/rules/{id}', function (Request $request, int $id) {
    $rule = DB::table('rules')->where('id', $id)->first();
    if (!$rule) {
        return redirect()->route('admin.rules.index')
            ->withErrors(['rule' => 'Peraturan tidak dijumpai.']);
    }

    $categories = ruleCategoryOptions();
    $categoryIds = $categories->pluck('id')->map(fn ($id) => (int) $id)->all();
    $validated = $request->validate([
        'title' => ['required', 'string', 'max:200'],
        'category_id' => ['required', Rule::in($categoryIds)],
        'description' => ['required', 'string'],
    ]);

    $selectedCategory = $categories->firstWhere('id', (int) $validated['category_id']);

    DB::table('rules')
        ->where('id', $id)
        ->update([
            'title' => $validated['title'],
            'category' => $selectedCategory?->name ?? 'General',
            'category_id' => $validated['category_id'],
            'description' => $validated['description'],
            'updated_by' => session('auth_user.id'),
            'updated_at' => now(),
        ]);

    return redirect()->route('admin.rules.index')
        ->with('success', __('messages.rule_updated'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.rules.update');

Route::delete('/admin/rules/{id}', function (int $id) {
    $deleted = DB::table('rules')->where('id', $id)->delete();
    if (!$deleted) {
        return redirect()->route('admin.rules.index')
            ->withErrors(['rule' => 'Peraturan tidak dijumpai.']);
    }

    return redirect()->route('admin.rules.index')
        ->with('success', __('messages.rule_deleted'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.rules.destroy');

Route::get('/admin/offenses', function (Request $request) {
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:150'],
        'status' => ['nullable', Rule::in(['unpaid', 'applied', 'paid'])],
        'date_from' => ['nullable', 'date'],
        'date_to' => ['nullable', 'date'],
    ]);

    $query = DB::table('offenses')
        ->join('students', 'students.id', '=', 'offenses.student_id')
        ->select(
            'offenses.id',
            'offenses.offense_date',
            'offenses.offense_time',
            'offenses.place',
            'offenses.evidence_photo_path',
            'offenses.fine_amount',
            'offenses.status',
            'students.full_name as student_name',
            'students.matric_no'
        );

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('students.full_name', 'like', "%{$q}%")
                ->orWhere('students.matric_no', 'like', "%{$q}%")
                ->orWhere('students.ic_no', 'like', "%{$q}%")
                ->orWhere('offenses.place', 'like', "%{$q}%");
        });
    }

    if (!empty($filters['status'])) {
        $query->where('offenses.status', $filters['status']);
    }

    if (!empty($filters['date_from'])) {
        $query->whereDate('offenses.offense_date', '>=', $filters['date_from']);
    }

    if (!empty($filters['date_to'])) {
        $query->whereDate('offenses.offense_date', '<=', $filters['date_to']);
    }

    $offenses = $query
        ->orderByDesc('offenses.offense_date')
        ->orderByDesc('offenses.offense_time')
        ->paginate(15)
        ->withQueryString();

    myhepAttachOffenseEvidence($offenses->items());
    $offenseIds = collect($offenses->items())->pluck('id')->map(fn ($id) => (int) $id)->all();
    $latestReceiptsByOffense = collect();

    if ($offenseIds !== []) {
        $latestReceiptsByOffense = DB::table('fine_payment_applications')
            ->whereIn('offense_id', $offenseIds)
            ->whereNotNull('receipt_path')
            ->select('offense_id', 'receipt_path', 'created_at', 'status')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('offense_id')
            ->map(fn ($rows) => $rows->first());

        foreach ($offenses->items() as $offense) {
            $offense->payment_receipt = $latestReceiptsByOffense->get((int) $offense->id);
        }
    }

    return view('admin.offenses.index', compact('offenses', 'filters'));
})->middleware(['auth.session:admin', 'admin.scope:offense.register', 'lecturer.page:offense_list'])->name('admin.offenses.index');

Route::get('/admin/offenses/export', function (Request $request) {
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:150'],
        'status' => ['nullable', Rule::in(['unpaid', 'applied', 'paid'])],
        'date_from' => ['nullable', 'date'],
        'date_to' => ['nullable', 'date'],
    ]);

    $query = DB::table('offenses')
        ->join('students', 'students.id', '=', 'offenses.student_id')
        ->select(
            'offenses.id',
            'students.full_name as student_name',
            'students.matric_no',
            'offenses.offense_date',
            'offenses.offense_time',
            'offenses.place',
            'offenses.evidence_photo_path',
            'offenses.fine_amount',
            'offenses.status'
        );

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('students.full_name', 'like', "%{$q}%")
                ->orWhere('students.matric_no', 'like', "%{$q}%")
                ->orWhere('offenses.place', 'like', "%{$q}%");
        });
    }

    if (!empty($filters['status'])) {
        $query->where('offenses.status', $filters['status']);
    }

    if (!empty($filters['date_from'])) {
        $query->whereDate('offenses.offense_date', '>=', $filters['date_from']);
    }

    if (!empty($filters['date_to'])) {
        $query->whereDate('offenses.offense_date', '<=', $filters['date_to']);
    }

    $rows = $query
        ->orderByDesc('offenses.offense_date')
        ->orderByDesc('offenses.offense_time')
        ->get()
        ->map(function ($offense) {
            return [
                $offense->id,
                $offense->student_name,
                $offense->matric_no,
                $offense->offense_date,
                $offense->offense_time,
                $offense->place,
                $offense->evidence_photo_path ? 'ada' : 'tiada',
                number_format((float) $offense->fine_amount, 2, '.', ''),
                $offense->status,
            ];
        });

    return downloadCsv(
        'offenses_' . now()->format('Ymd_His') . '.csv',
        ['ID', 'Pelajar', 'No Matrik', 'Tarikh', 'Masa', 'Tempat', 'Bukti Gambar', 'Denda (RM)', 'Status'],
        $rows
    );
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.offenses.export');

Route::get('/admin/offenses/{id}/print', function (int $id) {
    $offense = DB::table('offenses')
        ->join('students', 'students.id', '=', 'offenses.student_id')
        ->leftJoin('admins', 'admins.id', '=', 'offenses.admin_id')
        ->where('offenses.id', $id)
        ->select(
            'offenses.id',
            'offenses.offense_date',
            'offenses.offense_time',
            'offenses.place',
            'offenses.evidence_photo_path',
            'offenses.fine_amount',
            'offenses.status',
            'offenses.created_at',
            'students.full_name as student_name',
            'students.matric_no',
            'students.ic_no',
            'students.program',
            'admins.full_name as issued_by'
        )
        ->first();

    if (!$offense) {
        return redirect()->route('admin.offenses.index')
            ->withErrors(['offense' => __('messages.offense_not_found')]);
    }

    myhepAttachOffenseEvidence([$offense]);

    $items = DB::table('offense_items')
        ->join('offense_types', 'offense_types.id', '=', 'offense_items.offense_type_id')
        ->where('offense_items.offense_id', $id)
        ->select(
            'offense_types.rule_reference',
            'offense_types.description',
            'offense_items.note'
        )
        ->orderBy('offense_types.rule_reference')
        ->get();

    $backRoute = route('admin.offenses.index');
    $pdfRoute = route('admin.offenses.pdf', $offense->id);

    return view('admin.offenses.print', compact('offense', 'items', 'backRoute', 'pdfRoute'));
})->middleware(['auth.session:admin', 'admin.scope:offense.register', 'lecturer.page:offense_list'])->name('admin.offenses.print');

Route::get('/admin/offenses/{id}/pdf', function (int $id) {
    $offense = DB::table('offenses')
        ->join('students', 'students.id', '=', 'offenses.student_id')
        ->leftJoin('admins', 'admins.id', '=', 'offenses.admin_id')
        ->where('offenses.id', $id)
        ->select(
            'offenses.id',
            'offenses.offense_date',
            'offenses.offense_time',
            'offenses.place',
            'offenses.evidence_photo_path',
            'offenses.fine_amount',
            'offenses.status',
            'offenses.created_at',
            'students.full_name as student_name',
            'students.matric_no',
            'students.ic_no',
            'students.program',
            'admins.full_name as issued_by'
        )
        ->first();

    if (!$offense) {
        return redirect()->route('admin.offenses.index')
            ->withErrors(['offense' => __('messages.offense_not_found')]);
    }

    myhepAttachOffenseEvidence([$offense]);

    $items = DB::table('offense_items')
        ->join('offense_types', 'offense_types.id', '=', 'offense_items.offense_type_id')
        ->where('offense_items.offense_id', $id)
        ->select(
            'offense_types.rule_reference',
            'offense_types.description',
            'offense_items.note'
        )
        ->orderBy('offense_types.rule_reference')
        ->get();

    $html = view('admin.offenses.print', [
        'offense' => $offense,
        'items' => $items,
        'isPdf' => true,
    ])->render();

    $options = new Options();
    $options->set('isRemoteEnabled', false);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return response($dompdf->output(), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="saman_' . $offense->id . '.pdf"; filename*=UTF-8\'\'saman_' . $offense->id . '.pdf',
    ]);
})->middleware(['auth.session:admin', 'admin.scope:offense.register', 'lecturer.page:offense_list'])->name('admin.offenses.pdf');

Route::get('/admin/offenses/create', function () {
    $selectedStudent = filled(old('student_id'))
        ? DB::table('students')
        ->select('id', 'full_name', 'matric_no')
        ->where('id', (int) old('student_id'))
        ->first()
        : null;

    $offenseTypes = DB::table('offense_types')
        ->select('id', 'rule_reference', 'description', 'requires_note')
        ->orderBy('rule_reference')
        ->orderBy('description')
        ->get();

    return view('admin.offenses.create', compact('selectedStudent', 'offenseTypes'));
})->middleware(['auth.session:admin', 'admin.scope:offense.register', 'lecturer.page:offense_register'])->name('admin.offenses.create');

Route::post('/admin/offenses', function (Request $request) {
    $validated = $request->validate([
        'student_id' => ['required', 'integer', 'exists:students,id'],
        'offense_date' => ['required', 'date'],
        'offense_time' => ['required', 'date_format:H:i'],
        'place' => ['required', 'string', 'max:150'],
        'fine_amount' => ['required', 'numeric', 'min:0'],
        'evidence_photos' => ['nullable', 'array', 'max:3'],
        'evidence_photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        'offense_type_ids' => ['required', 'array', 'min:1'],
        'offense_type_ids.*' => ['integer', 'exists:offense_types,id'],
        'notes' => ['nullable', 'array'],
    ]);

    $adminId = session('auth_user.id');
    $photoPaths = [];

    foreach ((array) $request->file('evidence_photos', []) as $photo) {
        if ($photo) {
            $photoPaths[] = $photo->store('offenses/evidence', 'public');
        }
    }

    $offenseId = null;

    try {
        DB::transaction(function () use ($validated, $request, $adminId, $photoPaths, &$offenseId) {
            $offenseId = DB::table('offenses')->insertGetId([
                'student_id' => $validated['student_id'],
                'admin_id' => $adminId,
                'offense_date' => $validated['offense_date'],
                'offense_time' => $validated['offense_time'],
                'place' => $validated['place'],
                'evidence_photo_path' => $photoPaths[0] ?? null,
                'fine_amount' => $validated['fine_amount'],
                'status' => 'unpaid',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (Schema::hasTable('offense_evidence_photos')) {
                foreach (array_values(array_slice($photoPaths, 1)) as $index => $photoPath) {
                    DB::table('offense_evidence_photos')->insert([
                        'offense_id' => $offenseId,
                        'photo_path' => $photoPath,
                        'sort_order' => $index + 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $typeIds = array_values(array_unique($validated['offense_type_ids']));
            foreach ($typeIds as $typeId) {
                $note = data_get($request->input('notes', []), (string) $typeId);
                DB::table('offense_items')->insert([
                    'offense_id' => $offenseId,
                    'offense_type_id' => $typeId,
                    'note' => $note ?: null,
                    'created_at' => now(),
                ]);
            }
        });
    } catch (\Throwable $e) {
        if ($photoPaths !== []) {
            Storage::disk('public')->delete($photoPaths);
        }

        throw $e;
    }

    myhepSendPushNotification('student', (int) $validated['student_id'], [
        'category' => 'discipline',
        'title' => 'New offense recorded',
        'body' => 'A new discipline record has been added to your account. Please review it in My Offenses.',
        'url' => route('student.offenses.index'),
        'tag' => 'student-offense-' . $offenseId,
        'requireInteraction' => true,
    ]);

    $redirect = (session('auth_user.admin_role') === 'lecturer' && ! app(\App\Support\LecturerPageAccess::class)->enabled((int) session('auth_user.id'), 'offense_list'))
        ? route('admin.dashboard')
        : route('admin.offenses.index');

    if ($request->expectsJson()) {
        return response()->json([
            'ok' => true,
            'message' => __('messages.offense_saved'),
            'redirect' => $redirect,
        ]);
    }

    return redirect($redirect)
        ->with('success', __('messages.offense_saved'));
})->middleware(['auth.session:admin', 'admin.scope:offense.register', 'lecturer.page:offense_register'])->name('admin.offenses.store');

Route::get('/admin/offenses/{id}/edit', function (int $id) {
    $offense = DB::table('offenses')->where('id', $id)->first();
    if (!$offense) {
        return redirect()->route('admin.offenses.index')
            ->withErrors(['offense' => __('messages.offense_not_found')]);
    }

    $students = DB::table('students')
        ->select('id', 'full_name', 'matric_no')
        ->orderBy('full_name')
        ->get();

    $offenseTypes = DB::table('offense_types')
        ->select('id', 'rule_reference', 'description', 'requires_note')
        ->orderBy('rule_reference')
        ->orderBy('description')
        ->get();

    $selectedItems = DB::table('offense_items')
        ->where('offense_id', $id)
        ->select('offense_type_id', 'note')
        ->get();

    $selectedTypeIds = $selectedItems->pluck('offense_type_id')->all();
    $selectedNotes = $selectedItems
        ->mapWithKeys(fn ($item) => [(string) $item->offense_type_id => $item->note])
        ->all();

    myhepAttachOffenseEvidence([$offense]);

    return view('admin.offenses.edit', compact(
        'offense',
        'students',
        'offenseTypes',
        'selectedTypeIds',
        'selectedNotes'
    ));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.offenses.edit');

Route::put('/admin/offenses/{id}', function (Request $request, int $id) {
    $offense = DB::table('offenses')->where('id', $id)->first();
    if (!$offense) {
        return redirect()->route('admin.offenses.index')
            ->withErrors(['offense' => __('messages.offense_not_found')]);
    }

    $validated = $request->validate([
        'student_id' => ['required', 'integer', 'exists:students,id'],
        'offense_date' => ['required', 'date'],
        'offense_time' => ['required', 'date_format:H:i'],
        'place' => ['required', 'string', 'max:150'],
        'fine_amount' => ['required', 'numeric', 'min:0'],
        'status' => ['required', Rule::in(['unpaid', 'applied', 'paid'])],
        'evidence_photos' => ['nullable', 'array', 'max:3'],
        'evidence_photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        'remove_evidence_photo' => ['nullable', 'boolean'],
        'remove_evidence_extra_ids' => ['nullable', 'array'],
        'remove_evidence_extra_ids.*' => ['integer'],
        'offense_type_ids' => ['required', 'array', 'min:1'],
        'offense_type_ids.*' => ['integer', 'exists:offense_types,id'],
        'notes' => ['nullable', 'array'],
    ]);

    $existingExtras = Schema::hasTable('offense_evidence_photos')
        ? DB::table('offense_evidence_photos')
            ->where('offense_id', $id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
        : collect();

    $oldPhotoPath = $offense->evidence_photo_path ?: null;
    $removeExistingPhoto = $request->boolean('remove_evidence_photo');
    $removeExtraIds = collect($validated['remove_evidence_extra_ids'] ?? [])->map(fn ($value) => (int) $value)->all();
    $retainedExtraPaths = $existingExtras
        ->reject(fn ($extra) => in_array((int) $extra->id, $removeExtraIds, true))
        ->pluck('photo_path')
        ->values()
        ->all();
    $newUploadedPaths = [];

    foreach ((array) $request->file('evidence_photos', []) as $photo) {
        if ($photo) {
            $newUploadedPaths[] = $photo->store('offenses/evidence', 'public');
        }
    }

    $finalPaths = [];
    if (!$removeExistingPhoto && $oldPhotoPath) {
        $finalPaths[] = $oldPhotoPath;
    }
    $finalPaths = array_merge($finalPaths, $retainedExtraPaths, $newUploadedPaths);

    if (count($finalPaths) > 3) {
        if ($newUploadedPaths !== []) {
            Storage::disk('public')->delete($newUploadedPaths);
        }

        throw \Illuminate\Validation\ValidationException::withMessages([
            'evidence_photos' => __('messages.evidence_image_limit'),
        ]);
    }

    $newPrimaryPath = $finalPaths[0] ?? null;
    $newExtraPaths = array_values(array_slice($finalPaths, 1));

    try {
        DB::transaction(function () use ($validated, $request, $id, $newPrimaryPath, $newExtraPaths) {
            DB::table('offenses')
                ->where('id', $id)
                ->update([
                    'student_id' => $validated['student_id'],
                    'offense_date' => $validated['offense_date'],
                    'offense_time' => $validated['offense_time'],
                    'place' => $validated['place'],
                    'evidence_photo_path' => $newPrimaryPath,
                    'fine_amount' => $validated['fine_amount'],
                    'status' => $validated['status'],
                    'updated_at' => now(),
                ]);

            if (Schema::hasTable('offense_evidence_photos')) {
                DB::table('offense_evidence_photos')->where('offense_id', $id)->delete();

                foreach ($newExtraPaths as $index => $photoPath) {
                    DB::table('offense_evidence_photos')->insert([
                        'offense_id' => $id,
                        'photo_path' => $photoPath,
                        'sort_order' => $index + 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::table('offense_items')->where('offense_id', $id)->delete();

            $typeIds = array_values(array_unique($validated['offense_type_ids']));
            foreach ($typeIds as $typeId) {
                $note = data_get($request->input('notes', []), (string) $typeId);
                DB::table('offense_items')->insert([
                    'offense_id' => $id,
                    'offense_type_id' => $typeId,
                    'note' => $note ?: null,
                    'created_at' => now(),
                ]);
            }
        });
    } catch (\Throwable $e) {
        if ($newUploadedPaths !== []) {
            Storage::disk('public')->delete($newUploadedPaths);
        }

        throw $e;
    }

    $pathsToDelete = [];
    if ($oldPhotoPath && !in_array($oldPhotoPath, $finalPaths, true)) {
        $pathsToDelete[] = $oldPhotoPath;
    }
    foreach ($existingExtras as $extra) {
        if (!in_array($extra->photo_path, $finalPaths, true)) {
            $pathsToDelete[] = $extra->photo_path;
        }
    }
    if ($pathsToDelete !== []) {
        Storage::disk('public')->delete(array_values(array_unique($pathsToDelete)));
    }

    if ($request->expectsJson()) {
        return response()->json([
            'ok' => true,
            'message' => __('messages.offense_updated'),
            'redirect' => route('admin.offenses.index'),
        ]);
    }

    return redirect()->route('admin.offenses.index')
        ->with('success', __('messages.offense_updated'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.offenses.update');

Route::post('/admin/offenses/{id}/mark-paid', function (int $id) {
    $offense = DB::table('offenses')
        ->select('id', 'student_id')
        ->where('id', $id)
        ->first();

    if (!$offense) {
        return redirect()->route('admin.offenses.index')
            ->withErrors(['offense' => __('messages.offense_not_found')]);
    }

    $updated = DB::table('offenses')
        ->where('id', $id)
        ->update([
            'status' => 'paid',
            'updated_at' => now(),
        ]);

    if (!$updated) {
        return redirect()->route('admin.offenses.index');
    }
    auditLog('offenses.mark_paid', 'offenses', $id, 'Tukar status kesalahan ke paid');

    myhepSendPushNotification('student', (int) $offense->student_id, [
        'category' => 'discipline',
        'title' => 'Fine marked as paid',
        'body' => 'Your offense payment status has been updated to paid.',
        'url' => route('student.offenses.index'),
        'tag' => 'student-offense-paid-' . $id,
    ]);

    return redirect()->route('admin.offenses.index')
        ->with('success', __('messages.offense_marked_paid'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.offenses.mark-paid');

Route::delete('/admin/offenses/{id}', function (int $id) {
    $offense = DB::table('offenses')
        ->select('id', 'evidence_photo_path')
        ->where('id', $id)
        ->first();

    if (!$offense) {
        return redirect()->route('admin.offenses.index')
            ->withErrors(['offense' => __('messages.offense_not_found')]);
    }

    $extraPaths = Schema::hasTable('offense_evidence_photos')
        ? DB::table('offense_evidence_photos')->where('offense_id', $id)->pluck('photo_path')->all()
        : [];

    $deleted = DB::table('offenses')->where('id', $id)->delete();
    if (!$deleted) {
        return redirect()->route('admin.offenses.index')
            ->withErrors(['offense' => __('messages.offense_not_found')]);
    }

    $pathsToDelete = array_values(array_filter(array_merge(
        !empty($offense->evidence_photo_path) ? [$offense->evidence_photo_path] : [],
        $extraPaths
    )));
    if ($pathsToDelete !== []) {
        Storage::disk('public')->delete($pathsToDelete);
    }
    auditLog('offenses.delete', 'offenses', $id, 'Padam rekod kesalahan');

    return redirect()->route('admin.offenses.index')
        ->with('success', __('messages.offense_deleted'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.offenses.destroy');

Route::get('/student/offenses', function () {
    $studentId = session('auth_user.id');

    $offenses = DB::table('offenses')
        ->where('student_id', $studentId)
        ->select('id', 'offense_date', 'offense_time', 'place', 'evidence_photo_path', 'fine_amount', 'status')
        ->orderByDesc('offense_date')
        ->orderByDesc('offense_time')
        ->paginate(10);

    myhepAttachOffenseEvidence($offenses->items());

    $offenseIds = $offenses->pluck('id')->all();
    $itemsByOffense = collect();
    $fineAppsByOffense = collect();

    if (!empty($offenseIds)) {
        $itemsByOffense = DB::table('offense_items')
            ->join('offense_types', 'offense_types.id', '=', 'offense_items.offense_type_id')
            ->whereIn('offense_items.offense_id', $offenseIds)
            ->select(
                'offense_items.offense_id',
                'offense_types.rule_reference',
                'offense_types.description',
                'offense_items.note'
            )
            ->orderBy('offense_types.rule_reference')
            ->get()
            ->groupBy('offense_id');

        $fineAppsByOffense = DB::table('fine_payment_applications')
            ->whereIn('offense_id', $offenseIds)
            ->where('student_id', $studentId)
            ->select('offense_id', 'status', 'meeting_date', 'created_at', 'receipt_path')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('offense_id')
            ->map(fn ($rows) => $rows->first());
    }

    return view('student.offenses.index', compact('offenses', 'itemsByOffense', 'fineAppsByOffense'));
})->middleware('auth.session:student')->name('student.offenses.index');

Route::get('/student/offenses/{id}/print', function (int $id) {
    $studentId = (int) session('auth_user.id');

    $offense = DB::table('offenses')
        ->join('students', 'students.id', '=', 'offenses.student_id')
        ->leftJoin('admins', 'admins.id', '=', 'offenses.admin_id')
        ->where('offenses.id', $id)
        ->where('offenses.student_id', $studentId)
        ->select(
            'offenses.id',
            'offenses.offense_date',
            'offenses.offense_time',
            'offenses.place',
            'offenses.evidence_photo_path',
            'offenses.fine_amount',
            'offenses.status',
            'offenses.created_at',
            'students.full_name as student_name',
            'students.matric_no',
            'students.ic_no',
            'students.program',
            'admins.full_name as issued_by'
        )
        ->first();

    if (!$offense) {
        return redirect()->route('student.offenses.index')
            ->withErrors(['offense' => __('messages.offense_not_found')]);
    }

    myhepAttachOffenseEvidence([$offense]);

    $items = DB::table('offense_items')
        ->join('offense_types', 'offense_types.id', '=', 'offense_items.offense_type_id')
        ->where('offense_items.offense_id', $id)
        ->select(
            'offense_types.rule_reference',
            'offense_types.description',
            'offense_items.note'
        )
        ->orderBy('offense_types.rule_reference')
        ->get();

    $backRoute = route('student.offenses.index');
    $pdfRoute = null;

    return view('admin.offenses.print', compact('offense', 'items', 'backRoute', 'pdfRoute'));
})->middleware('auth.session:student')->name('student.offenses.print');

Route::get('/student/scholarships', function () {
    $studentId = session('auth_user.id');

    // Self-healing sync:
    // If student already submitted scholarship status form, ensure one managed
    // scholarship row exists so it always appears in student scholarship records.
    $statusForm = DB::table('student_scholarship_status_forms')
        ->where('student_id', $studentId)
        ->first();

    if ($statusForm) {
        $managedRecord = DB::table('scholarships')
            ->where('student_id', $studentId)
            ->where('proof_file', 'student_status_form')
            ->first();

        $managedPayload = [
            'student_id' => $studentId,
            'type' => $statusForm->has_scholarship === 'yes' ? 'scholarship' : 'none',
            'provider_name' => $statusForm->has_scholarship === 'yes'
                ? trim((string) ($statusForm->sponsor_name ?? ''))
                : null,
            'amount' => $statusForm->has_scholarship === 'yes'
                ? $statusForm->monthly_amount
                : null,
            // Do not downgrade admin-reviewed records when the student portal self-heals.
            'status' => $managedRecord && in_array($managedRecord->status, ['confirmed', 'rejected'], true)
                ? $managedRecord->status
                : ($statusForm->has_scholarship === 'yes' ? 'pending' : 'confirmed'),
            'proof_file' => 'student_status_form',
            'updated_at' => now(),
        ];

        if ($managedRecord) {
            DB::table('scholarships')
                ->where('id', $managedRecord->id)
                ->update($managedPayload);
        } else {
            DB::table('scholarships')->insert(array_merge($managedPayload, [
                'created_at' => now(),
            ]));
        }
    }

    $records = DB::table('scholarships')
        ->where('student_id', $studentId)
        ->orderByDesc('created_at')
        ->paginate(10);

    $announcements = DB::table('scholarship_announcements')
        ->select('id', 'title', 'body', 'type', 'link_url', 'link_label', 'created_at')
        ->orderByDesc('created_at')
        ->limit(8)
        ->get();

    return view('student.scholarships.index', compact('records', 'announcements'));
})->middleware('auth.session:student')->name('student.scholarships.index');

Route::get('/student/scholarship-announcements', function () {
    $announcements = DB::table('scholarship_announcements')
        ->select('id', 'title', 'body', 'type', 'link_url', 'link_label', 'created_at')
        ->orderByDesc('created_at')
        ->paginate(12);

    return view('student.scholarships.announcements', compact('announcements'));
})->middleware('auth.session:student')->name('student.scholarships.announcements');

Route::get('/student/rules', function (Request $request) {
    $categories = ruleCategoryOptions();
    $categoryIds = $categories->pluck('id')->map(fn ($id) => (int) $id)->all();
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:150'],
        'category_id' => ['nullable', Rule::in($categoryIds)],
    ]);

    $query = DB::table('rules')
        ->join('rule_categories', 'rule_categories.id', '=', 'rules.category_id')
        ->select('rules.id', 'rules.title', 'rules.category_id', 'rule_categories.name as category_name', 'rules.description', 'rules.updated_at');

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('title', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%");
        });
    }

    if (!empty($filters['category_id'])) {
        $query->where('rules.category_id', (int) $filters['category_id']);
    }

    $rules = $query
        ->orderBy('rule_categories.name')
        ->orderBy('rules.title')
        ->paginate(12)
        ->withQueryString();

    return view('student.rules.index', compact('rules', 'filters', 'categories'));
})->middleware('auth.session:student')->name('student.rules.index');

Route::get('/student/discipline-announcements', function () {
    $announcements = DB::table('discipline_announcements')
        ->join('admins', 'admins.id', '=', 'discipline_announcements.admin_id')
        ->select(
            'discipline_announcements.id',
            'discipline_announcements.title',
            'discipline_announcements.body',
            'discipline_announcements.created_at',
            'admins.full_name as admin_name'
        )
        ->orderByDesc('discipline_announcements.created_at')
        ->paginate(12);

    return view('student.discipline_announcements.index', compact('announcements'));
})->middleware('auth.session:student')->name('student.discipline-announcements.index');

Route::get('/student/vehicle-stickers', function () {
    $studentId = session('auth_user.id');
    $applications = DB::table('vehicle_sticker_applications')
        ->leftJoin('admins', 'admins.id', '=', 'vehicle_sticker_applications.approved_by')
        ->where('vehicle_sticker_applications.student_id', $studentId)
        ->select(
            'vehicle_sticker_applications.id',
            'vehicle_sticker_applications.vehicle_no',
            'vehicle_sticker_applications.vehicle_type',
            'vehicle_sticker_applications.license_card_path',
            'vehicle_sticker_applications.parent_permission_path',
            'vehicle_sticker_applications.vehicle_photo_path',
            'vehicle_sticker_applications.status',
            'vehicle_sticker_applications.created_at',
            'admins.full_name as approved_by_name'
        )
        ->orderByDesc('vehicle_sticker_applications.created_at')
        ->paginate(10);

    return view('student.vehicle_stickers.index', compact('applications'));
})->middleware('auth.session:student')->name('student.vehicle-stickers.index');

Route::post('/student/vehicle-stickers', function (Request $request) {
    $studentId = session('auth_user.id');
    $validated = $request->validate([
        'vehicle_no' => ['required', 'string', 'max:20'],
        'vehicle_type' => ['required', 'string', 'max:50'],
        'license_card_image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        'parent_permission_image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        'vehicle_plate_image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
    ]);

    $pendingExists = DB::table('vehicle_sticker_applications')
        ->where('student_id', $studentId)
        ->where('vehicle_no', $validated['vehicle_no'])
        ->where('status', 'pending')
        ->exists();

    if ($pendingExists) {
        return redirect()->route('student.vehicle-stickers.index')
            ->withErrors(['vehicle_no' => 'Permohonan pending untuk nombor kenderaan ini sudah wujud.'])
            ->withInput();
    }

    $licensePath = $request->file('license_card_image')->store('vehicle_stickers/license_cards', 'public');
    $permissionPath = $request->file('parent_permission_image')->store('vehicle_stickers/parent_permissions', 'public');
    $vehiclePhotoPath = $request->file('vehicle_plate_image')->store('vehicle_stickers/vehicle_photos', 'public');

    try {
        $applicationId = DB::table('vehicle_sticker_applications')->insertGetId([
            'student_id' => $studentId,
            'vehicle_no' => strtoupper(trim($validated['vehicle_no'])),
            'vehicle_type' => $validated['vehicle_type'],
            'license_card_path' => $licensePath,
            'parent_permission_path' => $permissionPath,
            'vehicle_photo_path' => $vehiclePhotoPath,
            'status' => 'pending',
            'approved_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    } catch (\Throwable $e) {
        Storage::disk('public')->delete([$licensePath, $permissionPath, $vehiclePhotoPath]);
        throw $e;
    }

    myhepSendPushToAdminsByScope('discipline', [
        'category' => 'discipline',
        'title' => 'New vehicle sticker application',
        'body' => 'A student submitted a vehicle sticker application for review.',
        'url' => route('admin.vehicle-stickers.index', ['status' => 'pending']),
        'tag' => 'admin-vehicle-sticker-' . $applicationId,
        'requireInteraction' => true,
    ]);

    return redirect()->route('student.vehicle-stickers.index')
        ->with('success', __('messages.vehicle_sticker_submitted'));
})->middleware('auth.session:student')->name('student.vehicle-stickers.store');

Route::post('/student/fine-applications', function (Request $request) {
    $studentId = session('auth_user.id');

    $validated = $request->validate([
        'offense_id' => ['required', 'integer', 'exists:offenses,id'],
        'student_note' => ['nullable', 'string'],
        'payment_receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
    ]);

    $offense = DB::table('offenses')
        ->where('id', $validated['offense_id'])
        ->where('student_id', $studentId)
        ->first();

    if (!$offense) {
        return redirect()->route('student.offenses.index')
            ->withErrors(['offense_id' => 'Kesalahan tidak ditemui untuk pelajar ini.']);
    }

    $pendingExists = DB::table('fine_payment_applications')
        ->where('offense_id', $validated['offense_id'])
        ->where('student_id', $studentId)
        ->where('status', 'pending')
        ->exists();

    if ($pendingExists) {
        return redirect()->route('student.offenses.index')
            ->withErrors(['offense_id' => 'Permohonan pembayaran sedang diproses.']);
    }

    $receiptPath = $request->file('payment_receipt')->store('fine_payment_receipts', 'public');

    try {
        DB::transaction(function () use ($validated, $studentId, $receiptPath) {
            DB::table('fine_payment_applications')->insert([
                'offense_id' => $validated['offense_id'],
                'student_id' => $studentId,
                'student_note' => $validated['student_note'] ?? null,
                'receipt_path' => $receiptPath,
                'status' => 'pending',
                'meeting_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('offenses')
                ->where('id', $validated['offense_id'])
                ->update([
                    'status' => 'applied',
                    'updated_at' => now(),
                ]);
        });
    } catch (\Throwable $e) {
        Storage::disk('public')->delete($receiptPath);
        throw $e;
    }

    myhepSendPushToAdminsByScope('discipline', [
        'category' => 'discipline',
        'title' => 'New payment receipt uploaded',
        'body' => 'A student submitted a fine payment receipt for admin review.',
        'url' => route('admin.offenses.index', ['status' => 'applied']),
        'tag' => 'admin-fine-receipt-' . $validated['offense_id'],
        'requireInteraction' => true,
    ]);

    return redirect()->route('student.offenses.index')
        ->with('success', __('messages.payment_application_submitted'));
})->middleware('auth.session:student')->name('student.fine-applications.store');

Route::get('/admin/fine-applications', function (Request $request) {
    $query = array_filter([
        'status' => 'applied',
        'q' => $request->query('q'),
        'date_from' => $request->query('date_from'),
        'date_to' => $request->query('date_to'),
    ], static fn ($value) => $value !== null && $value !== '');

    return redirect()->route('admin.offenses.index', $query);
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.fine-applications.index');

Route::get('/admin/fine-applications/export', function (Request $request) {
    $query = array_filter([
        'status' => 'applied',
        'q' => $request->query('q'),
        'date_from' => $request->query('date_from'),
        'date_to' => $request->query('date_to'),
    ], static fn ($value) => $value !== null && $value !== '');

    return redirect()->route('admin.offenses.index', $query);
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.fine-applications.export');

Route::get('/admin/vehicle-stickers', function (Request $request) {
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:150'],
        'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
    ]);

    $query = DB::table('vehicle_sticker_applications')
        ->join('students', 'students.id', '=', 'vehicle_sticker_applications.student_id')
        ->leftJoin('admins', 'admins.id', '=', 'vehicle_sticker_applications.approved_by')
        ->select(
            'vehicle_sticker_applications.id',
            'vehicle_sticker_applications.vehicle_no',
            'vehicle_sticker_applications.vehicle_type',
            'vehicle_sticker_applications.license_card_path',
            'vehicle_sticker_applications.parent_permission_path',
            'vehicle_sticker_applications.vehicle_photo_path',
            'vehicle_sticker_applications.status',
            'vehicle_sticker_applications.created_at',
            'students.full_name as student_name',
            'students.matric_no',
            'admins.full_name as approved_by_name'
        );

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('students.full_name', 'like', "%{$q}%")
                ->orWhere('students.matric_no', 'like', "%{$q}%")
                ->orWhere('students.ic_no', 'like', "%{$q}%")
                ->orWhere('vehicle_sticker_applications.vehicle_no', 'like', "%{$q}%");
        });
    }

    if (!empty($filters['status'])) {
        $query->where('vehicle_sticker_applications.status', $filters['status']);
    }

    $applications = $query
        ->orderByRaw("CASE vehicle_sticker_applications.status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
        ->orderByDesc('vehicle_sticker_applications.created_at')
        ->paginate(15)
        ->withQueryString();

    return view('admin.vehicle_stickers.index', compact('applications', 'filters'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.vehicle-stickers.index');

Route::get('/admin/vehicle-stickers/export', function (Request $request) {
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:150'],
        'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
    ]);

    $query = DB::table('vehicle_sticker_applications')
        ->join('students', 'students.id', '=', 'vehicle_sticker_applications.student_id')
        ->leftJoin('admins', 'admins.id', '=', 'vehicle_sticker_applications.approved_by')
        ->select(
            'vehicle_sticker_applications.id',
            'students.full_name as student_name',
            'students.matric_no',
            'vehicle_sticker_applications.vehicle_no',
            'vehicle_sticker_applications.vehicle_type',
            'vehicle_sticker_applications.status',
            'admins.full_name as approved_by_name',
            'vehicle_sticker_applications.created_at'
        );

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('students.full_name', 'like', "%{$q}%")
                ->orWhere('students.matric_no', 'like', "%{$q}%")
                ->orWhere('vehicle_sticker_applications.vehicle_no', 'like', "%{$q}%");
        });
    }

    if (!empty($filters['status'])) {
        $query->where('vehicle_sticker_applications.status', $filters['status']);
    }

    $rows = $query
        ->orderByRaw("CASE vehicle_sticker_applications.status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
        ->orderByDesc('vehicle_sticker_applications.created_at')
        ->get()
        ->map(function ($app) {
            return [
                $app->id,
                $app->student_name,
                $app->matric_no,
                $app->vehicle_no,
                $app->vehicle_type,
                $app->status,
                $app->approved_by_name ?? '',
                $app->created_at,
            ];
        });

    return downloadCsv(
        'vehicle_stickers_' . now()->format('Ymd_His') . '.csv',
        ['ID', 'Pelajar', 'No Matrik', 'No Kenderaan', 'Jenis Kenderaan', 'Status', 'Disemak Oleh', 'Tarikh Mohon'],
        $rows
    );
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.vehicle-stickers.export');

Route::post('/admin/vehicle-stickers/{id}/decision', function (Request $request, int $id) {
    $validated = $request->validate([
        'status' => ['required', Rule::in(['approved', 'rejected'])],
    ]);

    $application = DB::table('vehicle_sticker_applications')->where('id', $id)->first();
    if (!$application) {
         return redirect()->route('admin.vehicle-stickers.index')
            ->withErrors(['status' => 'Permohonan sticker tidak dijumpai.']);
    }

    DB::table('vehicle_sticker_applications')
        ->where('id', $id)
        ->update([
            'status' => $validated['status'],
            'approved_by' => session('auth_user.id'),
            'updated_at' => now(),
        ]);
    auditLog('vehicle_stickers.decision', 'vehicle_sticker_applications', $id, 'Status: ' . $validated['status']);

    myhepSendPushNotification('student', (int) $application->student_id, [
        'category' => 'discipline',
        'title' => $validated['status'] === 'approved' ? 'Vehicle sticker approved' : 'Vehicle sticker update',
        'body' => $validated['status'] === 'approved'
            ? 'Your vehicle sticker application has been approved.'
            : 'Your vehicle sticker application has been rejected. Please review the latest status.',
        'url' => route('student.vehicle-stickers.index'),
        'tag' => 'vehicle-sticker-' . $id,
        'requireInteraction' => $validated['status'] !== 'approved',
    ]);

    return redirect()->route('admin.vehicle-stickers.index')
        ->with('success', __('messages.sticker_application_updated'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.vehicle-stickers.decision');

Route::delete('/admin/vehicle-stickers/{id}', function (int $id) {
    $application = DB::table('vehicle_sticker_applications')->where('id', $id)->first();

    if (!$application) {
        return redirect()->route('admin.vehicle-stickers.index')
            ->withErrors(['status' => 'Permohonan sticker tidak dijumpai.']);
    }

    DB::transaction(function () use ($application) {
        DB::table('vehicle_sticker_applications')->where('id', $application->id)->delete();
    });

    foreach ([
        $application->license_card_path,
        $application->parent_permission_path,
        $application->vehicle_photo_path,
    ] as $path) {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    auditLog('vehicle_stickers.delete', 'vehicle_sticker_applications', $id, 'Deleted vehicle sticker application');

    return redirect()->route('admin.vehicle-stickers.index')
        ->with('success', __('messages.sticker_application_deleted'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.vehicle-stickers.destroy');

Route::post('/admin/fine-applications/{id}/decision', function (Request $request, int $id) {
    $validated = $request->validate([
        'status' => ['required', Rule::in(['approved', 'rejected'])],
        'meeting_date' => ['nullable', 'date'],
    ]);

    $application = DB::table('fine_payment_applications')->where('id', $id)->first();
    if (!$application) {
        return redirect()->route('admin.offenses.index', ['status' => 'applied'])
            ->withErrors(['status' => 'Permohonan tidak dijumpai.']);
    }

    DB::transaction(function () use ($validated, $application) {
        DB::table('fine_payment_applications')
            ->where('id', $application->id)
            ->update([
                'status' => $validated['status'],
                'meeting_date' => $validated['status'] === 'approved' ? $validated['meeting_date'] : null,
                'updated_at' => now(),
            ]);

        DB::table('offenses')
            ->where('id', $application->offense_id)
            ->update([
                'status' => $validated['status'] === 'approved' ? 'paid' : 'unpaid',
                'updated_at' => now(),
            ]);
    });
    auditLog('fine_applications.decision', 'fine_payment_applications', $id, 'Status: ' . $validated['status']);

    myhepSendPushNotification('student', (int) $application->student_id, [
        'category' => 'discipline',
        'title' => $validated['status'] === 'approved' ? 'Payment verified' : 'Payment receipt update',
        'body' => $validated['status'] === 'approved'
            ? 'Your fine payment receipt has been verified. The offense is now marked as paid.'
            : 'Your fine payment receipt was rejected. Please review your offense record and upload a new receipt if needed.',
        'url' => route('student.offenses.index'),
        'tag' => 'fine-application-' . $id,
        'requireInteraction' => $validated['status'] !== 'approved',
    ]);

    return redirect()->route('admin.offenses.index', ['status' => 'applied'])
        ->with('success', __('messages.application_status_updated'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.fine-applications.decision');
 
