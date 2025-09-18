<?php
try {
    // DB接続
    $pdo = new PDO('mysql:host=localhost;port=8889;dbname=simple_blog_db;charset=utf8', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // 接続エラーの場合はメッセージを表示して終了
    exit("データベースの接続に失敗しました: " . $e->getMessage());
}

    // タグ検索機能
    $search_tag = $_GET['tag'] ?? '';
    
    if ($search_tag) {
        // 特定のタグで検索
        $sql = "
        SELECT
            p.id,
            p.name AS post_title,
            p.content,
            p.created_at,
            GROUP_CONCAT(t.name SEPARATOR ', ') AS tags
        FROM
            posts AS p
        INNER JOIN
            post_tag AS pt ON p.id = pt.post_id
        INNER JOIN
            tags AS t ON pt.tag_id = t.id
        WHERE
            t.name = :search_tag
        GROUP BY
            p.id
        ORDER BY
            p.created_at DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':search_tag', $search_tag, PDO::PARAM_STR);
        $stmt->execute();
    } else {
        // 全投稿を取得
        $sql = "
        SELECT
            p.id,
            p.name AS post_title,
            p.content,
            p.created_at,
            GROUP_CONCAT(t.name SEPARATOR ', ') AS tags
        FROM
            posts AS p
        LEFT JOIN
            post_tag AS pt ON p.id = pt.post_id
        LEFT JOIN
            tags AS t ON pt.tag_id = t.id
        GROUP BY
            p.id
        ORDER BY
            p.created_at DESC
        ";
        $stmt = $pdo->query($sql);
    }
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC); // 結果を連想配列として取得

    // 利用可能なタグ一覧を取得（検索用）
    $tag_stmt = $pdo->query("SELECT DISTINCT name FROM tags ORDER BY name");
    $available_tags = $tag_stmt->fetchAll(PDO::FETCH_COLUMN);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ブログ一覧</title>
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
        
        .header-actions {
            display: flex;
            gap: 12px;
        }
        
        .new-post-btn, .tag-manage-btn {
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
            border: none;
            cursor: pointer;
        }
        
        .new-post-btn {
            background: #0d6efd;
        }
        
        .new-post-btn:hover {
            background: #0b5ed7;
        }
        
        .tag-manage-btn {
            background: #6c757d;
        }
        
        .tag-manage-btn:hover {
            background: #5c636a;
        }
        
        /* タグフィルタリング */
        .tag-filter {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        
        .tag-filter h3 {
            color: #1a1a1a;
            margin-bottom: 16px;
            font-size: 18px;
            font-weight: 600;
        }
        
        .tag-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .tag-btn {
            padding: 6px 14px;
            border: none;
            border-radius: 16px;
            background: #e9ecef;
            color: #495057;
            text-decoration: none;
            font-weight: 500;
            font-size: 13px;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .tag-btn:hover {
            background: #dee2e6;
        }
        
        
        /* 記事一覧 */
        .posts-section h3 {
            color: #1a1a1a;
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: 600;
        }
        
        .post { 
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.2s ease;
            position: relative;
        }
        
        .post:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }
        
        .post h2 { 
            color: #1a1a1a;
            margin-bottom: 8px;
            font-size: 20px;
            font-weight: 600;
        }
        
        .post-meta { 
            color: #6c757d; 
            font-size: 14px;
            margin-bottom: 12px;
        }
        
        .post-content {
            color: #495057;
            line-height: 1.6;
            margin-bottom: 12px;
            font-size: 15px;
        }
        
        .tags { 
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            margin: 2px;
        }
        
        /*タグカラー */
        .tag-react {
            background: #61dafb;
            color: #000;
        }
        
        .tag-typescript {
            background: #3178c6;
            color: white;
        }
        
        .tag-javascript {
            background: #f7df1e;
            color: #000;
        }
        
        .tag-css {
            background: #1572b6;
            color: white;
        }
        
        .tag-nodejs {
            background: #339933;
            color: white;
        }
        
        /* デフォルトタグカラー */
        .tag-default {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .actions {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 8px;
        }
        
        .actions a {
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
        }
        
        .actions .edit-btn {
            background:rgb(111, 111, 111);
        }
        
        .actions .edit-btn:hover {
            background:rgb(111, 111, 111);
        }
        
        .actions .delete-btn { 
            background:rgb(111, 111, 111);
        }
        
        .actions .delete-btn:hover {
            background:rgb(111, 111, 111);
        }
        
        /* レスポンシブ */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
                padding: 15px 20px;
            }
            
            .header-actions {
                justify-content: center;
            }
            
            .tag-buttons {
                justify-content: center;
            }
            
            .actions {
                position: static;
                margin-top: 15px;
                justify-content: flex-end;
            }
            
            .container {
                margin-top: 140px;
            }
        }
    </style>
    </style>
</head>
<body>
    <div class="container">
        <!-- ヘッダー -->
        <div class="header">
            <div>
                <h1>ブログシステム</h1>
                <div class="subtitle">記事の投稿・管理・閲覧</div>
            </div>
            <div class="header-actions">
                <a href="tags.php" class="tag-manage-btn">🏷️タグ管理</a>
                <a href="new.php" class="new-post-btn">➕ 新規投稿</a>
            </div>
        </div>
        
        <!-- タグで絞り込み -->
        <div class="tag-filter">
            <h3>タグで絞り込み</h3>
            <div class="tag-buttons">
                <?php foreach ($available_tags as $tag): ?>
                    <a href="index.php?tag=<?php echo urlencode($tag); ?>" 
                       class="tag-btn">
                        <?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
       
        <!-- 記事一覧 -->
        <div class="posts-section">
            <h3>記事一覧</h3>
            
            <?php if (empty($posts)): ?>
                <div class="post">
                    <p>記事が見つかりません。</p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post">
                        <h2><?php echo htmlspecialchars($post['post_title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p class="post-meta">
                            <?php echo date('Y年n月j日 H:i', strtotime($post['created_at'])); ?>
                        </p>
                        <div class="post-content">
                            <?php 
                            $content = htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8');
                            // 概要を表示
                            if (strlen($content) > 150) {
                                $content = substr($content, 0, 150) . '...';
                            }
                            echo nl2br($content); 
                            ?>
                        </div>
                        <?php if ($post['tags']): ?>
                            <div class="post-meta">
                                <?php 
                                $tag_array = explode(', ', $post['tags']);
                                foreach ($tag_array as $tag): 
                                    $tag_trimmed = trim($tag);
                                    $tag_class = 'tag-default';
                                    
                                    // タグカラーを適用
                                    switch (strtolower($tag_trimmed)) {
                                        case 'react':
                                            $tag_class = 'tag-react';
                                            break;
                                        case 'typescript':
                                            $tag_class = 'tag-typescript';
                                            break;
                                        case 'javascript':
                                            $tag_class = 'tag-javascript';
                                            break;
                                        case 'css':
                                            $tag_class = 'tag-css';
                                            break;
                                        case 'node.js':
                                        case 'nodejs':
                                            $tag_class = 'tag-nodejs';
                                            break;
                                    }
                                ?>
                                    <span class="tags <?php echo $tag_class; ?>"><?php echo htmlspecialchars($tag_trimmed, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <div class="actions">
                            <a href="edit.php?id=<?php echo $post['id']; ?>" class="edit-btn">✏︎ 編集</a>
                            <a href="delete.php?id=<?php echo $post['id']; ?>" class="delete-btn" onclick="return confirm('本当に削除しますか？');">🚮 削除</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>