<?php
namespace Deployer;

require 'recipe/symfony.php';

// Project name
set('application', 'vrtlite');

set('user', 'root');

// Project repository
set('repository', 'git@github.com:dreadnip/vrtlite.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

// Shared files/dirs between deploys 
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server 
add('writable_dirs', []);


// Hosts

host('vrtlite.be')
    ->set('deploy_path', '~/var/www/html');
    
// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

before('deploy:symlink', 'database:migrate');

