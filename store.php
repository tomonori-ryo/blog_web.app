<?php
try {
    $pdo = new PDO('mysql:host=localhost;port=8889;dbname=simple_blog_db;charset=utf8', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit("データベースの接続に失敗しました: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $content = $_POST['content'] ?? '';
    $tags = $_POST['tags'] ?? [];

    if ($name && $content) {
        // 投稿を保存
        $stmt = $pdo->prepare("INSERT INTO posts (name, content) VALUES (:name, :content)");
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->execute();
        
        // 投稿IDを取得
        $post_id = $pdo->lastInsertId();
        
        // タグを保存
        if (!empty($tags)) {
            $tag_stmt = $pdo->prepare("INSERT INTO post_tag (post_id, tag_id) VALUES (:post_id, :tag_id)");
            foreach ($tags as $tag_id) {
                if (is_numeric($tag_id)) {
                    $tag_stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
                    $tag_stmt->bindParam(':tag_id', $tag_id, PDO::PARAM_INT);
                    $tag_stmt->execute();
                }
            }
        }
        
        // 保存成功後、index.phpにリダイレクト
        header('Location: index.php');
        exit();
    } else {
        // エラー（何らかの理由で保存失敗）の場合はnew.phpに戻る
        header('Location: new.php?error=1');
        exit();
    }
} else {
    // POST以外のリクエストの場合はnew.phpにリダイレクト
    header('Location: new.php');
    exit();
}
?>
