PDH Nutrition: update for server 192.168.111.240

1. Back up the existing project files.
2. Extract this ZIP into the pdhnutrition project root, preserving app/, views/ and public/ folders. Replace only the included files.
3. Keep existing config.php, .env and database settings. No Apache/PHP server configuration change is required.
4. Open http://192.168.111.240/pdhnutrition/index.php?route=/login
5. Sign in and check Dashboard, search filters, reports and AJAX.
6. Open system_check.php and run URL tests. Remove system_check.php after diagnosis.

Old bookmarked /dashboard URLs still require rewrite; use index.php?route=/dashboard instead.
This package does not contain .env, config.php, SQL imports or database migrations.
