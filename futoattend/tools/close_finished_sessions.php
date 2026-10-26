<?php
// Configure this script as a once-per-minute cron job for reliable automatic closure.
require __DIR__.'/../config/config.php';
$pdo=db();
$now=(new DateTimeImmutable('now',new DateTimeZone('Africa/Lagos')))->format('Y-m-d H:i:s');
$pdo->beginTransaction();
try {
    $q=$pdo->prepare('SELECT * FROM class_sessions WHERE closed=0 AND cancelled=0 AND TIMESTAMP(session_date,end_time)<=? FOR UPDATE');
    $q->execute([$now]); $sessions=$q->fetchAll(); $closed=0;
    foreach($sessions as $s){
        $students=$pdo->prepare('SELECT student_id FROM enrolments WHERE course_id=?'); $students->execute([$s['course_id']]);
        $ins=$pdo->prepare('INSERT IGNORE INTO attendance(session_id,student_id,status,source) VALUES(?,?,"Absent","SYSTEM")');
        foreach($students as $student) $ins->execute([$s['id'],$student['student_id']]);
        $pdo->prepare('UPDATE class_sessions SET closed=1 WHERE id=?')->execute([$s['id']]); $closed++;
    }
    $pdo->commit(); echo "[$now WAT] Closed $closed session(s).\n";
} catch(Throwable $e) { if($pdo->inTransaction())$pdo->rollBack(); fwrite(STDERR,"Could not close sessions: ".$e->getMessage()."\n"); exit(1); }
