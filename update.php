<?php
try {
    $pdo = new PDO('mysql:host=localhost;port=8889;dbname=simple_blog_db;charset=utf8', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit("データベースの接続に失敗しました: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $content = $_POST['content'] ?? '';
    $tags = $_POST['tags'] ?? [];

    // 入力値の検証
    if (!$id || !is_numeric($id)) {
        header('Location: index.php');
        exit();
    }

    if ($name && $content) {
        // 投稿の存在確認
        $checkStmt = $pdo->prepare("SELECT id FROM posts WHERE id = :id");
        $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $checkStmt->execute();
        
        if ($checkStmt->fetch()) {
            // 投稿が存在する場合、更新処理を実行
            $stmt = $pdo->prepare("UPDATE posts SET name = :name, content = :content WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':content', $content, PDO::PARAM_STR);
            $stmt->execute();
            
            // 既存のタグを削除
            $delete_stmt = $pdo->prepare("DELETE FROM post_tag WHERE post_id = :post_id");
            $delete_stmt->bindParam(':post_id', $id, PDO::PARAM_INT);
            $delete_stmt->execute();
            
            // 新しいタグを保存
            if (!empty($tags)) {
                $tag_stmt = $pdo->prepare("INSERT INTO post_tag (post_id, tag_id) VALUES (:post_id, :tag_id)");
                foreach ($tags as $tag_id) {
                    if (is_numeric($tag_id)) {
                        $tag_stmt->bindParam(':post_id', $id, PDO::PARAM_INT);
                        $tag_stmt->bindParam(':tag_id', $tag_id, PDO::PARAM_INT);
                        $tag_stmt->execute();
                    }
                }
            }
            
            // 更新成功後、index.phpにリダイレクト
            header('Location: index.php');
            exit();
        } else {
            // 投稿が存在しない場合
            header('Location: index.php');
            exit();
        }
    } else {
        // 入力値が不正な場合、edit.phpに戻る
        header('Location: edit.php?id=' . $id . '&error=1');
        exit();
    }
} else {
    // POST以外のリクエストの場合はindex.phpにリダイレクト
    header('Location: index.php');
    exit();
}
?> 