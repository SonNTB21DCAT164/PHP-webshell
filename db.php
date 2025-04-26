<?php
if (getenv('APP_ENV') === 'dev') {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
}

$conn = new mysqli(getenv('MYSQL_HOSTNAME'), getenv('MYSQL_USER'), getenv('MYSQL_PASSWORD'), getenv('MYSQL_DATABASE'));


function execQuery($query)
{
    global $conn;
    try {
        $sth = $conn->query($query);
        return $sth;
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        return null;
    }
}

function selectAll($query)
{
    $res = execQuery($query);
    if ($res === null) {
        return [];
    }
    return $res->fetch_all(MYSQLI_ASSOC);
}

function selectOne($query)
{
    $res = execQuery($query);
    if ($res)
        return $res->fetch_assoc();
    else
        return false;
}

function getUserFromUsername($username)
{
    return selectOne("SELECT id, username, password FROM users WHERE username='" . $username . "' LIMIT 1");
}

function getAllBlogPosts($page = 1, $limit = 10)
{
    $offset = ($page - 1) * $limit;
    return selectAll("SELECT blog_posts.*, users.username FROM blog_posts 
                      JOIN users ON blog_posts.user_id = users.id 
                      ORDER BY blog_posts.created_at DESC 
                      LIMIT " . $limit . " OFFSET " . $offset);
}

function getBlogPostById($id)
{
    return selectOne("SELECT blog_posts.*, users.username FROM blog_posts 
                     JOIN users ON blog_posts.user_id = users.id 
                     WHERE blog_posts.id=" . $id . " LIMIT 1");
}

function createBlogPost($userId, $title, $summary, $content_path)
{
    global $conn;
    $userId = (int)$userId;
    $title = $conn->real_escape_string($title);
    $summary = $conn->real_escape_string($summary);
    $content_path = $conn->real_escape_string($content_path);
    return execQuery("INSERT INTO blog_posts(user_id, title, summary, content_path) 
                     VALUES (" . $userId . ", '" . $title . "', '" . $summary . "', '" . $content_path . "')");
}

function updateReadCount($id)
{
    return execQuery("UPDATE blog_posts SET read_count = read_count + 1 WHERE id=" . $id);
}

function getTotalBlogPosts()
{
    $result = selectOne("SELECT COUNT(*) as total FROM blog_posts");
    return $result['total'];
}

/*
$MYSQL_HOSTNAME: blogdb
$MYSQL_USER: dbuser
$MYSQL_PASSWORD: 48b105896e5aca02ebf2
$MYSQL_DATABASE: blog
*/