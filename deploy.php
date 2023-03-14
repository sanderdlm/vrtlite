<?php

namespace Deployer;

require 'recipe/symfony.php';

set('application', 'vrtlite');
set('repository', 'git@github.com:dreadnip/vrtlite.git');
set('composer_options', 'install --verbose --prefer-dist --no-progress --no-interaction --optimize-autoloader');
set('keep_releases', 3);

add('shared_files', ['.env']);
add('shared_dirs', []);

host('vrtlite.be')
    ->setRemoteUser('sander')
    ->setPort(22)
    ->set('branch', 'master')
    ->set('deploy_path', '/var/www/vrtlite');

after('deploy:failed', 'deploy:unlock');

task('clear:opcache', function () {
    run('{{bin/php}} {{ release_or_current_path }}/vendor/bin/chop');
})->addAfter('deploy:symlink');
