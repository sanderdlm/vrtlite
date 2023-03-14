<?php

namespace Deployer;

require 'recipe/symfony.php';

// Project name
set('application', 'vrtlite');

// Project repository
set('repository', 'git@github.com:dreadnip/vrtlite.git');

set('composer_options', 'install --verbose --prefer-dist --no-progress --no-interaction --optimize-autoloader');

set('keep_releases', 3);

// Shared files/dirs between deploys 
add('shared_files', ['.env']);
add('shared_dirs', []);

// Hosts
host('vrtlite.be')
    ->hostname('vrtlite.be')
    ->user('sander')
    ->port(22)
    ->set('branch', 'master')
    ->set('deploy_path', '/var/www/vrtlite');

after('deploy:failed', 'deploy:unlock');

task('clear:opcache', function () {
    run('{{bin/php}} {{ release_or_current_path }}/vendor/bin/chop');
})->addAfter('deploy:symlink');
