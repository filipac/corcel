<?php
// Usage: php tests/compatibility.php /path/to/Laravel/vendor/autoload.php
require $argv[1] ?? dirname(__DIR__).'/vendor/autoload.php';
$container = new Illuminate\Foundation\Application(sys_get_temp_dir());
Illuminate\Container\Container::setInstance($container);
$container->instance('config', new Illuminate\Config\Repository(['corcel'=>['connection'=>'wp']]));
$db = new Illuminate\Database\Capsule\Manager($container);
$db->addConnection(['driver'=>'sqlite','database'=>':memory:','prefix'=>'wp_'], 'wp');
$db->setAsGlobal(); $db->bootEloquent();
$db->schema('wp')->create('users', function ($table) {
    $table->increments('ID'); $table->string('user_login'); $table->string('user_email'); $table->string('user_pass');
});
$db->schema('wp')->create('usermeta', function ($table) {
    $table->increments('umeta_id'); $table->unsignedInteger('user_id'); $table->string('meta_key'); $table->text('meta_value');
});
$hash = (new Corcel\Services\PasswordService)->makeHash('fixture-password');
$db->connection('wp')->table('users')->insert(['ID'=>1,'user_login'=>'fixture','user_email'=>'fixture@example.invalid','user_pass'=>$hash]);
$provider = new Corcel\Laravel\Auth\AuthUserProvider;
$user = $provider->retrieveByCredentials(['email'=>'fixture@example.invalid']);
$checks = [
    'provider implements Laravel contract' => $provider instanceof Illuminate\Contracts\Auth\UserProvider,
    'model implements Laravel contract' => $user instanceof Illuminate\Contracts\Auth\Authenticatable,
    'WordPress password column' => $user->getAuthPasswordName() === 'user_pass',
    'retrieval by ID' => $provider->retrieveById(1)->user_login === 'fixture',
    'correct password accepted' => $provider->validateCredentials($user,['password'=>'fixture-password']),
    'incorrect password rejected' => !$provider->validateCredentials($user,['password'=>'wrong']),
    'missing password rejected' => !$provider->validateCredentials($user,[]),
];
$provider->rehashPasswordIfRequired($user,['password'=>'fixture-password'],true);
$checks['Laravel rehash leaves WordPress hash unchanged'] = $db->connection('wp')->table('users')->value('user_pass') === $hash;
foreach ($checks as $label=>$ok) { if (!$ok) throw new RuntimeException($label); echo "PASS $label\n"; }
echo count($checks)." Corcel compatibility checks passed\n";
