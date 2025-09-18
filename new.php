<?php
try {
    $pdo = new PDO('mysql:host=localhost;port=8889;dbname=simple_blog_db;charset=utf8', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit("データベースの接続に失敗しました: " . $e->getMessage());
}

// タグ一覧を取得
$stmt = $pdo->query("SELECT id, name FROM tags ORDER BY name");
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>新規投稿入力画面</title>
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
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
      }

      button:hover {
        background-color: #0056b3;
      }

      .cancel-btn {
        background-color: #6c757d;
        color: white;
        text-decoration: none;
        padding: 10px 20px;
        border-radius: 5px;
        display: inline-block;
        margin-left: 10px;
      }

      .cancel-btn:hover {
        background-color: #5a6268;
      }
    </style>
</head>
<body>
<form action="store.php" method="post" enctype="multipart/form-data">

  <p>
    <label for="name">タイトル（必須）:</label>
    <input type="text" id="name" name="name" required>
  </p>

  <p>
    <label for="content">本文（必須）:</label>
    <textarea id="content" name="content" rows="8" required></textarea>
  </p>

  <p>
    <fieldset class="tag-group">
      <legend>タグを選択（複数選択可）:</legend>

      <?php foreach ($tags as $tag): ?>
        <input
          type="checkbox"
          id="tag-<?php echo htmlspecialchars($tag['id']); ?>"
          name="tags[]"
          value="<?php echo htmlspecialchars($tag['id']); ?>">
        <label for="tag-<?php echo htmlspecialchars($tag['id']); ?>">
          <?php echo htmlspecialchars($tag['name']); ?>
        </label>
      <?php endforeach; ?>
      
      <?php if (empty($tags)): ?>
        <p>登録されているタグがありません。</p>
      <?php endif; ?>

    </fieldset>
  </p>

  <p>
    <button type="submit">投稿する</button>
    <a href="index.php" class="cancel-btn">キャンセル</a>
  </p>

</form>
</body>
</html>