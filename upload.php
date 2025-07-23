<?php
require_once "config.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['title']) || empty($data['content'])) {
    http_response_code(400);
    echo json_encode(["error" => "Thiếu dữ liệu."]);
    exit;
}

$title = $data['title'];
$content = $data['content'];
$slug = strtolower(preg_replace("/[^\w]+/", "-", $title));
$filename = "posts/{$slug}.html";

$apiUrl = "https://api.github.com/repos/" . GITHUB_USER . "/" . GITHUB_REPO . "/contents/" . $filename;
$encodedContent = base64_encode($content);

$payload = json_encode([
    "message" => "Thêm bài viết: $title",
    "content" => $encodedContent,
    "committer" => [
        "name" => GITHUB_USER,
        "email" => "user@example.com"
    ]
]);

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_USERAGENT, "MyBlogApp");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: token " . GITHUB_TOKEN,
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode >= 200 && $httpCode < 300) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Không thể lưu bài viết."]);
}
?>
