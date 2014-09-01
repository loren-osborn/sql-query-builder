Exec { path => [ "/bin/", "/sbin/" , "/usr/bin/", "/usr/sbin/" ] }

exec { 'apt-get update':
  command => 'apt-get update',
  timeout => 600,
  tries   => 5
} -> Package <| |>

class { 'apt':
  always_apt_update => true,
}

package { ['python-software-properties']:
  ensure  => 'installed',
  require => Exec['apt-get update'],
}

$sysPackages = [ 'build-essential', 'git', 'curl', 'bc']
package { $sysPackages:
  ensure => "installed",
  require => Exec['apt-get update'],
}

class { "apache": }

class { 'php': }

$phpModules = [ 'cli', 'intl']

php::module { $phpModules: }

package { "libpcre3-dev": } ->
php::pecl::module { "XDebug": } ->
php::pecl::module { "Weakref":
  use_package     => 'false',
  preferred_state => 'beta'
}

php::ini { 'php':
  value   => [
    'extension=weakref.so'
  ],
  target  => 'php.ini',
  service => 'apache'
}

class composer {
    exec { 'install composer php dependency management':
        command => 'curl -s http://getcomposer.org/installer | php -d suhosin.executor.include.whitelist=phar -- --install-dir=/usr/bin ; echo "#"\!"/bin/bashXphp -d suhosin.executor.include.whitelist=phar /usr/bin/composer.phar \$@"| tr X "\012" > /usr/bin/composer ; chmod 755 /usr/bin/composer',
        creates => '/usr/bin/composer',
        environment => ["HOME=/home/vagrant", "COMPOSER_HOME=/home/vagrant"],
        require => [Package['php5-cli'], Package['curl'], Php::Ini[php], Php::Pecl::Module[XDebug], Php::Pecl::Module[Weakref]],
    }

    exec { 'composer self update':
        command => 'composer self-update',
        environment => ["HOME=/home/vagrant", "COMPOSER_HOME=/home/vagrant"],
        require => [Exec['install composer php dependency management']],
    }

    exec { 'composer install':
        command => 'composer install',
        environment => ["HOME=/home/vagrant", "COMPOSER_HOME=/home/vagrant"],
        cwd => "/vagrant",
        require => [Exec['composer self update'], Php::Ini[php]],
        timeout => 900,
    }
}

class { 'composer': } -> file { '/usr/local/bin/phpunit':
   ensure => 'link',
   target => '/vagrant/vendor/phpunit/phpunit/phpunit',
}

file { "/home/vagrant/.bash_profile":
    mode => 755,
    ensure => file,
    owner => 'vagrant',
    source => '/vagrant/puppet/files/.bash_profile',
}


