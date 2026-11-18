<?php
require_once dirname(__DIR__) . '/config/database.php';
require_once __DIR__ . '/notifications.php';
function payment_fail(PDO $pdo, string $reference, string $reason): void {
    $stmt=$pdo->prepare("UPDATE payments SET status='failed',failure_reason=? WHERE reference=? AND status='pending'");$stmt->execute([mb_substr($reason,0,250),$reference]);
    if($stmt->rowCount()){$q=$pdo->prepare('SELECT p.user_id,u.full_name,c.title FROM payments p JOIN users u ON u.id=p.user_id JOIN courses c ON c.id=p.course_id WHERE p.reference=?');$q->execute([$reference]);$info=$q->fetch();if($info)notify_parties($pdo,(int)$info['user_id'],'Course payment was not completed',"Hello {$info['full_name']},\n\nThe KSh 5 payment for {$info['title']} was not confirmed. You can return to the course and try again.\nReference: {$reference}\n\nWarwa International School");}
}
function complete_payment(PDO $pdo, string $reference, string $providerReference, string $providerTransaction = ''): bool {
    $pdo->beginTransaction();
    try {
        $stmt=$pdo->prepare('SELECT * FROM payments WHERE reference=? FOR UPDATE');$stmt->execute([$reference]);$payment=$stmt->fetch();
        if(!$payment){$pdo->rollBack();return false;} if($payment['status']==='success'){$pdo->commit();return true;} if($payment['status']!=='pending'){$pdo->commit();return false;}
        $pdo->prepare("UPDATE payments SET status='success',provider_reference=?,provider_transaction=?,paid_at=NOW(),failure_reason=NULL WHERE id=? AND status='pending'")->execute([$providerReference,$providerTransaction?:null,$payment['id']]);
        $pdo->prepare('INSERT INTO enrollments(user_id,course_id,payment_id) VALUES(?,?,?) ON DUPLICATE KEY UPDATE payment_id=VALUES(payment_id)')->execute([$payment['user_id'],$payment['course_id'],$payment['id']]);
        $pdo->commit();$q=$pdo->prepare('SELECT u.full_name,c.title FROM payments p JOIN users u ON u.id=p.user_id JOIN courses c ON c.id=p.course_id WHERE p.id=?');$q->execute([$payment['id']]);$info=$q->fetch();
        if($info)notify_parties($pdo,(int)$payment['user_id'],'Course enrollment confirmed',"Hello {$info['full_name']},\n\nYour payment for {$info['title']} was confirmed. You are now enrolled.\nPayment reference: {$reference}\nAmount: KSh 5.00\n\nWarwa International School");return true;
    } catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('Payment completion failed: '.$e->getMessage());return false;}
}
