<?php
declare(strict_types=1);
define('ROOT',dirname(__DIR__));
if(is_file(ROOT.'/vendor/autoload.php'))require ROOT.'/vendor/autoload.php';else spl_autoload_register(function(string $class){if(str_starts_with($class,'App\\')){$file=ROOT.'/app/'.str_replace('\\','/',substr($class,4)).'.php';if(is_file($file))require $file;}});
function env(string $key, ?string $default=null): ?string { static $loaded=false,$data=[]; if(!$loaded){$file=ROOT.'/.env'; if(is_file($file)) foreach(file($file,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line){if(!str_starts_with(trim($line),'#')&&str_contains($line,'=')){[$k,$v]=explode('=',$line,2);$data[trim($k)]=trim($v," \t\n\r\0\x0B\"'");}} $loaded=true;} return $_ENV[$key]??getenv($key)?:($data[$key]??$default); }
$app=require ROOT.'/config/app.php'; date_default_timezone_set('UTC');
ini_set('display_errors',env('APP_ENV','production')==='production'?'0':'1'); ini_set('log_errors','1'); ini_set('error_log',ROOT.'/storage/logs/app.log');
set_exception_handler(function(Throwable $e){error_log((string)$e);http_response_code(500);echo env('APP_ENV')==='production'?'Terjadi kesalahan. Silakan coba lagi.':'<pre>'.htmlspecialchars((string)$e).'</pre>';});
session_set_cookie_params(['httponly'=>true,'secure'=>(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off'),'samesite'=>'Lax','path'=>'/']); session_start();
function db(): PDO {static $pdo;if(!$pdo){$c=require ROOT.'/config/database.php';$pdo=new PDO($c['dsn'],$c['user'],$c['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);}return $pdo;}
function e(?string $v):string{return htmlspecialchars($v??'',ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function csrf():string{return $_SESSION['csrf']??=bin2hex(random_bytes(32));}
function csrf_field():string{return '<input type="hidden" name="csrf" value="'.e(csrf()).'">';}
function verify_csrf():void{if(!hash_equals($_SESSION['csrf']??'',(string)($_POST['csrf']??''))){http_response_code(419);exit('Permintaan tidak valid. Muat ulang halaman.');}}
function user():?array{return $_SESSION['user']??null;}
function redirect(string $to,int $code=302):never{header('Location: '.$to,true,$code);exit;}
function slugify(string $s):string{$s=iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s)?:$s;$s=strtolower(preg_replace('/[^a-zA-Z0-9]+/','-',$s));return trim($s,'-')?:'artikel';}
