<?php require dirname(__DIR__).'/app/bootstrap.php';
$fail=0;function check(bool $ok,string $name):void{global $fail;echo ($ok?'PASS':'FAIL')." $name\n";if(!$ok)$fail++;}
check(slugify('Tips Belajar Aman!')==='tips-belajar-aman','slug normalisasi');
check(hash_equals(csrf(),csrf()),'token CSRF stabil dalam sesi');
if(class_exists('HTMLPurifier')){$s=new App\Services\HtmlSanitizer;$clean=$s->clean('<p onclick="bad()">Aman</p><script>alert(1)</script><a href="javascript:alert(1)">x</a>');check(!str_contains($clean,'script')&&!str_contains($clean,'onclick')&&!str_contains($clean,'javascript:'),'sanitasi HTML berbahaya');}else echo "SKIP sanitasi HTML (Composer belum tersedia)\n";
check(password_verify('rahasia-yang-panjang',password_hash('rahasia-yang-panjang',PASSWORD_DEFAULT)),'password hash/verify');
exit($fail?1:0);
