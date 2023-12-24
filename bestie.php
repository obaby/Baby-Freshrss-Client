<?php /* Template Name: Besties */ ?>
<?php get_header(); ?>

<style>
#block-nav a.post-page-numbers, #block-nav .post-page-numbers ,#block-nav .link-pages{
    font-size: 1.0em;
    border: 1px solid;
    padding: 5px 10px;
    background: white;
    color: black;
    transition: 0.3s;
}
#block-nav span.post-page-numbers.current {
    color: white;
    background: black;
    border: 1px solid black;
}
#block-nav a.post-page-numbers:hover {
    background-color: black;
    color: #ffffff;
}
</style>

<div class="row">

    <div class="col-lg-8 col-12">
        <main id="content" role="main" itemprop="mainContentOfPage" itemscope="itemscope" itemtype="http://schema.org/Blog">
        <div class="container">

                <?php
            // 获取JSON数据
            $jsonData = file_get_contents('/home/wwwroot/h4ck.org.cn/output.json');
            // 将JSON数据解析为PHP数组
            $articles = json_decode($jsonData, true);
            // 对文章按时间排序（最新的排在前面）
            usort($articles, function ($a, $b) {
                return strtotime($b['time']) - strtotime($a['time']);
            });
            // 设置每页显示的文章数量
            $itemsPerPage = 38;
            // 生成文章列表
            foreach (array_slice($articles, 0, $itemsPerPage) as $article) {
                $articles_list ='<article id="div-comment-105873" class="comment-body" style=" padding: 10px;
                margin-bottom:10px;
                border: 1px solid #ccc;
                border-radius: 5px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);   ">
                <footer class="comment-meta">
                    <div class="comment-author vcard">
                        <img alt="" src="' . htmlspecialchars($article['icon']) . '" class="avatar avatar-32 photo" height="32" width="32" loading="lazy" decoding="async">
                        <b class="fn">
                            <a href="' . htmlspecialchars($article['link']) . '" target="_blank" class="url" rel="ugc">' . htmlspecialchars($article['site_name']) . '</a>
                        </b>
                        <span class="says"> 发布了：<a href="' . htmlspecialchars($article['link']) . ' " target="_blank" class="url" rel="ugc">《' . htmlspecialchars($article['title']) . '》</span>
                    </div>
                    <div class="comment-metadata">
                        <a href="#">
                            <time datetime="' . htmlspecialchars($article['time']) . '">' . htmlspecialchars($article['time']) . '</time>
                        </a>
                    </div>
                </footer>
                <div class="comment-content">

                    <p>' . htmlspecialchars($article['description']) . '
                    </p>
                </div>

            </article>';
                echo $articles_list;
            }
        ?>

            </div>
        </main>
    </div>

    <div class="col-lg-4 col-12">
        <?php get_sidebar(); ?>
    </div>

</div><!-- /row -->

<?php get_footer(); ?>