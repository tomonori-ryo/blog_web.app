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

// 指定されたIDの投稿を取得
$stmt = $pdo->prepare("SELECT id, name, content FROM posts WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$post = $stmt->fetch(PDO::FETCH_ASSOC);

// 投稿が存在しない場合はindex.phpにリダイレクト
if (!$post) {
    header('Location: index.php');
    exit();
}

// この投稿に付いているタグを取得
$tag_stmt = $pdo->prepare("SELECT tag_id FROM post_tag WHERE post_id = :post_id");
$tag_stmt->bindParam(':post_id', $id, PDO::PARAM_INT);
$tag_stmt->execute();
$post_tags = $tag_stmt->fetchAll(PDO::FETCH_COLUMN);

// 全タグ一覧を取得
$all_tags_stmt = $pdo->query("SELECT id, name FROM tags ORDER BY name");
$all_tags = $all_tags_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>投稿編集</title>
    <link rel="stylesheet" type="text/css" href="./style.css">
    <style>
      body {
        font-family: Arial, sans-serif;
        max-width: 600px;
        margin: 20px auto;
        line-height: 1.6;
      }

      form p {
        margin-bottom: 15px;
      }

      input[type="text"], textarea, select {
        width: 100%;
        padding: 8px;
        box-sizing: border-box;
      }

      fieldset {
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
        margin: 10px 0;
      }

      legend {
        font-weight: bold;
        padding: 0 10px;
      }

      input[type="checkbox"] {
        margin-right: 8px;
      }

      label {
        margin-right: 15px;
        cursor: pointer;
      }

      button {
        padding: 10px 20px;
        font-size: 16px;
        cursor: pointer;
        margin-right: 10px;
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 5px;
      }

      button:hover {
        background-color: #218838;
      }

      .cancel-btn {
        background-color: #6c757d;
        color: white;
        text-decoration: none;
        padding: 10px 20px;
        border-radius: 5px;
        display: inline-block;
      }

      .cancel-btn:hover {
        background-color: #5a6268;
      }
    </style>
</head>
<body>
    <h1>投稿編集</h1>
    
    <form action="update.php" method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($post['id'], ENT_QUOTES, 'UTF-8'); ?>">
        
        <p>
            <label for="name">タイトル（必須）:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($post['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </p>

        <p>
            <label for="content">本文（必須）:</label>
            <textarea id="content" name="content" rows="8" required><?php echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); ?></textarea>
        </p>

        <p>
            <fieldset class="tag-group">
                <legend>タグを選択（複数選択可）:</legend>

                <?php foreach ($all_tags as $tag): ?>
                    <input
                        type="checkbox"
                        id="tag-<?php echo htmlspecialchars($tag['id']); ?>"
                        name="tags[]"
                        value="<?php echo htmlspecialchars($tag['id']); ?>"
                        <?php echo in_array($tag['id'], $post_tags) ? 'checked' : ''; ?>>
                    <label for="tag-<?php echo htmlspecialchars($tag['id']); ?>">
                        <?php echo htmlspecialchars($tag['name']); ?>
                    </label>
                <?php endforeach; ?>
                
                <?php if (empty($all_tags)): ?>
                    <p>登録されているタグがありません。</p>
                <?php endif; ?>

            </fieldset>
        </p>

        <p>
            <button type="submit">更新する</button>
            <a href="index.php" class="cancel-btn">キャンセル</a>
        </p>
    </form>
</body>
</html> 