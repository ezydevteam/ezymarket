<?php

use Illuminate\Support\Facades\Route;
use Codebay\Installer\App\Http\Controllers\InstallationController;

Route::middleware(['web'])->group(function () {
    Route::prefix('install')->name('installer.')->group(function () {
        Route::get('/', [InstallationController::class, 'redirectToInstaller'])->name('index');
        Route::get('requirements', [InstallationController::class, 'showRequirements']);
        Route::post('requirements', [InstallationController::class, 'validateRequirements'])->name('requirements');
        Route::get('permissions', [InstallationController::class, 'showPermissions']);
        Route::post('permissions', [InstallationController::class, 'validatePermissions'])->name('permissions');
        Route::get('license', [InstallationController::class, 'showLicense']);
        Route::post('license', [InstallationController::class, 'validateLicense'])->name('license');
        Route::get('database', [InstallationController::class, 'showDatabaseForm'])->name('database');
        Route::post('database', [InstallationController::class, 'validateDatabaseConnection'])->name('database.validate');
        Route::get('import', [InstallationController::class, 'showDatabaseImport'])->name('database.import');
        Route::post('import', [InstallationController::class, 'importDatabase'])->name('database.import.process');
        Route::post('import/download', [InstallationController::class, 'downloadDatabase'])->name('database.import.download');
        Route::post('import/skip', [InstallationController::class, 'skipDatabaseImport'])->name('database.import.skip');
        Route::get('complete', [InstallationController::class, 'showComplete']);
        Route::post('complete', [InstallationController::class, 'finishInstallation'])->name('complete');
        Route::post('complete/back', [InstallationController::class, 'returnToImport'])->name('complete.back');
    });
});


















