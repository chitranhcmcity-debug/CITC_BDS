<?php
/**
 * Component: Comment item (đệ quy – hiển thị 2 level)
 * Biến: $c (comment object với $c->replies array)
 */
$cUser   = Session::get('user_id');
$cAdmin  = (int)(Session::get('user_role_id') ?? 0) === 1;
$cOwner  = $cUser && (int)$c->nguoi_dung_id === (int)$cUser;
$deleted = ($c->noi_dung ?? '') === '[Đã xóa]';

$cAvatar = !empty($c->anh_dai_dien)
    ? URL_ROOT . '/uploads/avatars/' . htmlspecialchars($c->anh_dai_dien, ENT_QUOTES)
    : 'https://ui-avatars.com/api/?name=' . urlencode($c->ten_nguoi_dung ?? 'U') . '&background=64748b&color=fff&size=80';

$isRoot = empty($c->cha_id);
?>
<div class="comment-item <?= $isRoot ? '' : '' ?>" id="comment_<?= (int)$c->id ?>">
    <img src="<?= $cAvatar ?>" class="comment-avatar" alt="<?= htmlspecialchars($c->ten_nguoi_dung ?? '', ENT_QUOTES) ?>">
    <div class="flex-grow-1">
        <div class="comment-bubble <?= $deleted ? 'deleted' : '' ?>">
            <span class="comment-author">
                <?= htmlspecialchars($c->ten_nguoi_dung ?? 'Người dùng', ENT_QUOTES) ?>
                <?php if ((int)($c->ma_vai_tro ?? 0) === 1): ?>
                <span class="badge bg-primary ms-1" style="font-size:.6rem;">Admin</span>
                <?php endif; ?>
            </span>
            <span class="comment-date">
                <?= date('d/m/Y H:i', strtotime($c->ngay_tao ?? 'now')) ?>
                <?php if (!empty($c->ngay_cap_nhat) && $c->ngay_cap_nhat !== $c->ngay_tao): ?>
                <em class="ms-1">(đã sửa)</em>
                <?php endif; ?>
            </span>
            <div class="comment-text"><?= $deleted ? '<em>[Đã xóa]</em>' : nl2br(htmlspecialchars($c->noi_dung ?? '', ENT_QUOTES)) ?></div>

            <?php if (!$deleted): ?>
            <div class="comment-actions">
                <?php if ($cUser && $isRoot): ?>
                <button class="btn-reply" data-comment-id="<?= (int)$c->id ?>"
                        data-author-name="<?= htmlspecialchars($c->ten_nguoi_dung ?? '', ENT_QUOTES) ?>">
                    <i class="fas fa-reply me-1"></i>Trả lời
                </button>
                <?php endif; ?>
                <?php if ($cOwner || $cAdmin): ?>
                <button class="btn-delete-comment" data-comment-id="<?= (int)$c->id ?>">
                    <i class="fas fa-trash-alt me-1"></i>Xóa
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Replies (level 2) -->
        <div class="comment-replies">
            <?php if (!empty($c->replies)): ?>
                <?php foreach ($c->replies as $c): // reuse $c for reply ?>
                <?php require __FILE__; // đệ quy bản thân ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
