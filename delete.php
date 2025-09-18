<?php
try {
    $pdo = new PDO('mysql:host=localhost;port=8889;dbname=simple_blog_db;charset=utf8', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit("データベースの接続に失敗しました: " . $e->getMessage());
}

// IDパラメータの取得と検証
$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: index.php');
    exit();
}

// 投稿の存在確認
$checkStmt = $pdo->prepare("SELECT id FROM posts WHERE id = :id");
$checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
$checkStmt->execute();

if ($checkStmt->fetch()) {
    // 関連するpost_tagレコードを先に削除
    $tag_stmt = $pdo->prepare("DELETE FROM post_tag WHERE post_id = :id");
    $tag_stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $tag_stmt->execute();
    
    // 投稿を削除
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    // 削除成功後、index.phpにリダイレクト
    header('Location: index.php');
    exit();
} else {
    // 投稿が存在しない場合、index.phpにリダイレクト
    header('Location: index.php');
    exit();
}
?> 