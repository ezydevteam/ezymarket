# Migration Cleanup Scripts

## Choose Your Scenario

### Scenario A: Fresh Database - Keep Only Consolidated Migrations

```powershell
# Navigate to migrations folder
cd "c:\laragon\www\easymarket\database\migrations"

# BACKUP FIRST (IMPORTANT!)
Copy-Item "." "../migrations_backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')" -Recurse

# Delete original migrations (000015-000108)
Get-ChildItem -Filter "2025_10_13_0000[1-9][5-9]*.php" | Remove-Item -Verbose
Get-ChildItem -Filter "2025_10_13_0001[0-0][0-8]*.php" | Remove-Item -Verbose

# Also delete .bak files
Get-ChildItem -Filter "*.bak" | Remove-Item -Verbose

# What remains:
# - 2025_10_13_000001 through 2025_10_13_000014 (consolidated)
# - 2025_10_13_200620_change_documents... (fix)
# - Documentation .md files

Write-Host "`n✅ Cleanup complete! You now have 15 migration files." -ForegroundColor Green
Write-Host "Run: php artisan migrate" -ForegroundColor Cyan
```

### Scenario B: Existing Database - Keep Only Original Migrations

```powershell
# Navigate to migrations folder
cd "c:\laragon\www\easymarket\database\migrations"

# BACKUP FIRST (IMPORTANT!)
Copy-Item "." "../migrations_backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')" -Recurse

# Delete consolidated migrations (000001-000014)
Get-ChildItem -Filter "2025_10_13_00000[1-9]*.php" | Remove-Item -Verbose
Get-ChildItem -Filter "2025_10_13_00001[0-4]*.php" | Remove-Item -Verbose

# Also delete .bak files
Get-ChildItem -Filter "*.bak" | Remove-Item -Verbose

# What remains:
# - 2025_10_13_000015 through 2025_10_13_000108 (original)
# - 2025_10_13_200620_change_documents... (fix)
# - Documentation .md files

Write-Host "`n✅ Cleanup complete! You now have 95 migration files." -ForegroundColor Green
Write-Host "Run: php artisan migrate" -ForegroundColor Cyan
```

### Scenario C: Manual Verification (Safest)

```powershell
# Navigate to migrations folder
cd "c:\laragon\www\easymarket\database\migrations"

# List consolidated migrations
Write-Host "`nCONSOLIDATED MIGRATIONS (000001-000014):" -ForegroundColor Yellow
Get-ChildItem -Filter "2025_10_13_00000[1-9]*.php" | Select-Object Name
Get-ChildItem -Filter "2025_10_13_00001[0-4]*.php" | Select-Object Name

Write-Host "`nORIGINAL MIGRATIONS (000015-000108):" -ForegroundColor Yellow
Get-ChildItem -Filter "2025_10_13_0000[1-9][5-9]*.php" | Select-Object Name -First 5
Write-Host "... and 89 more files" -ForegroundColor Gray

Write-Host "`nRENAME MIGRATIONS (000099-000108):" -ForegroundColor Red
Get-ChildItem -Filter "*rename*.php" | Select-Object Name

Write-Host "`nBACKUP FILES (.bak):" -ForegroundColor Gray
Get-ChildItem -Filter "*.bak" | Select-Object Name

Write-Host "`nDECIDE:" -ForegroundColor Cyan
Write-Host "  - Fresh DB? Delete 000015-000108" -ForegroundColor Green
Write-Host "  - Existing DB? Delete 000001-000014" -ForegroundColor Green
```

## Safe Step-by-Step Process

### Step 1: Backup Everything
```powershell
# Full backup of migrations folder
cd "c:\laragon\www\easymarket\database"
Copy-Item "migrations" "migrations_backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')" -Recurse
Write-Host "✅ Backup created!" -ForegroundColor Green
```

### Step 2: Verify Backup
```powershell
# Check backup exists
Get-ChildItem "migrations_backup*" | Select-Object Name, CreationTime
```

### Step 3: Choose and Execute Cleanup
Run either **Scenario A** or **Scenario B** script above

### Step 4: Verify Result
```powershell
# Count remaining migrations
$count = (Get-ChildItem "migrations" -Filter "*.php").Count
Write-Host "Total migration files: $count" -ForegroundColor Cyan

# List all remaining migrations
Get-ChildItem "migrations" -Filter "*.php" | Sort-Object Name | Select-Object Name
```

### Step 5: Test Migrations
```bash
# Check migration status
php artisan migrate:status

# Try running migrations (on test database first!)
php artisan migrate
```

## Rollback if Needed

```powershell
# If something went wrong, restore from backup
cd "c:\laragon\www\easymarket\database"

# List available backups
Get-ChildItem "migrations_backup*" | Select-Object Name

# Restore (replace YYYYMMDD_HHMMSS with your backup timestamp)
Remove-Item "migrations" -Recurse -Force
Copy-Item "migrations_backup_YYYYMMDD_HHMMSS" "migrations" -Recurse

Write-Host "✅ Restored from backup!" -ForegroundColor Green
```

## Quick Answers

**Q: I have a fresh database, which files do I need?**
A: Files `000001-000014` + `200620` = 15 files total

**Q: I have an existing database, which files do I need?**
A: Files `000015-000108` + `200620` = 95 files total

**Q: What about the .bak files?**
A: Safe to delete, they're old backups

**Q: Can I keep both sets?**
A: Not recommended - they'll conflict. Choose one approach.

**Q: What if I'm not sure?**
A: Use **Scenario C** to review files first, then decide.

## Files to ALWAYS Keep

No matter which option:
- ✅ `2025_10_13_200620_change_documents_column_type_in_id_verifications_table.php`
- ✅ `MIGRATION_GUIDE_2025_10_13.md`
- ✅ `RENAMING_SUMMARY.md`
- ✅ `MIGRATION_CHECKLIST.md`
- ✅ `RENAME_MIGRATIONS_EXPLAINED.md`
- ✅ `CLEANUP_SCRIPTS.md` (this file)

These are documentation and important fixes!
