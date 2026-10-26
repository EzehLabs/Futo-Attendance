<?php
require __DIR__.'/../config/config.php';
// Keep unexpected backend failures machine-readable for the admin UI, and log details
// for diagnosis rather than returning an HTML error page that JavaScript cannot parse.
set_exception_handler(function(Throwable $e): void {
    error_log('[FUTO Attendance API] '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode(['ok'=>false,'error'=>'Server/database error. Check the PHP error log for details.'], JSON_UNESCAPED_SLASHES);
    exit;
});
$method=$_SERVER['REQUEST_METHOD'];
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$base='/api';
$route=isset($_GET['route']) ? trim((string)$_GET['route'],'/') : trim(substr($path,strlen($base)),'/');

function ok($data=[]){ json_out(['ok'=>true,'data'=>$data]); }
function fail(string $message,int $status=400){ json_out(['ok'=>false,'error'=>$message],$status); }
function audit_log(string $action,string $entity,?int $entityId=null,?string $details=null):void{
    $u=user(); $s=db()->prepare('INSERT INTO audit_logs(actor_id,action,entity,entity_id,details) VALUES(?,?,?,?,?)');
    $s->execute([$u['id']??null,$action,$entity,$entityId,$details]);
}
function csrf_ok():void{
    $token=$_SERVER['HTTP_X_CSRF_TOKEN']??'';
    if(!$token || !hash_equals($_SESSION['csrf_token']??'', $token)) fail('Invalid security token',419);
}
function csrf_token():string{
    if(empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(24));
    return $_SESSION['csrf_token'];
}
function setting(string $key,string $default):string{
    try{$s=db()->prepare('SELECT setting_value FROM settings WHERE setting_key=?');$s->execute([$key]);$r=$s->fetch();return $r['setting_value']??$default;}catch(Throwable $e){return $default;}
}
function auto_close_finished_sessions():void{
    try{$pdo=db();$now=(new DateTimeImmutable('now',new DateTimeZone('Africa/Lagos')))->format('Y-m-d H:i:s');$pdo->beginTransaction();
      $q=$pdo->prepare('SELECT * FROM class_sessions WHERE closed=0 AND cancelled=0 AND TIMESTAMP(session_date,end_time) <= ? FOR UPDATE');$q->execute([$now]);$rows=$q->fetchAll();
      foreach($rows as $ss){$e=$pdo->prepare('SELECT student_id FROM enrolments WHERE course_id=?');$e->execute([$ss['course_id']]);$ins=$pdo->prepare('INSERT IGNORE INTO attendance(session_id,student_id,status,source) VALUES(?,?,"Absent","SYSTEM")');foreach($e as $student)$ins->execute([$ss['id'],$student['student_id']]);$pdo->prepare('UPDATE class_sessions SET closed=1 WHERE id=?')->execute([$ss['id']]);}
      $pdo->commit();
    }catch(Throwable $e){if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();}
}
function smtp_command($socket, string $command, array $expected): string {
    if ($command !== '') fwrite($socket, $command."\r\n");
    $response='';
    do {
        $line=fgets($socket, 515);
        if ($line===false) throw new RuntimeException('SMTP server closed the connection');
        $response.=$line;
    } while (strlen($line)>=4 && $line[3]==='-');
    $code=(int)substr($response,0,3);
    if (!in_array($code,$expected,true)) throw new RuntimeException('SMTP command failed: '.trim($response));
    return $response;
}
function smtp_send(string $to, string $subject, string $message): bool {
    $host=envv('SMTP_HOST','');
    if (!$host) {
        // A local PHP mail transport may be configured instead, but SMTP is preferred.
        $from=envv('MAIL_FROM','no-reply@localhost');
        return @mail($to,$subject,$message,"From: {$from}\r\nContent-Type: text/plain; charset=UTF-8");
    }
    $port=(int)envv('SMTP_PORT','1025'); $secure=strtolower((string)envv('SMTP_SECURE','none'));
    $timeout=10; $context=stream_context_create();
    $target=($secure==='ssl'?'ssl://':'tcp://').$host.':'.$port;
    $socket=@stream_socket_client($target,$errno,$errstr,$timeout,STREAM_CLIENT_CONNECT,$context);
    if (!$socket) { error_log("SMTP connection failed: {$errstr} ({$errno})"); return false; }
    stream_set_timeout($socket,$timeout);
    try {
        smtp_command($socket,'',[220]);
        $hello=gethostname() ?: 'localhost';
        smtp_command($socket,'EHLO '.$hello,[250]);
        if ($secure==='tls') {
            smtp_command($socket,'STARTTLS',[220]);
            if (!stream_socket_enable_crypto($socket,true,STREAM_CRYPTO_METHOD_TLS_CLIENT)) throw new RuntimeException('Could not establish SMTP TLS');
            smtp_command($socket,'EHLO '.$hello,[250]);
        }
        $username=envv('SMTP_USER',''); $password=envv('SMTP_PASS','');
        if ($username!=='') { smtp_command($socket,'AUTH LOGIN',[334]); smtp_command($socket,base64_encode($username),[334]); smtp_command($socket,base64_encode((string)$password),[235]); }
        $from=envv('MAIL_FROM',$username ?: 'no-reply@localhost');
        $envelopeFrom=$from; if(preg_match('/<([^>]+)>/',$from,$fm))$envelopeFrom=$fm[1];
        smtp_command($socket,'MAIL FROM:<'.$envelopeFrom.'>',[250]);
        smtp_command($socket,'RCPT TO:<'.$to.'>',[250,251]);
        smtp_command($socket,'DATA',[354]);
        $safeSubject='=?UTF-8?B?'.base64_encode($subject).'?=';
        $headers = "From: {$from}\n"
        . "To: {$to}\n"
        . "Subject: {$safeSubject}\n"
        . "Date: " . date(DATE_RFC2822) . "\n"
        . "MIME-Version: 1.0\n"
        . "Content-Type: text/plain; charset=UTF-8\n"
        . "Content-Transfer-Encoding: 8bit\n";

        $body = str_replace(["\r\n", "\r"], "\n", $message);
        $body = preg_replace('/(?m)^\./', '..', $body);
        $data = str_replace("\n", "\r\n", $headers . "\n" . $body);
        fwrite($socket, $data . "\r\n.\r\n");
        $response=''; do { $line=fgets($socket,515); if($line===false)throw new RuntimeException('SMTP DATA response missing');$response.=$line; } while(strlen($line)>=4&&$line[3]==='-');
        if ((int)substr($response,0,3)!==250) throw new RuntimeException('SMTP rejected message: '.trim($response));
        smtp_command($socket,'QUIT',[221]); fclose($socket); return true;
    } catch(Throwable $e) { error_log('[FUTO email] '.$e->getMessage()); fclose($socket); return false; }
}
function notify_session_students(PDO $pdo,int $courseId,string $code,string $date,string $start,string $end,string $venue,bool $cancelled=false):int{
    $q=$pdo->prepare('SELECT DISTINCT u.email,u.first_name FROM users u JOIN enrolments e ON e.student_id=u.id WHERE e.course_id=? AND u.role="student" AND u.active=1 AND u.email IS NOT NULL AND u.email<>""');$q->execute([$courseId]);$sent=0;
    $subject=($cancelled?'Class session cancelled: ':'Class session created: ').$code;
    foreach($q as $r){
        $to=filter_var($r['email'],FILTER_VALIDATE_EMAIL);if(!$to)continue;
        if($cancelled) $message="Hello {$r['first_name']},\n\nThe scheduled class session for {$code} has been CANCELLED.\nDate: {$date}\nTime: {$start} - {$end}\nVenue: {$venue}\n\nPlease check your student portal for updates.";
        else $message="Hello {$r['first_name']},\n\nA class session has been scheduled for {$code}.\nDate: {$date}\nTime: {$start} - {$end}\nVenue: {$venue}\n\nPlease check your student portal for details.";
        if(smtp_send($to,$subject,$message))$sent++;
    }
    return $sent;
}
function process_punch(PDO $pdo,int $deviceId,string $deviceUserId,string $punchTime,?string $recordKey=null):array{
    if(!$recordKey) $recordKey=hash('sha256',$deviceId.'|'.$deviceUserId.'|'.$punchTime.'|'.microtime(true));
    $s=$pdo->prepare('INSERT INTO raw_punches(device_id,device_user_id,punch_time,device_record_key) VALUES(?,?,?,?)');
    $s->execute([$deviceId,$deviceUserId,$punchTime,$recordKey]);
    $punchId=(int)$pdo->lastInsertId();
    $pdo->prepare('UPDATE devices SET last_seen_at=NOW() WHERE id=?')->execute([$deviceId]);
    $q=$pdo->prepare('SELECT u.id student_id,s.id session_id,s.start_time,s.end_time,c.code FROM users u JOIN enrolments e ON e.student_id=u.id JOIN class_sessions s ON s.course_id=e.course_id JOIN courses c ON c.id=s.course_id WHERE u.login_id=? AND u.role="student" AND u.active=1 AND s.device_id=? AND s.session_date=DATE(?) AND s.cancelled=0 AND TIME(?) BETWEEN s.start_time AND s.end_time ORDER BY s.id DESC LIMIT 1');
    $q->execute([$deviceUserId,$deviceId,$punchTime,$punchTime]);
    $match=$q->fetch();
    $result=['punch_id'=>$punchId,'matched'=>false,'status'=>null];
    if($match){
        $late=(int)setting('late_window_minutes','15');
        $punchDate=date('Y-m-d',strtotime($punchTime));
        $cutoff=strtotime($punchDate.' '.$match['start_time'])+($late*60);
        $status=(strtotime($punchTime)<= $cutoff)?'Present':'Late';
        $ins=$pdo->prepare('INSERT IGNORE INTO attendance(session_id,student_id,status,punch_id,source) VALUES(?,?,?, ?,"DEVICE")');
        $ins->execute([(int)$match['session_id'],(int)$match['student_id'],$status,$punchId]);
        $result=['punch_id'=>$punchId,'matched'=>true,'status'=>$status,'session_id'=>(int)$match['session_id'],'course'=>$match['code']];
    }
    return $result;
}

auto_close_finished_sessions();
if($route==='health' && $method==='GET'){try{db()->query('SELECT 1');ok(['database'=>'ok']);}catch(Throwable $e){fail('Database unavailable',503);}}
if($route==='auth/login' && $method==='POST'){
    $b=body(); $id=trim($b['login_id']??''); $pw=$b['password']??'';
    if(!$id||!$pw) fail('Login ID and password are required',422);
    $s=db()->prepare('SELECT id,login_id,password_hash,role,first_name,last_name,email,phone,department,level,active FROM users WHERE login_id=? LIMIT 1');$s->execute([$id]);$u=$s->fetch();
    if(!$u||!$u['active']||!password_verify($pw,$u['password_hash'])) fail('Invalid login credentials',401);
    unset($u['password_hash']); session_regenerate_id(true); $_SESSION['user']=$u; csrf_token(); audit_log('LOGIN','user',(int)$u['id']); ok(['user'=>$u,'csrf'=>$_SESSION['csrf_token']]);
}
if($route==='auth/logout' && $method==='POST'){require_login();csrf_ok();$_SESSION=[];session_destroy();ok();}
if($route==='auth/me' && $method==='GET'){ $u=require_login(); ok(['user'=>$u,'csrf'=>csrf_token()]); }

if($route==='admin/dashboard' && $method==='GET'){
    require_login(['admin']); $p=db(); $counts=[];
    foreach(['student','lecturer','admin'] as $r){$s=$p->prepare('SELECT COUNT(*) n FROM users WHERE role=? AND active=1');$s->execute([$r]);$counts[$r]=(int)$s->fetch()['n'];}
    $counts['courses']=(int)$p->query('SELECT COUNT(*) n FROM courses WHERE active=1')->fetch()['n'];
    $counts['devices']=(int)$p->query('SELECT COUNT(*) n FROM devices WHERE active=1')->fetch()['n'];
    $counts['attendance_today']=(int)$p->query('SELECT COUNT(*) n FROM attendance a JOIN class_sessions s ON s.id=a.session_id WHERE s.session_date=CURDATE()')->fetch()['n'];
    $counts['unmatched']=(int)$p->query('SELECT COUNT(*) n FROM raw_punches rp LEFT JOIN users u ON u.login_id=rp.device_user_id WHERE u.id IS NULL')->fetch()['n'];
    $counts['closed_sessions']=(int)$p->query('SELECT COUNT(*) n FROM class_sessions WHERE closed=1 AND cancelled=0')->fetch()['n'];
    ok($counts);
}

if($route==='admin/users' && $method==='GET'){require_login(['admin']);ok(db()->query('SELECT id,login_id,role,first_name,last_name,email,phone,department,level,active FROM users ORDER BY role,first_name,last_name')->fetchAll());}
if($route==='admin/users' && $method==='POST'){
    require_login(['admin']);csrf_ok();$b=body();
    foreach(['login_id','password','role','first_name','last_name'] as $k)if(empty($b[$k]))fail("$k is required",422);
    if(!in_array($b['role'],['student','lecturer','admin'],true))fail('Invalid role',422);
    try{$s=db()->prepare('INSERT INTO users(login_id,password_hash,role,first_name,last_name,email,phone,department,level) VALUES(?,?,?,?,?,?,?,?,?)');$s->execute([$b['login_id'],password_hash($b['password'],PASSWORD_BCRYPT),$b['role'],$b['first_name'],$b['last_name'],$b['email']??null,$b['phone']??null,$b['department']??null,$b['level']??null]);$id=(int)db()->lastInsertId();audit_log('CREATE','user',$id,$b['role']);ok(['id'=>$id]);}catch(PDOException $e){fail('Login ID already exists',409);}
}
if($route==='admin/users/bulk' && $method==='POST'){
 require_login(['admin']);csrf_ok();$b=body();$role=$b['role']??'';$rows=$b['rows']??[];if(!in_array($role,['student','lecturer'],true)||!is_array($rows)||!count($rows))fail('Choose a role and provide at least one row',422);if(count($rows)>1000)fail('Maximum 1000 records per upload',422);$pdo=db();$created=0;$errors=[];$pdo->beginTransaction();try{foreach($rows as $i=>$r){$login=trim($r['login_id']??$r['matric_no']??$r['staff_id']??'');$first=trim($r['first_name']??'');$last=trim($r['last_name']??'');$email=trim($r['email']??'');if(!$login||!$first||!$last){$errors[]='Row '.($i+2).': login_id, first_name and last_name are required';continue;}if($email&&!filter_var($email,FILTER_VALIDATE_EMAIL)){$errors[]='Row '.($i+2).': invalid email';continue;}try{$st=$pdo->prepare('INSERT INTO users(login_id,password_hash,role,first_name,last_name,email,phone,department,level) VALUES(?,?,?,?,?,?,?,?,?)');$st->execute([$login,password_hash($r['password']??'password',PASSWORD_BCRYPT),$role,$first,$last,$email?:null,trim($r['phone']??'')?:null,trim($r['department']??'Computer Science'),trim((string)($r['level']??''))?:null]);$created++;}catch(PDOException $e){$errors[]='Row '.($i+2).': duplicate login ID or invalid data ('.$login.')';}}$pdo->commit();audit_log('BULK_CREATE','users',null,"Role $role; created $created");ok(['created'=>$created,'errors'=>$errors]);}catch(Throwable $e){$pdo->rollBack();fail('Bulk registration failed',500);}
}
if($route==='admin/users/update' && $method==='POST'){
    require_login(['admin']);csrf_ok();$b=body();$id=(int)($b['id']??0);if(!$id)fail('User ID is required',422);
    $fields=['email','phone','department','level'];$sets=[];$vals=[];foreach($fields as $f){if(array_key_exists($f,$b)){$sets[]="$f=?";$vals[]=$b[$f]!==''?$b[$f]:null;}}
    if(isset($b['active'])){$sets[]='active=?';$vals[]=$b['active']?1:0;}
    if(!$sets)fail('Nothing to update',422);$vals[]=$id;$s=db()->prepare('UPDATE users SET '.implode(',',$sets).' WHERE id=?');$s->execute($vals);audit_log('UPDATE','user',$id,'Profile/status updated');ok();
}
if($route==='admin/users/reset-password' && $method==='POST'){
    require_login(['admin']);csrf_ok();$b=body();$id=(int)($b['id']??0);$pw=$b['password']??'password';if(!$id)fail('User ID is required',422);$s=db()->prepare('UPDATE users SET password_hash=? WHERE id=?');$s->execute([password_hash($pw,PASSWORD_BCRYPT),$id]);audit_log('RESET_PASSWORD','user',$id);ok(['temporary_password'=>$pw]);
}

if($route==='profile/update' && $method==='POST'){
    $u=require_login();csrf_ok();$b=body();$email=$b['email']??null;$phone=$b['phone']??null;$s=db()->prepare('UPDATE users SET email=?,phone=? WHERE id=?');$s->execute([$email?:null,$phone?:null,$u['id']]);$_SESSION['user']['email']=$email;$_SESSION['user']['phone']=$phone;audit_log('UPDATE','profile',(int)$u['id']);ok();
}
if($route==='profile/password' && $method==='POST'){
    $u=require_login();csrf_ok();$b=body();$old=$b['current_password']??'';$new=$b['new_password']??'';if(strlen($new)<6)fail('New password must be at least 6 characters',422);$s=db()->prepare('SELECT password_hash FROM users WHERE id=?');$s->execute([$u['id']]);$r=$s->fetch();if(!$r||!password_verify($old,$r['password_hash']))fail('Current password is incorrect',422);$db=db();$db->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($new,PASSWORD_BCRYPT),$u['id']]);audit_log('CHANGE_PASSWORD','user',(int)$u['id']);ok();
}

if($route==='admin/courses' && $method==='GET'){
    require_login(['admin']);ok(db()->query('SELECT c.*,GROUP_CONCAT(CONCAT(u.first_name," ",u.last_name) SEPARATOR ", ") lecturer_names,(SELECT COUNT(*) FROM enrolments e WHERE e.course_id=c.id) enrolment_count FROM courses c LEFT JOIN course_lecturers cl ON cl.course_id=c.id LEFT JOIN users u ON u.id=cl.lecturer_id GROUP BY c.id ORDER BY c.code')->fetchAll());
}
if($route==='admin/courses' && $method==='POST'){
    require_login(['admin']);csrf_ok();$b=body();if(empty($b['code'])||empty($b['title']))fail('Course code and title are required',422);try{$planned=(int)($b['planned_sessions']??17);if($planned<1||$planned>100)fail('Planned sessions must be between 1 and 100',422);$s=db()->prepare('INSERT INTO courses(code,title,department,semester,planned_sessions) VALUES(?,?,?,?,?)');$s->execute([$b['code'],$b['title'],$b['department']??null,$b['semester']??null,$planned]);$id=(int)db()->lastInsertId();if(!empty($b['lecturer_id'])){db()->prepare('INSERT IGNORE INTO course_lecturers(course_id,lecturer_id) VALUES(?,?)')->execute([$id,(int)$b['lecturer_id']]);}audit_log('CREATE','course',$id,$b['code']);ok(['id'=>$id]);}catch(PDOException $e){fail('Course code already exists',409);}
}
if($route==='admin/courses/update' && $method==='POST'){
    require_login(['admin']);csrf_ok();$b=body();$id=(int)($b['id']??0);if(!$id)fail('Course ID is required',422);$planned=(int)($b['planned_sessions']??17);if($planned<1||$planned>100)fail('Planned sessions must be between 1 and 100',422);$s=db()->prepare('UPDATE courses SET title=?,department=?,semester=?,planned_sessions=?,active=? WHERE id=?');$s->execute([$b['title']??'', $b['department']??null,$b['semester']??null,$planned,!empty($b['active'])?1:0,$id]);audit_log('UPDATE','course',$id);ok();
}
if($route==='admin/courses/assign' && $method==='POST'){
    require_login(['admin']);csrf_ok();$b=body();$course=(int)($b['course_id']??0);$lect=(int)($b['lecturer_id']??0);if(!$course||!$lect)fail('Course and lecturer are required',422);db()->prepare('INSERT IGNORE INTO course_lecturers(course_id,lecturer_id) VALUES(?,?)')->execute([$course,$lect]);audit_log('ASSIGN','course',$course,'Lecturer '.$lect);ok();
}
if($route==='admin/courses/unassign' && $method==='POST'){
    require_login(['admin']);csrf_ok();$b=body();db()->prepare('DELETE FROM course_lecturers WHERE course_id=? AND lecturer_id=?')->execute([(int)$b['course_id'],(int)$b['lecturer_id']]);audit_log('UNASSIGN','course',(int)$b['course_id']);ok();
}

if($route==='admin/enrolments' && $method==='GET'){
    require_login(['admin']);$s=db()->prepare('SELECT e.course_id,e.student_id,c.code,c.title,u.login_id,CONCAT(u.first_name," ",u.last_name) student FROM enrolments e JOIN courses c ON c.id=e.course_id JOIN users u ON u.id=e.student_id WHERE u.role="student" ORDER BY c.code,student');$s->execute();ok($s->fetchAll());
}
if($route==='admin/enrolments' && $method==='POST'){
    require_login(['admin']);csrf_ok();$b=body();$course=(int)($b['course_id']??0);$student=(int)($b['student_id']??0);if(!$course||!$student)fail('Course and student are required',422);db()->prepare('INSERT IGNORE INTO enrolments(course_id,student_id) VALUES(?,?)')->execute([$course,$student]);audit_log('ENROL','enrolment',null,'Course '.$course.' Student '.$student);ok();
}
if($route==='admin/enrolments/bulk' && $method==='POST'){
 require_login(['admin']);csrf_ok();$b=body();$course=(int)($b['course_id']??0);$ids=$b['student_ids']??[];if(!$course||!is_array($ids)||!$ids)fail('Course and student IDs are required',422);$pdo=db();$st=$pdo->prepare('INSERT IGNORE INTO enrolments(course_id,student_id) SELECT ?,id FROM users WHERE id=? AND role="student" AND active=1');$added=0;foreach(array_unique(array_map('intval',$ids)) as $id){$st->execute([$course,$id]);$added+=$st->rowCount();}audit_log('BULK_ENROL','enrolment',null,"Course $course; added $added");ok(['added'=>$added]);
}
if($route==='admin/enrolments/delete' && $method==='POST'){
    require_login(['admin']);csrf_ok();$b=body();db()->prepare('DELETE FROM enrolments WHERE course_id=? AND student_id=?')->execute([(int)$b['course_id'],(int)$b['student_id']]);audit_log('UNENROL','enrolment',null,'Course '.$b['course_id'].' Student '.$b['student_id']);ok();
}

if($route==='admin/venues' && $method==='GET'){require_login(['admin']);ok(db()->query('SELECT * FROM venues ORDER BY name')->fetchAll());}
if($route==='admin/venues' && $method==='POST'){require_login(['admin']);csrf_ok();$b=body();if(empty($b['name']))fail('Venue name is required',422);try{$s=db()->prepare('INSERT INTO venues(name) VALUES(?)');$s->execute([$b['name']]);$id=(int)db()->lastInsertId();audit_log('CREATE','venue',$id);ok(['id'=>$id]);}catch(PDOException $e){fail('Venue already exists',409);}}

if($route==='admin/devices' && $method==='GET'){require_login(['admin']);ok(db()->query('SELECT d.*,v.name venue_name FROM devices d JOIN venues v ON v.id=d.venue_id ORDER BY d.name')->fetchAll());}
if($route==='admin/devices' && $method==='POST'){
    require_login(['admin']);csrf_ok();$b=body();foreach(['device_code','name','venue_id'] as $k)if(empty($b[$k]))fail("$k is required",422);try{$s=db()->prepare('INSERT INTO devices(device_code,name,venue_id,ip_address,port) VALUES(?,?,?,?,?)');$s->execute([$b['device_code'],$b['name'],(int)$b['venue_id'],$b['ip_address']??null,(int)($b['port']??4370)]);$id=(int)db()->lastInsertId();audit_log('CREATE','device',$id);ok(['id'=>$id]);}catch(PDOException $e){fail('Device code already exists',409);}
}
if($route==='admin/devices/touch' && $method==='POST'){require_login(['admin']);csrf_ok();$b=body();$id=(int)($b['id']??0);if(!$id)fail('Device ID required',422);db()->prepare('UPDATE devices SET last_seen_at=NOW() WHERE id=?')->execute([$id]);audit_log('TEST','device',$id,'Marked online for demo');ok();}

if($route==='admin/sessions' && $method==='GET'){
    require_login(['admin']);$sql='SELECT s.*,c.code,c.title,v.name venue_name,d.device_code,(SELECT COUNT(*) FROM enrolments e WHERE e.course_id=s.course_id) student_count FROM class_sessions s JOIN courses c ON c.id=s.course_id JOIN venues v ON v.id=s.venue_id JOIN devices d ON d.id=s.device_id ORDER BY s.session_date DESC,s.start_time DESC';ok(db()->query($sql)->fetchAll());
}
if($route==='admin/sessions' && $method==='POST'){require_login(['admin']);fail('Session creation is only available from the lecturer dashboard.',403);}
if($route==='admin/sessions/cancel' && $method==='POST'){require_login(['admin']);csrf_ok();$id=(int)(body()['id']??0);if(!$id)fail('Session ID required',422);db()->prepare('UPDATE class_sessions SET cancelled=1 WHERE id=?')->execute([$id]);audit_log('CANCEL','session',$id);ok();}
if($route==='admin/sessions/close-status' && $method==='GET'){
    require_login(['admin']);
    $wat=(new DateTimeImmutable('now',new DateTimeZone('Africa/Lagos')))->format('Y-m-d H:i:s');
    $q=db()->prepare('SELECT COUNT(*) n FROM class_sessions WHERE closed=0 AND cancelled=0 AND TIMESTAMP(session_date,end_time) < ?');
    $q->execute([$wat]);
    $n=(int)$q->fetch()['n'];
    ok(['finished_unclosed'=>$n,'can_close'=>$n>0,'checked_at_wat'=>$wat]);
}
if($route==='admin/sessions/close' && $method==='POST'){require_login(['admin']);fail('Session closing is only available from the lecturer dashboard or automatic session closure.',403);}

if($route==='admin/attendance' && $method==='GET'){
    require_login(['admin']);$sql='SELECT a.id,a.session_id,a.student_id,u.login_id,CONCAT(u.first_name," ",u.last_name) student,c.code course,c.title,s.session_date,s.start_time,s.end_time,a.status,a.source,a.correction_reason FROM attendance a JOIN users u ON u.id=a.student_id JOIN class_sessions s ON s.id=a.session_id JOIN courses c ON c.id=s.course_id ORDER BY s.session_date DESC,s.start_time DESC,a.id DESC';ok(db()->query($sql)->fetchAll());
}
if($route==='admin/attendance/update' && $method==='POST'){
    require_login(['admin']);csrf_ok();$b=body();$id=(int)($b['id']??0);$status=$b['status']??'';$reason=trim($b['reason']??'');if(!$id||!in_array($status,['Present','Late','Absent','Excused'],true)||$reason==='')fail('Attendance ID, valid status and reason are required',422);$s=db()->prepare('UPDATE attendance SET status=?,source="MANUAL",correction_reason=? WHERE id=?');$s->execute([$status,$reason,$id]);audit_log('CORRECT','attendance',$id,$status.' — '.$reason);ok();
}

if($route==='admin/test-punch' && $method==='POST'){
    require_login(['admin']);csrf_ok();$b=body();foreach(['device_id','device_user_id','punch_time'] as $k)if(empty($b[$k]))fail("$k is required",422);$pdo=db();$pdo->beginTransaction();try{$result=process_punch($pdo,(int)$b['device_id'],trim($b['device_user_id']),$b['punch_time'],null);$pdo->commit();audit_log('TEST_PUNCH','raw_punch',(int)$result['punch_id'],json_encode($result));ok($result);}catch(PDOException $e){$pdo->rollBack();if($e->getCode()==='23000')fail('This simulated punch is already recorded',409);fail('Punch could not be processed',500);}
}
if($route==='device/punch' && $method==='POST'){
    $key=$_SERVER['HTTP_X_SYNC_KEY']??'';if($key!==envv('SYNC_API_KEY','change-this-key'))fail('Invalid sync key',401);$b=body();foreach(['device_id','device_user_id','punch_time'] as $k)if(empty($b[$k]))fail("$k is required",422);$pdo=db();$pdo->beginTransaction();try{$result=process_punch($pdo,(int)$b['device_id'],$b['device_user_id'],$b['punch_time'],$b['device_record_key']??null);$pdo->commit();ok($result);}catch(PDOException $e){$pdo->rollBack();if($e->getCode()==='23000')ok(['duplicate'=>true]);fail('Punch could not be processed',500);}
}
if($route==='admin/unmatched' && $method==='GET'){
    require_login(['admin']);$sql='SELECT rp.id,rp.device_user_id,rp.punch_time,rp.received_at,d.device_code,d.name FROM raw_punches rp JOIN devices d ON d.id=rp.device_id LEFT JOIN users u ON u.login_id=rp.device_user_id WHERE u.id IS NULL ORDER BY rp.punch_time DESC';ok(db()->query($sql)->fetchAll());
}

if($route==='admin/reports/attendance.csv' && $method==='GET'){
    require_login(['admin','lecturer']);$u=user();$sql='SELECT u.login_id,CONCAT(u.first_name," ",u.last_name) student,c.code course,c.title,s.session_date,s.start_time,a.status,a.source,a.correction_reason FROM attendance a JOIN users u ON u.id=a.student_id JOIN class_sessions s ON s.id=a.session_id JOIN courses c ON c.id=s.course_id';$params=[];if($u['role']==='lecturer'){$sql.=' JOIN course_lecturers cl ON cl.course_id=c.id WHERE cl.lecturer_id=?';$params[]=$u['id'];}$sql.=' ORDER BY s.session_date DESC,s.start_time DESC';$st=db()->prepare($sql);$st->execute($params);header('Content-Type:text/csv; charset=utf-8');header('Content-Disposition:attachment; filename="attendance-report.csv"');$out=fopen('php://output','w');fputcsv($out,['Student ID','Student','Course','Title','Date','Start Time','Status','Source','Correction Reason']);while($r=$st->fetch())fputcsv($out,$r);fclose($out);exit;
}
if($route==='admin/audit' && $method==='GET'){require_login(['admin']);ok(db()->query('SELECT a.id,a.action,a.entity,a.entity_id,a.details,a.created_at,COALESCE(CONCAT(u.first_name," ",u.last_name),"System") actor FROM audit_logs a LEFT JOIN users u ON u.id=a.actor_id ORDER BY a.id DESC LIMIT 200')->fetchAll());}
if($route==='admin/settings' && $method==='GET'){require_login(['admin']);ok(db()->query('SELECT setting_key,setting_value FROM settings ORDER BY setting_key')->fetchAll());}
if($route==='admin/settings' && $method==='POST'){require_login(['admin']);csrf_ok();$b=body();foreach(['late_window_minutes','eligibility_threshold','current_semester'] as $k)if(isset($b[$k])){if($k==='late_window_minutes'&&!is_numeric($b[$k]))fail('Late window must be numeric',422);if($k==='eligibility_threshold'&&(!is_numeric($b[$k])||$b[$k]<0||$b[$k]>100))fail('Eligibility threshold must be between 0 and 100',422);db()->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)')->execute([$k,(string)$b[$k]]);}audit_log('UPDATE','settings',null,'Attendance rules updated');ok();}

if($route==='student/dashboard' && $method==='GET'){
    $u=require_login(['student']);$s=db()->prepare('SELECT COUNT(*) n FROM enrolments WHERE student_id=?');$s->execute([$u['id']]);$courses=(int)$s->fetch()['n'];$s=db()->prepare('SELECT status,COUNT(*) n FROM attendance a JOIN class_sessions cs ON cs.id=a.session_id WHERE a.student_id=? AND cs.session_date=CURDATE() GROUP BY status');$s->execute([$u['id']]);$today=['Present'=>0,'Late'=>0,'Absent'=>0,'Excused'=>0];foreach($s as $r)$today[$r['status']]=(int)$r['n'];ok(['courses'=>$courses,'today'=>$today,'threshold'=>(float)setting('eligibility_threshold','75')]);
}
if($route==='student/courses' && $method==='GET'){
    $u=require_login(['student']);
    $s=db()->prepare('SELECT c.id,c.code,c.title,c.planned_sessions,
        (SELECT COUNT(*) FROM class_sessions cs WHERE cs.course_id=c.id AND cs.closed=1 AND cs.cancelled=0) closed_sessions,
        (SELECT COUNT(*) FROM class_sessions cs WHERE cs.course_id=c.id AND cs.cancelled=0 AND TIMESTAMP(cs.session_date,cs.end_time) < ?) occurred_sessions,
        (SELECT COUNT(*) FROM attendance a JOIN class_sessions cs ON cs.id=a.session_id WHERE a.student_id=? AND cs.course_id=c.id AND a.status IN ("Present","Late","Excused")) attended
        FROM enrolments e JOIN courses c ON c.id=e.course_id WHERE e.student_id=? ORDER BY c.code');
    $wat=(new DateTimeImmutable('now',new DateTimeZone('Africa/Lagos')))->format('Y-m-d H:i:s');
    $s->execute([$wat,$u['id'],$u['id']]);
    $rows=$s->fetchAll();
    $threshold=(float)setting('eligibility_threshold','75');
    foreach($rows as &$r){
        $planned=max(1,(int)$r['planned_sessions']);
        $r['percentage']=round(min(100,$r['attended']/$planned*100),1);
        $r['progress_percentage']=$r['percentage'];
        $r['eligible']=$r['percentage']>=$threshold;
    }
    ok($rows);
}
if($route==='student/attendance' && $method==='GET'){$u=require_login(['student']);$s=db()->prepare('SELECT c.code,c.title,cs.session_date,cs.start_time,a.status,a.source FROM attendance a JOIN class_sessions cs ON cs.id=a.session_id JOIN courses c ON c.id=cs.course_id WHERE a.student_id=? ORDER BY cs.session_date DESC,cs.start_time DESC');$s->execute([$u['id']]);ok($s->fetchAll());}
if($route==='student/timetable' && $method==='GET'){
    $u=require_login(['student']);
    $q=db()->prepare('SELECT cs.id,cs.session_date,cs.start_time,cs.end_time,cs.cancelled,cs.closed,c.code,c.title,v.name venue_name,COALESCE(GROUP_CONCAT(DISTINCT CONCAT(l.first_name," ",l.last_name) SEPARATOR ", "),"Unassigned") lecturer_name,(SELECT COUNT(*) FROM enrolments ee WHERE ee.course_id=cs.course_id) student_count FROM class_sessions cs JOIN enrolments e ON e.course_id=cs.course_id JOIN courses c ON c.id=cs.course_id JOIN venues v ON v.id=cs.venue_id LEFT JOIN course_lecturers cl ON cl.course_id=c.id LEFT JOIN users l ON l.id=cl.lecturer_id WHERE e.student_id=? AND cs.session_date >= DATE_SUB(CURDATE(),INTERVAL 1 DAY) GROUP BY cs.id ORDER BY cs.session_date ASC,cs.start_time ASC');
    $q->execute([$u['id']]); ok($q->fetchAll());
}
if($route==='student/profile' && $method==='GET'){ $u=require_login(['student']);ok($u); }

if($route==='lecturer/session-options' && $method==='GET'){require_login(['lecturer']);ok(['venues'=>db()->query('SELECT id,name FROM venues ORDER BY name')->fetchAll(),'devices'=>db()->query('SELECT id,device_code,name,venue_id FROM devices WHERE active=1 ORDER BY name')->fetchAll()]);}
if($route==='lecturer/sessions' && $method==='GET'){
 $u=require_login(['lecturer']);$q=db()->prepare('SELECT s.*,c.code,c.title,v.name venue_name,d.device_code,(SELECT COUNT(*) FROM enrolments e WHERE e.course_id=s.course_id) student_count FROM class_sessions s JOIN courses c ON c.id=s.course_id JOIN venues v ON v.id=s.venue_id JOIN devices d ON d.id=s.device_id JOIN course_lecturers cl ON cl.course_id=c.id WHERE cl.lecturer_id=? ORDER BY s.session_date DESC,s.start_time DESC');$q->execute([$u['id']]);ok($q->fetchAll());
}
if($route==='lecturer/sessions' && $method==='POST'){
 $u=require_login(['lecturer']);csrf_ok();$b=body();foreach(['course_id','venue_id','device_id','session_date','start_time','end_time'] as $k)if(empty($b[$k]))fail("$k is required",422);if($b['start_time']>=$b['end_time'])fail('End time must be after start time',422);$pdo=db();$own=$pdo->prepare('SELECT COUNT(*) FROM course_lecturers WHERE course_id=? AND lecturer_id=?');$own->execute([(int)$b['course_id'],$u['id']]);if(!$own->fetchColumn())fail('You can only schedule sessions for your assigned courses',403);$q=$pdo->prepare('SELECT COUNT(*) FROM class_sessions WHERE venue_id=? AND session_date=? AND cancelled=0 AND start_time < ? AND end_time > ?');$q->execute([(int)$b['venue_id'],$b['session_date'],$b['end_time'],$b['start_time']]);if($q->fetchColumn())fail('This venue already has an overlapping session',409);$st=$pdo->prepare('INSERT INTO class_sessions(course_id,venue_id,device_id,session_date,start_time,end_time) VALUES(?,?,?,?,?,?)');$st->execute([(int)$b['course_id'],(int)$b['venue_id'],(int)$b['device_id'],$b['session_date'],$b['start_time'],$b['end_time']]);$id=(int)$pdo->lastInsertId();audit_log('CREATE','session',$id,'Lecturer created; notification queued for background request');ok(['id'=>$id,'notifications_pending'=>true]);
}
if($route==='lecturer/sessions/cancel' && $method==='POST'){
 $u=require_login(['lecturer']);csrf_ok();$id=(int)(body()['id']??0);$pdo=db();
 $q=$pdo->prepare('SELECT s.*,v.name venue_name FROM class_sessions s JOIN venues v ON v.id=s.venue_id JOIN course_lecturers cl ON cl.course_id=s.course_id WHERE s.id=? AND cl.lecturer_id=?');$q->execute([$id,$u['id']]);$ss=$q->fetch();
 if(!$ss)fail('Session not found for your courses',404);if((int)$ss['cancelled']===1)fail('This session is already cancelled',409);if((int)$ss['closed']===1)fail('A closed session cannot be cancelled',409);
 $pdo->prepare('UPDATE class_sessions SET cancelled=1 WHERE id=?')->execute([$id]);
 audit_log('CANCEL','session',$id,'Cancelled by lecturer; notification queued for background request');ok(['cancelled'=>true,'notifications_pending'=>true]);
}
if($route==='lecturer/sessions/notify' && $method==='POST'){
 $u=require_login(['lecturer']);csrf_ok();$b=body();$id=(int)($b['id']??0);$cancelled=!empty($b['cancelled']);$pdo=db();
 $q=$pdo->prepare('SELECT s.*,c.code,v.name venue_name FROM class_sessions s JOIN courses c ON c.id=s.course_id JOIN venues v ON v.id=s.venue_id JOIN course_lecturers cl ON cl.course_id=s.course_id WHERE s.id=? AND cl.lecturer_id=?');$q->execute([$id,$u['id']]);$ss=$q->fetch();
 if(!$ss)fail('Session not found for your courses',404);
 if($cancelled && (int)$ss['cancelled']!==1)fail('Class is not cancelled',409);
 if(!$cancelled && (int)$ss['cancelled']===1)fail('Cannot send a created-session notice for a cancelled class',409);
 // Release PHP's session lock before slow SMTP work so other lecturer-page requests stay responsive.
 if(session_status()===PHP_SESSION_ACTIVE)session_write_close();
 $sent=notify_session_students($pdo,(int)$ss['course_id'],(string)$ss['code'],$ss['session_date'],$ss['start_time'],$ss['end_time'],(string)$ss['venue_name'],$cancelled);
 audit_log($cancelled?'CANCEL_NOTIFY':'CREATE_NOTIFY','session',$id,'Notification emails successfully sent: '.$sent);
 ok(['emails_sent'=>$sent]);
}
if($route==='lecturer/sessions/close' && $method==='POST'){
 $u=require_login(['lecturer']);csrf_ok();$id=(int)(body()['id']??0);$pdo=db();$q=$pdo->prepare('SELECT s.*,v.name venue_name FROM class_sessions s JOIN venues v ON v.id=s.venue_id JOIN course_lecturers cl ON cl.course_id=s.course_id WHERE s.id=? AND cl.lecturer_id=?');$q->execute([$id,$u['id']]);$ss=$q->fetch();if(!$ss)fail('Session not found for your courses',404);$now=(new DateTimeImmutable('now',new DateTimeZone('Africa/Lagos')))->format('Y-m-d H:i:s');if(strtotime($ss['session_date'].' '.$ss['end_time'])>strtotime($now))fail('You can only close a session after its scheduled end time',422);$pdo->beginTransaction();try{$e=$pdo->prepare('SELECT student_id FROM enrolments WHERE course_id=?');$e->execute([$ss['course_id']]);$ins=$pdo->prepare('INSERT IGNORE INTO attendance(session_id,student_id,status,source) VALUES(?,?,"Absent","SYSTEM")');foreach($e as $st)$ins->execute([$id,$st['student_id']]);$pdo->prepare('UPDATE class_sessions SET closed=1 WHERE id=?')->execute([$id]);$pdo->commit();audit_log('CLOSE','session',$id,'Closed by lecturer');ok(['closed'=>true]);}catch(Throwable $e){$pdo->rollBack();fail('Could not close session',500);}
}
if($route==='lecturer/students' && $method==='GET'){
 $u=require_login(['lecturer']);
 $q=db()->prepare('SELECT c.id course_id,c.code,c.title,c.planned_sessions total_sessions,u.id student_id,u.login_id,CONCAT(u.first_name," ",u.last_name) student,u.email,u.department,u.level,
 (SELECT COUNT(*) FROM attendance a JOIN class_sessions ss ON ss.id=a.session_id WHERE ss.course_id=c.id AND a.student_id=u.id AND a.status IN ("Present","Late","Excused")) attended
 FROM course_lecturers cl JOIN courses c ON c.id=cl.course_id JOIN enrolments e ON e.course_id=c.id JOIN users u ON u.id=e.student_id
 WHERE cl.lecturer_id=? ORDER BY c.code,u.last_name,u.first_name');
 $q->execute([$u['id']]);$rows=$q->fetchAll();
 foreach($rows as &$r){$planned=max(0,(int)$r['total_sessions']);$attended=(int)$r['attended'];$r['attendance_percentage']=$planned>0?round(min(100,$attended/$planned*100),1):0;}
 unset($r);ok($rows);
}
if($route==='lecturer/dashboard' && $method==='GET'){$u=require_login(['lecturer']);$s=db()->prepare('SELECT COUNT(*) n FROM course_lecturers WHERE lecturer_id=?');$s->execute([$u['id']]);$courses=(int)$s->fetch()['n'];$s=db()->prepare('SELECT a.status,COUNT(*) n FROM attendance a JOIN class_sessions cs ON cs.id=a.session_id JOIN course_lecturers cl ON cl.course_id=cs.course_id WHERE cl.lecturer_id=? AND cs.session_date=CURDATE() GROUP BY a.status');$s->execute([$u['id']]);$today=['Present'=>0,'Late'=>0,'Absent'=>0,'Excused'=>0];foreach($s as $r)$today[$r['status']]=(int)$r['n'];ok(['courses'=>$courses,'today'=>$today]);}
if($route==='lecturer/courses' && $method==='GET'){$u=require_login(['lecturer']);$q=db()->prepare('SELECT c.id,c.code,c.title,(SELECT COUNT(*) FROM enrolments e WHERE e.course_id=c.id) students FROM courses c JOIN course_lecturers cl ON cl.course_id=c.id WHERE cl.lecturer_id=? AND c.active=1 ORDER BY c.code');$q->execute([$u['id']]);ok($q->fetchAll());}
if($route==='lecturer/timetable' && $method==='GET'){
    $u=require_login(['lecturer']);
    $q=db()->prepare('SELECT cs.id,cs.session_date,cs.start_time,cs.end_time,cs.cancelled,cs.closed,c.code,c.title,v.name venue_name,(SELECT COUNT(*) FROM enrolments ee WHERE ee.course_id=cs.course_id) student_count FROM class_sessions cs JOIN course_lecturers cl ON cl.course_id=cs.course_id JOIN courses c ON c.id=cs.course_id JOIN venues v ON v.id=cs.venue_id WHERE cl.lecturer_id=? AND cs.session_date >= DATE_SUB(CURDATE(),INTERVAL 1 DAY) ORDER BY cs.session_date ASC,cs.start_time ASC');
    $q->execute([$u['id']]); ok($q->fetchAll());
}
if($route==='lecturer/attendance' && $method==='GET'){$u=require_login(['lecturer']);$s=db()->prepare('SELECT a.id,a.session_id,u.login_id,CONCAT(u.first_name," ",u.last_name) student,c.code course,c.title,cs.session_date,cs.start_time,a.status,a.source FROM attendance a JOIN users u ON u.id=a.student_id JOIN class_sessions cs ON cs.id=a.session_id JOIN courses c ON c.id=cs.course_id JOIN course_lecturers cl ON cl.course_id=c.id WHERE cl.lecturer_id=? ORDER BY cs.session_date DESC,cs.start_time DESC,a.id DESC');$s->execute([$u['id']]);ok($s->fetchAll());}
if($route==='lecturer/attendance/update' && $method==='POST'){$u=require_login(['lecturer']);csrf_ok();$b=body();$id=(int)($b['id']??0);$reason=trim($b['reason']??'');$status=$b['status']??'';if(!$id||!in_array($status,['Present','Late','Absent','Excused'],true)||$reason==='')fail('Attendance ID, valid status and reason are required',422);$q=db()->prepare('SELECT a.id,c.code FROM attendance a JOIN class_sessions s ON s.id=a.session_id JOIN course_lecturers cl ON cl.course_id=s.course_id JOIN courses c ON c.id=s.course_id WHERE a.id=? AND cl.lecturer_id=?');$q->execute([$id,$u['id']]);if(!$q->fetch())fail('You can only edit attendance for your own courses',403);db()->prepare('UPDATE attendance SET status=?,source="MANUAL",correction_reason=? WHERE id=?')->execute([$status,$reason,$id]);audit_log('CORRECT','attendance',$id,$status.' — '.$reason);ok();}

fail('Route not found',404);
