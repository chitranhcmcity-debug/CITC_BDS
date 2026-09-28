<?php

namespace App\Services;

use App\Models\Database;
use App\Repositories\NewsCategoryRepository;
use App\Repositories\NewsCommentRepository;
use App\Repositories\NewsRepository;
use App\Repositories\NewsTagRepository;

/**
 * NewsService – Business Logic cho Module Tin tức.
 *
 * Tuân thủ:
 *  - Không chứa SQL trực tiếp (delegate sang Repository).
 *  - Không xử lý HTTP Request (Controller lo phần đó).
 *  - Cache file-based 5 phút (giống AuthorService).
 *  - Anti-spam view: session-based, cooldown 30 phút.
 */
class NewsService
{
    private NewsRepository $newsRepo;

    private NewsCategoryRepository $catRepo;

    private NewsTagRepository $tagRepo;

    private NewsCommentRepository $commentRepo;

    private int $perPage = 12;

    private int $cacheTtl = 300;  // 5 phút

    private string $cacheDir;

    public function __construct()
    {
        $this->newsRepo = new NewsRepository;
        $this->catRepo = new NewsCategoryRepository;
        $this->tagRepo = new NewsTagRepository;
        $this->commentRepo = new NewsCommentRepository;
        $this->cacheDir = rtrim(sys_get_temp_dir(), '/\\').'/timnhadat_news_';
    }

    /* =====================================================
       PAGE DATA
       ===================================================== */

    /**
     * Dữ liệu đầy đủ cho trang danh sách /tin-tuc.
     */
    public function getListPage(array $filters = [], int $page = 1): array
    {
        $posts = $this->newsRepo->findPublished($filters, $page, $this->perPage);
        $total = $this->newsRepo->countPublished($filters);
        $pagination = $this->buildPagination($total, $page, $this->perPage);
        $sidebar = $this->getSidebar();

        // Bài nổi bật (hero - lấy bài đầu tiên có noi_bat=1)
        $featured = null;
        if ($page === 1 && empty($filters)) {
            foreach ($posts as $p) {
                if ($p->noi_bat) {
                    $featured = $p;
                    break;
                }
            }
            if (! $featured && ! empty($posts)) {
                $featured = $posts[0];
            }
        }

        return [
            'posts' => $posts,
            'featured' => $featured,
            'pagination' => $pagination,
            'filters' => $filters,
            'sidebar' => $sidebar,
            'title' => 'Tin tức Bất động sản | '.SITE_NAME,
            'meta_desc' => 'Cập nhật tin tức bất động sản mới nhất, phân tích thị trường, xu hướng đầu tư BĐS tại Việt Nam.',
            'canonical' => URL_ROOT.'/tin-tuc',
        ];
    }

    /**
     * Dữ liệu đầy đủ cho trang chi tiết /tin-tuc/detail/{slug}.
     */
    public function getDetailPage(string $slug): array
    {
        $post = $this->newsRepo->findBySlug($slug);
        if (! $post) {
            return [];
        }

        // Tính thời gian đọc nếu chưa có
        if (empty($post->thoi_gian_doc)) {
            $post->thoi_gian_doc = $this->estimateReadTime($post->noi_dung ?? '');
        }

        // Tags
        $tags = $this->tagRepo->findByNews((int) $post->id);
        $tagIds = array_map(fn ($t) => (int) $t->id, $tags);

        // Bài liên quan
        $related = $this->newsRepo->findRelated(
            (int) $post->id,
            (int) $post->ma_danh_muc,
            $tagIds,
            6
        );

        // Comments
        $comments = $this->commentRepo->findByNews((int) $post->id, 1);
        $commentTree = $this->buildCommentTree($comments);

        // TOC + nội dung có anchor
        [$toc, $content] = $this->generateTOC($post->noi_dung ?? '');

        // Sidebar
        $sidebar = $this->getSidebar();

        // SEO
        $seo = $this->buildSeo($post, $tags);

        return [
            'post' => $post,
            'content' => $content,
            'toc' => $toc,
            'tags' => $tags,
            'related' => $related,
            'comments' => $commentTree,
            'commentCount' => $this->commentRepo->countByNews((int) $post->id),
            'sidebar' => $sidebar,
            'seo' => $seo,
            'title' => $seo['title'],
            'meta_desc' => $seo['description'],
            'canonical' => $seo['canonical'],
            'ogImage' => $seo['og_image'],
            'schemaJson' => $seo['schema_json'],
        ];
    }

    /**
     * Dữ liệu trang danh mục.
     */
    public function getCategoryPage(string $slug, int $page = 1): array
    {
        $category = $this->catRepo->findBySlug($slug);
        if (! $category) {
            return [];
        }

        $posts = $this->newsRepo->findByCategory($slug, $page, $this->perPage);
        $total = $this->newsRepo->countByCategory($slug);
        $pagination = $this->buildPagination($total, $page, $this->perPage);
        $sidebar = $this->getSidebar();

        return [
            'category' => $category,
            'posts' => $posts,
            'pagination' => $pagination,
            'sidebar' => $sidebar,
            'title' => ($category->ten ?? '').' | '.SITE_NAME,
            'meta_desc' => 'Danh sách bài viết trong chuyên mục '.($category->ten ?? '').'.',
            'canonical' => URL_ROOT.'/tin-tuc/category/'.$slug,
        ];
    }

    /**
     * Dữ liệu trang tag.
     */
    public function getTagPage(string $slug, int $page = 1): array
    {
        $tag = $this->tagRepo->findBySlug($slug);
        if (! $tag) {
            return [];
        }

        $posts = $this->newsRepo->findByTag($slug, $page, $this->perPage);
        $total = $this->newsRepo->countByTag($slug);
        $pagination = $this->buildPagination($total, $page, $this->perPage);
        $sidebar = $this->getSidebar();

        return [
            'tag' => $tag,
            'posts' => $posts,
            'pagination' => $pagination,
            'sidebar' => $sidebar,
            'title' => 'Thẻ: '.($tag->ten ?? '').' | '.SITE_NAME,
            'meta_desc' => 'Bài viết liên quan đến thẻ '.($tag->ten ?? '').'.',
            'canonical' => URL_ROOT.'/tin-tuc/tag/'.$slug,
        ];
    }

    /**
     * Dữ liệu trang tìm kiếm.
     */
    public function getSearchPage(string $keyword, int $page = 1): array
    {
        $kw = mb_substr(trim(strip_tags($keyword)), 0, 100);

        $posts = $kw ? $this->newsRepo->search($kw, $page, $this->perPage) : [];
        $total = $kw ? $this->newsRepo->countSearch($kw) : 0;
        $pagination = $this->buildPagination($total, $page, $this->perPage);
        $sidebar = $this->getSidebar();

        return [
            'keyword' => $kw,
            'posts' => $posts,
            'total' => $total,
            'pagination' => $pagination,
            'sidebar' => $sidebar,
            'title' => "Tìm kiếm: {$kw} | ".SITE_NAME,
            'meta_desc' => "Kết quả tìm kiếm cho: {$kw}",
            'canonical' => URL_ROOT.'/tin-tuc/search?q='.urlencode($kw),
        ];
    }

    /* =====================================================
       ACTIONS
       ===================================================== */

    /**
     * Anti-spam view: chỉ tăng nếu session chưa ghi nhận bài này trong 30 phút.
     */
    public function recordView(int $newsId): void
    {
        if (! session_id()) {
            session_start();
        }
        $key = 'news_view_'.$newsId;
        $now = time();

        if (! isset($_SESSION[$key]) || ($now - $_SESSION[$key]) >= 1800) {
            $this->newsRepo->incrementView($newsId);
            $_SESSION[$key] = $now;
        }
    }

    /**
     * Toggle like cho bài viết. Trả về ['liked'=>bool, 'count'=>int].
     */
    public function toggleLike(int $newsId, int $userId): array
    {
        if (! session_id()) {
            session_start();
        }
        $db = new Database;

        // Kiểm tra đã like chưa
        $db->query('SELECT 1 FROM luot_thich_tin_tuc WHERE bai_viet_id = :nid AND nguoi_dung_id = :uid');
        $db->bind(':nid', $newsId, PDO::PARAM_INT);
        $db->bind(':uid', $userId, PDO::PARAM_INT);
        $exists = (bool) $db->single();

        if ($exists) {
            $db->query('DELETE FROM luot_thich_tin_tuc WHERE bai_viet_id = :nid AND nguoi_dung_id = :uid');
            $db->bind(':nid', $newsId, PDO::PARAM_INT);
            $db->bind(':uid', $userId, PDO::PARAM_INT);
            $db->execute();
            $liked = false;
        } else {
            $db->query('INSERT IGNORE INTO luot_thich_tin_tuc (bai_viet_id, nguoi_dung_id) VALUES (:nid, :uid)');
            $db->bind(':nid', $newsId, PDO::PARAM_INT);
            $db->bind(':uid', $userId, PDO::PARAM_INT);
            $db->execute();
            $liked = true;
        }

        // Đồng bộ counter
        $db->query('SELECT COUNT(*) FROM luot_thich_tin_tuc WHERE bai_viet_id = :nid');
        $db->bind(':nid', $newsId, PDO::PARAM_INT);
        $count = (int) $db->single()->{'COUNT(*)'};
        $this->newsRepo->setLikeCount($newsId, $count);

        return ['liked' => $liked, 'count' => $count];
    }

    /**
     * Kiểm tra user đã like bài chưa.
     */
    public function hasLiked(int $newsId, int $userId): bool
    {
        $db = new Database;
        $db->query('SELECT 1 FROM luot_thich_tin_tuc WHERE bai_viet_id = :nid AND nguoi_dung_id = :uid');
        $db->bind(':nid', $newsId, PDO::PARAM_INT);
        $db->bind(':uid', $userId, PDO::PARAM_INT);

        return (bool) $db->single();
    }

    /**
     * Ghi nhận lượt chia sẻ.
     */
    public function recordShare(int $newsId): void
    {
        $this->newsRepo->incrementShare($newsId);
    }

    /**
     * Submit bình luận mới.
     *
     * @return array ['success'=>bool, 'message'=>string, 'comment'=>?object]
     */
    public function submitComment(int $newsId, int $userId, array $data): array
    {
        // Validate
        $content = mb_substr(trim(strip_tags($data['noi_dung'] ?? '')), 0, 2000);
        if (mb_strlen($content) < 3) {
            return ['success' => false, 'message' => 'Bình luận quá ngắn (tối thiểu 3 ký tự).'];
        }

        // Anti-spam: không gửi quá 3 comment trong 60 giây
        if (! session_id()) {
            session_start();
        }
        $spamKey = 'comment_ts_'.$userId;
        $now = time();
        $recent = $_SESSION[$spamKey] ?? [];
        $recent = array_filter($recent, fn ($t) => ($now - $t) < 60);

        if (count($recent) >= 3) {
            return ['success' => false, 'message' => 'Bạn đang gửi bình luận quá nhanh. Vui lòng chờ.'];
        }

        $chaId = isset($data['cha_id']) && (int) $data['cha_id'] > 0
            ? (int) $data['cha_id'] : null;

        $commentId = $this->commentRepo->create([
            'bai_viet_id' => $newsId,
            'nguoi_dung_id' => $userId,
            'noi_dung' => $content,
            'cha_id' => $chaId,
        ]);

        // Tăng counter bài viết
        $this->newsRepo->incrementComment($newsId);

        // Ghi spam guard
        $recent[] = $now;
        $_SESSION[$spamKey] = array_values($recent);

        // Lấy lại comment vừa tạo
        $comment = $this->commentRepo->findById($commentId);

        return [
            'success' => true,
            'message' => 'Bình luận đã được đăng.',
            'comment' => $comment,
        ];
    }

    /* =====================================================
       SIDEBAR (cached 5 phút)
       ===================================================== */

    public function getSidebar(): array
    {
        $cacheFile = $this->cacheDir.'sidebar.json';

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $this->cacheTtl) {
            $cached = json_decode(file_get_contents($cacheFile));
            if ($cached) {
                return (array) $cached;
            }
        }

        $sidebar = [
            'categories' => $this->catRepo->findAllWithCount(),
            'tags' => $this->tagRepo->findPopular(25),
            'latest' => $this->newsRepo->findLatest(5),
            'popular' => $this->newsRepo->findPopular(5),
            'featured' => $this->newsRepo->findFeatured(3),
        ];

        @file_put_contents($cacheFile, json_encode($sidebar));

        return $sidebar;
    }

    /* =====================================================
       SEO
       ===================================================== */

    public function buildSeo(object $post, array $tags = []): array
    {
        $metaTitle = ! empty($post->meta_title)
            ? $post->meta_title
            : ($post->tieu_de ?? '').' | '.SITE_NAME;

        $metaDesc = ! empty($post->meta_description)
            ? $post->meta_description
            : mb_substr(strip_tags($post->tom_tat ?? $post->noi_dung ?? ''), 0, 160);

        $canonical = URL_ROOT.'/tin-tuc/detail/'.($post->duong_dan ?? '');
        $ogImage = img_url($post->anh_thu_nho ?? '');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $post->tieu_de ?? '',
            'description' => $metaDesc,
            'url' => $canonical,
            'image' => [$ogImage],
            'datePublished' => $post->ngay_tao ?? '',
            'dateModified' => $post->ngay_cap_nhat ?? $post->ngay_tao ?? '',
            'author' => [
                '@type' => 'Person',
                'name' => $post->ten_tac_gia ?? SITE_NAME,
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => SITE_NAME,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => URL_ROOT.'/public/images/logo.png',
                ],
            ],
            'keywords' => implode(', ', array_map(fn ($t) => $t->ten, $tags)),
            'articleSection' => $post->ten_danh_muc ?? '',
            'wordCount' => str_word_count(strip_tags($post->noi_dung ?? '')),
            'timeRequired' => 'PT'.($post->thoi_gian_doc ?? 1).'M',
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical],
        ];

        return [
            'title' => $metaTitle,
            'description' => $metaDesc,
            'canonical' => $canonical,
            'og_image' => $ogImage,
            'schema_json' => json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
    }

    /* =====================================================
       RSS FEED
       ===================================================== */

    public function getRssFeed(): string
    {
        $posts = $this->newsRepo->findForRss(20);
        $siteUrl = URL_ROOT;
        $siteName = SITE_NAME;
        $now = date(DATE_RSS);

        $items = '';
        foreach ($posts as $p) {
            $url = $siteUrl.'/tin-tuc/detail/'.htmlspecialchars($p->duong_dan, ENT_XML1);
            $title = htmlspecialchars($p->tieu_de ?? '', ENT_XML1);
            $desc = htmlspecialchars(
                mb_substr(strip_tags($p->meta_description ?: ($p->tom_tat ?? '')), 0, 300),
                ENT_XML1
            );
            $date = date(DATE_RSS, strtotime($p->ngay_tao ?? 'now'));
            $cat = htmlspecialchars($p->ten_danh_muc ?? '', ENT_XML1);
            $items .= "<item>
                <title>{$title}</title>
                <link>{$url}</link>
                <description>{$desc}</description>
                <pubDate>{$date}</pubDate>
                <category>{$cat}</category>
                <guid isPermaLink=\"true\">{$url}</guid>
            </item>\n";
        }

        return '<?xml version="1.0" encoding="UTF-8"?>'."\n".
               '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'."\n".
               "<channel>\n".
               "<title>{$siteName} – Tin tức BĐS</title>\n".
               "<link>{$siteUrl}/tin-tuc</link>\n".
               "<description>Cập nhật tin tức bất động sản mới nhất từ {$siteName}</description>\n".
               "<language>vi</language>\n".
               "<lastBuildDate>{$now}</lastBuildDate>\n".
               "<atom:link href=\"{$siteUrl}/tin-tuc/rss\" rel=\"self\" type=\"application/rss+xml\"/>\n".
               $items.
               "</channel>\n</rss>";
    }

    /* =====================================================
       HELPERS
       ===================================================== */

    /**
     * Tự động tạo TOC từ nội dung HTML.
     * Inject id anchor vào các heading h2, h3.
     * Trả về [toc_array, content_with_anchors].
     */
    public function generateTOC(string $html): array
    {
        if (empty($html)) {
            return [[], $html];
        }

        $toc = [];
        $counter = [];

        $content = preg_replace_callback(
            '/<(h[23])(.*?)>(.*?)<\/h[23]>/si',
            function ($m) use (&$toc, &$counter) {
                $level = $m[1]; // h2 | h3
                $attrs = $m[2];
                $text = strip_tags($m[3]);
                $slug = $this->makeAnchorSlug($text);

                // Đảm bảo unique anchor
                $counter[$slug] = ($counter[$slug] ?? 0) + 1;
                if ($counter[$slug] > 1) {
                    $slug .= '-'.$counter[$slug];
                }

                $toc[] = [
                    'level' => (int) substr($level, 1),
                    'text' => $text,
                    'id' => $slug,
                ];

                return "<{$level}{$attrs} id=\"{$slug}\">{$m[3]}</{$level}>";
            },
            $html
        );

        return [$toc, $content];
    }

    /**
     * Ước tính thời gian đọc (phút).
     * Trung bình 200 từ/phút với tiếng Việt.
     */
    public function estimateReadTime(string $html): int
    {
        $text = strip_tags($html);
        $words = preg_match_all('/\S+/u', $text, $m) ? count($m[0]) : 0;

        return max(1, (int) ceil($words / 200));
    }

    /**
     * Xây dựng pagination object.
     */
    public function buildPagination(int $total, int $page, int $perPage): array
    {
        $totalPages = max(1, (int) ceil($total / $perPage));

        return [
            'total' => $total,
            'perPage' => $perPage,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'hasPrev' => $page > 1,
            'hasNext' => $page < $totalPages,
            'prevPage' => $page - 1,
            'nextPage' => $page + 1,
        ];
    }

    /**
     * Chuyển danh sách comment phẳng thành cấu trúc tree 2-level.
     * Root comments: cha_id IS NULL. Replies gắn vào comments[].replies.
     */
    public function buildCommentTree(array $comments): array
    {
        $roots = [];
        $byId = [];

        foreach ($comments as $c) {
            $c->replies = [];
            $byId[$c->id] = $c;
        }

        foreach ($byId as $c) {
            if ($c->cha_id === null || ! isset($byId[$c->cha_id])) {
                $roots[] = &$byId[$c->id];
            } else {
                $byId[$c->cha_id]->replies[] = &$byId[$c->id];
            }
        }

        return $roots;
    }

    /* =====================================================
       PRIVATE
       ===================================================== */

    private function makeAnchorSlug(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $text = str_replace(
            ['á', 'à', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ặ', 'ằ', 'ẳ', 'ẵ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ',
                'đ', 'é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ', 'í', 'ì', 'ỉ', 'ĩ', 'ị',
                'ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ',
                'ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự', 'ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ'],
            ['a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
                'd', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'i',
                'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
                'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'y', 'y', 'y', 'y', 'y'],
            $text
        );

        return preg_replace('/[^a-z0-9]+/', '-', $text);
    }
}
