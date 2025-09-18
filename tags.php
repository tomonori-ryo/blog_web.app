<?php
try {
    $pdo = new PDO('mysql:host=localhost;port=8889;dbname=simple_blog_db;charset=utf8', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit("データベースの接続に失敗しました: " . $e->getMessage());
}

// タグの追加処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['name'] ?? '';
    if ($name) {
        $stmt = $pdo->prepare("INSERT INTO tags (name) VALUES (:name)");
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->execute();
        header('Location: tags.php');
        exit();
    }
}

// タグの更新処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    if ($id && $name && is_numeric($id)) {
        $stmt = $pdo->prepare("UPDATE tags SET name = :name WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->execute();
        header('Location: tags.php');
        exit();
    }
}

// タグの削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = $_POST['id'] ?? '';
    if ($id && is_numeric($id)) {
        // 関連するpost_tagレコードも削除
        $stmt = $pdo->prepare("DELETE FROM post_tag WHERE tag_id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // タグを削除
        $stmt = $pdo->prepare("DELETE FROM tags WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        header('Location: tags.php');
        exit();
    }
}

// タグ一覧を取得
$stmt = $pdo->query("SELECT id, name FROM tags ORDER BY name");
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>タグ管理</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 2em; 
            line-height: 1.6;
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
        }
        .tag { 
            border: 1px solid #ccc; 
            padding: 1em; 
            margin-bottom: 1em; 
            border-radius: 5px; 
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .tag-name {
            font-weight: bold;
            font-size: 1.1em;
        }
        .actions a, .new-tag-btn, .back-btn {
            display: inline-block;
            margin-right: 10px;
            text-decoration: none;
            color: #fff;
            background-color: #007bff;
            padding: 5px 10px;
            border-radius: 3px;
        }
        .actions .delete-btn { 
            background-color: #dc3545; 
        }
        .actions .edit-btn { 
            background-color: #28a745; 
        }
        .add-form {
            border: 1px solid #ddd;
            padding: 1em;
            margin-bottom: 2em;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .add-form input[type="text"] {
            width: 200px;
            padding: 8px;
            margin-right: 10px;
        }
        .add-form button {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .edit-form {
            display: none;
            margin-top: 10px;
        }
        .edit-form input[type="text"] {
            width: 200px;
            padding: 8px;
            margin-right: 10px;
        }
        .edit-form button {
            padding: 8px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin-right: 5px;
        }
        .edit-form .cancel-btn {
            background-color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>タグ管理</h1>
        <p>
            <a href="index.php" class="back-btn">← ブログ一覧に戻る</a>
        </p>

        <!-- タグ追加フォーム -->
        <div class="add-form">
            <h3>新規タグ追加</h3>
            <form method="post">
                <input type="hidden" name="action" value="add">
                <input type="text" name="name" placeholder="タグ名を入力" required>
                <button type="submit">追加</button>
            </form>
        </div>

        <!-- タグ一覧 -->
        <h3>タグ一覧</h3>
        <?php if (empty($tags)): ?>
            <p>タグがありません。</p>
        <?php else: ?>
            <?php foreach ($tags as $tag): ?>
                <div class="tag">
                    <div class="tag-name"><?php echo htmlspecialchars($tag['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="actions">
                        <a href="#" class="edit-btn" onclick="showEditForm(<?php echo $tag['id']; ?>)">編集</a>
                        <form method="post" style="display: inline;" onsubmit="return confirm('このタグを削除しますか？関連する投稿からも削除されます。');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $tag['id']; ?>">
                            <button type="submit" class="delete-btn" style="border: none; cursor: pointer;">削除</button>
                        </form>
                    </div>
                </div>
                
                <!-- 編集フォーム -->
                <div id="edit-form-<?php echo $tag['id']; ?>" class="edit-form">
                    <form method="post">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?php echo $tag['id']; ?>">
                        <input type="text" name="name" value="<?php echo htmlspecialchars($tag['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        <button type="submit">更新</button>
                        <button type="button" class="cancel-btn" onclick="hideEditForm(<?php echo $tag['id']; ?>)">キャンセル</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        function showEditForm(tagId) {
            document.getElementById('edit-form-' + tagId).style.display = 'block';
        }
        
        function hideEditForm(tagId) {
            document.getElementById('edit-form-' + tagId).style.display = 'none';
        }
    </script>
</body>
</html> 