$serverName = "task.dev"
$phpTimeZone = "Europe/Vilnius"

# Ensure the time is accurate, reducing the possibilities of apt repositories
# failing for invalid certificates
class { 'ntp': }

Exec { path => [ '/bin/', '/sbin/', '/usr/bin/', '/usr/sbin/' ] }

group { 'puppet':   ensure => present }
group { 'www-data': ensure => present }
group { 'www-user': ensure => present }

user { 'vagrant':
  shell   => '/bin/bash',
  home    => "/home/vagrant",
  ensure  => present,
  groups  => ['www-data', 'www-user'],
  require => [Group['www-data'], Group['www-user']]
}

ensure_packages( ['vim', 'curl'] )

apt::source { 'packages.dotdeb.org':
  location          => 'http://packages.dotdeb.org',
  release           => $lsbdistcodename,
  repos             => 'all',
  required_packages => 'debian-keyring debian-archive-keyring',
  key               => '89DF5277',
  key_server        => 'keys.gnupg.net',
  include_src       => true
}

user { ['nginx', 'www-data']:
  shell  => '/bin/bash',
  ensure => present,
  groups => 'www-data',
  require => Group['www-data']
}

file { "/home/vagrant":
  ensure => directory,
  owner  => "vagrant",
}

### APT ###

class {'apt':
  always_apt_update => true,
}

Class['::apt::update'] -> Package <|
  title != 'python-software-properties'
  and title != 'software-properties-common'
|>

if ! defined(Package['augeas-tools']) {
  package { 'augeas-tools':
    ensure => present,
  }
}

### NGINX ###

class { 'nginx': }

nginx::resource::vhost { "${serverName}":
  ensure       => present,
  server_name  => [
    "${serverName}",
    "www.${serverName}",
  ],
  index_files  => [
    'app_dev.php',
    'app.php',
  ],
  listen_port  => 80,
  www_root     => "/var/www/${serverName}/web/",
  try_files    => ['$uri', '$uri/', '/app_dev.php?$args'],
}

$path_translated = 'PATH_TRANSLATED $document_root$fastcgi_path_info'
$script_filename = 'SCRIPT_FILENAME $document_root$fastcgi_script_name'

nginx::resource::location { "${serverName}-php":
  ensure              => 'present',
  index_files  => [
    'app_dev.php',
    'app.php',
    'index.php',
    'index.html'
  ],
  vhost               => "${serverName}",
  location            => '~ \.php$',
  proxy               => undef,
  try_files           => ['$uri', '$uri/', '/app_dev.php?$args'],
  www_root            => "/var/www/${serverName}/web/",
  location_cfg_append => {
    'fastcgi_split_path_info' => '^(.+\.php)(/.+)$',
    'fastcgi_param'           => 'PATH_INFO $fastcgi_path_info',
    'fastcgi_param '          => $path_translated,
    'fastcgi_param  '         => $script_filename,
    'fastcgi_pass'            => 'unix:/var/run/php5-fpm.sock',
    'fastcgi_index'           => 'app_dev.php',
    'include'                 => 'fastcgi_params'
  },
  notify              => Class['nginx::service'],
}

### PHP ###

class { 'php':
  package             => 'php5-fpm',
  service             => 'php5-fpm',
  service_autorestart => false,
  config_file         => '/etc/php5/fpm/php.ini',
  module_prefix       => ''
}

php::module {
  [
    'php5-cli',
    'php5-curl',
    'php5-intl',
    'php5-mcrypt',
    'php5-common',
    'php5-xdebug',
    'php5-mongo',
  ]:
    require => Class['php'],
}

exec { "php-fpm-owner-fix":
  command => "sed -i 's/;listen.owner/listen.owner/g' /etc/php5/fpm/pool.d/www.conf",
  require => Class["php"],
  notify => Service["php5-fpm"]
}

exec { "php-fpm-group-fix":
  command => "sed -i 's/;listen.group/listen.group/g' /etc/php5/fpm/pool.d/www.conf",
  require => Class["php"],
  notify => Service["php5-fpm"]
}

php::ini { 'custom':
  value   => [
    'memory_limit = 1G'
  ],
  require => Package["php5-cli", "php5-fpm"]
}

service { 'php5-fpm':
  ensure     => running,
  enable     => true,
  hasrestart => true,
  hasstatus  => true,
  require    => Class['php'],
}

class { 'composer':
  require => Package['php5-fpm', 'curl'],
}

augeas { "xdebug":
  context => "/files/etc/php5/conf.d/custom_xdebug.ini",
  changes => [
  #"set Extension/zend_extension xdebug.so",
    "set REMOTE/xdebug.default_enable 1",
    "set REMOTE/xdebug.remote_autostart 0",
    "set REMOTE/xdebug.remote_connect_back 1",
    "set REMOTE/xdebug.remote_enable 1",
    "set REMOTE/xdebug.remote_handler dbgp",
    "set REMOTE/xdebug.remote_port 9000",
    "set REMOTE/xdebug.remote_host 10.15.10.1",
    "set REMOTE/xdebug.max_nesting_level 250"
  ],
  notify  => Service['php5-fpm'],
  require => [Class['php']]
}

augeas { "custom":
  context => "/files/etc/php5/conf.d/custom_xdebug.ini",
  changes => [
  #"set Extension/zend_extension xdebug.so",
    "set CUSTOM/display_errors On",
    "set CUSTOM/error_reporting -1",
    "set CUSTOM/date.timezone ${phpTimeZone}"
  ],
  notify  => Service['php5-fpm'],
  require => Class['php'],
}

file { '/etc/php5/conf.d/':
  ensure => "directory",
  owner => "root",
  group => "root",
  mode => 777,
  require => Class['php'],
}

file { '/usr/local/bin/debug':
  ensure => present,
  mode => 755,
  content => "#!/bin/sh\nenv PHP_IDE_CONFIG=\"serverName=tsp\" XDEBUG_CONFIG=\"idekey=PHPSTORM\" SYMFONY_DEBUG=\"1\" $@"
}

### MONGODB ###

file { ['/data', '/data/db']:
  ensure  => directory,
  mode    => 0775,
  before  => Class['Mongodb::Globals'],
}

class {'::mongodb::globals':
  manage_package_repo => true,
}->
class {'::mongodb::server':
  port    => 27017,
  verbose => true, }->
class {'::mongodb::client': }
