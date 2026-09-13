<?php
function view(string $name,array $data=[]):void{extract($data);$app=require ROOT.'/config/app.php';ob_start();require ROOT.'/app/Views/'.$name.'.php';$content=ob_get_clean();require ROOT.'/app/Views/layout.php';}
function setting(string $key,string $fallback=''):string{static $s=[];if(!array_key_exists($key,$s)){$q=db()->prepare('SELECT value FROM settings WHERE `key`=?');$q->execute([$key]);$s[$key]=$q->fetchColumn()?:$fallback;}return $s[$key];}
function fmt_date(?string $date):string{if(!$date)return '';$tz=new DateTimeZone((require ROOT.'/config/app.php')['timezone']);return (new DateTimeImmutable($date,new DateTimeZone('UTC')))->setTimezone($tz)->format('d M Y');}
function admin_only():void{if(!user())redirect('/admin/login');if(user()['role']!=='admin'){http_response_code(403);exit('Akses ditolak.');}}
