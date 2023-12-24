<?php
/**
 * 获取最新订阅文章并生成JSON文件
 */
function getAllSubscribedArticlesAndSaveToJson($user, $password)
{
    $apiUrl = 'https://你部署FreshRSS的域名/api/greader.php';
    $loginUrl = $apiUrl . '/accounts/ClientLogin?Email=' . urlencode($user) . '&Passwd=' . urlencode($password);
    $loginResponse = curlRequest($loginUrl);
    if (strpos($loginResponse, 'Auth=') !== false) {
        $authToken = substr($loginResponse, strpos($loginResponse, 'Auth=') + 5);
        $articlesUrl = $apiUrl . '/reader/api/0/stream/contents/reading-list?&n=1000';
        $articlesResponse = curlRequest($articlesUrl, $authToken);
        $articles = json_decode($articlesResponse, true);
        if (isset($articles['items'])) {
            usort($articles['items'], function ($a, $b) {
                return $b['published'] - $a['published'];
            });
            $subscriptionsUrl = $apiUrl . '/reader/api/0/subscription/list?output=json';
            $subscriptionsResponse = curlRequest($subscriptionsUrl, $authToken);
            $subscriptions = json_decode($subscriptionsResponse, true);
            if (isset($subscriptions['subscriptions'])) {
                $subscriptionMap = array();
                foreach ($subscriptions['subscriptions'] as $subscription) {
                    $subscriptionMap[$subscription['id']] = $subscription;
                }
                $formattedArticles = array();
                foreach ($articles['items'] as $article) {
                    $desc_length = mb_strlen(strip_tags(html_entity_decode($article['summary']['content'], ENT_QUOTES, 'UTF-8')), 'UTF-8');
                    if ($desc_length > 20) {
                        $short_desc = mb_substr(strip_tags(html_entity_decode($article['summary']['content'], ENT_QUOTES, 'UTF-8')), 0, 99, 'UTF-8') . '...';
                    } else {
                        $short_desc = strip_tags(html_entity_decode($article['summary']['content'], ENT_QUOTES, 'UTF-8'));
                    }

                    $formattedArticle = array(
                        'site_name' => $article['origin']['title'],
                        'title' => $article['title'],
                        'link' => $article['alternate'][0]['href'],
                        'time' => date('Y-m-d H:i', $article['published']),
                        'description' => $short_desc,
                    );

                    $subscriptionId = $article['origin']['streamId'];
                    if (isset($subscriptionMap[$subscriptionId])) {
                        $subscription = $subscriptionMap[$subscriptionId];
                        $iconUrl = $subscription['iconUrl'];
                        $filename = 'https://你部署FreshRSS的域名/'.substr($iconUrl, strrpos($iconUrl, '/') + 1);
                        $formattedArticle['icon'] = $filename;
                    }

                    $formattedArticles[] = $formattedArticle;
                }

                saveToJsonFile($formattedArticles);
                return $formattedArticles;
            } else {
                echo 'Error retrieving articles.';
            }
        } else {
            echo 'Error retrieving articles.';
        }
    } else {
        echo 'Login failed.';
    }
    return null;
}
function curlRequest($url, $authToken = null)
{
    $ch = curl_init($url);
    if ($authToken) {
        $headers = array(
            'Authorization: GoogleLogin auth=' . $authToken,
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}
/**
 * 将数据保存到JSON文件中
 */
function saveToJsonFile($data)
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents('output.json', $json);
    echo '数据已保存到JSON文件中';
}

// 调用函数并提供用户名和密码
getAllSubscribedArticlesAndSaveToJson('这里是FreshRSS的用户名', '这里是第3步设置的api密码');