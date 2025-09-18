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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background-color: #f8f9fa;
            color: #212529;
            line-height: 1.6;
        }
        
        .container { 
            max-width: 1000px; 
            margin: 0 auto; 
            padding: 30px 20px;
            margin-top: 100px;
        }
        
        /* ヘッダー */
        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        
        .header h1 {
            color: #1a1a1a;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .header .subtitle {
            color: #6c757d;
            font-size: 16px;
            font-weight: 400;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            color: #fff;
            padding: 10px 16px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
            background: #6c757d;
        }
        
        .back-btn:hover {
            background: #5c636a;
        }
        
        /* タグ追加フォーム */
        .add-form {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        
        .add-form h3 {
            color: #1a1a1a;
            margin-bottom: 16px;
            font-size: 18px;
            font-weight: 600;
        }
        
        .add-form input[type="text"] {
            width: 300px;
            padding: 10px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .add-form button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
        }
        
        .add-form button:hover {
            background-color: #0056b3;
        }
        
        /* タグ一覧 */
        .tags-section h3 {
            color: #1a1a1a;
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: 600;
        }
        
        .tag { 
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.2s ease;
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .tag:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }
        
        .tag-name {
            color: #1a1a1a;
            font-size: 18px;
            font-weight: 600;
        }
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .actions a, .actions button {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            text-decoration: none;
            color: #fff;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }
        
        .actions .edit-btn {
            background:rgb(90, 90, 90);
        }
        
        .actions .edit-btn:hover {
            background: #157347;
        }
        
        .actions .delete-btn { 
            background: #dc3545;
        }
        
        .actions .delete-btn:hover {
            background: #bb2d3b;
        }
        
        /* 編集フォーム */
        .edit-form {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .edit-form input[type="text"] {
            width: 300px;
            padding: 10px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .edit-form button {
            padding: 10px 20px;
            background-color:rgb(90, 90, 90);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            margin-right: 5px;
        }
        
        .edit-form button:hover {
            background-color:rgb(90, 90, 90);
        }
        
        .edit-form .cancel-btn {
            background-color: #6c757d;
        }
        
        .edit-form .cancel-btn:hover {
            background-color: #5a6268;
        }
        
        /* レスポンシブ */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
                padding: 15px 20px;
            }
            
            .container {
                margin-top: 140px;
            }
            
            .add-form input[type="text"],
            .edit-form input[type="text"] {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .tag {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .actions {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- ヘッダー -->
        <div class="header">
            <div>
                <h1>タグ管理</h1>
                <div class="subtitle">タグの追加・編集・削除</div>
            </div>
            <div>
                <a href="index.php" class="back-btn">← ブログ一覧に戻る</a>
            </div>
        </div>

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
        <div class="tags-section">
            <h3>タグ一覧</h3>
            <?php if (empty($tags)): ?>
                <div class="tag">
                    <div class="tag-name">タグがありません。</div>
                </div>
            <?php else: ?>
                <?php foreach ($tags as $tag): ?>
                    <div class="tag">
                        <div class="tag-name"><?php echo htmlspecialchars($tag['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="actions">
                            <a href="#" class="edit-btn" onclick="showEditForm(<?php echo $tag['id']; ?>)">編集</a>
                            <form method="post" style="display: inline;" onsubmit="return confirm('このタグを削除しますか？関連する投稿からも削除されます。');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $tag['id']; ?>">
                                <button type="submit" class="delete-btn"> 削除</button>
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