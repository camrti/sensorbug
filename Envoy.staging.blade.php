@setup
    $server = '77.235.41.192';

    $baseDir = '/home/sensordash/staging-sensor.evolutiongroup.digital';
    $user = 'sensordash';

    $php = '/usr/bin/php';
    $npm = '/usr/bin/npm';
    $userAndServer = $user . '@'. $server;
    $githubServer = 'git@github.com-sensordash';
    $repository = "/Evolution-adv/sensor-dashboard";

    $branch = $branch ?? 'staging';
    $env = $env ?? 'staging';
    $test = $test ?? 'local';

    $local_dir = getcwd();

    # naming convention
    $releasesDir = "{$baseDir}/releases";
    $persistentDir = "{$baseDir}/persistent";
    $currentDir = "{$baseDir}/current";
    $newReleaseName = date('Ymd-His');
    $newReleaseDir = "{$releasesDir}/{$newReleaseName}";


    function logMessage($message) {
    return "echo '\033[32m" .$message. "\033[0m';\n";
    }
@endsetup

@servers(['local' => '127.0.0.1', 'remote' => $userAndServer])

@story('deploy')
    runTests
    cloneRepository
    runComposer
    runNpm
    generateAssets
    updateSymlinks
    optimizeInstallation
    migrateDatabase
    blessNewRelease
    cleanOldReleases
    finishDeploy
@endstory

@story('deployOnlyCode')
    runTests
    codeOnly
@endstory

@task('runTests', ['on' => 'local'])
    echo "Executing tests (Mode: {{ $test }})...";

    @if ($test === 'sail')
        echo "Sail mode selected for tests.";

        if [ ! -f "vendor/bin/sail" ]; then
            echo "Error: Sail script (vendor/bin/sail) not found in vendor/bin!";
            echo "Cannot run tests with Sail if it's not installed in the project.";
            exit 1;
        fi

        ./vendor/bin/sail test --compact;
    @else
        echo "Local Artisan mode selected (test mode is '{{ $test }}'). Executing tests with local PHP Artisan...";
        php artisan test --compact;
    @endif
@endtask

@task('dummy', ['on' => 'local'])
    echo "Dummy task executed!"
@endtask

@task('setupEnv', ['on' => 'local'])
    [ -f {{ $local_dir }}/.env.{{ $env }} ] && scp {{ $local_dir }}/.env.{{ $env }} {{ $userAndServer }}:{{ $baseDir }}/.env;
    {{ logMessage('💾 Env file copied!') }}
@endtask

@task('cloneRepository', ['on' => 'remote'])
    {{ logMessage('🌀  Cloning repository…') }}
    [ -d {{ $releasesDir }} ] || mkdir {{ $releasesDir }};
    [ -d {{ $persistentDir }} ] || mkdir {{ $persistentDir }};
    [ -d {{ $persistentDir }}/uploads ] || mkdir {{ $persistentDir }}/uploads;
    [ -d {{ $persistentDir }}/storage ] || mkdir {{ $persistentDir }}/storage;
    [ -d {{ $persistentDir }}/storage/framework ] || mkdir {{ $persistentDir }}/storage/framework;
    [ -d {{ $persistentDir }}/storage/framework/cache ] || mkdir {{ $persistentDir }}/storage/framework/cache;
    [ -d {{ $persistentDir }}/storage/framework/sessions ] || mkdir {{ $persistentDir }}/storage/framework/sessions;
    [ -d {{ $persistentDir }}/storage/framework/views ] || mkdir {{ $persistentDir }}/storage/framework/views;

    cd {{ $releasesDir }};

    # Create the release dir
    mkdir {{ $newReleaseDir }};


    # Clone the repo
    git clone --depth 1 --branch {{ $branch }} {{ $githubServer }}:{{ $repository }} {{ $newReleaseName }}

    # Configure sparse checkout
    cd {{ $newReleaseDir }}
    git config core.sparsecheckout true
    echo "*" > .git/info/sparse-checkout
    echo "!storage" >> .git/info/sparse-checkout
    echo "!public/build" >> .git/info/sparse-checkout
    git read-tree -mu HEAD

    # Mark release
    cd {{ $newReleaseDir }}
    echo "{{ $newReleaseName }}" > public/release-name.txt
@endtask

@task('runComposer', ['on' => 'remote'])
    cd {{ $newReleaseDir }};
    {{ logMessage('🚚  Running Composer…') }}
    composer install --prefer-dist --no-scripts --no-dev -q -o
@endtask

@task('runNpm', ['on' => 'remote'])
    {{ logMessage('📦  Running Npm…') }}
    cd {{ $newReleaseDir }};
    {{ $npm }} install
@endtask

@task('generateAssets', ['on' => 'remote'])
    {{ logMessage('🌅  Generating assets…') }}
    cd {{ $newReleaseDir }};
    {{ $npm }} run build
@endtask

@task('updateSymlinks', ['on' => 'remote'])
    {{ logMessage('🔗  Updating symlinks to persistent data…') }}
    # Remove the storage directory and replace with persistent data
    rm -rf {{ $newReleaseDir }}/storage;
    cd {{ $newReleaseDir }};
    ln -nfs {{ $baseDir }}/persistent/storage storage;

    # Import the environment config
    cd {{ $newReleaseDir }};
    ln -nfs {{ $baseDir }}/.env .env;

    # Symlink the persistent fonts to the public directory
    #cd {{ $baseDir }}/persistent/fonts
    #git pull origin master
    #ln -nfs {{ $baseDir }}/persistent/fonts {{ $newReleaseDir }}/public/fonts;
@endtask

@task('optimizeInstallation', ['on' => 'remote'])
    {{ logMessage('✨  Optimizing installation…') }}
    cd {{ $newReleaseDir }};
    {{ $php }} artisan clear-compiled;
@endtask

@task('backupDatabase', ['on' => 'remote'])
    {{ logMessage('📀  Backing up database…') }}
    cd {{ $newReleaseDir }}
    {{ $php }} artisan backup:run
@endtask

@task('migrateDatabase', ['on' => 'remote'])
    {{ logMessage('🙈  Migrating database…') }}
    cd {{ $newReleaseDir }};
    {{ $php }} artisan migrate --force;
@endtask

@task('blessNewRelease', ['on' => 'remote'])
    {{ logMessage('🙏  Blessing new release…') }}
    ln -nfs {{ $newReleaseDir }} {{ $currentDir }};
    cd {{ $newReleaseDir }}

    {{ $php }} artisan config:clear
    {{ $php }} artisan view:clear
    {{ $php }} artisan cache:clear
    {{ $php }} artisan event:clear
    {{ $php }} artisan route:clear

    {{ $php }} artisan queue:restart

    {{ $php }} artisan config:cache
    {{ $php }} artisan storage:link
    {{ $php }} artisan event:cache
    {{ $php }} artisan route:cache
    {{ $php }} artisan view:cache

    echo "" | sudo -S /usr/sbin/service php8.3-fpm reload
@endtask

@task('cleanOldReleases', ['on' => 'remote'])
    {{ logMessage('🚾  Cleaning up old releases…') }}
    # Delete all but the 5 most recent.
    cd {{ $releasesDir }}
    ls -dt {{ $releasesDir }}/* | tail -n +6 | xargs -d "\n" chown -R {{ $user }} .;
    ls -dt {{ $releasesDir }}/* | tail -n +6 | xargs -d "\n" rm -rf;
@endtask

@task('finishDeploy', ['on' => 'local'])
    {{ logMessage('🚀  Application deployed!') }}
@endtask

@task('codeOnly', ['on' => 'remote'])
    {{ logMessage('💻  Deploying code changes…') }}
    cd {{ $currentDir }}
    git pull origin {{ $branch }}
    {{ $php }} artisan config:clear
    {{ $php }} artisan view:clear
    {{ $php }} artisan cache:clear
    {{ $php }} artisan event:clear
    {{ $php }} artisan route:clear

    {{ $php }} artisan queue:restart

    {{ $php }} artisan config:cache
    {{ $php }} artisan event:cache
    {{ $php }} artisan route:cache
    {{ $php }} artisan view:cache

    echo "" | sudo -S /usr/sbin/service php8.3-fpm reload
@endtask

@error
   echo "\033[1;31mERROR: \033[0m The following task failed:\033[0;33m $task \n";
@enderror
